<?php

class PostCheckoutService
{
    private $db;
    private $transactions;
    private $registeredProfiles;
    private $vipAccessService;
    private $jobCreator;
    private $inlineRunnerFactory;
    private $userEventsFactory;

    public function __construct(
        DB $db,
        CheckoutTransactionsRepository $transactions,
        RegisteredProfileRepository $registeredProfiles,
        VipAccessService $vipAccessService,
        UserEventJobCreator $jobCreator,
        callable $inlineRunnerFactory,
        PostCheckoutUserEventsFactory $userEventsFactory
    ) {
        $this->db = $db;
        $this->transactions = $transactions;
        $this->registeredProfiles = $registeredProfiles;
        $this->vipAccessService = $vipAccessService;
        $this->jobCreator = $jobCreator;
        $this->inlineRunnerFactory = $inlineRunnerFactory;
        $this->userEventsFactory = $userEventsFactory;
    }

    public function completeApprovedPayment(array $context, string $paymentId): array
    {
        $commitInvoked = false;
        $this->db->beginTransaction();

        try {
            $transaction = $this->transactions->lockByPaymentId($paymentId);
            if ($transaction === null) {
                throw new Exception('payment_not_found_for_completion');
            }
            if (!CheckoutTransactionStatus::isConsistent($transaction)) {
                throw new Exception('inconsistent_payment_for_completion');
            }
            if ($transaction['status'] === CheckoutTransactionStatus::APPROVED) {
                $commitInvoked = true;
                $this->db->commit();
                return $transaction;
            }
            $this->assertCompletable($transaction);

            $event = $context['eventContext'];
            $customer = $context['customer'];
            $profileBefore = $this->registeredProfiles->findByEmailForEventForUpdate(
                $customer['email'],
                $event['registeredFreeColumn'],
                $event['registeredVipColumn']
            );
            $shouldEmitFreeEffects = !($profileBefore && (int) ($profileBefore['is_free'] ?? 0) === 1);

            $registeredId = $this->vipAccessService->upgrade($event, $customer, $event['eventPhase']);
            if (!$this->transactions->markApproved($paymentId, $registeredId)) {
                throw new Exception('payment_approval_transition_failed');
            }

            $transaction = $this->transactions->findByPaymentId($paymentId);
            $jobs = $this->userEventsFactory->buildApprovedJobs(
                $customer,
                $event,
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

            $commitInvoked = true;
            $this->db->commit();
        } catch (Throwable $e) {
            if (!$commitInvoked) {
                try {
                    $this->db->rollback();
                } catch (Throwable $ignored) {
                    try {
                        $this->db->close();
                    } catch (Throwable $alsoIgnored) {
                    }
                }
            } else {
                try {
                    $this->db->close();
                } catch (Throwable $ignored) {
                }
            }
            throw $e;
        }

        $this->deferAfterCommit($transaction);
        return $transaction;
    }

    private function assertCompletable(array $transaction): void
    {
        if ($transaction['status'] !== CheckoutTransactionStatus::PROCESSING) {
            throw new Exception('payment_not_processing');
        }
        if ($transaction['payment_method'] === 'card' && empty($transaction['provider_approved_at'])) {
            throw new Exception('card_marker_required');
        }
        if ($transaction['payment_method'] === 'coupon'
            && ((string) $transaction['final_amount'] !== '0.00' || empty($transaction['coupon_id']))) {
            throw new Exception('invalid_coupon_completion');
        }
    }

    private function deferAfterCommit(array $transaction): void
    {
        $runnerFactory = $this->inlineRunnerFactory;
        register_shutdown_function(static function () use ($runnerFactory, $transaction): void {
            try {
                $runner = $runnerFactory();
                $runner->runForAggregate('checkout_transaction', (int) $transaction['id'], [
                    'correlation_id' => $transaction['correlation_id'],
                    'payment_id' => $transaction['payment_id'],
                ]);
            } catch (Throwable $e) {
                Logger::event('inline_outbox_failed_after_payment_approved', [
                    'payment_id' => $transaction['payment_id'],
                    'correlation_id' => $transaction['correlation_id'],
                ], 'USER_EVENT', Logger::ERROR);
            }
        });
    }
}
