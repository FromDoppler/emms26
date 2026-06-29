<?php

class PostCheckoutService
{
    private $transactions;
    private $registeredProfiles;
    private $vipAccessService;
    private $jobCreator;
    private $inlineRunner;
    private $userEventsFactory;

    public function __construct(
        CheckoutTransactionsRepository $transactions,
        RegisteredProfileRepository $registeredProfiles,
        VipAccessService $vipAccessService,
        UserEventJobCreator $jobCreator,
        InlineUserEventJobRunner $inlineRunner,
        PostCheckoutUserEventsFactory $userEventsFactory
    ) {
        $this->transactions = $transactions;
        $this->registeredProfiles = $registeredProfiles;
        $this->vipAccessService = $vipAccessService;
        $this->jobCreator = $jobCreator;
        $this->inlineRunner = $inlineRunner;
        $this->userEventsFactory = $userEventsFactory;
    }

    public function fulfillApprovedCheckout(array $context, array $transaction, array $approvalData, CheckoutExecutionRuntime $runtime): array
    {
        $profileBeforeUpgrade = $this->registeredProfiles->findByEmailForEvent(
            $context['customer']['email'],
            $context['eventContext']['registeredFreeColumn'],
            $context['eventContext']['registeredVipColumn']
        );
        $shouldEmitFreeEffects = !($profileBeforeUpgrade && (int) ($profileBeforeUpgrade['is_free'] ?? 0) === 1);

        $registeredId = $this->vipAccessService->upgrade(
            $context['eventContext'],
            $context['customer'],
            $context['eventContext']['eventPhase']
        );

        $approved = $this->transactions->markApproved((int) $transaction['id'], $approvalData + [
            'registered_id' => $registeredId,
        ]);

        if (!$approved) {
            $reloadedTransaction = $this->transactions->findByIdempotencyKey($runtime->idempotencyKey());
            Logger::event('payment_transaction_status_transition_skipped', [
                'correlation_id' => $transaction['correlation_id'] ?? $runtime->correlationId(),
                'idempotency_key' => $runtime->idempotencyKey(),
                'checkout_transaction_id' => (int) ($transaction['id'] ?? 0),
                'checkout_public_id' => $transaction['public_id'] ?? null,
                'desired_transition' => 'approved',
                'reloaded_status' => $reloadedTransaction['status'] ?? null,
            ], 'PAYMENTS', Logger::ERROR);

            throw new Exception('invalid_payment_transaction_status_transition');
        }

        $transaction = $this->transactions->findByIdempotencyKey($runtime->idempotencyKey());
        $jobs = $this->userEventsFactory->buildApprovedJobs(
            $context['customer'],
            $context['eventContext'],
            $transaction,
            $context['pricing'],
            $context['input'],
            $shouldEmitFreeEffects
        );

        $this->jobCreator->createJobs([
            'type' => 'checkout_transaction',
            'id' => (int) $transaction['id'],
            'correlation_id' => $transaction['correlation_id'],
        ], $registeredId, $jobs);

        // Caller (CheckoutPaymentProcessor) owns commit and runtime state marks.
        return $transaction;
    }

    public function afterApprovedCheckoutCommitted(array $transaction): array
    {
        try {
            Logger::event('user_event_jobs_created', [
                'correlation_id' => $transaction['correlation_id'],
                'aggregate_type' => 'checkout_transaction',
                'aggregate_id' => (int) $transaction['id'],
                'aggregate_public_id' => $transaction['public_id'],
            ], 'PAYMENTS', Logger::INFO);

            $inlineJobsResult = $this->runInlineJobsBestEffort($transaction);

            $reloadedTransaction = $this->transactions->findByIdempotencyKey($transaction['idempotency_key']);
            if ($reloadedTransaction !== null) {
                $transaction = $reloadedTransaction;
            }

            Logger::event('payment_approved', [
                'correlation_id' => $transaction['correlation_id'],
                'aggregate_type' => 'checkout_transaction',
                'aggregate_id' => (int) $transaction['id'],
                'aggregate_public_id' => $transaction['public_id'],
                'response_code' => $transaction['response_code'],
                'provider' => $transaction['provider'],
                'inline_jobs_status' => $inlineJobsResult['status'] ?? 'not_run',
            ], 'PAYMENTS', Logger::INFO);
        } catch (Throwable $e) {
            Logger::event('post_checkout_after_commit_failed', [
                'correlation_id' => $transaction['correlation_id'] ?? null,
                'checkout_transaction_id' => (int) ($transaction['id'] ?? 0),
                'checkout_public_id' => $transaction['public_id'] ?? null,
                'error' => $e->getMessage(),
            ], 'PAYMENTS', Logger::ERROR);
        }

        return $transaction;
    }

    private function runInlineJobsBestEffort(array $transaction): ?array
    {
        try {
            $result = $this->inlineRunner->runForAggregate('checkout_transaction', (int) $transaction['id'], [
                'correlation_id' => $transaction['correlation_id'],
                'aggregate_public_id' => $transaction['public_id'],
            ]);

            return [
                'attempted' => true,
                'hasFailures' =>
                    ($result['status'] ?? null) !== 'done'
                    || !empty($result['failed'])
                    || !empty($result['claim_failed'])
                    || !empty($result['skipped'])
                    || !empty($result['uncertain']),
                'mode' => $result['mode'] ?? 'inline_outbox',
                'status' => $result['status'] ?? 'done',
                'processed' => $result['processed'] ?? null,
                'failed' => $result['failed'] ?? null,
                'error' => null,
            ];
        } catch (Throwable $e) {
            Logger::event('inline_outbox_failed_after_payment_approved', [
                'correlation_id' => $transaction['correlation_id'] ?? null,
                'checkout_transaction_id' => (int) ($transaction['id'] ?? 0),
                'checkout_public_id' => $transaction['public_id'] ?? null,
                'error' => $e->getMessage(),
            ], 'USER_EVENT', Logger::ERROR);

            return [
                'attempted' => true,
                'hasFailures' => true,
                'mode' => 'inline_outbox',
                'status' => 'failed',
                'error' => 'inline_outbox_failed',
            ];
        }
    }
}
