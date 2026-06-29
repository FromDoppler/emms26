<?php

class CreateCheckoutUseCase
{
    private $pricingService;
    private $transactions;
    private $eligibilityService;
    private $eventContextResolver;
    private $idempotencyResolver;
    private $paymentProcessor;
    private $failureHandler;

    public function __construct(
        CheckoutPricingService $pricingService,
        CheckoutTransactionsRepository $transactions,
        CheckoutEligibilityService $eligibilityService,
        CheckoutEventContextResolver $eventContextResolver,
        CheckoutIdempotencyResolver $idempotencyResolver,
        CheckoutPaymentProcessor $paymentProcessor,
        CheckoutFailureHandler $failureHandler
    ) {
        $this->pricingService = $pricingService;
        $this->transactions = $transactions;
        $this->eligibilityService = $eligibilityService;
        $this->eventContextResolver = $eventContextResolver;
        $this->idempotencyResolver = $idempotencyResolver;
        $this->paymentProcessor = $paymentProcessor;
        $this->failureHandler = $failureHandler;
    }

    public function execute(array $input): array
    {
        $runtime = $this->initializeRuntime();

        try {
            $context = $this->prepareRequestContext($input, $runtime);
            if (isset($context['response'])) {
                return $context['response'];
            }

            $existingTransaction = $this->transactions->findByIdempotencyKey($runtime->idempotencyKey());
            if ($existingTransaction !== null) {
                $runtime->markTransactionCreated((int) $existingTransaction['id']);

                $conflictResponse = $this->validateIdempotentRequestMatchesTransaction($context, $existingTransaction, $runtime);
                if ($conflictResponse !== null) {
                    return $conflictResponse;
                }

                $idempotentResponse = $this->resolveIdempotentResponse($existingTransaction);
                if ($idempotentResponse !== null) {
                    return $idempotentResponse;
                }

                $context = $this->hydrateContextFromTransaction($context, $existingTransaction);

                $eligibilityResponse = $this->validateEligibility($context, $runtime);
                if ($eligibilityResponse !== null) {
                    return $eligibilityResponse;
                }

                $paymentValidationResponse = $this->validatePaymentRequirements($context, $runtime);
                if ($paymentValidationResponse !== null) {
                    return $paymentValidationResponse;
                }

                $claimResponse = $this->claimTransactionForProcessing($existingTransaction);
                if ($claimResponse !== null) {
                    return $claimResponse;
                }

                return $this->paymentProcessor->process($context, $existingTransaction, $runtime);
            }

            $context = $this->resolvePricingContext($context, $runtime);
            if (isset($context['response'])) {
                return $context['response'];
            }

            $eligibilityResponse = $this->validateEligibility($context, $runtime);
            if ($eligibilityResponse !== null) {
                return $eligibilityResponse;
            }

            $paymentValidationResponse = $this->validatePaymentRequirements($context, $runtime);
            if ($paymentValidationResponse !== null) {
                return $paymentValidationResponse;
            }

            $transaction = $this->createOrLoadPendingTransaction($context, $runtime);

            $idempotentResponse = $this->resolveIdempotentResponse($transaction);
            if ($idempotentResponse !== null) {
                return $idempotentResponse;
            }

            $claimResponse = $this->claimTransactionForProcessing($transaction);
            if ($claimResponse !== null) {
                return $claimResponse;
            }

            return $this->paymentProcessor->process($context, $transaction, $runtime);
        } catch (Throwable $e) {
            return $this->handleExecutionFailure($e, $runtime);
        }
    }

    private function initializeRuntime(): CheckoutExecutionRuntime
    {
        return CheckoutExecutionRuntime::start();
    }

    private function prepareRequestContext(array $input, CheckoutExecutionRuntime $runtime): array
    {
        $runtime->setIdempotencyKey(trim((string) ($input['checkout']['idempotencyKey'] ?? '')));
        if ($runtime->idempotencyKey() === '') {
            return [
                'response' => [
                    'httpStatus' => 422,
                    'payload' => [
                        'success' => false,
                        'error' => 'idempotency_key_required',
                        'correlationId' => $runtime->correlationId(),
                    ],
                ],
            ];
        }

        if (!$this->isValidIdempotencyKey($runtime->idempotencyKey())) {
            return [
                'response' => [
                    'httpStatus' => 422,
                    'payload' => [
                        'success' => false,
                        'error' => 'idempotency_key_invalid',
                        'correlationId' => $runtime->correlationId(),
                    ],
                ],
            ];
        }

        $eventContext = $this->eventContextResolver->resolve();
        $customer = $this->normalizeCustomer($input, $eventContext['eventFreeId'], $eventContext['eventPhase'], $runtime);
        if (isset($customer['response'])) {
            return $customer;
        }

        $origin = trim((string) (($input['checkout']['origin'] ?? $input['origin'] ?? 'checkout')));
        $input['customer']['country'] = $customer['country'];

        $sanitizedRawRequest = CheckoutPayloadSanitizer::sanitize($input);

        Logger::event('payment_request_received', [
            'correlation_id' => $runtime->correlationId(),
            'idempotency_key' => $runtime->idempotencyKey(),
            'event_key' => $eventContext['eventKey'],
            'requires_payment' => null,
        ], 'PAYMENTS', Logger::INFO);

        return [
            'input' => $input,
            'eventContext' => $eventContext,
            'customer' => $customer,
            'origin' => $origin,
            'sanitizedRawRequest' => $sanitizedRawRequest,
        ];
    }

