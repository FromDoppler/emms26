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
            'worldPayLowValueToken' => $context['input']['payment']['worldPayLowValueToken'] ?? '',
            'ccExpMonth' => $context['input']['payment']['ccExpMonth'] ?? null,
            'ccExpYear' => $context['input']['payment']['ccExpYear'] ?? null,
            'ccType' => $context['input']['payment']['ccType'] ?? null,
        ]);

        $result = $this->providerClient->purchase($request);
        if ($result->status === ProviderPaymentResult::UNKNOWN) {
            return $this->processing($transaction);
        }
        $evidence = $this->evidence($result);
        if ($result->status === ProviderPaymentResult::ERROR) {
            if ($this->isPurchaseRejectedBeforeHandler($result)) {
                return $this->persistKnownTerminalOutcome(
                    $context,
                    $transaction,
                    'error',
                    function (CheckoutTransactionsRepository $repository) use ($transaction, $evidence): bool {
                        return $repository->markPurchaseNotStartedError(
                            $transaction['payment_id'],
                            $evidence,
                            'payment_error'
                        );
                    }
                );
            }

            return $this->persistKnownTerminalOutcome(
                $context,
                $transaction,
                'error',
                function (CheckoutTransactionsRepository $repository) use ($transaction): bool {
                    return $repository->markErrorBeforeProvider($transaction['payment_id'], 'payment_error');
                }
            );
        }

        if ($result->status === ProviderPaymentResult::REJECTED) {
            return $this->persistKnownTerminalOutcome(
                $context,
                $transaction,
                'rejected',
                function (CheckoutTransactionsRepository $repository) use ($transaction, $evidence, $result): bool {
                    return $repository->markProviderRejected(
                        $transaction['payment_id'],
                        $evidence,
                        $this->rejectionResponseCode($result)
                    );
                }
            );
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
            $persisted = $this->transactions->persistApprovalMarker($transaction['payment_id'], $evidence);
        } catch (Throwable $e) {
            return $this->recoverMarkerOnce($context, $transaction['payment_id'], $evidence);
        }

        if (!$persisted) {
            return $this->recoverMarkerOnce($context, $transaction['payment_id'], $evidence);
        }

        $state = $this->readRecoveryState(
            $transaction['payment_id'],
            'approved_marker',
            'marker_read',
            $this->currentRecoveryState()
        );
        if ($state === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id']
            )];
        }

        $classified = $this->classifyMarkerState(
            $state['completion'],
            $context,
            $state['transaction'],
            $transaction['payment_id']
        );
        return $classified ?: $this->recoverMarkerOnce($context, $transaction['payment_id'], $evidence);
    }

    private function recoverMarkerOnce(array $context, string $paymentId, array $evidence): array
    {
        $state = $this->readRecoveryState($paymentId, 'approved_marker', 'marker_fresh_read');
        if ($state === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal('corr_unknown')];
        }

        $classified = $this->classifyMarkerState(
            $state['completion'],
            $context,
            $state['transaction'],
            $paymentId
        );
        if ($classified !== null) {
            return $classified;
        }

        try {
            $persisted = $state['transactions']->persistApprovalMarker($paymentId, $evidence);
        } catch (Throwable $e) {
            $persisted = false;
        }

        $confirmed = $persisted
            ? $this->readRecoveryState(
                $paymentId,
                'approved_marker',
                'marker_confirmed_read',
                $state
            )
            : $this->readRecoveryState($paymentId, 'approved_marker', 'marker_last_read');

        if ($confirmed === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $state['transaction']['correlation_id']
            )];
        }

        $classified = $this->classifyMarkerState(
            $confirmed['completion'],
            $context,
            $confirmed['transaction'],
            $paymentId
        );
        return $classified ?: $this->processing($confirmed['transaction']);
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

    private function persistKnownTerminalOutcome(
        array $context,
        array $transaction,
        string $terminalType,
        callable $persist
    ): array {
        $paymentId = $transaction['payment_id'];
        $firstAttempt = $this->attemptTerminalPersist($this->transactions, $persist);
        if (!$firstAttempt['persisted']) {
            $this->logTerminalCasFailure(
                'payment_terminal_first_cas_failed',
                $paymentId,
                $transaction['correlation_id'],
                $terminalType,
                $firstAttempt['error']
            );
        }

        $state = $firstAttempt['persisted']
            ? $this->readRecoveryState(
                $paymentId,
                $terminalType,
                'first_read',
                $this->currentRecoveryState()
            )
            : $this->readRecoveryState($paymentId, $terminalType, 'first_recovery');

        if ($state === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id']
            )];
        }

        $classified = $this->classifyKnownTerminalState(
            $context,
            $state['transaction'],
            $state['completion'],
            $paymentId,
            $terminalType
        );
        if ($classified !== null) {
            return $classified;
        }

        if (!$this->canRetryTerminalRecovery($state['transaction'])) {
            return $this->processing($state['transaction']);
        }

        $secondState = $this->readRecoveryState($paymentId, $terminalType, 'second_precheck');
        if ($secondState === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id']
            )];
        }

        $classified = $this->classifyKnownTerminalState(
            $context,
            $secondState['transaction'],
            $secondState['completion'],
            $paymentId,
            $terminalType
        );
        if ($classified !== null) {
            return $classified;
        }

        if (!$this->canRetryTerminalRecovery($secondState['transaction'])) {
            return $this->processing($secondState['transaction']);
        }

        Logger::event('payment_terminal_second_cas_attempted', [
            'payment_id' => $paymentId,
            'correlation_id' => $transaction['correlation_id'],
            'terminal_type' => $terminalType,
        ], 'PAYMENTS', Logger::INFO);

        $secondAttempt = $this->attemptTerminalPersist($secondState['transactions'], $persist);
        if (!$secondAttempt['persisted']) {
            $this->logTerminalCasFailure(
                'payment_terminal_second_cas_failed',
                $paymentId,
                $transaction['correlation_id'],
                $terminalType,
                $secondAttempt['error']
            );
        }

        $confirmed = $secondAttempt['persisted']
            ? $this->readRecoveryState(
                $paymentId,
                $terminalType,
                'second_read',
                $secondState
            )
            : $this->readRecoveryState($paymentId, $terminalType, 'second_recovery');

        if ($confirmed === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id']
            )];
        }

        $classified = $this->classifyKnownTerminalState(
            $context,
            $confirmed['transaction'],
            $confirmed['completion'],
            $paymentId,
            $terminalType
        );
        if ($classified !== null) {
            if ($this->isExpectedTerminal($confirmed['transaction'], $terminalType)) {
                Logger::event('payment_terminal_recovery_confirmed', [
                    'payment_id' => $paymentId,
                    'correlation_id' => $transaction['correlation_id'],
                    'terminal_type' => $terminalType,
                ], 'PAYMENTS', Logger::INFO);
            }
            return $classified;
        }

        Logger::event('payment_terminal_recovery_not_confirmed', [
            'payment_id' => $paymentId,
            'correlation_id' => $transaction['correlation_id'],
            'terminal_type' => $terminalType,
        ], 'PAYMENTS', Logger::WARNING);

        return $this->processing($confirmed['transaction']);
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

    private function canRetryTerminalRecovery(?array $transaction): bool
    {
        return $transaction !== null
            && CheckoutTransactionStatus::isConsistent($transaction)
            && $transaction['status'] === CheckoutTransactionStatus::PROCESSING
            && empty($transaction['provider_approved_at'])
            && CheckoutTransactionStatus::hasNoProviderEvidence($transaction);
    }

    private function attemptTerminalPersist(CheckoutTransactionsRepository $repository, callable $persist): array
    {
        try {
            return ['persisted' => (bool) $persist($repository), 'error' => null];
        } catch (Throwable $e) {
            return ['persisted' => false, 'error' => $e];
        }
    }

    private function currentRecoveryState(): array
    {
        return [
            'transactions' => $this->transactions,
            'completion' => $this->postCheckoutService,
        ];
    }

    private function readRecoveryState(
        string $paymentId,
        string $terminalType,
        string $phase,
        ?array $preferredState = null
    ): ?array {
        if ($preferredState !== null) {
            $state = $this->tryReadRecoveryState(
                $preferredState,
                $paymentId,
                $terminalType,
                $phase,
                'preferred'
            );
            if ($state !== null) {
                return $state;
            }
        }

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $factory = $this->freshCompletionFactory;
                $freshState = $factory();
            } catch (Throwable $e) {
                $this->logTerminalReadFailure(
                    $paymentId,
                    $terminalType,
                    $phase,
                    'fresh_factory_' . $attempt,
                    $e
                );
                continue;
            }

            $state = $this->tryReadRecoveryState(
                $freshState,
                $paymentId,
                $terminalType,
                $phase,
                'fresh_' . $attempt
            );
            if ($state !== null) {
                return $state;
            }
        }

        return null;
    }

    private function tryReadRecoveryState(
        array $state,
        string $paymentId,
        string $terminalType,
        string $phase,
        string $repositoryScope
    ): ?array {
        try {
            $transaction = $state['transactions']->findByPaymentId($paymentId);
        } catch (Throwable $e) {
            $this->logTerminalReadFailure(
                $paymentId,
                $terminalType,
                $phase,
                $repositoryScope,
                $e
            );
            return null;
        }

        if ($transaction === null) {
            return null;
        }

        return [
            'transaction' => $transaction,
            'transactions' => $state['transactions'],
            'completion' => $state['completion'],
        ];
    }

    private function logTerminalCasFailure(
        string $event,
        string $paymentId,
        string $correlationId,
        string $terminalType,
        ?Throwable $error
    ): void {
        Logger::event($event, [
            'payment_id' => $paymentId,
            'correlation_id' => $correlationId,
            'terminal_type' => $terminalType,
            'error_type' => $error !== null ? get_class($error) : 'repository_write_conflict',
            'error_code' => $error !== null ? $this->safeRecoveryErrorCode($error) : 'terminal_cas_no_rows',
        ], 'PAYMENTS', Logger::WARNING);
    }

    private function logTerminalReadFailure(
        string $paymentId,
        string $terminalType,
        string $phase,
        string $repositoryScope,
        Throwable $error
    ): void {
        Logger::event('payment_terminal_recovery_read_failed', [
            'payment_id' => $paymentId,
            'terminal_type' => $terminalType,
            'phase' => $phase,
            'repository_scope' => $repositoryScope,
            'error_type' => get_class($error),
            'error_code' => $this->safeRecoveryErrorCode($error),
        ], 'PAYMENTS', Logger::WARNING);
    }

    private function safeRecoveryErrorCode(Throwable $error): string
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

    private function classifyKnownTerminalState(
        array $context,
        ?array $transaction,
        PostCheckoutService $completion,
        string $paymentId,
        string $terminalType
    ): ?array {
        if ($transaction === null) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal('corr_unknown')];
        }
        if (!CheckoutTransactionStatus::isConsistent($transaction)) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal(
                $transaction['correlation_id'] ?? 'corr_unknown'
            )];
        }
        if (CheckoutTransactionStatus::isTerminal($transaction['status'])) {
            Logger::event('payment_terminal_ledger_already_terminal', [
                'payment_id' => $paymentId,
                'correlation_id' => $transaction['correlation_id'],
                'terminal_type' => $terminalType,
            ], 'PAYMENTS', Logger::INFO);
            return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($transaction)];
        }
        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING
            && $transaction['payment_method'] === 'card'
            && !empty($transaction['provider_approved_at'])) {
            return $this->classifyMarkerState($completion, $context, $transaction, $paymentId);
        }
        return null;
    }

    private function isExpectedTerminal(array $transaction, string $terminalType): bool
    {
        $expectedStatus = $terminalType === 'rejected'
            ? CheckoutTransactionStatus::REJECTED
            : CheckoutTransactionStatus::ERROR;

        return CheckoutTransactionStatus::isConsistent($transaction)
            && $transaction['status'] === $expectedStatus;
    }

    private function isPurchaseRejectedBeforeHandler(ProviderPaymentResult $result): bool
    {
        return $result->provider === 'doppler-payments-api'
            && $result->responseCode === 'provider_purchase_unauthorized'
            && $result->authorizationResponseCode === '000'
            && $result->purchaseResponseCode === null
            && $result->authorizationNumber === null;
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

        return CheckoutProviderRejectionCatalog::responseCodeFor($code);
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
                'provider' => $transaction['provider'],
                'authorization_number' => $transaction['authorization_number'],
                'transaction_link_id' => $transaction['transaction_link_id'],
                'authorization_response_code' => $transaction['authorization_response_code'],
                'purchase_response_code' => $transaction['purchase_response_code'],
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
