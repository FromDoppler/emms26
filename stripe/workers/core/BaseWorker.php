<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../../utils/DB.php');
require_once(__DIR__ . '/../../../utils/Logger.php');
require_once(__DIR__ . '/../../models/StripeCustomersJobsDatabase.php');
require_once(__DIR__ . '/RedisManager.php');

abstract class BaseWorker
{
    protected $db;
    protected $jobsModel;
    protected $redisManager;
    protected $stream;
    protected $group;
    protected $consumer;
    protected $running = true;
    private $lastDbPingAt = 0;
    protected $serviceName;

    public function __construct($stream, $group = null, $consumer = null)
    {
        $this->stream   = $stream;
        $this->group    = $group ?: RedisManager::getGroupName($stream);

        if ($consumer) {
            $this->consumer = $consumer;
        } elseif ($this->serviceName) {
            $randomId = bin2hex(random_bytes(4));
            $this->consumer = $this->serviceName . '-' . $randomId;
        } else {
            $this->consumer = 'worker_' . bin2hex(random_bytes(4));
        }

        $this->db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 'utf8mb4');
        $this->jobsModel    = new StripeCustomersJobsDatabase($this->db);
        $this->redisManager = RedisManager::getInstance();

        $this->redisManager->initializeGroup($this->stream, $this->group);

        // Limpiar consumers inactivos (pending=0 y idle>10min), excepto el actual
        $cleaned = $this->redisManager->cleanupInactiveConsumers(
            $this->stream,
            $this->group,
            600000,
            $this->consumer
        );

        Logger::info('worker_started', [
            'stream' => $this->stream,
            'consumer' => $this->consumer,
            'service' => $this->serviceName,
            'cleaned_consumers' => $cleaned
        ], $this->getWorkerName());

