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

    public function handle(Throwable $error, ?string $paymentId, string $requestCorrelationId, array $requestIntent): array
    {
        Logger::event('payment_request_failed', [
            'payment_id' => $paymentId,
            'correlation_id' => $requestCorrelationId,
            'error_code' => $this->errorCodeFor($error),
            'error_type' => get_class($error),
        ], 'PAYMENTS', Logger::ERROR);

        if ($paymentId !== null) {
            try {
                $factory = $this->dbFactory;
                $repository = new CheckoutTransactionsRepository($factory());
                $transaction = $repository->findByPaymentId($paymentId);
                if ($transaction !== null && CheckoutTransactionStatus::isConsistent($transaction)) {
                    if (!$this->intentMatches($transaction, $requestIntent)) {
                        return ['httpStatus' => 409, 'payload' => $this->responses->intentConflict()];
                    }
                    if (!CheckoutTransactionStatus::isTerminal($transaction['status'])
                        && $transaction['status'] !== CheckoutTransactionStatus::PROCESSING) {
                        return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                            $transaction['correlation_id']
                        )];
                    }
                    $httpStatus = $transaction['status'] === CheckoutTransactionStatus::PROCESSING ? 202 : 200;
                    return ['httpStatus' => $httpStatus, 'payload' => $this->responses->fromTransaction($transaction)];
                }
            } catch (Throwable $reloadError) {
                Logger::event('payment_failure_ledger_reload_failed', [
                    'payment_id' => $paymentId,
                    'correlation_id' => $requestCorrelationId,
                    'error_code' => $this->errorCodeFor($error),
                    'error_type' => get_class($error),
                    'reload_error_code' => $this->errorCodeFor($reloadError),
                    'reload_error_type' => get_class($reloadError),
                ], 'PAYMENTS', Logger::ERROR);
            }
        }

        return ['httpStatus' => 500, 'payload' => $this->responses->internal($requestCorrelationId)];
    }

    private function intentMatches(array $transaction, array $requestIntent): bool
    {
        $transactionCoupon = CheckoutCouponCode::normalize(
            isset($transaction['coupon_code']) ? (string) $transaction['coupon_code'] : null
        );

        return (string) ($transaction['customer_email'] ?? '') === $requestIntent['customerEmail']
            && $transactionCoupon === $requestIntent['couponCode'];
    }

    private function errorCodeFor(Throwable $error): string
    {
        $message = trim((string) $error->getMessage());
        if ($message !== '' && preg_match('/^[a-z0-9_]+$/D', $message) === 1) {
            return $message;
        }

        $code = (int) $error->getCode();
        if ($code !== 0) {
            return 'exception_code_' . $code;
        }

        return 'unexpected_exception';
    }
}
