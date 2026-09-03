<?php

class SalesReportRepository
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
        $this->db->query("SET time_zone = '+00:00'");
    }

    public function findPaidSales(DateTimeImmutable $startUtc, DateTimeImmutable $endUtc): array
    {
        return $this->db->query(
            "SELECT
                customer_email,
                customer_name,
                customer_phone,
                final_amount,
                raw_request,
                updated_at
             FROM payment_transactions
             WHERE status = ?
               AND payment_method = ?
               AND provider_approved_at IS NOT NULL
               AND final_amount > 0
               AND currency = ?
               AND updated_at >= ?
               AND updated_at < ?
             ORDER BY updated_at ASC, id ASC",
            [
                CheckoutTransactionStatus::APPROVED,
                'card',
                'USD',
                $startUtc->format('Y-m-d H:i:s'),
                $endUtc->format('Y-m-d H:i:s'),
            ]
        )->fetchAll();
    }

    public function countCouponVipUsers(DateTimeImmutable $startUtc, DateTimeImmutable $endUtc): int
    {
        $rows = $this->db->query(
            "SELECT COUNT(DISTINCT registered_id) AS total
             FROM payment_transactions
             WHERE status = ?
               AND payment_method = ?
               AND final_amount = 0
               AND registered_id IS NOT NULL
               AND currency = ?
               AND updated_at >= ?
               AND updated_at < ?",
            [
                CheckoutTransactionStatus::APPROVED,
                'coupon',
                'USD',
                $startUtc->format('Y-m-d H:i:s'),
                $endUtc->format('Y-m-d H:i:s'),
            ]
        )->fetchAll();

        return isset($rows[0]['total']) ? (int) $rows[0]['total'] : 0;
    }
}
