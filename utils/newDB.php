<?php
class DB
{

    private $db;


    public function __construct($dbhost, $dbuser, $dbpass, $dbname, $charset = 'utf8')
    {
        try {
            //Data Source Name
            $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=$charset";
            $this->db = new PDO($dsn, $dbuser, $dbpass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            http_response_code(500);
            throw new Exception("Internal Server Error");
        }
    }

    public function getSponsorsByType($type)
    {
        try {
            $query = $this->db->prepare("SELECT * FROM sponsors WHERE status = '1' AND sponsor_type = ? ORDER BY priority_home");
            $query->execute(array($type));
            $sponsors = $query->fetchAll(PDO::FETCH_OBJ);
            return $sponsors;
        } catch (PDOException $e) {
            error_log("Error in getSponsorsByType: " . $e->getMessage());
            http_response_code(500);
            throw new Exception("Internal Server Error");
        }
    }
}
