<?php

class CheckoutPricingService
{
    private $ticketsDatabase;
    private $couponService;

    public function __construct(CheckoutTicketsRepository $ticketsDatabase, CheckoutCouponService $couponService)
    {
        $this->ticketsDatabase = $ticketsDatabase;
        $this->couponService = $couponService;
    }

    public function calculate(array $event, array $input): array
    {
        $availableTickets = $this->ticketsDatabase->findActiveTicketsForEvent($event);

        if (count($availableTickets) === 0) {
            return [
                'success' => false,
                'error' => 'ticket_unavailable',
                'availableTickets' => [],
            ];
        }

        $ticketCode = trim((string) ($input['ticketCode'] ?? ''));

        if ($ticketCode === '') {
            return [
                'success' => false,
                'error' => 'ticket_required',
                'availableTickets' => $this->formatTickets($availableTickets),
            ];
        }

        $ticket = $this->ticketsDatabase->findActiveTicketByCodeForEvent($event, $ticketCode);

        if ($ticket === null) {
            return [
                'success' => false,
                'error' => 'ticket_unavailable',
                'availableTickets' => $this->formatTickets($availableTickets),
            ];
        }

        $couponResolution = $this->couponService->resolve(
            $input['couponCode'] ?? null,
            (int) $ticket['id'],
            $event
        );

        if (isset($couponResolution['error'])) {
            return [
                'success' => false,
                'error' => $couponResolution['error'],
                'ticket' => $this->formatTicket($ticket),
                'availableTickets' => $this->formatTickets($availableTickets),
            ];
        }

        $coupon = $couponResolution['coupon'];
        $amount = (float) $ticket['price'];
        $discountAmount = 0.0;
        $discount = null;

        if ($coupon) {
            if ($coupon['discount_type'] === 'percentage') {
                $discountAmount = round($amount * (((float) $coupon['discount_value']) / 100), 2);
                $discount = [
                    'couponCode' => $coupon['code'],
                    'percent' => (float) $coupon['discount_value'],
                    'amount' => $discountAmount,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'coupon_discount_type_unsupported',
                    'ticket' => $this->formatTicket($ticket),
                    'availableTickets' => $this->formatTickets($availableTickets),
                ];
            }
        }

        $finalAmount = max(0.0, round($amount - $discountAmount, 2));

        return [
            'success' => true,
            'ticket' => $this->formatTicket($ticket),
            'availableTickets' => $this->formatTickets($availableTickets),
            'coupon' => $coupon,
            'discount' => $discount,
            'amount' => $amount,
            'discountAmount' => $discountAmount,
            'finalAmount' => $finalAmount,
            'currency' => $ticket['currency'],
            'requiresPayment' => $finalAmount > 0,
        ];
    }

    public function validateResolvedCoupon(array $coupon, array $event, ?int $ticketId): ?string
    {
        return $this->couponService->validateCoupon($coupon, $ticketId, $event);
    }

    private function formatTickets(array $tickets): array
    {
        return array_map(function ($ticket) {
            return $this->formatTicket($ticket);
        }, $tickets);
    }

    private function formatTicket(array $ticket): array
    {
        return [
            'id' => (int) $ticket['id'],
            'code' => $ticket['ticket_code'],
            'name' => $ticket['name'],
            'price' => (float) $ticket['price'],
            'currency' => $ticket['currency'],
        ];
    }
}
