<?php

class CheckoutCouponsRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function findByCode(string $couponCode): ?array
    {
        $couponCode = CheckoutCouponCode::normalize($couponCode);
        if ($couponCode === null) {
            return null;
        }

        $result = $this->db->query(
            "SELECT * FROM payment_coupons WHERE code = ? LIMIT 1",
            [$couponCode]
        )->fetchAll();

        return $result[0] ?? null;
    }
}
