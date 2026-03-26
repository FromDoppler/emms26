<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');


try {
    $ip = GeoIp::getIp();
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $phases = $db->getCurrentPhase()[0];
    $db->close();
    $current_phase = array_search(1, $phases);
    echo json_encode($current_phase);
} catch (Exception $e) {
    processError("getPhase", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
