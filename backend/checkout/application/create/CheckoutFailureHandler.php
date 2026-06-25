<?php

class CheckoutFailureHandler
{
    private $db;
    private $dbFactory;
    private $responseFactory;

    public function __construct(
        DB $db,
        callable $dbFactory,
        CheckoutResponseFactory $responseFactory
    ) {
        $this->db = $db;
        $this->dbFactory = $dbFactory;
        $this->responseFactory = $responseFactory;
    }

    public function handle(Throwable $e, CheckoutExecutionRuntime $runtime): array
    {
        if ($runtime->hasOpenDbTransaction()) {
            try {
                $this->db->rollback();
                $runtime->markDbTransactionClosed();
            } catch (Throwable $rollbackException) {
                Logger::event('payment_rollback_failed', [
                    'correlation_id' => $runtime->correlationId(),
                    'checkout_transaction_id' => $runtime->transactionId(),
                    'error' => $rollbackException->getMessage(),
                    'original_error' => $e->getMessage(),
                ], 'PAYMENTS', Logger::ERROR);

                try {
                    $this->db->close();
                } catch (Throwable $closeException) {
                    Logger::event('payment_db_close_after_rollback_failed', [
                        'correlation_id' => $runtime->correlationId(),
                        'checkout_transaction_id' => $runtime->transactionId(),
                        'error' => $closeException->getMessage(),
                        'original_error' => $e->getMessage(),
                    ], 'PAYMENTS', Logger::ERROR);
                }
            }
        }

        if ($runtime->transactionId() !== null) {
            try {
                $dbFactory = $this->dbFactory;
                $transactionsDatabase = new CheckoutTransactionsRepository($dbFactory());
                $existingTransaction = $transactionsDatabase->findByIdempotencyKey($runtime->idempotencyKey());
                if ($runtime->providerApprovedButLocalCommitIncomplete()) {
                    $this->bestEffortMarkError($transactionsDatabase, $runtime->transactionId(), $runtime->idempotencyKey(), [
                        'provider' => $runtime->approvedProviderResult()->provider,
                        'provider_transaction_id' => $runtime->approvedProviderResult()->providerTransactionId,
                        'authorization_number' => $runtime->approvedProviderResult()->authorizationNumber,
                        'transaction_link_id' => $runtime->approvedProviderResult()->transactionLinkId,
                        'authorization_response_code' => $runtime->approvedProviderResult()->authorizationResponseCode,
                        'purchase_response_code' => $runtime->approvedProviderResult()->purchaseResponseCode,
                        'response_code' => 'local_commit_failed_after_provider_approved',
                        'response_message' => $e->getMessage(),
                        'raw_response' => CheckoutPayloadSanitizer::sanitize($runtime->approvedProviderResult()->rawResponse),
                    ], 'local_commit_failed_after_provider_approved');

                    Logger::event('payment_approved_local_commit_failed', [
                        'correlation_id' => $existingTransaction['correlation_id'] ?? $runtime->correlationId(),
                        'checkout_transaction_id' => $runtime->transactionId(),
                        'checkout_public_id' => $existingTransaction['public_id'] ?? null,
                        'authorization_number' => $runtime->approvedProviderResult()->authorizationNumber,
                        'purchase_response_code' => $runtime->approvedProviderResult()->purchaseResponseCode,
                    ], 'PAYMENTS', Logger::ERROR);
                } elseif ($existingTransaction && !CheckoutTransactionStatus::isTerminal($existingTransaction['status'])) {
                    $this->bestEffortMarkError($transactionsDatabase, $runtime->transactionId(), $runtime->idempotencyKey(), [
                        'provider' => $existingTransaction['provider'] ?: 'local',
                        'provider_transaction_id' => $existingTransaction['provider_transaction_id'] ?? null,
                        'authorization_number' => $existingTransaction['authorization_number'] ?? null,
                        'transaction_link_id' => $existingTransaction['transaction_link_id'] ?? null,
                        'authorization_response_code' => $existingTransaction['authorization_response_code'] ?? null,
                        'purchase_response_code' => $existingTransaction['purchase_response_code'] ?? null,
                        'response_code' => 'local_exception',
                        'response_message' => $e->getMessage(),
                        'raw_response' => ['exception' => $e->getMessage()],
                    ], 'local_exception');
                }
            } catch (Throwable $markErrorException) {
                Logger::event('payment_mark_error_failed', [
                    'correlation_id' => $runtime->correlationId(),
                    'checkout_transaction_id' => $runtime->transactionId(),
                    'error' => $markErrorException->getMessage(),
                    'original_error' => $e->getMessage(),
                ], 'PAYMENTS', Logger::ERROR);
            }
        }

        Logger::event('payment_error', [
            'correlation_id' => $runtime->correlationId(),
            'error' => $e->getMessage(),
        ], 'PAYMENTS', Logger::ERROR);

        return [
            'httpStatus' => 500,
            'payload' => $this->responseFactory->buildInternalErrorPayload($runtime->correlationId()),
        ];
    }

    private function bestEffortMarkError(CheckoutTransactionsRepository $transactionsDatabase, int $transactionId, string $idempotencyKey, array $transitionData, string $transitionContext): void
    {
        if ($transactionsDatabase->markError($transactionId, $transitionData)) {
            return;
        }

        $reloadedTransaction = $transactionsDatabase->findByIdempotencyKey($idempotencyKey);
        if ($reloadedTransaction !== null && CheckoutTransactionStatus::isTerminal($reloadedTransaction['status'])) {
            $this->logTransitionSkipped($reloadedTransaction, 'error', $transitionContext);
            return;
        }

        $this->logTransitionFailure($transactionId, $idempotencyKey, $reloadedTransaction, 'error', $transitionContext);
    }

    private function logTransitionSkipped(array $transaction, string $desiredTransition, string $transitionContext): void
    {
        Logger::event('payment_transaction_status_transition_skipped', [
            'correlation_id' => $transaction['correlation_id'],
            'idempotency_key' => $transaction['idempotency_key'],
            'checkout_transaction_id' => (int) $transaction['id'],
            'checkout_public_id' => $transaction['public_id'],
            'desired_transition' => $desiredTransition,
            'reloaded_status' => $transaction['status'],
            'transition_context' => $transitionContext,
        ], 'PAYMENTS', Logger::WARNING);
    }

    private function logTransitionFailure(int $transactionId, string $idempotencyKey, ?array $transaction, string $desiredTransition, string $transitionContext): void
    {
        Logger::event('payment_transaction_status_transition_failed', [
            'correlation_id' => $transaction['correlation_id'] ?? null,
            'idempotency_key' => $idempotencyKey,
            'checkout_transaction_id' => $transaction['id'] ?? $transactionId,
            'checkout_public_id' => $transaction['public_id'] ?? null,
            'desired_transition' => $desiredTransition,
            'reloaded_status' => $transaction['status'] ?? null,
            'transition_context' => $transitionContext,
        ], 'PAYMENTS', Logger::ERROR);
    }
}
