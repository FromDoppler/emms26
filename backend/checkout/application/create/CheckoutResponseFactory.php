<?php

class CheckoutResponseFactory
{
    public function buildCreatePaymentResponse(array $transaction, bool $isTechnicalError = false): array
    {
        $payload = [
            'success' => $transaction['status'] === CheckoutTransactionStatus::APPROVED,
            'status' => $transaction['status'],
            'payment' => [
                'publicId' => $transaction['public_id'],
                'status' => $transaction['status'],
                'finalAmount' => (float) $transaction['final_amount'],
                'currency' => $transaction['currency'],
            ],
            'correlationId' => $transaction['correlation_id'],
        ];

        if ($isTechnicalError || $transaction['status'] === CheckoutTransactionStatus::ERROR) {
            $payload['error'] = 'payment_error';
        }

        return $payload;
    }

    public function buildInternalErrorPayload(string $correlationId): array
    {
        return [
            'success' => false,
            'error' => 'internal_error',
            'correlationId' => $correlationId,
        ];
    }

    public function buildProcessingRetryPayload(array $transaction): array
    {
        return [
            'success' => false,
            'status' => $transaction['status'],
            'payment' => [
                'publicId' => $transaction['public_id'],
                'status' => $transaction['status'],
            ],
            'correlationId' => $transaction['correlation_id'],
            'retryable' => $transaction['status'] === CheckoutTransactionStatus::PROCESSING,
        ];
    }
}
