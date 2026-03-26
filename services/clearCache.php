<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');

try {
    $ip = GeoIp::getIp();
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);
    $m = new Memcached();
    $m->addServer('localhost', 11211);
    /* flush all items in 1 seconds */
    $m->flush(1);
    echo 'Cleared cache';
} catch (Exception $e) {
    processError("clearCache", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
