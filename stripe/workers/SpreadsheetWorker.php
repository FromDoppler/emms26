#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/BaseWorker.php');
require_once(__DIR__ . '/../../services/functions.php');
require_once(__DIR__ . '/../utils/SpreadSheetGoogle.php');

class SpreadsheetWorker extends BaseWorker
{
    protected $serviceName = 'spreadsheet';

    public function __construct()
    {
        parent::__construct(RedisManager::STREAM_SPREADSHEET);
    }
   
    protected function processJob($job)
    {
        $snapshot = $job['user_snapshot'];
        $email    = $snapshot['email'];

        try {
            saveSubscriptionSpreadSheet($snapshot, ID_SPREADSHEET_DT_VIP);

            Logger::success("spreadsheet_saved", [
                'job_id'   => $job['id'],
                'email'    => $email,
                'event_id' => $snapshot['event_id'] ?? 'unknown'
            ], 'SPREADSHEET_WORKER');

            return true;

        } catch (Exception $e) {
            Logger::error("spreadsheet_failed", [
                'job_id' => $job['id'],
                'email'  => $email,
                'error'  => $e->getMessage()
            ], 'SPREADSHEET_WORKER');

            return false;
        }
    }

    protected function isJobProcessed($job)
    {
        return $job['spreadsheet_saved'] == 1;
    }

    protected function markJobAsProcessed($jobId)
    {
        return $this->jobsModel->updateJobStatus($jobId, 'spreadsheet_saved');
    }

    protected function getWorkerName() { return 'SPREADSHEET_WORKER'; }
}

// Run worker if called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $worker = new SpreadsheetWorker();
    $worker->run();
}
