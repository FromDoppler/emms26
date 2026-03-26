<?php
require_once(__DIR__ . '/../../../utils/Logger.php');

class RedisManager
{
    const STREAM_EMAIL       = 'stripe_jobs_email';
    const STREAM_SPREADSHEET = 'stripe_jobs_spreadsheet';
    const STREAM_LIST        = 'stripe_jobs_list';
    const GROUP_SUFFIX       = '_group';

    private static $instance = null;
    private $redis;
    private $initializedGroups = [];

    private function __construct()
    {
        $this->connect();
        $this->initializeAllGroups();
    }

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    public function connect()
    {
        $tries = 0; $max = 5;
        while ($tries < $max) {
            try {
                $this->redis = new Redis();
                $this->redis->connect(REDIS_HOST, REDIS_PORT);
                if (REDIS_PASSWORD) $this->redis->auth(REDIS_PASSWORD);
                $pong = $this->redis->ping();
                if ($pong === true || (is_string($pong) && stripos($pong,'PONG')!==false)) {
                    Logger::info('redis_connected', ['host'=>REDIS_HOST,'port'=>REDIS_PORT],'REDIS_MANAGER');
                    return;
                }
            } catch (Exception $e) {
                $tries++;
                Logger::warning('redis_connection_failed', ['attempt'=>$tries,'error'=>$e->getMessage()], 'REDIS_MANAGER');
                usleep($tries*200000);
            }
        }
        throw new Exception("Unable to connect to Redis");
    }

    public function isRedisAlive()
    {
        try {
            $pong = $this->redis->ping();
            return $pong === true || (is_string($pong) && stripos($pong,'PONG')!==false);
        } catch (Exception $e) {
            Logger::warning('redis_ping_failed', ['error'=>$e->getMessage()], 'REDIS_MANAGER');
            return false;
        }
    }

    public function reconnect()
    {
        try { if ($this->redis instanceof Redis) @$this->redis->close(); } catch (Exception $e) {}
        $this->connect();
    }

    private function initializeAllGroups()
    {
        foreach (self::getAllStreams() as $s) {
            $this->initializeGroup($s, self::getGroupName($s));
        }
        Logger::info('all_consumer_groups_initialized', [], 'REDIS_MANAGER');
    }

    public function initializeGroup($stream, $group)
    {
        $key = "$stream:$group";
        if (isset($this->initializedGroups[$key])) return true;

        try {
            $this->redis->xGroup('CREATE', $stream, $group, '0', true);
        } catch (Exception $e) {
            if (strpos($e->getMessage(),'BUSYGROUP') === false) {
                Logger::warning('consumer_group_init_warning', ['stream'=>$stream,'group'=>$group,'error'=>$e->getMessage()], 'REDIS_MANAGER');
            }
        }
        $this->initializedGroups[$key] = true;
        return true;
    }

    public function cleanupInactiveConsumers($stream, $group, $inactiveThresholdMs = 600000, $currentConsumer = null)
    {
        try {
            $consumers = $this->redis->rawCommand('XINFO', 'CONSUMERS', $stream, $group);
            if (!$consumers || !is_array($consumers)) return 0;

            $cleaned = 0;

            foreach ($consumers as $consumer) {
                if (!is_array($consumer)) continue;

                $consumerData = [];
                for ($i = 0; $i < count($consumer); $i += 2) {
                    if (isset($consumer[$i]) && isset($consumer[$i + 1])) {
                        $consumerData[$consumer[$i]] = $consumer[$i + 1];
                    }
                }

                $name = $consumerData['name'] ?? null;
                $pending = $consumerData['pending'] ?? 0;
                $idle = $consumerData['idle'] ?? 0;

                // Regla simple: borrar si pending=0, idle>10min y no es el consumer actual
                if ($name && $name !== $currentConsumer && $pending == 0 && $idle > $inactiveThresholdMs) {
                    try {
                        $this->redis->rawCommand('XGROUP', 'DELCONSUMER', $stream, $group, $name);
                        $cleaned++;
                        Logger::info('consumer_cleaned', [
                            'stream' => $stream,
                            'group' => $group,
                            'consumer' => $name,
                            'idle_ms' => $idle,
                            'pending' => $pending
                        ], 'REDIS_MANAGER');
                    } catch (Exception $e) {
                        Logger::warning('consumer_cleanup_failed', [
                            'stream' => $stream,
                            'group' => $group,
                            'consumer' => $name,
                            'error' => $e->getMessage()
                        ], 'REDIS_MANAGER');
                    }
                }
            }

            if ($cleaned > 0) {
                Logger::info('consumers_cleanup_completed', [
                    'stream' => $stream,
                    'group' => $group,
                    'cleaned_count' => $cleaned
                ], 'REDIS_MANAGER');
            }

            return $cleaned;
        } catch (Exception $e) {
            Logger::warning('consumers_cleanup_error', [
                'stream' => $stream,
                'group' => $group,
                'error' => $e->getMessage()
            ], 'REDIS_MANAGER');
            return 0;
        }
    }

