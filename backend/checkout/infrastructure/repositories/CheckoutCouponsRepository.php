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
        $result = $this->db->query(
            "SELECT * FROM payment_coupons WHERE code = ? OR link_code = ? LIMIT 1",
            [$couponCode, $couponCode]
        )->fetchAll();

        return $result[0] ?? null;
    }
}
