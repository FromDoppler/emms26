<?php

class CheckoutPayloadSanitizer
{
    private static $exactSensitiveKeys = [
        'pan',
    ];

    private static $sensitiveKeys = [
        'worldpaylowvaluetoken',
        'paypageregistrationid',
        'tokenizedpan',
        'paymenttoken',
        'cardnumber',
        'ccnumber',
        'cvv',
        'cvc',
        'ccsecuritycode',
        'ccverification',
    ];

    public static function sanitize($value, string $path = '')
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $keyString = (string) $key;
                $normalizedKey = self::normalize($keyString);
                $fullPath = $path === '' ? $keyString : $path . '.' . $keyString;
                $normalizedPath = self::normalize($fullPath);

                if (self::isSensitive($normalizedKey, $normalizedPath)) {
                    $sanitized[$key] = '[REDACTED]';
                    continue;
                }

                $sanitized[$key] = self::sanitize($item, $fullPath);
            }

            return $sanitized;
        }

        if (is_object($value)) {
            return self::sanitize((array) $value, $path);
        }

        return $value;
    }

    private static function isSensitive(string $normalizedKey, string $normalizedPath): bool
    {
        if (in_array($normalizedKey, self::$exactSensitiveKeys, true)) {
            return true;
        }

        foreach (self::$sensitiveKeys as $sensitiveKey) {
            if ($normalizedKey === $sensitiveKey || strpos($normalizedPath, $sensitiveKey) !== false) {
                return true;
            }
        }

        return in_array($normalizedKey, ['number', 'code'], true)
            && (
                strpos($normalizedPath, 'creditcard') !== false
                || strpos($normalizedPath, 'encryptedcreditcard') !== false
                || strpos($normalizedPath, 'card') !== false
            );
    }

    private static function normalize(string $value): string
    {
        return str_replace(['-', '_', '.'], '', strtolower($value));
    }
}
