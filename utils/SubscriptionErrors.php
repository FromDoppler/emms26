
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
        $reason = "";
        $errorCode = "";

        if (preg_match('/Reason: (.*?) \| errorCode= (\d+)/', $errorMessage, $matches)) {
            if (isset($matches[1])) {
                $reason = $matches[1];
            }
            if (isset($matches[2])) {
                $errorCode = $matches[2];
            }
        }

        return array(
            'reason' => ($reason) ? $reason : $errorMessage,
            'errorCode' => ($errorCode) ? $errorCode : 0
        );
    }
}
