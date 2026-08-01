<?php

class CheckoutCouponService
{
    private $couponsDatabase;

    public function __construct(CheckoutCouponsRepository $couponsDatabase)
    {
        $this->couponsDatabase = $couponsDatabase;
    }

    public function resolve(?string $couponCode, ?int $ticketId, array $event): array
    {
        $couponCode = CheckoutCouponCode::normalize($couponCode);

        if ($couponCode === null) {
            return ['coupon' => null];
        }

        $couponByCode = $this->couponsDatabase->findByCode($couponCode);

        if (!$couponByCode) {
            return ['error' => 'coupon_invalid'];
        }
        $coupon = $couponByCode;
        $coupon['code'] = $couponCode;

        $validationError = $this->validateCoupon($coupon, $ticketId, $event);
        if ($validationError !== null) {
            return ['error' => $validationError];
        }

        return ['coupon' => $coupon];
    }

    public function validateCoupon(array $coupon, ?int $ticketId, array $event): ?string
    {
        if ((int) $coupon['is_active'] !== 1) {
            return 'coupon_inactive';
        }

        $now = time();
        if (!empty($coupon['starts_at']) && strtotime($coupon['starts_at']) > $now) {
            return 'coupon_inactive';
        }

        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
            return 'coupon_expired';
        }

        if (!empty($coupon['event_key']) && $coupon['event_key'] !== $event['eventKey']) {
            return 'coupon_out_of_scope';
        }

        if (!empty($coupon['event_vip_id']) && $coupon['event_vip_id'] !== $event['eventVipId']) {
            return 'coupon_out_of_scope';
        }

        if (!empty($coupon['ticket_id']) && $ticketId !== null && (int) $coupon['ticket_id'] !== $ticketId) {
            return 'coupon_out_of_scope';
        }

        if ($ticketId === null && !empty($coupon['ticket_id'])) {
            return 'coupon_out_of_scope';
        }

        if ($coupon['discount_type'] === 'percentage') {
            $value = (float) $coupon['discount_value'];
            if ($value < 0.0 || $value > 100.0) {
                return 'coupon_invalid';
            }
        }

        return null;
    }
}
