<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

class StripeCustomersDatabase
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insertCustomer($customerData)
    {
        $email = $customerData['customer_email'] ?? 'unknown';

        $query = "INSERT INTO stripe_customers (session_id, price, discount, final_price, customer_name, customer_email, customer_country, customer_tax, payment_status, coupon_id, coupon_name, event_name, event_phase, ticket_name, ticket_price_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $customerData['session_id'] ?? '',
            (float)($customerData['price'] ?? $customerData['final_price'] ?? 0),
            (float)($customerData['discount'] ?? 0),
            (float)($customerData['final_price'] ?? 0),
            $customerData['customer_name'] ?? '',
            $customerData['customer_email'] ?? '',
            $customerData['customer_country'] ?? '',
            $customerData['tax_id'] ?? '',
            $customerData['payment_status'] ?? '',
            $customerData['coupon_id'] ?? '',
            $customerData['coupon_name'] ?? '',
            $customerData['event_name'] ?? '',
            $customerData['event_phase'] ?? '',
            $customerData['ticket_name'] ?? '',
            $customerData['ticket_price_id'] ?? ''
        ];

        return Logger::withDatabase($this->db, function($db) use ($query, $params) {
            $db->query($query, $params);
            return true; // If no exception, insertion was successful
        }, [
            'email' => $email,
            'table' => 'stripe_customers',
            'action' => 'insert_customer'
        ], 'STRIPE');
    }

    public function getCustomerByEmail($email)
    {
        $query = "SELECT * FROM stripe_customers WHERE customer_email = ? LIMIT 1";

        return Logger::withDatabase($this->db, function($db) use ($query, $email) {
            $result = $db->query($query, [$email]);
            $customers = $result->fetchAll();

            return !empty($customers) ? $customers[0] : null;
        }, [
            'email' => $email,
            'table' => 'stripe_customers',
            'action' => 'get_customer_by_email'
        ], 'STRIPE');
    }

    public function getCustomerBySessionId($sessionId)
    {
        $query = "SELECT * FROM stripe_customers WHERE session_id = ? LIMIT 1";

        return Logger::withDatabase($this->db, function($db) use ($query, $sessionId) {
            $result = $db->query($query, [$sessionId]);
            $customers = $result->fetchAll();

            return !empty($customers) ? $customers[0] : null;
        }, [
            'session_id' => $sessionId,
            'table' => 'stripe_customers',
            'action' => 'get_customer_by_session_id'
        ], 'STRIPE');
    }
}
