<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

class RegisteredDatabase
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insertRegistered($registeredData)
    {
        $email = $registeredData['email'] ?? 'unknown';

        $query = "INSERT INTO registered (register, phase, email, firstname, lastname, country, phone, company, jobPosition, ecommerce, `ecommerce-vip`, `digital-trends`, `digital-trends-vip`, source_utm, medium_utm, campaign_utm, content_utm, term_utm)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $registeredData['register'],
            $registeredData['phase'],
            $registeredData['email'],
            $registeredData['firstname'],
            $registeredData['lastname'],
            $registeredData['country'],
            $registeredData['phone'],
            $registeredData['company'],
            $registeredData['jobPosition'],
            $registeredData['ecommerce'],
            $registeredData['ecommerce-vip'],
            $registeredData['digital-trends'],
            $registeredData['digital-trends-vip'],
            $registeredData['source_utm'],
            $registeredData['medium_utm'],
            $registeredData['campaign_utm'],
            $registeredData['content_utm'],
            $registeredData['term_utm']
        ];

        return Logger::withDatabase($this->db, function($db) use ($query, $params) {
            $db->query($query, $params);
            return true; // If no exception, insertion was successful
        }, [
            'email' => $email,
            'table' => 'registered',
            'action' => 'insert_registered'
        ], 'REGISTER');
    }

    public function updateEcommerceVIPByEmail($email)
    {
        $query = "UPDATE registered SET `ecommerce-vip` = 1 WHERE email = ?";

        return Logger::withDatabase($this->db, function($db) use ($query, $email) {
            $db->query($query, [$email]);
            return true; // If no exception, update was successful
        }, [
            'email' => $email,
            'table' => 'registered',
            'action' => 'update_ecommerce_vip'
        ], 'STRIPE');
    }

    public function updateDTVIPByEmail($email)
    {
        $query = "UPDATE registered SET `digital-trends-vip` = 1 WHERE email = ?";

        return Logger::withDatabase($this->db, function($db) use ($query, $email) {
            $db->query($query, [$email]);
            return true; // If no exception, update was successful
        }, [
            'email' => $email,
            'table' => 'registered',
            'action' => 'update_dt_vip'
        ], 'STRIPE');
    }

    public function getRegisteredByEmail($email)
    {
        $query = "SELECT * FROM registered WHERE email = ?";

        return Logger::withDatabase($this->db, function($db) use ($query, $email) {
            $result = $db->query($query, [$email]);
            $users = $result->fetchAll();
            return !empty($users) ? $users[0] : null;
        }, [
            'email' => $email,
            'table' => 'registered',
            'action' => 'get_registered_by_email'
        ], 'STRIPE');
    }


    public function insertAutomatedRegistered($registeredData)
    {
        $email = $registeredData['customer_email'] ?? 'unknown';
        $ecommerceValue = ($registeredData['event_name'] === 'ecommerce') ? 1 : 0;
        $DTValue = ($registeredData['event_name'] === 'digital-trends') ? 1 : 0;
        $currentDate = date('Y-m-d h:i:s A');

        $query = "INSERT INTO registered (register, phase, email, firstname, lastname, country, phone, company, jobPosition, ecommerce, `ecommerce-vip`, `digital-trends`, `digital-trends-vip`, source_utm, medium_utm, campaign_utm, content_utm, term_utm)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $currentDate,
            $registeredData['event_phase'] ?? '',
            $registeredData['customer_email'] ?? '',
            $registeredData['customer_name'] ?? '',
            '', // lastname
            $registeredData['customer_country'] ?? '',
            '', // phone
            '', // company
            '', // jobPosition
            $ecommerceValue,         // ecommerce
            $ecommerceValue,         // ecommerce-vip
            $DTValue,                // digital-trends
            $DTValue,                // digital-trends-vip
            $registeredData['utm_source'] ?? '',
            $registeredData['utm_medium'] ?? '',
            $registeredData['utm_campaign'] ?? '',
            $registeredData['utm_content'] ?? '',
            $registeredData['utm_term'] ?? ''
        ];

        return Logger::withDatabase($this->db, function($db) use ($query, $params) {
            $db->query($query, $params);
            return $db->lastInsertID(); // Return the inserted ID
        }, [
            'email' => $email,
            'table' => 'registered',
            'action' => 'insert_automated_registered'
        ], 'STRIPE');
    }
}
