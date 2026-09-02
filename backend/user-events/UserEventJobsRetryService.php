<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/repositories/UserEventJobsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/InlineUserEventJobRunner.php');

class UserEventJobsRetryService
{
    private const DEFAULT_MAX_ATTEMPTS = 6;
    private const DEFAULT_BATCH_SIZE = 50;
    private const DEFAULT_STALE_PROCESSING_MINUTES = 15;

    private $jobsRepository;
    private $runnerFactory;

    public function __construct(UserEventJobsRepository $jobsRepository, callable $runnerFactory)
    {
        $this->jobsRepository = $jobsRepository;
        $this->runnerFactory = $runnerFactory;
    }

    public function run(
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        int $staleProcessingMinutes = self::DEFAULT_STALE_PROCESSING_MINUTES
    ): array {
        if ($maxAttempts < 2) {
            throw new InvalidArgumentException('maxAttempts must be at least 2');
        }
        if ($batchSize < 1) {
            throw new InvalidArgumentException('batchSize must be at least 1');
        }
        if ($staleProcessingMinutes < 1) {
            throw new InvalidArgumentException('staleProcessingMinutes must be at least 1');
        }

        $staleProcessing = $this->jobsRepository->releaseStaleProcessingJobs(
            $maxAttempts,
            $staleProcessingMinutes
        );

        $failedJobs = $this->jobsRepository->findRetryableFailedJobs($maxAttempts, $batchSize);
        $failedRequeued = 0;

        foreach ($failedJobs as $job) {
            if ($this->jobsRepository->requeueFailedJob((int) $job['id'], $maxAttempts)) {
                $failedRequeued++;
            }
        }

        $pendingAggregates = $this->jobsRepository->findAvailablePendingAggregates($batchSize);
        $runner = null;
        if (!empty($pendingAggregates)) {
            $runner = call_user_func($this->runnerFactory);
            if (!$runner instanceof InlineUserEventJobRunner) {
                throw new RuntimeException('runnerFactory must return InlineUserEventJobRunner');
            }
        }

        $aggregates = 0;
        $processed = 0;
        $failed = 0;
        $claimFailed = 0;
        $skipped = 0;
        $uncertain = 0;

        foreach ($pendingAggregates as $aggregate) {
            $aggregates++;

            $result = $runner->runForAggregate(
                (string) $aggregate['aggregate_type'],
                (int) $aggregate['aggregate_id']
            );

            $processed += (int) ($result['processed'] ?? 0);
            $failed += (int) ($result['failed'] ?? 0);
            $claimFailed += (int) ($result['claim_failed'] ?? 0);
            $skipped += (int) ($result['skipped'] ?? 0);
            $uncertain += (int) ($result['uncertain'] ?? 0);
        }

        return [
            'stale_processing_requeued' => (int) ($staleProcessing['requeued'] ?? 0),
            'stale_processing_exhausted' => (int) ($staleProcessing['exhausted'] ?? 0),
            'failed_candidates' => count($failedJobs),
            'failed_requeued' => $failedRequeued,
            'pending_aggregates' => count($pendingAggregates),
            'aggregates' => $aggregates,
            'recovered' => $processed,
            'still_failed' => $failed,
            'claim_failed' => $claimFailed,
            'skipped' => $skipped,
            'uncertain' => $uncertain,
            'exhausted' => $this->jobsRepository->countExhaustedFailedJobs($maxAttempts),
            'max_attempts' => $maxAttempts,
            'stale_processing_minutes' => $staleProcessingMinutes,
        ];
    }
}
