<?php

class CheckoutPaymentProcessor
{
    private $db;
    private $pricingService;
    private $providerClient;
    private $postCheckoutService;
    private $transitionHandler;
    private $responseFactory;

    public function __construct(
        DB $db,
        CheckoutPricingService $pricingService,
        PaymentProviderClient $providerClient,
        PostCheckoutService $postCheckoutService,
        CheckoutTransactionTransitionHandler $transitionHandler,
        CheckoutResponseFactory $responseFactory
    ) {
        $this->db = $db;
        $this->pricingService = $pricingService;
        $this->providerClient = $providerClient;
        $this->postCheckoutService = $postCheckoutService;
        $this->transitionHandler = $transitionHandler;
        $this->responseFactory = $responseFactory;
    }

    public function process(array $context, array $transaction, CheckoutExecutionRuntime $runtime): array
    {
        if ($context['pricing']['requiresPayment']) {
            return $this->processCardPayment($context, $transaction, $runtime);
        }

        return $this->processCouponPayment($context, $transaction, $runtime);
    }

    private function processCouponPayment(array $context, array $transaction, CheckoutExecutionRuntime $runtime): array
    {
        return $this->fulfillApprovedTransaction($context, $transaction, [
            'provider' => 'coupon',
            'provider_transaction_id' => null,
            'authorization_number' => null,
            'transaction_link_id' => null,
            'authorization_response_code' => null,
            'purchase_response_code' => null,
            'response_code' => 'coupon_approved',
            'response_message' => 'Approved by 100% coupon.',
            'raw_response' => [],
        ], $runtime);
    }

    private function processCardPayment(array $context, array $transaction, CheckoutExecutionRuntime $runtime): array
    {
        if (!empty($context['pricing']['coupon']) && empty($context['pricing']['couponAlreadyResolvedFromLedger'])) {
            $couponError = $this->pricingService->validateResolvedCoupon(
                $context['pricing']['coupon'],
                $context['eventContext'],
                (int) $context['pricing']['ticket']['id']
            );

            if ($couponError !== null) {
                return $this->transitionHandler->handleRejectedTransactionTransition((int) $transaction['id'], $runtime->idempotencyKey(), [
                    'provider' => 'doppler-payments-api',
                    'provider_transaction_id' => null,
                    'authorization_number' => null,
                    'transaction_link_id' => null,
                    'authorization_response_code' => null,
                    'purchase_response_code' => null,
                    'response_code' => $couponError,
                    'response_message' => $this->couponErrorMessage($couponError),
                    'raw_response' => [],
                ], $couponError, 422, 'coupon_validation');
            }
        }

        $paymentRequest = new ProviderPaymentRequest([
            'publicId' => $transaction['public_id'],
            'correlationId' => $transaction['correlation_id'],
            'idempotencyKey' => $transaction['idempotency_key'],
            'checkoutTransactionId' => (int) $transaction['id'],
            // Doppler expects an integer CustomerId; we reuse the local transaction id
            // as a technical reference, not as a business/customer identifier.
            'customerId' => (int) $transaction['id'],
            'finalAmount' => $context['pricing']['finalAmount'],
            'currency' => $context['pricing']['currency'],
            'customerEmail' => $context['customer']['email'],
            'customerName' => $context['customer']['firstname'],
            'worldPayLowValueToken' => trim((string) ($context['input']['payment']['worldPayLowValueToken'] ?? '')),
            'ccExpMonth' => $context['input']['payment']['ccExpMonth'] ?? null,
            'ccExpYear' => $context['input']['payment']['ccExpYear'] ?? null,
            'ccType' => $context['input']['payment']['ccType'] ?? 1,
        ]);

        // Pricing validation already checked that the resolved coupon is still active and in scope.
        $result = $this->providerClient->purchase($paymentRequest);

        if ($result->status === ProviderPaymentResult::APPROVED) {
            $runtime->markProviderApproved($result);
            return $this->fulfillApprovedTransaction($context, $transaction, [
                'provider' => $result->provider,
                'provider_transaction_id' => $result->providerTransactionId,
                'authorization_number' => $result->authorizationNumber,
                'transaction_link_id' => $result->transactionLinkId,
                'authorization_response_code' => $result->authorizationResponseCode,
                'purchase_response_code' => $result->purchaseResponseCode,
                'response_code' => $result->responseCode,
                'response_message' => $result->responseMessage,
                'raw_response' => CheckoutPayloadSanitizer::sanitize($result->rawResponse),
            ], $runtime);
        }

        if ($result->status === ProviderPaymentResult::REJECTED) {
            return $this->transitionHandler->handleRejectedTransactionTransition((int) $transaction['id'], $runtime->idempotencyKey(), [
                'provider' => $result->provider,
                'provider_transaction_id' => $result->providerTransactionId,
                'authorization_number' => $result->authorizationNumber,
                'transaction_link_id' => $result->transactionLinkId,
                'authorization_response_code' => $result->authorizationResponseCode,
                'purchase_response_code' => $result->purchaseResponseCode,
                'response_code' => $result->responseCode,
                'response_message' => $result->responseMessage,
                'raw_response' => CheckoutPayloadSanitizer::sanitize($result->rawResponse),
            ], 'provider_rejected', 200, 'provider_rejected');
        }

        return $this->transitionHandler->handleErrorTransactionTransition((int) $transaction['id'], $runtime->idempotencyKey(), [
            'provider' => $result->provider,
            'provider_transaction_id' => $result->providerTransactionId,
            'authorization_number' => $result->authorizationNumber,
            'transaction_link_id' => $result->transactionLinkId,
            'authorization_response_code' => $result->authorizationResponseCode,
            'purchase_response_code' => $result->purchaseResponseCode,
            'response_code' => $result->responseCode,
            'response_message' => $result->responseMessage,
            'raw_response' => CheckoutPayloadSanitizer::sanitize($result->rawResponse),
        ], 'provider_error');
    }