        Logger::info('consumer_ready', [
            'stream' => $this->stream,
            'group' => $this->group,
            'consumer' => $this->consumer,
            'service' => $this->serviceName
        ], $this->getWorkerName());
    }

    public function run()
    {
        while ($this->running) {
            try {
                $this->ensureDb();

                $messages = $this->redisManager->readFromStream(
                    $this->stream, $this->group, $this->consumer,
                    REDIS_STREAM_DEFAULT_COUNT, $this->getReadTimeout()
                );
                foreach ($messages as $chunk) {
                    foreach ($chunk as $id => $data) $this->processMessage($id, $data);
                }

                $this->retryOwnPending();

                $this->retryOrphans();

            } catch (Exception $e) {
                Logger::error('worker_error', ['error'=>$e->getMessage(),'stream'=>$this->stream], $this->getWorkerName());
                $this->reconnectDb();
                sleep(2);
            }
        }

        $this->redisManager->close();
        Logger::info('worker_stopped', ['stream'=>$this->stream], $this->getWorkerName());
    }

    public function shutdown()
    {
        $this->db->close();
    }

    private function ensureDb()
    {
        $now = microtime(true);
        if ($now - $this->lastDbPingAt > 300) {
            try {
                $this->db->query('SELECT 1');
                $this->lastDbPingAt = $now;
            } catch (Exception $e) {
                Logger::warning('db_ping_failed_reconnecting', ['stream'=>$this->stream,'error'=>$e->getMessage()], $this->getWorkerName());
                $this->reconnectDb();
            }
        }
    }

    private function reconnectDb()
    {
        try { if (method_exists($this->db, 'close')) { $this->db->close(); } } catch (Exception $e) {}
        $this->db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 'utf8mb4');
        $this->jobsModel = new StripeCustomersJobsDatabase($this->db);
        $this->lastDbPingAt = microtime(true);
        Logger::info('db_reconnected', ['stream'=>$this->stream], $this->getWorkerName());
    }

    private function withDbRetry(callable $fn) {
        try {
            return $fn();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '2006') !== false || strpos($msg, '2013') !== false || stripos($msg, 'gone away') !== false) {
                $this->reconnectDb();
                return $fn();
            }
            throw $e;
        }
    }

    private function retryOwnPending()
    {
        $rows = $this->redisManager->xPendingRange($this->stream, $this->group, '-', '+', 50, $this->consumer);
        if (empty($rows)) return;

        foreach ($rows as $row) {
            if (!is_array($row) || count($row) < 4) continue;
            [$messageId, $owner, $idle, $deliveries] = $row;

            $backoff = 0;
            if ($deliveries > 1) {
                $backoff = $this->computeBackoffMs($deliveries);
                if ($idle < $backoff) continue;
            }

            $claimed = $this->redisManager->xClaim($this->stream, $this->group, $this->consumer, 0, [$messageId]);
            if (empty($claimed)) continue;
            $mid  = array_key_first($claimed);
            $data = $claimed[$mid];
            $jobId = $data['job_id'] ?? null;
            if (!$jobId) { $this->redisManager->ackMessage($this->stream, $this->group, $mid); continue; }

            $job = $this->withDbRetry(fn() => $this->jobsModel->getJobById($jobId));
            if (!$job) { $this->redisManager->ackMessage($this->stream, $this->group, $mid); continue; }

            $retryCount = max(1, (int)$deliveries);
            $logContext = [
                'message_id' => $mid,
                'job_id'=>$jobId,'service'=>$this->serviceName,
                'stream' => $this->stream,
                'consumer' => $this->consumer,
                'retry'=>$retryCount,'idle_ms'=>$idle,'backoff'=>$backoff
            ];

            if ($retryCount > 1) {
                Logger::warning('retry_attempt', $logContext, $this->getWorkerName());
            } else {
                Logger::info('processing_pending', $logContext, $this->getWorkerName());
            }

            $this->processMessage($mid, $data);
        }
    }

    private function retryOrphans()
    {
        $minIdle = max(REDIS_STREAM_DEFAULT_MIN_IDLE, $this->getBackoffBase()); // conservador: max(MIN_IDLE, backoff_base)
        $claimed = $this->redisManager->autoClaim($this->stream, $this->group, $this->consumer, $minIdle, 10);
        foreach ($claimed as $mid => $data) {
            $jobId = $data['job_id'] ?? null;
            if (!$jobId) { $this->redisManager->ackMessage($this->stream, $this->group, $mid); continue; }
            $job = $this->withDbRetry(fn() => $this->jobsModel->getJobById($jobId));
            if (!$job) { $this->redisManager->ackMessage($this->stream, $this->group, $mid); continue; }
            $this->processMessage($mid, $data);
        }

    }

    private function processMessage($messageId, $data)
    {
        $jobId = $data['job_id'] ?? null;
        $ack   = false;

        try {
            if (!$jobId) {
                Logger::warning("message_skipped_no_job_id", [
                    'stream' => $this->stream,
                    'message_id' => $messageId
                ], $this->getWorkerName());
                $ack = true;
                return;
            }

            $job = $this->withDbRetry(fn() => $this->jobsModel->getJobById($jobId));
            if (!$job) {
                Logger::warning("job_not_found_for_message", [
                    'stream' => $this->stream,
                    'message_id' => $messageId,
                    'job_id' => $jobId
                ], $this->getWorkerName());
                $ack = true;
                return;
            }

            if ($this->isJobProcessed($job)) {
                Logger::info("job_already_processed", [
                    'job_id' => $jobId,
                    'service' => $this->serviceName
                ], $this->getWorkerName());
                $ack = true;
                return;
            }

            $t0 = microtime(true);
            $ok = $this->processJob($job);
            $elapsedMs = (int)round((microtime(true) - $t0) * 1000);
            if ($elapsedMs > 5000) {
                Logger::warning('job_slow', [
                    'job_id' => $job['id'],
                    'service' => $this->serviceName,
                    'elapsed_ms' => $elapsedMs
                ], $this->getWorkerName());
            }

            if ($ok) {
                $this->withDbRetry(fn() => $this->markJobAsProcessed($job['id']));

                Logger::info('job_success', [
                    'message_id' => $messageId,
                    'job_id'     => $jobId,
                    'service'    => $this->serviceName,
                    'stream'     => $this->stream,
                    'consumer'   => $this->consumer,
                    'elapsed_ms' => $elapsedMs
                ], $this->getWorkerName());

                $ack = true;
            } else {
                $this->onFailure($messageId, $jobId);
            }
        } catch (Exception $e) {
            $this->onFailure($messageId, $jobId, $e->getMessage());
        } finally {
            if ($ack) $this->redisManager->ackMessage($this->stream, $this->group, $messageId);
        }
    }

    private function onFailure($messageId, $jobId, $err = null)
    {
        $info = $this->redisManager->xPending($this->stream, $this->group, $messageId);
        $retry = $info['count'] ?? 1;
        $max = defined('WORKERS_MAX_RETRIES') ? WORKERS_MAX_RETRIES : 5;

        Logger::error('job_failed', [
            'message_id' => $messageId,
            'job_id'=>$jobId,'service'=>$this->serviceName,
            'retry'=>$retry,'max_retries'=>$max,'error'=>$err ?: 'unknown'
        ], $this->getWorkerName());

        if ($retry >= $max) {
            Logger::error('job_failed_permanent', [
                'message_id' => $messageId,
                'job_id' => $jobId,
                'service' => $this->serviceName,
                'retries' => $retry,
                'last_error' => $err ?: 'unknown'
            ], $this->getWorkerName());
            $this->redisManager->ackMessage($this->stream, $this->group, $messageId);
        }
    }

    protected function getWorkerConfig()
    {
        $service = $this->serviceName;
        return WORKERS_CONFIG[$service] ?? ['read_timeout'=>1000, 'backoff_base'=>30000, 'backoff_max'=>120000];
    }

    protected function getReadTimeout()  { return (int)$this->getWorkerConfig()['read_timeout']; }
    protected function getBackoffBase()  { return (int)$this->getWorkerConfig()['backoff_base']; }

    protected function computeBackoffMs($deliveries)
    {
        $cfg = $this->getWorkerConfig();
        $base = (int)$cfg['backoff_base'];
        $max  = (int)$cfg['backoff_max'];
        $r = max(1, (int)$deliveries);
        return min($base * (2 ** ($r - 1)), $max);
    }

    abstract protected function processJob($job);
    abstract protected function isJobProcessed($job);
    abstract protected function markJobAsProcessed($jobId);
    abstract protected function getWorkerName();
}
