<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');

function restrictOriginAccess()
{
    $ip = GeoIp::getIp();
    if (in_array($ip, ALLOW_IPS)) {
        header("Access-Control-Allow-Origin: " . SITE_URL);
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: Content-Type");
    } else {
        header("HTTP/1.1 403 Forbidden");
        echo json_encode(['status' => 'error', 'message' => 'Forbidden origin '. $ip]);
        exit();
    }
}
restrictOriginAccess();
?>
