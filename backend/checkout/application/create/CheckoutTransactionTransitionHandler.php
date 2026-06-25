<?php

class CheckoutTransactionTransitionHandler
{
    private $transactions;
    private $responseFactory;

    public function __construct(
        CheckoutTransactionsRepository $transactions,
        CheckoutResponseFactory $responseFactory
    ) {
        $this->transactions = $transactions;
        $this->responseFactory = $responseFactory;
    }

    public function handleRejectedTransactionTransition(int $transactionId, string $idempotencyKey, array $transitionData, string $desiredTransition, int $successHttpStatus, string $transitionContext): array
    {
        if ($this->transactions->markRejected($transactionId, $transitionData)) {
            $transaction = $this->reloadTransactionOrFail($idempotencyKey);
            $this->logRejectedTransaction($transaction);

            return [
                'httpStatus' => $successHttpStatus,
                'payload' => $this->responseFactory->buildCreatePaymentResponse($transaction),
            ];
        }

        $reloadedTransaction = $this->transactions->findByIdempotencyKey($idempotencyKey);
        if ($reloadedTransaction === null) {
            $this->logTransitionFailure($transactionId, $idempotencyKey, null, $desiredTransition, $transitionContext);
            throw new Exception('invalid_payment_transaction_status_transition');
        }

        if (!CheckoutTransactionStatus::isTerminal($reloadedTransaction['status'])) {
            $this->logTransitionFailure($transactionId, $idempotencyKey, $reloadedTransaction, $desiredTransition, $transitionContext);
            throw new Exception('invalid_payment_transaction_status_transition');
        }

        $this->logTransitionSkipped($reloadedTransaction, $desiredTransition, $transitionContext);

        return [
            'httpStatus' => $successHttpStatus,
            'payload' => $this->responseFactory->buildCreatePaymentResponse($reloadedTransaction),
        ];
    }

    public function handleErrorTransactionTransition(int $transactionId, string $idempotencyKey, array $transitionData, string $transitionContext): array
    {
        if ($this->transactions->markError($transactionId, $transitionData)) {
            $transaction = $this->reloadTransactionOrFail($idempotencyKey);

            return [
                'httpStatus' => 500,
                'payload' => $this->responseFactory->buildCreatePaymentResponse($transaction, true),
            ];
        }

        $reloadedTransaction = $this->transactions->findByIdempotencyKey($idempotencyKey);
        if ($reloadedTransaction === null) {
            $this->logTransitionFailure($transactionId, $idempotencyKey, null, 'error', $transitionContext);
            throw new Exception('invalid_payment_transaction_status_transition');
        }

        if (!CheckoutTransactionStatus::isTerminal($reloadedTransaction['status'])) {
            $this->logTransitionFailure($transactionId, $idempotencyKey, $reloadedTransaction, 'error', $transitionContext);
            throw new Exception('invalid_payment_transaction_status_transition');
        }

        $this->logTransitionSkipped($reloadedTransaction, 'error', $transitionContext);

        return [
            'httpStatus' => 200,
            'payload' => $this->responseFactory->buildCreatePaymentResponse($reloadedTransaction),
        ];
    }

    private function reloadTransactionOrFail(string $idempotencyKey): array
    {
        $transaction = $this->transactions->findByIdempotencyKey($idempotencyKey);
        if ($transaction === null) {
            throw new Exception('invalid_payment_transaction_status_transition');
        }

        return $transaction;
    }

    private function logRejectedTransaction(array $transaction): void
    {
        Logger::event('payment_rejected', [
            'correlation_id' => $transaction['correlation_id'],
            'checkout_transaction_id' => (int) $transaction['id'],
            'checkout_public_id' => $transaction['public_id'],
            'response_code' => $transaction['response_code'],
            'provider' => $transaction['provider'],
        ], 'PAYMENTS', Logger::WARNING);
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
