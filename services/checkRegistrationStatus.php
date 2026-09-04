<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Validator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');


header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit();
}

$email = null;
try {
    $ip = GeoIp::getIp();
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);

    $input = json_decode(file_get_contents('php://input'), true);
    $email = (isset($input['email']) && is_string($input['email'])) ? trim($input['email']) : null;
    Validator::validateEmail($email);

    $currentEvent = getCurrentEvent();

    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $isRegistered = $db->isRegisteredForEvent($email, $currentEvent['freeId']);
    $db->close();

    echo json_encode(['registered' => $isRegistered]);
} catch (EmailValidationException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'code' => $e->getReason(),
        'suggestion' => $e->getSuggestion(),
        'message' => 'Invalid email address',
    ]);
    exit();
} catch (Exception $e) {
    processError("checkRegistrationStatus", $e->getMessage(), ['email' => $email, 'event' => $currentEvent['freeId'] ?? null]);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to check registration status']);
    exit();
}