    private function resolvePricingContext(array $context, CheckoutExecutionRuntime $runtime): array
    {
        $pricing = $this->pricingService->calculate($context['eventContext'], [
            'ticketCode' => $context['input']['ticketCode'] ?? null,
            'couponCode' => $context['input']['couponCode'] ?? null,
            'customerEmail' => $context['customer']['email'],
        ]);

        if (!$pricing['success']) {
            return [
                'response' => [
                    'httpStatus' => 422,
                    'payload' => [
                        'success' => false,
                        'error' => $pricing['error'],
                        'ticket' => $pricing['ticket'] ?? null,
                        'availableTickets' => $pricing['availableTickets'] ?? [],
                        'correlationId' => $runtime->correlationId(),
                    ],
                ],
            ];
        }

        $context['pricing'] = $pricing;
        return $context;
    }

    private function validateEligibility(array $context, CheckoutExecutionRuntime $runtime): ?array
    {
        $eligibilityError = $this->eligibilityService->validate(
            $context['eventContext'],
            $context['customer']
        );

        if ($eligibilityError === null) {
            return null;
        }

        return [
            'httpStatus' => $eligibilityError['httpStatus'],
            'payload' => [
                'success' => false,
                'error' => $eligibilityError['error'],
                'correlationId' => $runtime->correlationId(),
            ],
        ];
    }

    private function createOrLoadPendingTransaction(array $context, CheckoutExecutionRuntime $runtime): array
    {
        $transaction = $this->transactions->createPendingTransaction([
            'public_id' => $this->generatePublicId(),
            'correlation_id' => $runtime->correlationId(),
            'idempotency_key' => $runtime->idempotencyKey(),
            'provider' => $context['pricing']['requiresPayment'] ? 'doppler-payments-api' : 'coupon',
            'payment_method' => $context['pricing']['requiresPayment'] ? 'card' : 'coupon',
            'origin' => $context['origin'],
            'customer_email' => $context['customer']['email'],
            'customer_name' => $context['customer']['firstname'],
            'customer_phone' => $context['customer']['phone'],
            'ticket_id' => (int) $context['pricing']['ticket']['id'],
            'ticket_code' => $context['pricing']['ticket']['code'],
            'ticket_name' => $context['pricing']['ticket']['name'],
            'coupon_id' => $context['pricing']['coupon']['id'] ?? null,
            'coupon_code' => $context['pricing']['coupon']['code'] ?? null,
            'coupon_link_code' => $context['pricing']['coupon']['link_code'] ?? null,
            'discount_type' => $context['pricing']['coupon']['discount_type'] ?? null,
            'discount_value' => $context['pricing']['coupon']['discount_value'] ?? 0,
            'amount' => $context['pricing']['amount'],
            'discount_amount' => $context['pricing']['discountAmount'],
            'final_amount' => $context['pricing']['finalAmount'],
            'currency' => $context['pricing']['currency'],
            'event_key' => $context['eventContext']['eventKey'],
            'event_free_id' => $context['eventContext']['eventFreeId'],
            'event_vip_id' => $context['eventContext']['eventVipId'],
            'raw_request' => $context['sanitizedRawRequest'],
        ]);

        $runtime->markTransactionCreated((int) $transaction['id']);
        return $transaction;
    }

