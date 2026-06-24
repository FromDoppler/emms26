<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');

class SubscriptionErrors
{
    private $db;

    public function __construct()
    {
        $this->db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    public function saveSubscriptionErrorsTable($email, $list, $reason, $errorCode)
    {
        try {
            // Convertir cadena vacía a null
            $errorCode = $errorCode !== '' ? $errorCode : null;
            $this->db->insertSubscriptionErrors($email, $list, $reason, $errorCode);
        } catch (Exception $e) {
            throw new Exception("saveSubscriptionErrorsTable: " . json_encode($e) . ' email ' . $email);
        }
    }

    public function saveSubscriptionErrors($email, $list, $errorMessage)
    {
        $parseErrorMessage = $this->parseErrorMessage($errorMessage);
        $this->saveSubscriptionErrorsTable($email, $list, $parseErrorMessage['reason'], $parseErrorMessage['errorCode']);
    }

    private function parseErrorMessage($errorMessage)
    {
        // "Doppler: Error <detail> | errorCode= <code>"
        if (preg_match('/Doppler: Error (.+?) \| errorCode= (\d+)/', $errorMessage, $matches)) {
            return ['reason' => $matches[1], 'errorCode' => $matches[2]];
        }

        // "Doppler: Error <key>-><detail>[; ...]" (from errors array, no errorCode)
        if (preg_match('/^Doppler: Error (.+->.+)$/', $errorMessage, $matches)) {
            return ['reason' => $matches[1], 'errorCode' => 0];
        }

        // "Doppler: cURL error <errno> - <error>"
        if (preg_match('/Doppler: cURL error (\d+) - (.+)/', $errorMessage, $matches)) {
            return ['reason' => 'cURL error: ' . $matches[2], 'errorCode' => $matches[1]];
        }

        // "Doppler: HTTP <status> | <detail>" (invalid response or HTTP >= 400 fallback)
        if (preg_match('/Doppler: HTTP (\d+) \| (.+)/', $errorMessage, $matches)) {
            return ['reason' => $matches[2], 'errorCode' => $matches[1]];
        }

        // Legacy: "Reason: <reason> | errorCode= <code>"
        if (preg_match('/Reason: (.+?) \| errorCode= (\d+)/', $errorMessage, $matches)) {
            return ['reason' => $matches[1], 'errorCode' => $matches[2]];
        }

        return ['reason' => $errorMessage, 'errorCode' => 0];
    }
}
