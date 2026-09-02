<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

class UserEventJobsRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function createJob(array $data): int
    {
        $sql = "INSERT INTO user_event_jobs (
                    event_type, job_type, aggregate_type, aggregate_id, registered_id,
                    status, payload, attempts, available_at, idempotency_key, correlation_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

        $payloadJson = json_encode($data['payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payloadJson === false) {
            throw new Exception('Could not encode user event job payload: ' . json_last_error_msg());
        }

        try {
            $this->db->query($sql, [
                $data['event_type'],
                $data['job_type'],
                $data['aggregate_type'],
                $data['aggregate_id'],
                $data['registered_id'],
                $data['status'],
                $payloadJson,
                $data['attempts'],
                $data['idempotency_key'],
                $data['correlation_id'],
            ]);

            return (int) $this->db->lastInsertID();
        } catch (Exception $e) {
            if ($this->db->lastErrno() !== 1062) {
                throw $e;
            }

            $existing = $this->db->query(
                "SELECT id, aggregate_type, aggregate_id, job_type
                 FROM user_event_jobs
                 WHERE idempotency_key = ?
                 LIMIT 1
                 FOR UPDATE",
                [$data['idempotency_key']]
            )->fetchAll();

            if (empty($existing)) {
                throw new Exception('user_event_job_not_found_after_duplicate_insert');
            }

            $existingJob = $existing[0];
            $semanticMismatch =
                $existingJob['aggregate_type'] !== $data['aggregate_type']
                || (int) $existingJob['aggregate_id'] !== (int) $data['aggregate_id']
                || $existingJob['job_type'] !== $data['job_type'];

            Logger::event('user_event_job_duplicate_idempotency_key', [
                'idempotency_key' => $data['idempotency_key'],
                'existing_job_id' => (int) $existingJob['id'],
                'existing_aggregate_type' => $existingJob['aggregate_type'],
                'existing_aggregate_id' => (int) $existingJob['aggregate_id'],
                'existing_job_type' => $existingJob['job_type'],
                'requested_aggregate_type' => $data['aggregate_type'],
                'requested_aggregate_id' => (int) $data['aggregate_id'],
                'requested_job_type' => $data['job_type'],
                'semantic_mismatch' => $semanticMismatch,
            ], 'USER_EVENT', $semanticMismatch ? Logger::WARNING : Logger::DUPLICATE);

            if ($semanticMismatch) {
                throw new Exception('user_event_job_idempotency_key_semantic_mismatch');
            }

            return (int) $existingJob['id'];
        }
    }

    // Caller owns the transaction boundary. Do not open a transaction here —
    // checkout needs jobs to participate in the same local commit as VIP access and payment approval.
    public function createJobsBatch(array $jobsData): array
    {
        $ids = [];
        foreach ($jobsData as $data) {
            $ids[] = $this->createJob($data);
        }
        return $ids;
    }

    public function getJobsByAggregate(string $aggregateType, int $aggregateId): array
    {
        $rows = $this->db->query(
            "SELECT * FROM user_event_jobs
             WHERE aggregate_type = ? AND aggregate_id = ?
               AND status = 'pending' AND available_at <= NOW()
             ORDER BY id ASC",
            [$aggregateType, $aggregateId]
        )->fetchAll();

        return $rows;
    }

    public function findRetryableFailedJobs(int $maxAttempts, int $limit): array
    {
        $limit = max(1, $limit);

        return $this->db->query(
            "SELECT id, aggregate_type, aggregate_id, job_type, attempts
             FROM user_event_jobs
             WHERE status = 'failed'
               AND attempts < ?
             ORDER BY updated_at ASC, id ASC
             LIMIT " . $limit,
            [$maxAttempts]
        )->fetchAll();
    }

    public function requeueFailedJob(int $jobId, int $maxAttempts): bool
    {
        $this->db->query(
            "UPDATE user_event_jobs
             SET status = 'pending', available_at = NOW(), processed_at = NULL
             WHERE id = ?
               AND status = 'failed'
               AND attempts < ?",
            [$jobId, $maxAttempts]
        );

        return $this->db->affectedRows() === 1;
    }

    public function restorePendingRetryJobs(array $jobIds, string $error): int
    {
        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));
        if (empty($jobIds)) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($jobIds), '?'));
        $params = array_merge([$error], $jobIds);

        $this->db->query(
            "UPDATE user_event_jobs
             SET status = 'failed', processed_at = NOW(), last_error = ?
             WHERE status = 'pending'
               AND id IN (" . $placeholders . ")",
            $params
        );

        return (int) $this->db->affectedRows();
    }

    public function countExhaustedFailedJobs(int $maxAttempts): int
    {
        $rows = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM user_event_jobs
             WHERE status = 'failed'
               AND attempts >= ?",
            [$maxAttempts]
        )->fetchAll();

        return isset($rows[0]['total']) ? (int) $rows[0]['total'] : 0;
    }

    public function decodePayload(array $job): array
    {
        $payload = json_decode($job['payload'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            throw new Exception('Invalid user event job payload for job_id=' . $job['id'] . ': ' . json_last_error_msg());
        }

        $job['payload'] = $payload;

        return $job;
    }

    public function claimProcessing(int $jobId): bool
    {
        $this->db->query(
            "UPDATE user_event_jobs
             SET status = ?, attempts = attempts + 1, processed_at = NULL, last_error = NULL
             WHERE id = ?
               AND status = ?
               AND available_at <= NOW()",
            ['processing', $jobId, 'pending']
        );

        return $this->db->affectedRows() === 1;
    }

    public function markDone(int $jobId): bool
    {
        $this->db->query(
            "UPDATE user_event_jobs
             SET status = ?, processed_at = NOW(), last_error = NULL
             WHERE id = ? AND status = ?",
            ['done', $jobId, 'processing']
        );

        return $this->db->affectedRows() === 1;
    }

    public function annotateUncertain(int $jobId, string $reason, ?string $errorDetail = null): bool
    {
        $annotation = 'uncertain: ' . $reason;
        if ($errorDetail !== null) {
            $annotation .= ': ' . $errorDetail;
        }

        $this->db->query(
            "UPDATE user_event_jobs
             SET last_error = ?
             WHERE id = ? AND status = ?",
            [$annotation, $jobId, 'processing']
        );

        return $this->db->affectedRows() === 1;
    }

    public function markFailed(int $jobId, string $error): bool
    {
        $this->db->query(
            "UPDATE user_event_jobs
             SET status = ?, processed_at = NOW(), last_error = ?
             WHERE id = ? AND status = ?",
            ['failed', $error, $jobId, 'processing']
        );

        return $this->db->affectedRows() === 1;
    }

}
