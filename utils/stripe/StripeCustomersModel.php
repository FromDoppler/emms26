<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
class StripeCustomersModel
{

    private $db;

    public function __construct($dbhost = DB_HOST, $dbuser = DB_USER, $dbpass = DB_PASSWORD, $dbname = DB_NAME, $charset = 'utf8')
    {
        try {
            //Data Source Name
            $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=$charset";
            $this->db = new PDO($dsn, $dbuser, $dbpass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log error
            error_log("Connection failed: " . $e->getMessage());
            http_response_code(500);
            throw new Exception("Internal Server Error");
        }
    }

    public function getRegisteredByEmail($email)
    {
        try {
            $query = $this->db->prepare("SELECT * FROM registered WHERE email = ?");
            $query->execute(array($email));
            $user = $query->fetch(PDO::FETCH_OBJ);
            return $user;
        } catch (PDOException $e) {
            // Log error
            error_log("Error in getRegisteredByEmail: " . $e->getMessage());
            http_response_code(500);
            throw new Exception("Internal Server Error");
        }
    }
}
