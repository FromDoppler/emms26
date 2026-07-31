<?php

final class CheckoutRequestNormalizer
{
    private const CUSTOMER_TEXT_FIELDS = [
        'name',
        'lastname',
        'phone',
        'company',
        'jobPosition',
        'website',
        'emailPlatform',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'emms_ref',
    ];

    private const PAYMENT_DIGIT_FIELDS = [
        'ccExpMonth',
        'ccExpYear',
        'ccType',
    ];

    public static function normalizeCreate(array $input): ?array
    {
        if (!array_key_exists('paymentId', $input) || !is_string($input['paymentId'])) {
            return null;
        }

        $paymentId = strtolower(trim($input['paymentId']));
        if (!self::isUuid($paymentId)) {
            return null;
        }

        if (!array_key_exists('customer', $input) || !is_array($input['customer'])) {
            return null;
        }

        $customer = self::normalizeCustomer($input['customer']);
        if ($customer === null) {
            return null;
        }

        $couponCode = self::normalizeCouponCode($input);
        if ($couponCode === self::INVALID_VALUE) {
            return null;
        }

        $origin = self::normalizeOrigin($input);
        if ($origin === self::INVALID_VALUE) {
            return null;
        }

        $payment = null;
        if (array_key_exists('payment', $input)) {
            if (!is_array($input['payment'])) {
                return null;
            }

            $payment = self::normalizePayment($input['payment']);
            if ($payment === self::INVALID_VALUE) {
                return null;
            }
        }

        return [
            'paymentId' => $paymentId,
            'customer' => $customer,
            'couponCode' => $couponCode,
            'origin' => $origin,
            'checkout' => ['origin' => $origin],
            'payment' => $payment,
        ];
    }

    public static function normalizeCalculate(array $input): ?array
    {
        $couponCode = self::normalizeCouponCode($input);
        if ($couponCode === self::INVALID_VALUE) {
            return null;
        }

        if (array_key_exists('customerEmail', $input)
            && $input['customerEmail'] !== null
            && !is_string($input['customerEmail'])) {
            return null;
        }
        if (array_key_exists('origin', $input)
            && ($input['origin'] === null || !is_scalar($input['origin']))) {
            return null;
        }

        return [
            'couponCode' => $couponCode,
            'customerEmail' => isset($input['customerEmail'])
                ? strtolower(trim((string) $input['customerEmail']))
                : null,
            'origin' => array_key_exists('origin', $input)
                ? self::normalizeOriginValue($input['origin'])
                : 'checkout',
        ];
    }

    private static function normalizeCouponCode(array $input)
    {
        if (!array_key_exists('couponCode', $input) || $input['couponCode'] === null) {
            return null;
        }
        if (!is_string($input['couponCode'])) {
            return self::INVALID_VALUE;
        }

        return CheckoutCouponCode::normalize($input['couponCode']);
    }

    private static function normalizeCustomer(array $customer): ?array
    {
        if (!array_key_exists('email', $customer) || !is_string($customer['email'])) {
            return null;
        }

        $email = strtolower(trim($customer['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $normalized = [
            'email' => $email,
            'firstname' => '',
            'lastname' => '',
            'phone' => '',
            'company' => '',
            'jobPosition' => '',
            'website' => '',
            'emailPlatform' => '',
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_content' => '',
            'utm_term' => '',
            'emms_ref' => '',
            'acceptPolicies' => false,
            'acceptPromotions' => false,
        ];

        foreach (self::CUSTOMER_TEXT_FIELDS as $field) {
            if (!array_key_exists($field, $customer) || $customer[$field] === null) {
                continue;
            }
            if (!is_string($customer[$field])) {
                return null;
            }
            $normalized[$field === 'name' ? 'firstname' : $field] = trim($customer[$field]);
        }

        foreach (['acceptPolicies', 'acceptPromotions'] as $field) {
            if (!array_key_exists($field, $customer)) {
                continue;
            }
            if (!is_bool($customer[$field])) {
                return null;
            }
            $normalized[$field] = $customer[$field];
        }

        return $normalized;
    }

    private static function normalizePayment(array $payment)
    {
        $normalized = [];

        if (array_key_exists('worldPayLowValueToken', $payment)) {
            if (!is_string($payment['worldPayLowValueToken'])) {
                return self::INVALID_VALUE;
            }
            $normalized['worldPayLowValueToken'] = trim($payment['worldPayLowValueToken']);
        }

        foreach (self::PAYMENT_DIGIT_FIELDS as $field) {
            if (!array_key_exists($field, $payment)) {
                continue;
            }

            $value = $payment[$field];
            if (is_bool($value) || is_float($value) || $value === null || is_array($value) || is_object($value)) {
                return self::INVALID_VALUE;
            }

            if (is_int($value)) {
                if ($value < 0) {
                    return self::INVALID_VALUE;
                }
                $value = (string) $value;
            } elseif (is_string($value)) {
                $value = trim($value);
            } else {
                return self::INVALID_VALUE;
            }

            if ($value === '' || !ctype_digit($value)) {
                return self::INVALID_VALUE;
            }

            $normalized[$field] = $value;
        }

        return $normalized;
    }

    private static function normalizeOrigin(array $input)
    {
        if (array_key_exists('checkout', $input) && !is_array($input['checkout'])) {
            return self::INVALID_VALUE;
        }

        if (array_key_exists('checkout', $input)
            && is_array($input['checkout'])
            && array_key_exists('origin', $input['checkout'])) {
            if ($input['checkout']['origin'] === null || !is_scalar($input['checkout']['origin'])) {
                return self::INVALID_VALUE;
            }

            return self::normalizeOriginValue($input['checkout']['origin']);
        }

        if (array_key_exists('origin', $input)) {
            if ($input['origin'] === null || !is_scalar($input['origin'])) {
                return self::INVALID_VALUE;
            }

            return self::normalizeOriginValue($input['origin']);
        }

        return 'checkout';
    }

    private static function normalizeOriginValue($origin): string
    {
        $value = trim((string) ($origin ?? 'checkout'));
        if ($value === '') {
            return 'checkout';
        }
        return substr($value, 0, 50);
    }

    private const INVALID_VALUE = '__checkout_invalid__';

    private static function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D',
            $value
        ) === 1;
    }
}
