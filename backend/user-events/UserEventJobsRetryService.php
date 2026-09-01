<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/repositories/UserEventJobsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/InlineUserEventJobRunner.php');

class UserEventJobsRetryService
{
    private const DEFAULT_MAX_ATTEMPTS = 6;
    private const DEFAULT_BATCH_SIZE = 50;

    private $jobsRepository;
    private $runnerFactory;

    public function __construct(UserEventJobsRepository $jobsRepository, callable $runnerFactory)
    {
        $this->jobsRepository = $jobsRepository;
        $this->runnerFactory = $runnerFactory;
    }

    public function run(
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ): array {
        if ($maxAttempts < 2) {
            throw new InvalidArgumentException('maxAttempts must be at least 2');
        }
        if ($batchSize < 1) {
            throw new InvalidArgumentException('batchSize must be at least 1');
        }

        $jobs = $this->jobsRepository->findRetryableFailedJobs($maxAttempts, $batchSize);
        $aggregates = [];
        $requeued = 0;

        foreach ($jobs as $job) {
            if (!$this->jobsRepository->requeueFailedJob((int) $job['id'], $maxAttempts)) {
                continue;
            }

            $requeued++;
            $aggregateType = (string) $job['aggregate_type'];
            $aggregateId = (int) $job['aggregate_id'];
            $key = $aggregateType . ':' . $aggregateId;
            $aggregates[$key] = [
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
            ];
        }

        $processed = 0;
        $failed = 0;
        $claimFailed = 0;
        $skipped = 0;
        $uncertain = 0;

        if (!empty($aggregates)) {
            $runner = call_user_func($this->runnerFactory);
            if (!$runner instanceof InlineUserEventJobRunner) {
                throw new RuntimeException('runnerFactory must return InlineUserEventJobRunner');
            }

            foreach ($aggregates as $aggregate) {
                $result = $runner->runForAggregate(
                    $aggregate['aggregate_type'],
                    $aggregate['aggregate_id']
                );

                $processed += (int) ($result['processed'] ?? 0);
                $failed += (int) ($result['failed'] ?? 0);
                $claimFailed += (int) ($result['claim_failed'] ?? 0);
                $skipped += (int) ($result['skipped'] ?? 0);
                $uncertain += (int) ($result['uncertain'] ?? 0);
            }
        }

        return [
            'candidates' => count($jobs),
            'requeued' => $requeued,
            'aggregates' => count($aggregates),
            'recovered' => $processed,
            'still_failed' => $failed,
            'claim_failed' => $claimFailed,
            'skipped' => $skipped,
            'uncertain' => $uncertain,
            'exhausted' => $this->jobsRepository->countExhaustedFailedJobs($maxAttempts),
            'max_attempts' => $maxAttempts,
        ];
    }
}
