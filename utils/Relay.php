<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/EmailTemplateManager.php');

class Relay
{
    private static $apiKey;
    private static $account;

    private const urlBase = 'https://api.dopplerrelay.com/accounts/';
    private const fromName = 'EMMS 2025';
    private const fromEmail = 'info@goemms.com';

    private static function executeCurl($url, $data, $headers, $method)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return curl_exec($ch);
    }

    public static function init($account, $apiKey)
    {
        self::$apiKey = $apiKey;
        self::$account = $account;
    }

    private static function ensureInitialized()
    {
        if (!self::$apiKey || !self::$account) {
            self::$account = defined('ACCOUNT_RELAY') ? ACCOUNT_RELAY : null;
            self::$apiKey = defined('API_KEY_RELAY') ? API_KEY_RELAY : null;

            if (!self::$apiKey || !self::$account) {
                throw new Exception("Relay API Key o Account no configurados.");
            }
        }
    }

    public static function sendEmail($toEmail, $subject, $html)
    {
        try {
            self::ensureInitialized();

            $data = [
                'from_name' => self::fromName,
                'from_email' => self::fromEmail,
                'reply_to' => ["email" => self::fromEmail],
                'recipients' => [['type' => 'to', 'email' => $toEmail]],
                'subject' => $subject,
                'html' => $html
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: token ' . self::$apiKey,
                'Content-Length: ' . strlen(json_encode($data)),
            ];

            $endpoint = self::urlBase . self::$account . "/messages";
            $response = json_decode(self::executeCurl($endpoint, json_encode($data), $headers, 'POST'));

            if (isset($response->errorCode)) {
                $errorDetails = is_array($response->errors) || is_object($response->errors)
                    ? json_encode($response->errors)
                    : ($response->errors ?? 'No details');

                if (strlen($errorDetails) > 250) {
                    $errorDetails = substr($errorDetails, 0, 250) . '...[truncated]';
                }

                Logger::error("Relay API error", ['email' => $toEmail, 'error_code' => $response->errorCode, 'errors' => $errorDetails], 'RELAY');
                throw new Exception("Relay API error: " . $errorDetails);
            }

        } catch (Exception $e) {
            Logger::error("Relay sending failed", [
                'email' => $toEmail,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'RELAY');
            throw $e;
        }
    }

}
