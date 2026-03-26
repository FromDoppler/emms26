<?php

// Fix for CLI/worker contexts where DOCUMENT_ROOT is not defined
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
}

// Evento actual
$currentEventKey = 'DIGITALTRENDS';

$ALLOW_IPS = array('::1', '200.5.229.58', '200.5.253.210', '127.0.0.1', '172.18.0.1', '172.22.0.1');
$ACCOUNT_DOPPLER = getenv("ACCOUNT_DOPPLER");
$API_KEY_DOPPLER = getenv("API_KEY_DOPPLER");

$ACCOUNT_RELAY = getenv("ACCOUNT_RELAY");
$API_KEY_RELAY = getenv("API_KEY_RELAY");
$GOOGLE_CLIENT_ID = getenv("GOOGLE_CLIENT_ID");
$GOOGLE_CLIENT_SECRET = getenv("GOOGLE_CLIENT_SECRET");
$ID_SPREADSHEET =  getenv("ID_SPREADSHEET");
$ID_SPREADSHEET_VIP =  getenv("ID_SPREADSHEET_VIP");
$ID_SPREADSHEET_DT_VIP =  getenv("ID_SPREADSHEET_DT_VIP");
$DB_NAME = getenv("MYSQL_DATABASE");
$DB_USER = getenv("MYSQL_USER");
$DB_PASSWORD = getenv("MYSQL_PASSWORD");
$DB_HOST = getenv("MYSQL_HOST");
//$SECRET_REFRESH = getenv("SECRET_REFRESH");

$STRIPE_PUBLIC_KEY = getenv("STRIPE_PUBLIC_KEY");
$STRIPE_URL_SERVER = getenv("STRIPE_URL_SERVER");

if (!defined('STRIPE_PUBLIC_KEY')) define('STRIPE_PUBLIC_KEY', $STRIPE_PUBLIC_KEY);
if (!defined('STRIPE_URL_SERVER')) define('STRIPE_URL_SERVER', $STRIPE_URL_SERVER);

#ADMIN
$ADMIN_RESTRICTED_SERVERS = json_decode(getenv("ADMIN_RESTRICTED_SERVERS"));
$ADMIN_ALLOW_IPS = json_decode(getenv("ADMIN_ALLOW_IPS"));

if (!defined('VERSION')) define('VERSION', '1.0.0');
if (!defined('PRODUCTION')) define('PRODUCTION', false);
if (!defined('SECURITYHELPER_ENABLE')) define('SECURITYHELPER_ENABLE', false);
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/');
if (!defined('ENABLE_DIGITALTRENDS_SPONSORS')) define('ENABLE_DIGITALTRENDS_SPONSORS', true);

if (!defined('CACHE_TIME')) define('CACHE_TIME', 60); // En segundos(60) (1 minuto)
if (!defined('CACHE_TIME_ID')) define('CACHE_TIME_ID', 1800); // En segundos(1800) (30 minutos)
if (!defined('CACHE_BACKUP_TIME')) define('CACHE_BACKUP_TIME', 3600); // En segundos (1 Hora)

# LOAD EVENT ROUTES CONFIGURATION
$routesConfig = include_once($_SERVER['DOCUMENT_ROOT'] . '/config/event-routes.php');
$events = $routesConfig['events'];
$sharedPages = $routesConfig['sharedPages'];

if (!array_key_exists($currentEventKey, $events)) {
    throw new Exception("Evento '$currentEventKey' no definido.");
}

$currentEventData = $events[$currentEventKey];
$currentEventData['redirects'] = getRedirectsForEvent($currentEventData, $sharedPages);
$currentEventData['sharedPages'] = $sharedPages;

// Definiciones para compatibilidad
if (!defined('ECOMMERCE')) define('ECOMMERCE', $events['ECOMMERCE']['freeId']);
if (!defined('DIGITALTRENDS')) define('DIGITALTRENDS', $events['DIGITALTRENDS']['freeId']);
if (!defined('ECOMMERCEVIP')) define('ECOMMERCEVIP', $events['ECOMMERCE']['vipId']);
if (!defined('DIGITALTRENDSVIP')) define('DIGITALTRENDSVIP', $events['DIGITALTRENDS']['vipId']);
// Variable global para el servicio
$GLOBALS['CURRENT_EVENT_DATA'] = $currentEventData;