    public function addToStream($stream, $jobId)
    {
        $maxlen = defined('REDIS_STREAM_MAXLEN') ? REDIS_STREAM_MAXLEN : REDIS_STREAM_DEFAULT_MAXLEN;
        return $this->redis->xAdd($stream, '*', ['job_id'=>$jobId,'ts'=>time()], $maxlen, true);
    }

    public function readFromStream($stream, $group, $consumer, $count = null, $timeout = null)
    {
        $count   = $count ?? REDIS_STREAM_DEFAULT_COUNT;
        $timeout = $timeout ?? REDIS_STREAM_DEFAULT_TIMEOUT;

        try {
            $raw = $this->redis->xReadGroup($group, $consumer, [$stream => '>'], $count, $timeout);
        } catch (Exception $e) {
            if (strpos($e->getMessage(),'NOGROUP') !== false) {
                Logger::warning('group_recreated_on_nogroup', [
                    'stream' => $stream,
                    'group' => $group
                ], 'REDIS_MANAGER');
                $this->initializeGroup($stream, $group);
                $raw = $this->redis->xReadGroup($group, $consumer, [$stream => '>'], $count, $timeout);
            } else {
                Logger::error('xreadgroup_failed', ['stream'=>$stream,'group'=>$group,'error'=>$e->getMessage()], 'REDIS_MANAGER');
                return [];
            }
        }

        $out = [];
        if (is_array($raw)) {
            foreach ($raw as $entries) {
                $norm = [];
                if (is_array($entries)) {
                    foreach ($entries as $e) {
                        if (isset($e[0], $e[1])) {
                            $id = $e[0]; $fields = $e[1]; $data = [];
                            if (is_array($fields)) {
                                $isAssoc = array_keys($fields) !== range(0, count($fields)-1);
                                if ($isAssoc) $data = $fields;
                                else for ($i=0;$i+1<count($fields);$i+=2) $data[$fields[$i]]=$fields[$i+1];
                            }
                            $norm[$id] = $data;
                        }
                    }
                }
                $out[] = $norm;
            }
        }
        return $out;
    }

    public function ackMessage($stream, $group, $messageId)
    {
        return $this->redis->xAck($stream, $group, [$messageId]);
    }

    public function xPendingRange($stream, $group, $start='-', $end='+', $count=50, $consumer=null)
    {
        try {
            return $this->redis->xPending($stream, $group, $start, $end, $count, $consumer);
        } catch (Exception $e) {
            Logger::error('xpending_range_failed', ['stream'=>$stream,'group'=>$group,'error'=>$e->getMessage()], 'REDIS_MANAGER');
            return [];
        }
    }

    public function xPending($stream, $group, $messageId)
    {
        try {
            $pending = $this->redis->xPending($stream, $group, $messageId, $messageId, 1);
            if (!empty($pending)) {
                $entry = $pending[0];
                return [
                    'message_id' => $entry[0],
                    'consumer'   => $entry[1],
                    'idle'       => $entry[2],
                    'count'      => $entry[3]
                ];
            }
            return [];
        } catch (Exception $e) {
            Logger::error('xpending_failed', ['stream'=>$stream,'group'=>$group,'message_id'=>$messageId,'error'=>$e->getMessage()], 'REDIS_MANAGER');
            return [];
        }
    }

    public function autoClaim($stream, $group, $consumer, $minIdle, $count=10)
    {
        try {
            $res = $this->redis->xAutoClaim($stream, $group, $consumer, $minIdle, '0-0', $count);
            $msgs = $res[1] ?? ($res['messages'] ?? []); $out = [];
            foreach ($msgs as $e) {
                if (isset($e[0], $e[1])) {
                    $id = $e[0]; $fields = $e[1]; $data = [];
                    if (is_array($fields)) {
                        $isAssoc = array_keys($fields) !== range(0, count($fields)-1);
                        if ($isAssoc) $data = $fields;
                        else for ($i=0;$i+1<count($fields);$i+=2) $data[$fields[$i]]=$fields[$i+1];
                    }
                    $out[$id] = $data;
                }
            }
            return $out;
        } catch (Exception $e) {
            Logger::error('xautoclaim_failed', ['stream'=>$stream,'group'=>$group,'error'=>$e->getMessage()], 'REDIS_MANAGER');
            return [];
        }
    }

    public function xClaim($stream, $group, $consumer, $minIdle, $ids)
    {
        try {
            return $this->redis->xClaim($stream, $group, $consumer, $minIdle, $ids);
        } catch (Exception $e) {
            Logger::error('xclaim_failed', [
                'stream'   => $stream,
                'group'    => $group,
                'error'    => $e->getMessage()
            ], 'REDIS_MANAGER');
            return [];
        }
    }

    public static function getGroupName($stream) { return $stream . self::GROUP_SUFFIX; }

    public static function getAllStreams()
    {
        return [ self::STREAM_EMAIL, self::STREAM_SPREADSHEET, self::STREAM_LIST ];
    }

    public function close()
    {
        if ($this->redis instanceof Redis) $this->redis->close();
        self::$instance = null;
    }
}
