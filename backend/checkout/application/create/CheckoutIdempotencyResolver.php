<?php

class CheckoutIdempotencyResolver
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

    public function resolveIdempotentResponse(array $transaction): ?array
    {
        if (CheckoutTransactionStatus::isTerminal($transaction['status'])) {
            Logger::event('payment_idempotency_reused', [
                'correlation_id' => $transaction['correlation_id'],
                'checkout_transaction_id' => (int) $transaction['id'],
                'checkout_public_id' => $transaction['public_id'],
                'status' => $transaction['status'],
            ], 'PAYMENTS', Logger::INFO);

            return [
                'httpStatus' => 200,
                'payload' => $this->responseFactory->buildCreatePaymentResponse($transaction),
            ];
        }

        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING) {
            Logger::event('payment_idempotency_reused', [
                'correlation_id' => $transaction['correlation_id'],
                'checkout_transaction_id' => (int) $transaction['id'],
                'checkout_public_id' => $transaction['public_id'],
                'status' => $transaction['status'],
            ], 'PAYMENTS', Logger::INFO);

            return [
                'httpStatus' => 202,
                'payload' => $this->responseFactory->buildProcessingRetryPayload($transaction),
            ];
        }

        return null;
    }

    public function claimTransactionForProcessing(array &$transaction): ?array
    {
        if (!$this->transactions->claimProcessing((int) $transaction['id'])) {
            $reloadedTransaction = $this->transactions->findByIdempotencyKey($transaction['idempotency_key']);

            if ($reloadedTransaction !== null) {
                Logger::event('payment_idempotency_reused', [
                    'correlation_id' => $reloadedTransaction['correlation_id'],
                    'checkout_transaction_id' => (int) $reloadedTransaction['id'],
                    'checkout_public_id' => $reloadedTransaction['public_id'],
                    'status' => $reloadedTransaction['status'],
                ], 'PAYMENTS', Logger::INFO);

                if (CheckoutTransactionStatus::isTerminal($reloadedTransaction['status'])) {
                    $transaction = $reloadedTransaction;

                    return [
                        'httpStatus' => 200,
                        'payload' => $this->responseFactory->buildCreatePaymentResponse($transaction),
                    ];
                }

                if ($reloadedTransaction['status'] === CheckoutTransactionStatus::PROCESSING) {
                    $transaction = $reloadedTransaction;

                    return [
                        'httpStatus' => 202,
                        'payload' => $this->responseFactory->buildProcessingRetryPayload($transaction),
                    ];
                }
            }

            Logger::event('payment_claim_processing_failed_unexpectedly', [
                'correlation_id' => $reloadedTransaction['correlation_id'] ?? $transaction['correlation_id'],
                'idempotency_key' => $transaction['idempotency_key'],
                'checkout_transaction_id' => (int) ($reloadedTransaction['id'] ?? $transaction['id']),
                'checkout_public_id' => $reloadedTransaction['public_id'] ?? $transaction['public_id'],
                'original_status' => $transaction['status'],
                'reloaded_status' => $reloadedTransaction['status'] ?? null,
            ], 'PAYMENTS', Logger::WARNING);

            return [
                'httpStatus' => 500,
                'payload' => $this->responseFactory->buildInternalErrorPayload($reloadedTransaction['correlation_id'] ?? $transaction['correlation_id']),
            ];
        }

        Logger::event('payment_processing_started', [
            'correlation_id' => $transaction['correlation_id'],
            'checkout_transaction_id' => (int) $transaction['id'],
            'checkout_public_id' => $transaction['public_id'],
        ], 'PAYMENTS', Logger::INFO);

        return null;
    }
}
