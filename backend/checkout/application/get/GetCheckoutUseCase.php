<?php

class GetCheckoutUseCase
{
    private $transactions;

    public function __construct(CheckoutTransactionsRepository $transactions)
    {
        $this->transactions = $transactions;
    }

    public function execute(string $publicId): array
    {
        $transaction = $this->transactions->findByPublicId($publicId);
        if (!$transaction) {
            return [
                'httpStatus' => 404,
                'payload' => [
                    'success' => false,
                    'error' => 'payment_not_found',
                ],
            ];
        }

        $payment = [
            'publicId' => $transaction['public_id'],
            'status' => $transaction['status'],
            'ticketName' => $transaction['ticket_name'],
            'customerName' => $transaction['customer_name'],
            'finalAmount' => (float) $transaction['final_amount'],
            'amount' => (float) $transaction['amount'],
            'discountAmount' => (float) $transaction['discount_amount'],
            'currency' => $transaction['currency'],
            'paymentMethod' => $transaction['payment_method'],
            'createdAt' => $transaction['created_at'],
        ];

        if ($transaction['status'] === CheckoutTransactionStatus::APPROVED) {
            $payment['customerEmail'] = $transaction['customer_email'];
        }

        $payload = [
            'success' => $transaction['status'] === CheckoutTransactionStatus::APPROVED,
            'payment' => $payment,
            'correlationId' => $transaction['correlation_id'],
        ];

        if ($transaction['status'] === CheckoutTransactionStatus::ERROR) {
            $payload['error'] = 'payment_error';
        }

        return [
            'httpStatus' => 200,
            'payload' => $payload,
        ];
    }
}
