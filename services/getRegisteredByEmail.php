<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/stripe/StripeCustomersModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');


try {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $ip = GeoIp::getIp();
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);
    $stripe = new StripeCustomersModel();
    $user = $stripe->getRegisteredByEmail($_POST['email']);
    echo json_encode($user);
} catch (Exception $e) {
    processError("getPhase", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
