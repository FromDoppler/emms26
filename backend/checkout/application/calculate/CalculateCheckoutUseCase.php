<?php

class CalculateCheckoutUseCase
{
    private $eventContextResolver;
    private $pricingService;
    private $registeredProfiles;

    public function __construct(
        CheckoutEventContextResolver $eventContextResolver,
        CheckoutPricingService $pricingService,
        RegisteredProfileRepository $registeredProfiles
    ) {
        $this->eventContextResolver = $eventContextResolver;
        $this->pricingService = $pricingService;
        $this->registeredProfiles = $registeredProfiles;
    }

    public function execute(array $input): array
    {
        $input = CheckoutRequestNormalizer::normalizeCalculate($input);
        if ($input === null) {
            return [
                'httpStatus' => 422,
                'payload' => ['success' => false, 'error' => 'validation_error'],
            ];
        }
        $eventContext = $this->eventContextResolver->resolve();
        $result = $this->pricingService->calculate($eventContext, $input);
        $customerProfile = $this->resolveCustomerProfile($eventContext, $input);
        $result['customerProfile'] = $this->toPublicCustomerProfile($customerProfile);
        $publicResult = $this->sanitizePublicResult($result);

        if ($customerProfile['exists']) {
            Logger::event('checkout_profile_found', [
                'event_key' => $eventContext['eventKey'],
                'origin' => $input['origin'] ?? null,
                'registered_id' => $customerProfile['registeredId'],
                'is_free' => $customerProfile['isFree'],
                'is_vip' => $customerProfile['isVip'],
            ], 'PAYMENTS', Logger::INFO);

            if ($customerProfile['isVip']) {
                Logger::event('checkout_profile_vip_already_owned', [
                    'event_key' => $eventContext['eventKey'],
                    'origin' => $input['origin'] ?? null,
                    'registered_id' => $customerProfile['registeredId'],
                ], 'PAYMENTS', Logger::INFO);
            }
        } elseif (($input['customerEmail'] ?? '') !== '') {
            Logger::event('checkout_profile_not_found', [
                'event_key' => $eventContext['eventKey'],
                'origin' => $input['origin'] ?? null,
            ], 'PAYMENTS', Logger::INFO);
        }

        return [
            'httpStatus' => $result['success'] ? 200 : 422,
            'payload' => $publicResult,
        ];
    }

    private function sanitizePublicResult(array $result): array
    {
        unset($result['coupon']);
        return $result;
    }

    private function toPublicCustomerProfile(array $customerProfile): array
    {
        return [
            'exists' => (bool) ($customerProfile['exists'] ?? false),
            'firstname' => (string) ($customerProfile['firstname'] ?? ''),
            'phone' => (string) ($customerProfile['phone'] ?? ''),
            'isFree' => (bool) ($customerProfile['isFree'] ?? false),
            'isVip' => (bool) ($customerProfile['isVip'] ?? false),
            'emailLocked' => (bool) ($customerProfile['emailLocked'] ?? false),
        ];
    }

    private function resolveCustomerProfile(array $eventContext, array $input): array
    {
        $email = $input['customerEmail'] ?? '';

        if ($email === '') {
            return [
                'exists' => false,
                'registeredId' => null,
                'email' => null,
                'firstname' => null,
                'phone' => null,
                'company' => null,
                'jobPosition' => null,
                'isFree' => false,
                'isVip' => false,
                'emailLocked' => false,
            ];
        }

        $profile = $this->registeredProfiles->findByEmailForEvent(
            $email,
            $eventContext['registeredFreeColumn'],
            $eventContext['registeredVipColumn']
        );

        if (!$profile) {
            return [
                'exists' => false,
                'registeredId' => null,
                'email' => $email,
                'firstname' => null,
                'phone' => null,
                'company' => null,
                'jobPosition' => null,
                'isFree' => false,
                'isVip' => false,
                'emailLocked' => false,
            ];
        }

        return [
            'exists' => true,
            'registeredId' => (int) $profile['id'],
            'email' => $profile['email'],
            'firstname' => $profile['firstname'] ?: null,
            'phone' => $profile['phone'] ?: null,
            'company' => $profile['company'] ?: null,
            'jobPosition' => $profile['jobPosition'] ?: null,
            'isFree' => (int) ($profile['is_free'] ?? 0) === 1,
            'isVip' => (int) ($profile['is_vip'] ?? 0) === 1,
            'emailLocked' => false,
        ];
    }

}
