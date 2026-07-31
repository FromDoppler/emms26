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

        if (!in_array($status, [self::PENDING, self::PROCESSING, self::APPROVED, self::REJECTED, self::ERROR], true)) {
            return false;
        }
        if (!in_array($method, ['card', 'coupon'], true)) {
            return false;
        }
        if (($transaction['currency'] ?? null) !== 'USD') {
            return false;
        }
        if ($status === self::APPROVED && empty($transaction['registered_id'])) {
            return false;
        }
        if ($method === 'coupon' && $hasMarker) {
            return false;
        }
        if (in_array($status, [self::PENDING, self::REJECTED, self::ERROR], true) && $hasMarker) {
            return false;
        }
        if ($status === self::APPROVED && $method === 'card' && !$hasMarker) {
            return false;
        }

        return true;
    }
}