    private function validatePaymentRequirements(array $context, CheckoutExecutionRuntime $runtime): ?array
    {
        if (empty($context['pricing']['requiresPayment'])) {
            return null;
        }

        $payment = $context['input']['payment'] ?? [];
        $emptyField = null;

        if (trim((string) ($payment['worldPayLowValueToken'] ?? '')) === '') {
            $emptyField = 'payment.worldPayLowValueToken';
        } elseif (trim((string) ($payment['ccExpMonth'] ?? '')) === '') {
            $emptyField = 'payment.ccExpMonth';
        } elseif (trim((string) ($payment['ccExpYear'] ?? '')) === '') {
            $emptyField = 'payment.ccExpYear';
        } elseif (trim((string) ($payment['ccType'] ?? '')) === '') {
            $emptyField = 'payment.ccType';
        }

        if ($emptyField !== null) {
            return $this->buildPaymentValidationError($emptyField, $runtime);
        }

        $month = trim((string) $payment['ccExpMonth']);
        $year = trim((string) $payment['ccExpYear']);
        $type = trim((string) $payment['ccType']);

        if (!ctype_digit($month) || (int) $month < 1 || (int) $month > 12) {
            return $this->buildPaymentValidationError('payment.ccExpMonth', $runtime);
        }

        $normalizedYear = $this->normalizeExpirationYear($year);
        if ($normalizedYear === null) {
            return $this->buildPaymentValidationError('payment.ccExpYear', $runtime);
        }

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($normalizedYear < $currentYear || ($normalizedYear === $currentYear && (int) $month < $currentMonth)) {
            return $this->buildPaymentValidationError('payment.ccExpYear', $runtime);
        }

        if ($normalizedYear > $currentYear + 30) {
            return $this->buildPaymentValidationError('payment.ccExpYear', $runtime);
        }

        if (!ctype_digit($type) || (int) $type <= 0) {
            return $this->buildPaymentValidationError('payment.ccType', $runtime);
        }

        return null;
    }

    private function normalizeExpirationYear(string $year): ?int
    {
        if (!ctype_digit($year)) {
            return null;
        }

        if (strlen($year) === 2) {
            return 2000 + (int) $year;
        }

        if (strlen($year) === 4) {
            return (int) $year;
        }

        return null;
    }

    private function buildPaymentValidationError(string $field, CheckoutExecutionRuntime $runtime): array
    {
        return [
            'httpStatus' => 422,
            'payload' => [
                'success' => false,
                'error' => 'validation_error',
                'field' => $field,
                'correlationId' => $runtime->correlationId(),
            ],
        ];
    }

    private function resolveIdempotentResponse(array $transaction): ?array
    {
        return $this->idempotencyResolver->resolveIdempotentResponse($transaction);
    }

    private function claimTransactionForProcessing(array &$transaction): ?array
    {
        return $this->idempotencyResolver->claimTransactionForProcessing($transaction);
    }

    private function hydrateContextFromTransaction(array $context, array $transaction): array
    {
        $rawRequest = json_decode((string) ($transaction['raw_request'] ?? '{}'), true);
        if (!is_array($rawRequest)) {
            $rawRequest = [];
        }
        $originalCustomer = is_array($rawRequest['customer'] ?? null) ? $rawRequest['customer'] : [];

        $context['origin'] = trim((string) ($transaction['origin'] ?? $context['origin'] ?? 'checkout'));
        $context['customer'] = [
            'email' => $transaction['customer_email'],
            'firstname' => $transaction['customer_name'],
            'lastname' => trim((string) ($originalCustomer['lastname'] ?? '')),
            'register' => $transaction['created_at'] ?? date('Y-m-d H:i:s'),
            'phone' => trim((string) ($transaction['customer_phone'] ?? ($originalCustomer['phone'] ?? ''))),
            'company' => trim((string) ($originalCustomer['company'] ?? '')),
            'jobPosition' => trim((string) ($originalCustomer['jobPosition'] ?? '')),
            'website' => trim((string) ($originalCustomer['website'] ?? '')),
            'emailPlatform' => trim((string) ($originalCustomer['emailPlatform'] ?? '')),
            'country' => trim((string) ($originalCustomer['country'] ?? '')),
            'privacy' => !empty($originalCustomer['acceptPolicies'] ?? false),
            'promotions' => !empty($originalCustomer['acceptPromotions'] ?? false),
            'source_utm' => trim((string) ($originalCustomer['utm_source'] ?? '')),
            'medium_utm' => trim((string) ($originalCustomer['utm_medium'] ?? '')),
            'campaign_utm' => trim((string) ($originalCustomer['utm_campaign'] ?? '')),
            'content_utm' => trim((string) ($originalCustomer['utm_content'] ?? '')),
            'term_utm' => trim((string) ($originalCustomer['utm_term'] ?? '')),
            'emms_ref' => trim((string) ($originalCustomer['emms_ref'] ?? '')),
        ];
        $context['pricing'] = [
            'success' => true,
            'requiresPayment' => $transaction['payment_method'] === 'card',
            'ticket' => [
                'id' => (int) $transaction['ticket_id'],
                'code' => $transaction['ticket_code'],
                'name' => $transaction['ticket_name'],
            ],
            'coupon' => $transaction['coupon_id'] !== null ? [
                'id' => $transaction['coupon_id'],
                'code' => $transaction['coupon_code'],
                'link_code' => $transaction['coupon_link_code'],
                'discount_type' => $transaction['discount_type'],
                'discount_value' => $transaction['discount_value'],
            ] : null,
            'couponAlreadyResolvedFromLedger' => true,
            'amount' => (float) $transaction['amount'],
            'discountAmount' => (float) $transaction['discount_amount'],
            'finalAmount' => (float) $transaction['final_amount'],
            'currency' => $transaction['currency'],
        ];

        return $context;
    }

