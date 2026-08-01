<?php

class GetCheckoutUseCase
{
    private $transactions;
    private $responses;

    public function __construct(CheckoutTransactionsRepository $transactions, CheckoutResponseFactory $responses)
    {
        $this->transactions = $transactions;
        $this->responses = $responses;
    }

    public function execute(string $paymentId): array
    {
        $paymentId = strtolower(trim($paymentId));
        if (!self::isUuid($paymentId)) {
            return ['httpStatus' => 422, 'payload' => ['success' => false, 'error' => 'validation_error']];
        }

        $transaction = $this->transactions->findByPaymentId($paymentId);
        if ($transaction === null) {
            return ['httpStatus' => 404, 'payload' => ['success' => false, 'error' => 'payment_not_found']];
        }
        if (!CheckoutTransactionStatus::isConsistent($transaction)) {
            return [
                'httpStatus' => 500,
                'payload' => $this->responses->internal($transaction['correlation_id']),
            ];
        }

        return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($transaction)];
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', strtolower($value)) === 1;
    }
}
