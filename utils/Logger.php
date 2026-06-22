<?php

class Logger
{
    const ERROR = 'ERROR';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const DEBUG = 'DEBUG';
    const SUCCESS = 'SUCCESS';
    const DUPLICATE = 'DUPLICATE';

    private static $requestId = null;
    private static $logDir = null;

    private static function getRequestId()
    {
        if (self::$requestId === null) {
            self::$requestId = bin2hex(random_bytes(6));
        }
        return self::$requestId;
    }

    private static function getLogDir()
    {
        if (self::$logDir === null) {
            self::$logDir = __DIR__ . '/../logs';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }
        return self::$logDir;
    }

    private static function getLogFile($level)
    {
        return self::getLogDir() . '/' . strtolower($level) . '.log';
    }

    private static function normalizeLevel($level)
    {
        $allowed = [
            self::ERROR,
            self::WARNING,
            self::INFO,
            self::DEBUG,
            self::SUCCESS,
            self::DUPLICATE,
        ];

        return in_array($level, $allowed, true) ? $level : self::INFO;
    }

    private static function encodeJson($value, $level = null)
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json !== false) {
            return $json;
        }

        return json_encode([
            'timestamp' => date('c'),
            'service' => 'LOGGER',
            'level' => $level ?? self::ERROR,
            'message' => 'Could not encode log entry',
            'request_id' => self::getRequestId(),
            'logger_error' => 'json_encode_failed',
            'json_error' => json_last_error_msg(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function shouldLog($level)
    {
        if (defined('PRODUCTION') && PRODUCTION) {
            return in_array($level, [self::ERROR, self::WARNING, self::INFO, self::SUCCESS, self::DUPLICATE]);
        }
        return true;
    }

    private static function writeJson($level, $message, $context = [], $service = 'APP', $event = null)
    {
        $level = self::normalizeLevel($level);

        if (!self::shouldLog($level)) {
            return;
        }

        if (!is_array($context)) {
            $context = ['value' => $context];
        }

        $payload = [
            'timestamp' => date('c'),
            'service' => $service,
            'level' => $level,
            'message' => $message,
            'request_id' => self::getRequestId(),
        ];

        if ($event !== null) {
            $payload['event'] = $event;
        }

        if (!empty($context)) {
            $payload['context'] = $context;
        }

        $logFile = self::getLogFile($level);
        file_put_contents($logFile, self::encodeJson($payload, $level) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public static function log($level, $message, $context = [], $service = 'APP')
    {
        // Logger writes structured JSONL only; callers must provide safe context.
        self::writeJson($level, $message, $context, $service);
    }

    public static function error($message, $context = [], $service = 'APP')
    {
        self::log(self::ERROR, $message, $context, $service);
    }

    public static function info($message, $context = [], $service = 'APP')
    {
        self::log(self::INFO, $message, $context, $service);
    }

    public static function warning($message, $context = [], $service = 'APP')
    {
        self::log(self::WARNING, $message, $context, $service);
    }

    public static function debug($message, $context = [], $service = 'APP')
    {
        self::log(self::DEBUG, $message, $context, $service);
    }

    public static function success($message, $context = [], $service = 'APP')
    {
        self::log(self::SUCCESS, $message, $context, $service);
    }

    public static function duplicate($message, $context = [], $service = 'APP')
    {
        self::log(self::DUPLICATE, $message, $context, $service);
    }

    public static function event($event, array $context = [], $service = 'APP', $level = self::INFO)
    {
        self::writeJson($level, $event, $context, $service, $event);
    }

    public static function newRequest()
    {
        self::$requestId = null;
    }

    // Helper method for database operations with automatic logging
    public static function withDatabase($db, callable $callback, array $context = [], $service = 'DATABASE')
    {
        $action = $context['action'] ?? 'db_operation';

        self::debug("{$action}_started", $context, $service);

        try {
            $result = $callback($db);
            self::debug("{$action}_completed", $context, $service);
            return $result;
        } catch (Exception $e) {
            $errorContext = $context;
            $errorContext['error'] = $e->getMessage();
            self::error("{$action}_failed", $errorContext, $service);
            throw new Exception("Database operation failed: " . $e->getMessage(), 0, $e);
        }
    }
}