#GTM (Google Tag Manager) Containers
if (!defined('GTM_IDS')) define('GTM_IDS', [
    'dhtrack' => 'GTM-M768WZR',
    'hotjar'  => 'GTM-TMMV2DF'
]);

#IPS WHITE LIST

if (!defined('ALLOW_IPS')) define('ALLOW_IPS', $ALLOW_IPS);

#API DOPPLER

if (!defined('ACCOUNT_DOPPLER')) define('ACCOUNT_DOPPLER', $ACCOUNT_DOPPLER);
if (!defined('API_KEY_DOPPLER')) define('API_KEY_DOPPLER', $API_KEY_DOPPLER);
if (!defined('LIST_LANDING_ECOMMERCE')) define('LIST_LANDING_ECOMMERCE', 29037684);//QA
if (!defined('LIST_LANDING_ECOMMERCE_VIP')) define('LIST_LANDING_ECOMMERCE_VIP', 29055123);//QA
if (!defined('LIST_LANDING_DIGITALT')) define('LIST_LANDING_DIGITALT', 29116798); //QA
if (!defined('LIST_LANDING_DIGITALT_VIP')) define('LIST_LANDING_DIGITALT_VIP', 28979585);
if (!defined('LIST_LANDING_DIGITALT_WIX')) define('LIST_LANDING_DIGITALT_WIX', 28789885);

#SPONSORS LIST
if (!defined('LIST_SPONSORS')) define('LIST_SPONSORS', 28845743);
if (!defined('LIST_MEDIA_PARTNERS')) define('LIST_MEDIA_PARTNERS', 28876952);
if (!defined('EMAIL_SPONSORS')) define('EMAIL_SPONSORS', 'lbsales@makingsense.com');
if (!defined('EMAIL_PARTNERS')) define('EMAIL_PARTNERS', 'partners@fromdoppler.com');

#API RELAY

if (!defined('ACCOUNT_RELAY')) define('ACCOUNT_RELAY', $ACCOUNT_RELAY);
if (!defined('API_KEY_RELAY')) define('API_KEY_RELAY', $API_KEY_RELAY);

#EMAIL SUBJECT
## Ecommerce
// --- FREE ---
if (!defined('SUBJECT_FREE_PRE_ECOMMERCE'))
    define('SUBJECT_FREE_PRE_ECOMMERCE', html_entity_decode('&#x1F389;', ENT_QUOTES, 'UTF-8') . ' Tienes tu lugar en el EMMS E-commerce 2025');

if (!defined('SUBJECT_FREE_DURING_ECOMMERCE'))
    define('SUBJECT_FREE_DURING_ECOMMERCE', html_entity_decode('&#x1F389;', ENT_QUOTES, 'UTF-8') . ' Ya eres parte del EMMS E-commerce 2025');

if (!defined('SUBJECT_FREE_POST_ECOMMERCE'))
    define('SUBJECT_FREE_POST_ECOMMERCE', 'Revive las mejores estrategias del EMMS E-commerce 2025 ' . html_entity_decode('&#x1F4A1;', ENT_QUOTES, 'UTF-8'));

// --- VIP ---
if (!defined('SUBJECT_VIP_PRE_ECOMMERCE'))
    define('SUBJECT_VIP_PRE_ECOMMERCE', html_entity_decode('&#x1F39F;', ENT_QUOTES, 'UTF-8') . ' Tu acceso VIP al EMMS E-commerce 2025');

if (!defined('SUBJECT_VIP_DURING_ECOMMERCE'))
    define('SUBJECT_VIP_DURING_ECOMMERCE', html_entity_decode('&#x1F39F;', ENT_QUOTES, 'UTF-8') . ' Ya eres VIP en el EMMS E-commerce 2025');

if (!defined('SUBJECT_VIP_POST_ECOMMERCE'))
    define('SUBJECT_VIP_POST_ECOMMERCE', html_entity_decode('&#x1F39F;', ENT_QUOTES, 'UTF-8') . ' Disfruta de lo que fue el EMMS E-commerce 2025');

## DT
// --- FREE ---
if (!defined('SUBJECT_FREE_PRE_DIGITALT'))
    define('SUBJECT_FREE_PRE_DIGITALT', html_entity_decode('&#x1F389;', ENT_QUOTES, 'UTF-8') . ' Tienes tu lugar en el EMMS Digital Trends 2025');

