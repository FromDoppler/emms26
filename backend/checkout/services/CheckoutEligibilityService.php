<?php

class CheckoutEligibilityService
{
    private $registeredProfiles;

    public function __construct(RegisteredProfileRepository $registeredProfiles)
    {
        $this->registeredProfiles = $registeredProfiles;
    }

    public function validate(array $eventContext, array $customer): ?array
    {
        $profile = $this->registeredProfiles->findByEmailForEvent(
            $customer['email'],
            $eventContext['registeredFreeColumn'],
            $eventContext['registeredVipColumn']
        );

        if ($profile && (int) ($profile['is_vip'] ?? 0) === 1) {
            return [
                'error' => 'already_vip',
                'httpStatus' => 409,
            ];
        }

        if (trim((string) ($customer['firstname'] ?? '')) === '') {
            return [
                'error' => 'customer_name_required',
                'httpStatus' => 422,
            ];
        }

        if (trim((string) ($customer['phone'] ?? '')) === '') {
            return [
                'error' => 'customer_phone_required',
                'httpStatus' => 422,
            ];
        }

        if ((!$profile || (int) ($profile['is_free'] ?? 0) !== 1) && empty($customer['privacy'])) {
            return [
                'error' => 'privacy_required',
                'httpStatus' => 422,
            ];
        }

        return null;
    }
}
