<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/repositories/UserEventJobsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobHandlerRegistry.php');

class InlineUserEventJobRunner
{
    private $jobsRepository;
    private $handlerRegistry;

    public function __construct(UserEventJobsRepository $jobsRepository, UserEventJobHandlerRegistry $handlerRegistry)
    {
        $this->jobsRepository = $jobsRepository;
        $this->handlerRegistry = $handlerRegistry;
    }

    public function runForAggregate(string $aggregateType, int $aggregateId, array $context = []): array
    {
        try {
            $jobs = $this->jobsRepository->getJobsByAggregate($aggregateType, $aggregateId);
        } catch (Throwable $e) {
            Logger::event('inline_outbox_read_failed', [
                'correlation_id' => $context['correlation_id'] ?? null,
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'aggregate_payment_id' => $context['aggregate_payment_id'] ?? null,
                'error' => substr($e->getMessage(), 0, 1000),
            ], 'USER_EVENT', Logger::ERROR);

            return [
                'mode' => 'inline_outbox',
                'status' => 'done_with_failures',
                'processed' => 0,
                'failed' => 0,
                'claim_failed' => 0,
                'skipped' => 0,
                'uncertain' => 0,
            ];
        }

        $hasFailures = false;
        $processed = 0;
        $failed = 0;
        $claimFailed = 0;
        $skipped = 0;
        $uncertain = 0;
        $correlationId = $context['correlation_id'] ?? null;
        $aggregatePaymentId = $context['aggregate_payment_id'] ?? null;

        Logger::event('inline_outbox_started', [
            'correlation_id' => $correlationId,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'aggregate_payment_id' => $aggregatePaymentId,
            'job_count' => count($jobs),
        ], 'USER_EVENT', Logger::INFO);

        foreach ($jobs as $job) {
            try {
                $claimed = $this->jobsRepository->claimProcessing((int) $job['id']);
            } catch (Throwable $e) {
                $hasFailures = true;
                $claimFailed++;

                Logger::event('inline_outbox_job_claim_failed', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => (int) $job['id'],
                    'job_type' => $job['job_type'],
                    'error' => $this->truncateError($e->getMessage()),
                ], 'USER_EVENT', Logger::ERROR);

                continue;
            }

            if (!$claimed) {
                $skipped++;

                Logger::event('inline_outbox_job_claim_skipped', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => (int) $job['id'],
                    'job_type' => $job['job_type'],
                ], 'USER_EVENT', Logger::WARNING);

                continue;
            }

            Logger::event('inline_outbox_job_started', [
                'correlation_id' => $correlationId,
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'aggregate_payment_id' => $aggregatePaymentId,
                'job_id' => (int) $job['id'],
                'job_type' => $job['job_type'],
            ], 'USER_EVENT', Logger::INFO);

            $decodedJob = null;

            try {
                $decodedJob = $this->jobsRepository->decodePayload($job);
                $this->handlerRegistry->get($decodedJob['job_type'])->handle($decodedJob);
            } catch (Throwable $e) {
                $hasFailures = true;
                $failed++;
                $failedPersisted = false;
                $failedPersistError = null;

                try {
                    $failedPersisted = $this->jobsRepository->markFailed(
                        (int) $job['id'],
                        $this->truncateError($e->getMessage())
                    );
                } catch (Throwable $persistError) {
                    $failedPersistError = $this->truncateError($persistError->getMessage());

                    Logger::event('inline_outbox_job_mark_failed_persist_error', [
                        'correlation_id' => $correlationId,
                        'aggregate_type' => $aggregateType,
                        'aggregate_id' => $aggregateId,
                        'aggregate_payment_id' => $aggregatePaymentId,
                        'job_id' => (int) $job['id'],
                        'job_type' => $job['job_type'],
                        'error' => $failedPersistError,
                    ], 'USER_EVENT', Logger::ERROR);
                }

                if (!$failedPersisted) {
                    $uncertain++;
                    $this->tryAnnotateUncertain(
                        (int) $job['id'],
                        'mark_failed_not_persisted_after_handler_failed',
                        $correlationId, $aggregateType, $aggregateId, $aggregatePaymentId, $job['job_type'],
                        $failedPersistError
                    );
                }

                Logger::event('inline_outbox_job_failed', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => (int) $job['id'],
                    'job_type' => $job['job_type'],
                    'error' => $this->truncateError($e->getMessage()),
                    'failed_persisted' => $failedPersisted,
                    'failed_persist_error' => $failedPersistError,
                ], 'USER_EVENT', Logger::ERROR);

