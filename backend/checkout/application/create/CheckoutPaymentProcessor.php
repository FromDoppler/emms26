<?php

class CheckoutPaymentProcessor
{
    private $transactions;
    private $providerClient;
    private $postCheckoutService;
    private $freshCompletionFactory;
    private $responses;

    public function __construct(
        CheckoutTransactionsRepository $transactions,
        PaymentProviderClient $providerClient,
        PostCheckoutService $postCheckoutService,
        callable $freshCompletionFactory,
        CheckoutResponseFactory $responses
    ) {
        $this->transactions = $transactions;
        $this->providerClient = $providerClient;
        $this->postCheckoutService = $postCheckoutService;
        $this->freshCompletionFactory = $freshCompletionFactory;
        $this->responses = $responses;
    }

    public function process(array $context, array $transaction): array
    {
        if ($transaction['payment_method'] === 'coupon') {
            return $this->complete($this->postCheckoutService, $context, $transaction['payment_id']);
        }

        $request = new ProviderPaymentRequest([
            'paymentId' => $transaction['payment_id'],
            'correlationId' => $transaction['correlation_id'],
            'checkoutTransactionId' => (int) $transaction['id'],
            'customerId' => (int) $transaction['id'],
            'finalAmount' => $transaction['final_amount'],
            'currency' => $transaction['currency'],
            'customerEmail' => $transaction['customer_email'],
            'customerName' => $transaction['customer_name'],
            'worldPayLowValueToken' => trim((string) ($context['input']['payment']['worldPayLowValueToken'] ?? '')),
            'ccExpMonth' => $context['input']['payment']['ccExpMonth'] ?? null,
            'ccExpYear' => $context['input']['payment']['ccExpYear'] ?? null,
            'ccType' => $context['input']['payment']['ccType'] ?? null,
        ]);

        $result = $this->providerClient->purchase($request);
        if ($result->status === ProviderPaymentResult::UNKNOWN) {
            return $this->processing($transaction);
        }
        if ($result->status === ProviderPaymentResult::ERROR) {
            $this->transactions->markErrorBeforeProvider($transaction['payment_id'], 'payment_error');
            return $this->reloadResponse($this->transactions, $transaction['payment_id'], 200);
        }

        $evidence = $this->evidence($result);
        if ($result->status === ProviderPaymentResult::REJECTED) {
            $this->transactions->markProviderRejected(
                $transaction['payment_id'],
                $evidence,
                $this->rejectionResponseCode($result)
            );
            return $this->reloadResponse($this->transactions, $transaction['payment_id'], 200);
        }
        if ($result->status !== ProviderPaymentResult::APPROVED) {
            return $this->processing($transaction);
        }

        return $this->persistMarkerAndComplete($context, $transaction, $evidence);
    }

    public function completeExisting(array $context, array $transaction): array
    {
        return $this->complete($this->postCheckoutService, $context, $transaction['payment_id']);
    }

    private function persistMarkerAndComplete(array $context, array $transaction, array $evidence): array
    {
        try {
            $this->transactions->persistApprovalMarker($transaction['payment_id'], $evidence);
            $reloaded = $this->transactions->findByPaymentId($transaction['payment_id']);
        } catch (Throwable $e) {
            return $this->recoverMarkerOnce($context, $transaction['payment_id'], $evidence);
        }

        $classified = $this->classifyMarkerState(
            $this->postCheckoutService,
            $context,
            $reloaded,
            $transaction['payment_id']
        );
        return $classified ?: $this->processing($reloaded ?: $transaction);
    }

    private function recoverMarkerOnce(array $context, string $paymentId, array $evidence): array
    {
        $factory = $this->freshCompletionFactory;
        $fresh = $factory();
        $transaction = $fresh['transactions']->findByPaymentId($paymentId);
        $classified = $this->classifyMarkerState($fresh['completion'], $context, $transaction, $paymentId);
        if ($classified !== null) {
            return $classified;
        }

        try {
            $fresh['transactions']->persistApprovalMarker($paymentId, $evidence);
        } catch (Throwable $e) {
            $factory = $this->freshCompletionFactory;
            $last = $factory();
            $transaction = $last['transactions']->findByPaymentId($paymentId);
            $classified = $this->classifyMarkerState($last['completion'], $context, $transaction, $paymentId);
            return $classified ?: $this->processing($transaction);
        }

        $transaction = $fresh['transactions']->findByPaymentId($paymentId);
        $classified = $this->classifyMarkerState($fresh['completion'], $context, $transaction, $paymentId);
        return $classified ?: $this->processing($transaction);
    }

    private function complete(PostCheckoutService $service, array $context, string $paymentId): array
    {
        $transaction = $service->completeApprovedPayment($context, $paymentId);
        if (!CheckoutTransactionStatus::isConsistent($transaction)
            || $transaction['status'] !== CheckoutTransactionStatus::APPROVED) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id'] ?? 'corr_unknown'
            )];
        }
        return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($transaction)];
    }

    private function reloadResponse(CheckoutTransactionsRepository $repository, string $paymentId, int $httpStatus): array
    {
        $transaction = $repository->findByPaymentId($paymentId);
        if ($transaction === null || !CheckoutTransactionStatus::isConsistent($transaction)) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id'] ?? 'corr_unknown'
            )];
        }
        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING) {
            $httpStatus = 202;
        }
        return ['httpStatus' => $httpStatus, 'payload' => $this->responses->fromTransaction($transaction)];
    }

    private function processing(?array $transaction, ?string $error = null): array
    {
        if ($transaction === null || $transaction['status'] !== CheckoutTransactionStatus::PROCESSING) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id'] ?? 'corr_unknown'
            )];
        }
        return ['httpStatus' => 202, 'payload' => $this->responses->processing($transaction, $error)];
    }

    private function evidence(ProviderPaymentResult $result): array
    {
        return [
            'provider' => $result->provider,
            'authorization_number' => $result->authorizationNumber,
            'transaction_link_id' => $result->transactionLinkId,
            'authorization_response_code' => $result->authorizationResponseCode,
            'purchase_response_code' => $result->purchaseResponseCode,
        ];
    }

    private function rejectionResponseCode(ProviderPaymentResult $result): string
    {
        $code = $result->purchaseResponseCode
            ?: ($result->authorizationResponseCode ?: $result->responseCode);

        return CheckoutProviderRejectionCatalog::categoryFor($code) ?? 'provider_rejected';
    }

    private function classifyMarkerState(
        PostCheckoutService $completion,
        array $context,
        ?array $transaction,
        string $paymentId
    ): ?array {
        if ($transaction === null || !CheckoutTransactionStatus::isConsistent($transaction)) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id'] ?? 'corr_unknown'
            )];
        }
        if (CheckoutTransactionStatus::isTerminal($transaction['status'])) {
            return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($transaction)];
        }
        if ($this->hasValidApprovalMarker($transaction)) {
            Logger::event('payment_provider_approval_persisted', [
                'payment_id' => $paymentId,
                'correlation_id' => $transaction['correlation_id'],
            ], 'PAYMENTS', Logger::INFO);
            return $this->complete($completion, $context, $paymentId);
        }
        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING
            && empty($transaction['provider_approved_at'])
            && CheckoutTransactionStatus::hasNoProviderEvidence($transaction)) {
            return null;
        }
        return ['httpStatus' => 500, 'payload' => $this->responses->internal($transaction['correlation_id'])];
    }

    private function hasValidApprovalMarker(array $transaction): bool
    {
        return $transaction['status'] === CheckoutTransactionStatus::PROCESSING
            && !empty($transaction['provider_approved_at']);
    }
}
