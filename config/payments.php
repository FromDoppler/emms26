<?php

if (!defined('DOPPLER_PAYMENTS_API_URL')) {
    define('DOPPLER_PAYMENTS_API_URL', rtrim((string) (getenv('DOPPLER_PAYMENTS_API_URL') ?: ''), '/'));
}

if (!defined('DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN')) {
    define(
        'DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN',
        (string) (getenv('DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN') ?: '')
    );
}

if (!defined('DOPPLER_PAYMENTS_API_AUTH_MODE')) {
    $defaultAuthMode = 'jwt_superuser';
    define('DOPPLER_PAYMENTS_API_AUTH_MODE', (string) (getenv('DOPPLER_PAYMENTS_API_AUTH_MODE') ?: $defaultAuthMode));
}

if (!defined('DOPPLER_PAYMENTS_API_PRIVATE_KEY')) {
    define('DOPPLER_PAYMENTS_API_PRIVATE_KEY', (string) (getenv('DOPPLER_PAYMENTS_API_PRIVATE_KEY') ?: ''));
}

if (!defined('DOPPLER_PAYMENTS_API_JWT_AUDIENCE')) {
    define('DOPPLER_PAYMENTS_API_JWT_AUDIENCE', (string) (getenv('DOPPLER_PAYMENTS_API_JWT_AUDIENCE') ?: 'doppler-payments-api'));
}

if (!defined('DOPPLER_PAYMENTS_API_JWT_ISSUER')) {
    define('DOPPLER_PAYMENTS_API_JWT_ISSUER', (string) (getenv('DOPPLER_PAYMENTS_API_JWT_ISSUER') ?: ''));
}

if (!defined('EPROTECT_SCRIPT_URL')) {
    define('EPROTECT_SCRIPT_URL', (string) (getenv('EPROTECT_SCRIPT_URL') ?: ''));
}

if (!defined('EPROTECT_PAYPAGE_ID')) {
    define('EPROTECT_PAYPAGE_ID', (string) (getenv('EPROTECT_PAYPAGE_ID') ?: ''));
}

if (!defined('EPROTECT_REPORT_GROUP')) {
    define('EPROTECT_REPORT_GROUP', (string) (getenv('EPROTECT_REPORT_GROUP') ?: ''));
}

if (!defined('EPROTECT_STYLE')) {
    define('EPROTECT_STYLE', (string) (getenv('EPROTECT_STYLE') ?: ''));
}

if (!defined('EPROTECT_TIMEOUT_MS')) {
    define('EPROTECT_TIMEOUT_MS', (int) (getenv('EPROTECT_TIMEOUT_MS') ?: 5000));
}
