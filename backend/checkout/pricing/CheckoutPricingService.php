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

        if (count($availableTickets) > 1) {
            throw new Exception('multiple_active_tickets_configuration_error');
        }

        $ticket = $availableTickets[0];
        $amountCents = $this->decimalToCents((string) $ticket['price']);
        if ($amountCents <= 0) {
            throw new Exception('invalid_active_ticket_price_configuration');
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
        $discountCents = 0;
        $discount = null;

        if ($coupon) {
            if ($coupon['discount_type'] === 'percentage') {
                $percentBasisPoints = $this->decimalToCents((string) $coupon['discount_value']);
                $discountCents = intdiv(($amountCents * $percentBasisPoints) + 5000, 10000);
                $discount = [
                    'couponCode' => $coupon['code'],
                    'percent' => $this->centsToDecimal($percentBasisPoints),
                    'amount' => $this->centsToDecimal($discountCents),
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

        $finalCents = max(0, $amountCents - $discountCents);

        return [
            'success' => true,
            'ticket' => $this->formatTicket($ticket),
            'availableTickets' => $this->formatTickets($availableTickets),
            'coupon' => $coupon,
            'discount' => $discount,
            'amount' => $this->centsToDecimal($amountCents),
            'discountAmount' => $this->centsToDecimal($discountCents),
            'finalAmount' => $this->centsToDecimal($finalCents),
            'currency' => $ticket['currency'],
            'requiresPayment' => $finalCents > 0,
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
            'price' => $this->centsToDecimal($this->decimalToCents((string) $ticket['price'])),
            'currency' => $ticket['currency'],
        ];
    }

    private function decimalToCents(string $value): int
    {
        $normalized = trim($value);
        if (!preg_match('/^-?\d+(?:\.\d{1,2})?$/D', $normalized)) {
            throw new InvalidArgumentException('invalid_decimal_amount');
        }
        $negative = strpos($normalized, '-') === 0;
        $normalized = ltrim($normalized, '-');
        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        return $negative ? -$cents : $cents;
    }

    private function centsToDecimal(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign . intdiv($absolute, 100) . '.' . str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
