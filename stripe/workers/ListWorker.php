#!/usr/bin/env php
<?php
require_once(__DIR__ . '/core/BaseWorker.php');
require_once(__DIR__ . '/../../services/functions.php');
require_once(__DIR__ . '/../models/SubscriberDopplerList.php');

class ListWorker extends BaseWorker
{
    protected $serviceName = 'list';

    public function __construct()
    {
        parent::__construct(RedisManager::STREAM_LIST);
    }

    protected function processJob($job)
    {
        $snapshot = $job['user_snapshot'];
        $email    = $snapshot['email'];
        $listId   = $snapshot['list'] ?? null;

        try {
            $dopplerHandler = new SubscriberDopplerList();
            $result = strtolower((string)$dopplerHandler->saveSubscription($snapshot));

            // Éxitos "normales"
            $ok = in_array($result, ['success', 'success-doble-optin']);

            // Casos idempotentes (no bloquear, pero loguear warning)
            $idempotent = (bool)preg_match('/already|exist|duplic/i', $result);

            if ($ok) {
                Logger::success("list_added", [
                    'job_id'   => $job['id'],
                    'email'    => $email,
                    'list_id'  => $listId,
                    'result'   => $result,
                    'event_id' => $snapshot['event_id'] ?? 'unknown'
                ], 'LIST_WORKER');
                return true; // se marca como procesado
            }

            if ($idempotent) {
                Logger::warning("list_already_present", [
                    'job_id'   => $job['id'],
                    'email'    => $email,
                    'list_id'  => $listId,
                    'result'   => $result,
                    'event_id' => $snapshot['event_id'] ?? 'unknown',
                    'idempotent' => true
                ], 'LIST_WORKER');
                return true; // también se marca como procesado, sin reintentos
            }

            // Cualquier otro resultado inesperado: warning + reintento
            Logger::warning("doppler_result_unexpected", [
                'job_id'   => $job['id'],
                'email'    => $email,
                'list_id'  => $listId,
                'result'   => $result
            ], 'LIST_WORKER');

            return false;

        } catch (Exception $e) {
            $msg = $e->getMessage();

            // Idempotencia: si la excepción indica que ya existe, tratar como éxito
            if (preg_match('/already|exist|duplic/i', $msg)) {
                Logger::warning("list_already_present", [
                    'job_id' => $job['id'],
                    'email' => $email,
                    'list_id' => $listId,
                    'error' => $msg,
                    'idempotent' => true
                ], 'LIST_WORKER');
                return true; // no reintentar
            }

            // Error real - reintentar
            Logger::error("list_failed", [
                'job_id'  => $job['id'],
                'email'   => $email,
                'list_id' => $listId,
                'error'   => $msg
            ], 'LIST_WORKER');

            return false;
        }
    }


    protected function isJobProcessed($job)
    {
        return $job['list_added'] == 1;
    }

    protected function markJobAsProcessed($jobId)
    {
        return $this->jobsModel->updateJobStatus($jobId, 'list_added');
    }

    protected function getWorkerName() { return 'LIST_WORKER'; }
}

// Run worker if called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $worker = new ListWorker();
    $worker->run();
}

