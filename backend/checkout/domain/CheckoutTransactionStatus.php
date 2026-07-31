<?php

class CheckoutTransactionStatus
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const ERROR = 'error';

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::APPROVED, self::REJECTED, self::ERROR], true);
    }

    public static function isConsistent(array $transaction): bool
    {
        $status = $transaction['status'] ?? '';
        $method = $transaction['payment_method'] ?? '';
        $hasMarker = !empty($transaction['provider_approved_at']);
        $registeredId = $transaction['registered_id'] ?? null;

        if (!in_array($status, [self::PENDING, self::PROCESSING, self::APPROVED, self::REJECTED, self::ERROR], true)) {
            return false;
        }
        if (!in_array($method, ['card', 'coupon'], true)) {
            return false;
        }
        if (($transaction['currency'] ?? null) !== 'USD') {
            return false;
        }
        if ($status === self::APPROVED
            && (empty($transaction['registered_id']) || ($transaction['response_code'] ?? null) !== 'approved')) {
            return false;
        }
        if ($status !== self::APPROVED && !empty($registeredId)) {
            return false;
        }
        if ($method === 'coupon') {
            return ($transaction['provider'] ?? '') === 'coupon'
                && !$hasMarker
                && self::hasNoCardProviderEvidence($transaction)
                && (string) ($transaction['final_amount'] ?? '') === '0.00'
                && !empty($transaction['coupon_id'])
                && self::isConsistentCouponStatus($transaction);
        }
        if (($transaction['provider'] ?? '') !== 'doppler-payments-api') {
            return false;
        }
        if (!self::isPositiveDecimalAmount($transaction['final_amount'] ?? null)) {
            return false;
        }
        if ($status === self::APPROVED) {
            return $hasMarker && self::hasApprovedEvidence($transaction);
        }
        if ($status === self::ERROR) {
            return !$hasMarker
                && self::hasNoCardProviderEvidence($transaction)
                && ($transaction['response_code'] ?? null) === 'payment_error';
        }
        if ($status === self::REJECTED) {
            return !$hasMarker && self::isConsistentCardRejection($transaction);
        }
        if ($hasMarker) {
            return $status === self::PROCESSING
                && self::hasApprovedEvidence($transaction)
                && ($transaction['response_code'] ?? null) === 'provider_approved';
        }

        return self::hasNoProviderEvidence($transaction);
    }

    private static function isConsistentCouponStatus(array $transaction): bool
    {
        $status = $transaction['status'];
        $responseCode = $transaction['response_code'] ?? null;
        if (in_array($status, [self::PENDING, self::PROCESSING], true)) {
            return $responseCode === null && empty($transaction['registered_id']);
        }
        if ($status === self::REJECTED) {
            return $responseCode === 'already_vip' && empty($transaction['registered_id']);
        }
        return $status === self::APPROVED;
    }

    private static function isConsistentCardRejection(array $transaction): bool
    {
        if (trim((string) ($transaction['authorization_number'] ?? '')) !== '') {
            return false;
        }
        if (($transaction['response_code'] ?? null) === 'already_vip') {
            return self::hasNoCardProviderEvidence($transaction);
        }
        $authorizationCode = $transaction['authorization_response_code'] ?? null;
        $purchaseCode = $transaction['purchase_response_code'] ?? null;
        $providerCode = $purchaseCode ?? $authorizationCode;
        $expectedCategory = CheckoutProviderRejectionCatalog::categoryFor($providerCode);
        if ($expectedCategory === null || ($transaction['response_code'] ?? null) !== $expectedCategory) {
            return false;
        }

        return ($purchaseCode === null && $authorizationCode !== null && $authorizationCode !== '000')
            || ($authorizationCode === '000' && $purchaseCode !== null && $purchaseCode !== '000');
    }

    private static function hasNoCardProviderEvidence(array $transaction): bool
    {
        foreach ([
            'authorization_number',
            'transaction_link_id',
            'authorization_response_code',
            'purchase_response_code',
        ] as $field) {
            if (($transaction[$field] ?? null) !== null) {
                return false;
            }
        }
        return true;
    }

    private static function isPositiveDecimalAmount($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $amount = trim($value);
        return preg_match('/^\d+\.\d{2}$/D', $amount) === 1 && $amount !== '0.00';
    }

    public static function hasNoProviderEvidence(array $transaction): bool
    {
        foreach ([
            'authorization_number',
            'transaction_link_id',
            'authorization_response_code',
            'purchase_response_code',
            'response_code',
        ] as $field) {
            if (($transaction[$field] ?? null) !== null) {
                return false;
            }
        }
        return true;
    }

    private static function hasApprovedEvidence(array $transaction): bool
    {
        return ($transaction['provider'] ?? '') === 'doppler-payments-api'
            && ($transaction['authorization_response_code'] ?? null) === '000'
            && ($transaction['purchase_response_code'] ?? null) === '000'
            && trim((string) ($transaction['authorization_number'] ?? '')) !== '';
    }
}
