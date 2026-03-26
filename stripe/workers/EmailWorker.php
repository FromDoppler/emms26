#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/BaseWorker.php');
require_once(__DIR__ . '/../../services/functions.php');
require_once(__DIR__ . '/../utils/sendEmail.php');

class EmailWorker extends BaseWorker
{
    protected $serviceName = 'email';

    public function __construct()
    {
        parent::__construct(RedisManager::STREAM_EMAIL);
    }

    protected function processJob($job)
    {
        $snapshot = $job['user_snapshot'];
        $email = $snapshot['email'];
        $subject = $snapshot['subject'];

        try {
            if (empty($_SERVER['HTTP_HOST'])) {
                $_SERVER['HTTP_HOST'] = 'localhost';
            }

            sendEmail($snapshot, $subject);

            Logger::success("email_sent", [
                'job_id' => $job['id'],
                'email'  => $email,
                'event_id' => $snapshot['event_id'] ?? 'unknown'
            ], 'EMAIL_WORKER');

            return true;

        } catch (Exception $e) {
            Logger::error("email_failed", [
                'job_id' => $job['id'],
                'email'  => $email,
                'error'  => $e->getMessage()
            ], 'EMAIL_WORKER');

            return false;
        }
    }

    protected function isJobProcessed($job)
    {
        return $job['email_sent'] == 1;
    }

    protected function markJobAsProcessed($jobId)
    {
        return $this->jobsModel->updateJobStatus($jobId, 'email_sent');
    }

    protected function getWorkerName() { return 'EMAIL_WORKER'; }
}

// Run worker if called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $worker = new EmailWorker();
        $worker->run();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
        echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
    }
}
