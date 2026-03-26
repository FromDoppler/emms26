<?php
require_once(__DIR__ . '/../../utils/Logger.php');

class StripeCustomersJobsDatabase
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create a new job
     */
    public function createJob($registeredId, $stripeCustomerId, $snapshot)
    {
        $query = "INSERT INTO stripe_customers_jobs (registered_id, stripe_customer_id, user_snapshot) VALUES (?, ?, ?)";
        $snapshotJson = json_encode(
            $snapshot,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $params = [$registeredId, $stripeCustomerId, $snapshotJson];

        $this->db->query($query, $params);
        return $this->db->lastInsertID();
    }

    /**
     * Get job by ID
     */
    public function getJobById($jobId)
    {
        $query = "SELECT * FROM stripe_customers_jobs WHERE id = ?";
        $result = $this->db->query($query, [$jobId])->fetchAll();

        if (empty($result)) {
            return null;
        }

        $job = $result[0];
        $job['user_snapshot'] = json_decode($job['user_snapshot'], true);
        return $job;
    }

    /**
     * Update job status flags
     */
    public function updateJobStatus($jobId, $field, $value = 1)
    {
        $allowedFields = ['email_sent', 'spreadsheet_saved', 'list_added'];

        if (!in_array($field, $allowedFields)) {
            throw new Exception("Invalid field: $field");
        }

        $query = "UPDATE stripe_customers_jobs SET $field = ? WHERE id = ?";
        $this->db->query($query, [$value, $jobId]);

        return $this->db->affectedRows() > 0;
    }

}
