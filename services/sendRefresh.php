<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');


try {
    $ip = GeoIp::getIp();
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);
    $_POST = json_decode(file_get_contents('php://input'), true);
    if (!isset($_POST['msg']) || ($_POST['msg'] != 'refresh')) {
        exit("bad request");
    }
    $headers = array(
        'Content-Type: application/json'
    );
    $ch = curl_init("https://".URL_REFRESH."/".PATH_REFRESH."/".SECRET_REFRESH);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_exec($ch);
} catch (Exception $e) {
    processError("sendRefresh", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
