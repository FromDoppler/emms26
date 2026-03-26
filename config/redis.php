<?php
if (!defined('USE_JOB_QUEUE')) define('USE_JOB_QUEUE', false);
#REDIS
$REDIS_HOST = getenv("REDIS_HOST") ?: "redis";
$REDIS_PORT = getenv("REDIS_PORT") ?: 6379;
$REDIS_PASSWORD = getenv("REDIS_PASSWORD") ?: null;

if (!defined('REDIS_HOST')) define('REDIS_HOST', $REDIS_HOST);
if (!defined('REDIS_PORT')) define('REDIS_PORT', $REDIS_PORT);
if (!defined('REDIS_PASSWORD')) define('REDIS_PASSWORD', $REDIS_PASSWORD);

#REDIS STREAMS DEFAULTS
if (!defined('REDIS_STREAM_DEFAULT_MAXLEN')) define('REDIS_STREAM_DEFAULT_MAXLEN', 1000); // 1000 messages
if (!defined('REDIS_STREAM_DEFAULT_TIMEOUT')) define('REDIS_STREAM_DEFAULT_TIMEOUT', 1000); // 1s
if (!defined('REDIS_STREAM_DEFAULT_COUNT')) define('REDIS_STREAM_DEFAULT_COUNT', 1); // 1 message
if (!defined('REDIS_STREAM_DEFAULT_MIN_IDLE')) define('REDIS_STREAM_DEFAULT_MIN_IDLE', 2000); // 2s

#REDIS STREAMS CONFIGURATION
$REDIS_STREAM_MAXLEN = getenv("REDIS_STREAM_MAXLEN") ?: 10000; // 10k messages
if (!defined('REDIS_STREAM_MAXLEN')) define('REDIS_STREAM_MAXLEN', $REDIS_STREAM_MAXLEN); // 10k messages

#WORKERS DEFAULTS
if (!defined('WORKERS_MAX_RETRIES')) define('WORKERS_MAX_RETRIES', 5); // 5 retries

if (!defined('WORKERS_CONFIG')) {
    define('WORKERS_CONFIG', [
        'email' => [
            'read_timeout' => 2000,    // 2s
            'backoff_base' => 60000,  // 1min
            'backoff_max'  => 120000  // 2min cap
        ],
        'list' => [
            'read_timeout' => 3000,   // 3s
            'backoff_base' => 60000,  // 1min
            'backoff_max'  => 120000  // 2min cap
        ],
        'spreadsheet' => [
            'read_timeout' => 3000,   // 3s
            'backoff_base' => 60000,  // 1min
            'backoff_max'  => 120000  // 2min cap
        ]
    ]);
}
