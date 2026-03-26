<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');
function sendEmail($user, $subject)
{
    // Delegate to EmailService without duplicate logging
    // EmailService handles all logging internally
    try {
        EmailService::sendEmailRegister($user, $subject);
    } catch (Exception $e) {
        // Re-throw with consistent error message
        throw new Exception("Email sending failed: " . $e->getMessage());
    }
}
