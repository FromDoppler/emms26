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
}