    private function fulfillApprovedTransaction(
        array $context,
        array $transaction,
        array $approvalData,
        CheckoutExecutionRuntime $runtime
    ): array {
        $this->db->beginTransaction();
        $runtime->markDbTransactionOpen();

        $fulfilledTransaction = $this->postCheckoutService->fulfillApprovedCheckout(
            $context,
            $transaction,
            $approvalData,
            $runtime
        );

        $this->db->commit();
        $runtime->markDbTransactionClosed();
        $runtime->markLocalCommitCompleted();

        $this->deferAfterApprovedCheckoutCommitted($fulfilledTransaction);

        return [
            'httpStatus' => 200,
            'payload' => $this->buildCreatePaymentResponse($fulfilledTransaction),
        ];
    }

    private function couponErrorMessage(string $couponError): string
    {
        $messages = [
            'coupon_invalid' => 'Coupon is invalid.',
            'coupon_inactive' => 'Coupon is inactive.',
            'coupon_expired' => 'Coupon expired.',
            'coupon_out_of_scope' => 'Coupon is out of scope.',
            'coupon_discount_type_unsupported' => 'Coupon discount type is unsupported.',
        ];

        return $messages[$couponError] ?? 'Coupon is no longer eligible.';
    }

    private function buildCreatePaymentResponse(array $transaction, bool $isTechnicalError = false): array
    {
        return $this->responseFactory->buildCreatePaymentResponse($transaction, $isTechnicalError);
    }

    private function deferAfterApprovedCheckoutCommitted(array $fulfilledTransaction): void
    {
        // Post-checkout inline effects are best effort and must not delay the
        // approved payment response. The durable work already happened before:
        // local commit, VIP access and job persistence.
        $postCheckoutService = $this->postCheckoutService;

        register_shutdown_function(static function () use ($postCheckoutService, $fulfilledTransaction): void {
            try {
                $postCheckoutService->afterApprovedCheckoutCommitted($fulfilledTransaction);
            } catch (Throwable $e) {
                Logger::event('post_checkout_after_commit_deferred_failed', [
                    'checkout_transaction_id' => $fulfilledTransaction['id'] ?? null,
                    'checkout_public_id' => $fulfilledTransaction['public_id'] ?? null,
                    'error' => $e->getMessage(),
                ], 'PAYMENTS', Logger::ERROR);
            }
        });
    }
}
