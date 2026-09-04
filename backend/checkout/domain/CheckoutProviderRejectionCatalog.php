<?php

class CheckoutProviderRejectionCatalog
{
    private const TERMINAL_REJECTIONS = [
        '004' => 'card_invalid_expiration_date',
        '005' => 'card_declined',
        '013' => 'card_invalid_security_code',
        '016' => 'card_declined',
        '017' => 'card_suspected_fraud',
        '018' => 'card_invalid_number',
        '019' => 'card_suspected_fraud',
        '025' => 'card_suspected_fraud',
        '028' => null,
        '039' => 'card_insufficient_funds',
        '045' => 'card_invalid_expiration_date',
        '078' => 'card_invalid_number',
        'DeclinedPaymentTransaction' => 'card_declined',
        'DoNotHonorPaymentResponse' => 'card_declined',
        'FraudPaymentTransaction' => 'card_suspected_fraud',
    ];

    public static function categoryFor(?string $providerCode): ?string
    {
        return $providerCode === null ? null : (self::TERMINAL_REJECTIONS[$providerCode] ?? null);
    }

    public static function isTerminalRejection(?string $providerCode): bool
    {
        return $providerCode !== null && array_key_exists($providerCode, self::TERMINAL_REJECTIONS);
    }

    public static function responseCodeFor(?string $providerCode): string
    {
        return self::categoryFor($providerCode) ?? 'provider_rejected';
    }
}
