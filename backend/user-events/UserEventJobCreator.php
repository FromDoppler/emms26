<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/repositories/UserEventJobsRepository.php');

class UserEventJobCreator
{
    private $jobsRepository;

    public function __construct(UserEventJobsRepository $jobsRepository)
    {
        $this->jobsRepository = $jobsRepository;
    }

    public function createJobs(array $aggregate, int $registeredId, array $jobs): array
    {
        if (empty($aggregate['type'])) {
            throw new InvalidArgumentException('Missing aggregate.type for user event jobs');
        }
        if (empty($aggregate['id']) || (int) $aggregate['id'] <= 0) {
            throw new InvalidArgumentException('Missing or invalid aggregate.id for user event jobs');
        }
        if (empty($aggregate['correlation_id'])) {
            throw new InvalidArgumentException('Missing aggregate.correlation_id for user event jobs');
        }
        if (strlen($aggregate['correlation_id']) > 64) {
            throw new InvalidArgumentException('aggregate.correlation_id is too long for user event jobs');
        }

        $seenKeys = [];
        $jobsData = [];
        foreach ($jobs as $job) {
            if (empty($job['event_type'])) {
                throw new InvalidArgumentException('Missing event_type in user event job');
            }
            if (empty($job['job_type'])) {
                throw new InvalidArgumentException('Missing job_type in user event job');
            }
            if (!isset($job['payload']) || !is_array($job['payload'])) {
                throw new InvalidArgumentException('Missing or invalid payload in user event job');
            }
            if (empty($job['idempotency_key'])) {
                throw new InvalidArgumentException('Missing idempotency_key in user event job');
            }
            if (strlen($job['idempotency_key']) > 255) {
                throw new InvalidArgumentException('idempotency_key is too long for user event job');
            }
            if (isset($seenKeys[$job['idempotency_key']])) {
                throw new InvalidArgumentException('Duplicate idempotency_key within batch: ' . $job['idempotency_key']);
            }
            $seenKeys[$job['idempotency_key']] = true;

            $jobsData[] = [
                'event_type' => $job['event_type'],
                'job_type' => $job['job_type'],
                'aggregate_type' => $aggregate['type'],
                'aggregate_id' => (int) $aggregate['id'],
                'registered_id' => $registeredId,
                'status' => 'pending',
                'payload' => $job['payload'],
                'attempts' => 0,
                'idempotency_key' => $job['idempotency_key'],
                'correlation_id' => $aggregate['correlation_id'],
            ];
        }

        return $this->jobsRepository->createJobsBatch($jobsData);
    }
}