                continue;
            }

            try {
                if ($this->jobsRepository->markDone((int) $decodedJob['id'])) {
                    $processed++;

                    Logger::event('inline_outbox_job_done', [
                        'correlation_id' => $correlationId,
                        'aggregate_type' => $aggregateType,
                        'aggregate_id' => $aggregateId,
                        'aggregate_payment_id' => $aggregatePaymentId,
                        'job_id' => (int) $decodedJob['id'],
                        'job_type' => $decodedJob['job_type'],
                    ], 'USER_EVENT', Logger::INFO);
                    continue;
                }

                $hasFailures = true;
                $uncertain++;
                $this->tryAnnotateUncertain(
                    (int) $decodedJob['id'],
                    'mark_done_returned_false_after_handler_completed',
                    $correlationId, $aggregateType, $aggregateId, $aggregatePaymentId, $decodedJob['job_type']
                );

                Logger::event('inline_outbox_job_state_persist_uncertain', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => (int) $decodedJob['id'],
                    'job_type' => $decodedJob['job_type'],
                    'reason' => 'mark_done_returned_false_after_handler_completed',
                ], 'USER_EVENT', Logger::ERROR);
            } catch (Throwable $e) {
                $hasFailures = true;
                $uncertain++;
                $this->tryAnnotateUncertain(
                    (int) $decodedJob['id'],
                    'mark_done_failed_after_handler_completed',
                    $correlationId, $aggregateType, $aggregateId, $aggregatePaymentId, $decodedJob['job_type'],
                    $this->truncateError($e->getMessage())
                );

                Logger::event('inline_outbox_job_state_persist_uncertain', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => (int) $decodedJob['id'],
                    'job_type' => $decodedJob['job_type'],
                    'reason' => 'mark_done_failed_after_handler_completed',
                    'error' => $this->truncateError($e->getMessage()),
                ], 'USER_EVENT', Logger::ERROR);
            }
        }

        $result = [
            'mode' => 'inline_outbox',
            'status' => $hasFailures ? 'done_with_failures' : 'done',
            'processed' => $processed,
            'failed' => $failed,
            'claim_failed' => $claimFailed,
            'skipped' => $skipped,
            'uncertain' => $uncertain,
        ];

        Logger::event('inline_outbox_finished', [
            'correlation_id' => $correlationId,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'aggregate_payment_id' => $aggregatePaymentId,
            'status' => $result['status'],
            'processed' => $result['processed'],
            'failed' => $result['failed'],
            'claim_failed' => $result['claim_failed'],
            'skipped' => $result['skipped'],
            'uncertain' => $result['uncertain'],
        ], 'USER_EVENT', ($hasFailures || $skipped > 0) ? Logger::WARNING : Logger::INFO);

        return $result;
    }

    private function tryAnnotateUncertain(
        int $jobId,
        string $reason,
        ?string $correlationId,
        string $aggregateType,
        int $aggregateId,
        ?string $aggregatePaymentId,
        string $jobType,
        ?string $errorDetail = null
    ): void {
        try {
            $persisted = $this->jobsRepository->annotateUncertain($jobId, $reason, $errorDetail);
            if (!$persisted) {
                Logger::event('inline_outbox_job_annotate_uncertain_not_persisted', [
                    'correlation_id' => $correlationId,
                    'aggregate_type' => $aggregateType,
                    'aggregate_id' => $aggregateId,
                    'aggregate_payment_id' => $aggregatePaymentId,
                    'job_id' => $jobId,
                    'job_type' => $jobType,
                    'reason' => $reason,
                ], 'USER_EVENT', Logger::WARNING);
            }
        } catch (Throwable $e) {
            Logger::event('inline_outbox_job_annotate_uncertain_persist_error', [
                'correlation_id' => $correlationId,
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'aggregate_payment_id' => $aggregatePaymentId,
                'job_id' => $jobId,
                'job_type' => $jobType,
                'reason' => $reason,
                'error' => $this->truncateError($e->getMessage()),
            ], 'USER_EVENT', Logger::ERROR);
        }
    }

    private function truncateError(string $error): string
    {
        return substr($error, 0, 1000);
    }
}