    private function validateIdempotentRequestMatchesTransaction(array $context, array $transaction, CheckoutExecutionRuntime $runtime): ?array
    {
        $requestedTicketCode = trim((string) ($context['input']['ticketCode'] ?? ''));
        $requestedEmail = $context['customer']['email'];
        $requestedEventKey = $context['eventContext']['eventKey'];
        $requestedCoupon = strtolower(trim((string) ($context['input']['couponCode'] ?? '')));
        $txCouponCode = strtolower(trim((string) ($transaction['coupon_code'] ?? '')));
        $txCouponLinkCode = strtolower(trim((string) ($transaction['coupon_link_code'] ?? '')));

        // For an existing idempotency key, the ledger snapshot is authoritative for
        // ticket pricing, coupon resolution, final_amount and currency. Replays may
        // omit ticketCode/couponCode; if provided, they must match the persisted intent.
        $couponMatch = $requestedCoupon === ''
            || $requestedCoupon === $txCouponCode
            || $requestedCoupon === $txCouponLinkCode;

        $ticketMatch = $requestedTicketCode === ''
            || $requestedTicketCode === (string) $transaction['ticket_code'];

        $sameIntent =
            $ticketMatch
            && $requestedEmail === (string) $transaction['customer_email']
            && $requestedEventKey === (string) $transaction['event_key']
            && $couponMatch;

        if ($sameIntent) {
            return null;
        }

        Logger::event('payment_idempotency_conflict', [
            'correlation_id' => $transaction['correlation_id'] ?? $runtime->correlationId(),
            'idempotency_key' => $runtime->idempotencyKey(),
            'checkout_transaction_id' => (int) $transaction['id'],
            'checkout_public_id' => $transaction['public_id'] ?? null,
            'email_match' => $requestedEmail === (string) $transaction['customer_email'],
            'ticket_match' => $ticketMatch,
            'event_match' => $requestedEventKey === (string) $transaction['event_key'],
            'coupon_match' => $couponMatch,
        ], 'PAYMENTS', Logger::WARNING);

        return [
            'httpStatus' => 409,
            'payload' => [
                'success' => false,
                'error' => 'idempotency_key_conflict',
                'correlationId' => $runtime->correlationId(),
            ],
        ];
    }

    private function handleExecutionFailure(Throwable $e, CheckoutExecutionRuntime $runtime): array
    {
        return $this->failureHandler->handle($e, $runtime);
    }

    private function normalizeCustomer(array $input, string $eventFreeId, string $phase, CheckoutExecutionRuntime $runtime): array
    {
        $customer = $input['customer'] ?? [];
        $email = strtolower(trim((string) ($customer['email'] ?? '')));
        if ($email === '') {
            return [
                'response' => [
                    'httpStatus' => 422,
                    'payload' => [
                        'success' => false,
                        'error' => 'customer_email_required',
                        'correlationId' => $runtime->correlationId(),
                    ],
                ],
            ];
        }

        $firstname = trim((string) ($customer['name'] ?? $customer['firstname'] ?? ''));

        return [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => trim((string) ($customer['lastname'] ?? '')),
            'phone' => trim((string) ($customer['phone'] ?? '')),
            'company' => trim((string) ($customer['company'] ?? '')),
            'jobPosition' => trim((string) ($customer['jobPosition'] ?? '')),
            'website' => trim((string) ($customer['website'] ?? '')),
            'emailPlatform' => trim((string) ($customer['emailPlatform'] ?? '')),
            'country' => GeoIp::getCountryName(),
            'privacy' => !empty($customer['acceptPolicies']),
            'promotions' => !empty($customer['acceptPromotions']),
            'source_utm' => trim((string) ($customer['utm_source'] ?? '')),
            'medium_utm' => trim((string) ($customer['utm_medium'] ?? '')),
            'campaign_utm' => trim((string) ($customer['utm_campaign'] ?? '')),
            'content_utm' => trim((string) ($customer['utm_content'] ?? '')),
            'term_utm' => trim((string) ($customer['utm_term'] ?? '')),
            'emms_ref' => trim((string) ($customer['emms_ref'] ?? '')),
            'origin' => trim((string) ($customer['origin'] ?? '')),
            'type' => $eventFreeId,
            'form_id' => $phase,
            'register' => date('Y-m-d H:i:s'),
        ];
    }

    private function isValidIdempotencyKey(string $idempotencyKey): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $idempotencyKey);
    }

    private function generatePublicId(): string
    {
        return 'pay_' . bin2hex(random_bytes(16));
    }
}
