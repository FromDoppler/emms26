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
    header('Content-Type: application/json; charset=utf-8');
    if (isset($_POST)) {
        $data = file_get_contents("php://input");
        $type = json_decode($data, true);
        $type = $type['type'];
        $allies = getMediaPartnersByType($type);
        echo json_encode($allies);
    }
} catch (Exception $e) {
    processError("getMediaPartners", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}


function getMediaPartnersByType($type)
{

    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($type === 'exclusive') {
        $allies = $db->getSponsorsByType("PREMIUM");
        $db->close();
        return $allies;
    } else if ($type === 'starters') {
        $allies = $db->getSponsorsByType("STARTER");
        $db->close();
        return $allies;
    }

}
