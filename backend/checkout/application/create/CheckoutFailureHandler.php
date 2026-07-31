<?php

class CheckoutFailureHandler
{
    private $dbFactory;
    private $responses;

    public function __construct(callable $dbFactory, CheckoutResponseFactory $responses)
    {
        $this->dbFactory = $dbFactory;
        $this->responses = $responses;
    }

    public function handle(Throwable $error, ?string $paymentId, string $requestCorrelationId): array
    {
        Logger::event('payment_request_failed', [
            'payment_id' => $paymentId,
            'correlation_id' => $requestCorrelationId,
            'error_type' => get_class($error),
        ], 'PAYMENTS', Logger::ERROR);

        if ($paymentId !== null) {
            try {
                $factory = $this->dbFactory;
                $repository = new CheckoutTransactionsRepository($factory());
                $transaction = $repository->findByPaymentId($paymentId);
                if ($transaction !== null && CheckoutTransactionStatus::isConsistent($transaction)) {
                    if (!CheckoutTransactionStatus::isTerminal($transaction['status'])
                        && $transaction['status'] !== CheckoutTransactionStatus::PROCESSING) {
                        return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                            $transaction['correlation_id']
                        )];
                    }
                    $httpStatus = $transaction['status'] === CheckoutTransactionStatus::PROCESSING ? 202 : 200;
                    return ['httpStatus' => $httpStatus, 'payload' => $this->responses->fromTransaction($transaction)];
                }
            } catch (Throwable $ignored) {
            }
        }

        return ['httpStatus' => 500, 'payload' => $this->responses->internal($requestCorrelationId)];
    }
}
