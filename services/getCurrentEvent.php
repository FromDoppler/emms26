<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');

function getCurrentEvent() {
    try {
        if (!isset($GLOBALS['CURRENT_EVENT_DATA'])) {
            throw new Exception('CURRENT_EVENT_DATA not defined');
        }
        return $GLOBALS['CURRENT_EVENT_DATA'];
    } catch (Exception $e) {
        processError("getCurrentEvent", $e->getMessage(), []);
        return null;
    }
}

// Endpoint JSON para frontend - solo si se accede directamente
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'getCurrentEvent.php') {
    try {
        $ip = GeoIp::getIp();
        SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
        SecurityHelper::isSubmitValid(ALLOW_IPS);

        $eventData = getCurrentEvent();
        if ($eventData === null) {
            throw new Exception('Failed to get current event data');
        }

        header('Content-Type: application/json');
        echo json_encode($eventData);
    } catch (Exception $e) {
        processError("getCurrentEvent", $e->getMessage(), []);
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    }
    exit;
}
?>