if (!defined('SUBJECT_FREE_DURING_DIGITALT'))
    define('SUBJECT_FREE_DURING_DIGITALT', html_entity_decode('&#x1F389;', ENT_QUOTES, 'UTF-8') . ' Ya eres parte del EMMS Digital Trends 2025');

if (!defined('SUBJECT_FREE_POST_DIGITALT'))
    define('SUBJECT_FREE_POST_DIGITALT', html_entity_decode('&#x1F389;', ENT_QUOTES, 'UTF-8') . ' ¡Te damos la bienvenida al EMMS!');

// --- VIP ---
if (!defined('SUBJECT_VIP_PRE_DIGITALT'))
    define('SUBJECT_VIP_PRE_DIGITALT', html_entity_decode('&#x1F39F;', ENT_QUOTES, 'UTF-8') . ' Tu entrada VIP al EMMS Digital Trends');

if (!defined('SUBJECT_VIP_DURING_DIGITALT'))
    define('SUBJECT_VIP_DURING_DIGITALT', html_entity_decode('&#x1F39F;', ENT_QUOTES, 'UTF-8') . ' Ya eres VIP en el EMMS Digital Trends 2025');

if (!defined('SUBJECT_VIP_POST_DIGITALT'))
    define('SUBJECT_VIP_POST_DIGITALT', html_entity_decode('&#x1F31F;', ENT_QUOTES, 'UTF-8') . ' Eres parte del EMMS VIP: vive la experiencia completa');

#GOOGLE SPREADSHEET
//https://docs.google.com/spreadsheets/d/1irsIKBdRzGlmeGpUlJjFcSJFaYZLN9ujvY-cTYpyeM8/edit#gid=0

if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', $GOOGLE_CLIENT_ID);
if (!defined('GOOGLE_CLIENT_SECRET')) define('GOOGLE_CLIENT_SECRET', $GOOGLE_CLIENT_SECRET);
if (!defined('ID_SPREADSHEET')) define('ID_SPREADSHEET', $ID_SPREADSHEET);
if (!defined('ID_SPREADSHEET_VIP')) define('ID_SPREADSHEET_VIP', $ID_SPREADSHEET_VIP);
if (!defined('ID_SPREADSHEET_DT_VIP')) define('ID_SPREADSHEET_DT_VIP', $ID_SPREADSHEET_DT_VIP);
if (!defined('GOOGLE_SPREAD_CALLBACK')) define('GOOGLE_SPREAD_CALLBACK', 'http://localhost/utils/spread/callback.php');

#DATABASE

if (!defined('DB_NAME')) define('DB_NAME', $DB_NAME);
if (!defined('DB_USER')) define('DB_USER', $DB_USER);
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', $DB_PASSWORD);
if (!defined('DB_HOST')) define('DB_HOST', $DB_HOST);

#ADMIN
if (!defined('ADMIN_RESTRICTED_SERVERS')) define('ADMIN_RESTRICTED_SERVERS', $ADMIN_RESTRICTED_SERVERS);
if (!defined('ADMIN_ALLOW_IPS')) define('ADMIN_ALLOW_IPS', $ADMIN_ALLOW_IPS);

#SERVER NODE SOCKET

if (!defined('URL_REFRESH')) define('URL_REFRESH', 'apisqa.fromdoppler.net');
if (!defined('PATH_REFRESH')) define('PATH_REFRESH', 'emms-socket');
//if (!defined('SECRET_REFRESH')) define('SECRET_REFRESH', $SECRET_REFRESH);

#MEMCACHED
if (!defined('MEMCACHED_SERVER')) define('MEMCACHED_SERVER', "memcached");

#During Days System
$dayDuring = 1;
if (!defined('DAY_DURING')) define('DAY_DURING', $dayDuring);
$duringDaysArray = array(
    "1" => array(
        "youtube" => "rhrLoHn3qmI",
        "twitch" => "fromdoppler"
    ),
    "2" => array(
        "youtube" => "3znU96iSNO4",
        "twitch" => "fromdoppler"
    ),
    "3" => array(
        "youtube" => "LjjXLpU_Kmg",
        "twitch" => "fromdoppler"
    )
);

require_once (__DIR__ . '/config/redis.php');
