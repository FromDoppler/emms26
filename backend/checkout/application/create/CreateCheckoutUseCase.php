<?php

class CreateCheckoutUseCase
{
    private $pricing;
    private $transactions;
    private $eligibility;
    private $events;
    private $processor;
    private $failureHandler;
    private $responses;

    public function __construct(
        CheckoutPricingService $pricing,
        CheckoutTransactionsRepository $transactions,
        CheckoutEligibilityService $eligibility,
        CheckoutEventContextResolver $events,
        CheckoutPaymentProcessor $processor,
        CheckoutFailureHandler $failureHandler,
        CheckoutResponseFactory $responses
    ) {
        $this->pricing = $pricing;
        $this->transactions = $transactions;
        $this->eligibility = $eligibility;
        $this->events = $events;
        $this->processor = $processor;
        $this->failureHandler = $failureHandler;
        $this->responses = $responses;
    }

    public function execute(array $input): array
    {
        $requestCorrelationId = 'corr_' . bin2hex(random_bytes(16));
        $paymentId = strtolower(trim((string) ($input['paymentId'] ?? '')));

        if (!$this->isUuid($paymentId)) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation($requestCorrelationId)];
        }

        try {
            $existing = $this->transactions->findByPaymentId($paymentId);
            if ($existing !== null) {
                return $this->handleExisting($existing, $input, $requestCorrelationId);
            }
            return $this->handleNew($paymentId, $input, $requestCorrelationId);
        } catch (Throwable $e) {
            return $this->failureHandler->handle($e, $paymentId, $requestCorrelationId);
        }
    }

    private function handleExisting(array $transaction, array $input, string $requestCorrelationId): array
    {
        if (!CheckoutTransactionStatus::isConsistent($transaction)) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal($transaction['correlation_id'])];
        }

        $intent = $this->extractIntent($input);
        if ($intent === null) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation($requestCorrelationId)];
        }
        if (!$this->intentMatches($transaction, $intent)) {
            return [
                'httpStatus' => 409,
                'payload' => ['success' => false, 'error' => 'payment_intent_conflict', 'correlationId' => null],
            ];
        }

        if (CheckoutTransactionStatus::isTerminal($transaction['status'])) {
            return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($transaction)];
        }
        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING
            && $transaction['payment_method'] === 'card'
            && empty($transaction['provider_approved_at'])) {
            return ['httpStatus' => 202, 'payload' => $this->responses->processing($transaction)];
        }

        $context = $this->hydrateContext($transaction, $input);
        if ($transaction['status'] === CheckoutTransactionStatus::PROCESSING) {
            return $this->processor->completeExisting($context, $transaction);
        }

        if ($transaction['payment_method'] === 'card' && !$this->validPayment($input['payment'] ?? [])) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation(
                $transaction['correlation_id'],
                'validation_error',
                $transaction
            )];
        }

        $eligibilityError = $this->eligibility->validateAlreadyVip($context['eventContext'], $context['customer']);
        if ($eligibilityError !== null) {
            return $this->rejectAlreadyVip($transaction);
        }

        return $this->claimAndProcess($context, $transaction);
    }

    private function handleNew(string $paymentId, array $input, string $correlationId): array
    {
        $intent = $this->extractIntent($input);
        if ($intent === null) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation($correlationId)];
        }
        $input['checkout'] = ['origin' => $this->resolveOrigin($input)];

        $event = $this->events->resolve();
        $customer = $this->normalizeCustomer($input, $event);
        $correctableError = $this->eligibility->validateCorrectable($event, $customer);
        if ($correctableError !== null) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation(
                $correlationId,
                $correctableError['error']
            )];
        }

        $pricing = $this->pricing->calculate($event, $intent);
        if (!$pricing['success']) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation($correlationId, $pricing['error'])];
        }
        if (($pricing['currency'] ?? null) !== 'USD') {
            throw new Exception('unsupported_checkout_currency');
        }
        if ($pricing['requiresPayment'] && !$this->validPayment($input['payment'] ?? [])) {
            return ['httpStatus' => 422, 'payload' => $this->responses->validation($correlationId)];
        }

        $transaction = $this->transactions->createPending([
            'payment_id' => $paymentId,
            'correlation_id' => $correlationId,
            'provider' => $pricing['requiresPayment'] ? 'doppler-payments-api' : 'coupon',
            'payment_method' => $pricing['requiresPayment'] ? 'card' : 'coupon',
            'origin' => $input['checkout']['origin'],
            'customer_email' => $customer['email'],
            'customer_name' => $customer['firstname'],
            'customer_phone' => $customer['phone'],
            'customer_ip' => $customer['ip'],
            'ticket_id' => (int) $pricing['ticket']['id'],
            'ticket_code' => $pricing['ticket']['code'],
            'ticket_name' => $pricing['ticket']['name'],
            'coupon_id' => $pricing['coupon']['id'] ?? null,
            'coupon_code' => $pricing['coupon']['code'] ?? null,
            'amount' => $pricing['amount'],
            'discount_amount' => $pricing['discountAmount'],
            'final_amount' => $pricing['finalAmount'],
            'currency' => $pricing['currency'],
            'event_key' => $event['eventKey'],
            'event_free_id' => $event['eventFreeId'],
            'event_vip_id' => $event['eventVipId'],
            'event_phase' => $event['eventPhase'],
            'raw_request' => ['customer' => $this->customerForLedger($customer)],
        ]);

        if (!$this->intentMatches($transaction, $intent)) {
            return [
                'httpStatus' => 409,
                'payload' => ['success' => false, 'error' => 'payment_intent_conflict', 'correlationId' => null],
            ];
        }
        return $this->handleExisting($transaction, $input, $correlationId);
    }

    private function claimAndProcess(array $context, array $transaction): array
    {
        if (!$this->transactions->claimProcessing($transaction['payment_id'])) {
            $reloaded = $this->transactions->findByPaymentId($transaction['payment_id']);
            if ($reloaded === null || !CheckoutTransactionStatus::isConsistent($reloaded)) {
                return ['httpStatus' => 500, 'payload' => $this->responses->internal($transaction['correlation_id'])];
            }
            if ($reloaded['status'] === CheckoutTransactionStatus::PROCESSING) {
                return ['httpStatus' => 202, 'payload' => $this->responses->processing($reloaded)];
            }
            if (CheckoutTransactionStatus::isTerminal($reloaded['status'])) {
                return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($reloaded)];
            }
            return ['httpStatus' => 500, 'payload' => $this->responses->internal($transaction['correlation_id'])];
        }
        $transaction = $this->transactions->findByPaymentId($transaction['payment_id']);
        return $this->processor->process($context, $transaction);
    }

    private function rejectAlreadyVip(array $transaction): array
    {
        $this->transactions->rejectPendingAsAlreadyVip($transaction['payment_id']);
        $reloaded = $this->transactions->findByPaymentId($transaction['payment_id']);
        if ($reloaded === null || !CheckoutTransactionStatus::isConsistent($reloaded)) {
            return ['httpStatus' => 500, 'payload' => $this->responses->internal($transaction['correlation_id'])];
        }
        if ($reloaded['status'] === CheckoutTransactionStatus::REJECTED
            && $reloaded['response_code'] === 'already_vip') {
            return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($reloaded)];
        }
        if ($reloaded['status'] === CheckoutTransactionStatus::PROCESSING) {
            return ['httpStatus' => 202, 'payload' => $this->responses->processing($reloaded)];
        }
        if (CheckoutTransactionStatus::isTerminal($reloaded['status'])) {
            return ['httpStatus' => 200, 'payload' => $this->responses->fromTransaction($reloaded)];
        }
        return ['httpStatus' => 500, 'payload' => $this->responses->internal($reloaded['correlation_id'])];
    }

    private function hydrateContext(array $transaction, array $input): array
    {
        $raw = json_decode((string) $transaction['raw_request'], true);
        if (!is_array($raw) || !isset($raw['customer']) || !is_array($raw['customer'])) {
            throw new Exception('invalid_payment_raw_request');
        }
        $event = $this->events->resolveByPayment($transaction);
        $input['checkout'] = ['origin' => $transaction['origin']];
        return [
            'input' => $input,
            'eventContext' => $event,
            'customer' => array_merge($raw['customer'], [
                'register' => $transaction['created_at'],
                'ip' => $transaction['customer_ip'] ?? '',
            ]),
            'pricing' => [
                'requiresPayment' => $transaction['payment_method'] === 'card',
                'ticket' => ['id' => (int) $transaction['ticket_id'], 'code' => $transaction['ticket_code'], 'name' => $transaction['ticket_name']],
                'coupon' => $transaction['coupon_id'] ? ['id' => (int) $transaction['coupon_id'], 'code' => $transaction['coupon_code']] : null,
                'amount' => $transaction['amount'],
                'discountAmount' => $transaction['discount_amount'],
                'finalAmount' => $transaction['final_amount'],
                'currency' => $transaction['currency'],
            ],
        ];
    }

    private function extractIntent(array $input): ?array
    {
        if (!isset($input['customer']) || !is_array($input['customer'])
            || !isset($input['customer']['email'])) {
            return null;
        }
        $email = strtolower(trim((string) $input['customer']['email']));
        $couponCode = array_key_exists('couponCode', $input)
            ? trim((string) $input['couponCode'])
            : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        if ($couponCode === '') {
            $couponCode = null;
        }
        return [
            'customerEmail' => $email,
            'couponCode' => $couponCode,
        ];
    }

    private function intentMatches(array $transaction, array $intent): bool
    {
        return $transaction['customer_email'] === $intent['customerEmail']
            && (($transaction['coupon_code'] ?: null) === ($intent['couponCode'] ?: null));
    }

    private function normalizeCustomer(array $input, array $event): array
    {
        $customer = $input['customer'] ?? [];
        return [
            'email' => strtolower(trim((string) ($customer['email'] ?? ''))),
            'firstname' => trim((string) ($customer['name'] ?? '')),
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
            'type' => $event['eventFreeId'],
            'form_id' => $event['eventPhase'],
            'register' => date('Y-m-d H:i:s'),
            'ip' => GeoIp::getIp(),
        ];
    }

    private function validPayment(array $payment): bool
    {
        $month = trim((string) ($payment['ccExpMonth'] ?? ''));
        $year = trim((string) ($payment['ccExpYear'] ?? ''));
        $type = trim((string) ($payment['ccType'] ?? ''));
        if (trim((string) ($payment['worldPayLowValueToken'] ?? '')) === ''
            || !ctype_digit($month) || (int) $month < 1 || (int) $month > 12
            || !ctype_digit($type) || (int) $type <= 0) {
            return false;
        }

        if (strlen($year) === 2 && ctype_digit($year)) {
            $year = '20' . $year;
        }
        if (strlen($year) !== 4 || !ctype_digit($year)) {
            return false;
        }

        $expirationYear = (int) $year;
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        return $expirationYear >= $currentYear
            && $expirationYear <= $currentYear + 30
            && ($expirationYear !== $currentYear || (int) $month >= $currentMonth);
    }

    private function customerForLedger(array $customer): array
    {
        unset($customer['register'], $customer['ip'], $customer['type'], $customer['form_id']);
        return $customer;
    }

    private function resolveOrigin(array $input): string
    {
        $checkout = isset($input['checkout']) && is_array($input['checkout']) ? $input['checkout'] : [];
        $origin = trim((string) ($checkout['origin'] ?? $input['origin'] ?? 'checkout'));
        return $origin === '' ? 'checkout' : substr($origin, 0, 50);
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D',
            strtolower($value)
        ) === 1;
    }
}
