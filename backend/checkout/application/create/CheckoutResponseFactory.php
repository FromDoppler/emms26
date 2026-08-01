<?php

class CheckoutResponseFactory
{
    public function fromTransaction(array $transaction, ?string $error = null): array
    {
        $status = $transaction['status'];
        $payload = [
            'success' => $status === CheckoutTransactionStatus::APPROVED,
            'status' => $status,
            'payment' => [
                'paymentId' => $transaction['payment_id'],
                'status' => $status,
                'finalAmount' => (string) $transaction['final_amount'],
                'currency' => $transaction['currency'],
                'ticketName' => $transaction['ticket_name'],
                'paymentMethod' => $transaction['payment_method'],
                'createdAt' => $transaction['created_at'],
            ],
            'correlationId' => $transaction['correlation_id'],
        ];

        $error = $error ?: $this->errorFor($transaction);
        if ($error !== null) {
            $payload['error'] = $error;
        }
        return $payload;
    }

    public function processing(array $transaction, ?string $error = null): array
    {
        return $this->fromTransaction($transaction, $error);
    }

    public function internal(string $correlationId): array
    {
        return ['success' => false, 'error' => 'internal_error', 'correlationId' => $correlationId];
    }

    public function intentConflict(): array
    {
        return ['success' => false, 'error' => 'payment_intent_conflict', 'correlationId' => null];
    }

    public function validation(string $correlationId, string $error = 'validation_error', ?array $transaction = null): array
    {
        if ($transaction !== null) {
            return $this->fromTransaction($transaction, $error);
        }
        return ['success' => false, 'error' => $error, 'correlationId' => $correlationId];
    }

    private function errorFor(array $transaction): ?string
    {
        if ($transaction['status'] === CheckoutTransactionStatus::ERROR) {
            return 'payment_error';
        }
        if ($transaction['status'] === CheckoutTransactionStatus::REJECTED) {
            return $transaction['response_code'] ?: 'provider_rejected';
        }
        return null;
    }
}
