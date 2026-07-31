<?php

class CheckoutEligibilityService
{
    private $registeredProfiles;

    public function __construct(RegisteredProfileRepository $registeredProfiles)
    {
        $this->registeredProfiles = $registeredProfiles;
    }

    public function validateCorrectable(array $eventContext, array $customer): ?array
    {
        return $this->validateCorrectableWithProfile($this->findProfile($eventContext, $customer), $customer);
    }

    public function validateAlreadyVip(array $eventContext, array $customer): ?array
    {
        return $this->validateAlreadyVipWithProfile($this->findProfile($eventContext, $customer));
    }

    private function findProfile(array $eventContext, array $customer): ?array
    {
        return $this->registeredProfiles->findByEmailForEvent(
            $customer['email'],
            $eventContext['registeredFreeColumn'],
            $eventContext['registeredVipColumn']
        );
    }

    private function validateCorrectableWithProfile(?array $profile, array $customer): ?array
    {
        if (trim((string) ($customer['email'] ?? '')) === '') {
            return [
                'error' => 'customer_email_required',
                'httpStatus' => 422,
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

    private function validateAlreadyVipWithProfile(?array $profile): ?array
    {
        if ($profile && (int) ($profile['is_vip'] ?? 0) === 1) {
            return [
                'error' => 'already_vip',
                'httpStatus' => 200,
            ];
        }
        return null;
    }
}
