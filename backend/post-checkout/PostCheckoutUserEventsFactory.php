<?php

class PostCheckoutUserEventsFactory
{
    private const VARIANT_FREE = 'free';
    private const VARIANT_VIP = 'vip';

    public function buildApprovedJobs(
        array $customer,
        array $eventContext,
        array $transaction,
        array $pricing,
        array $input,
        bool $shouldEmitFreeEffects
    ): array
    {
        $jobs = [];

        if ($shouldEmitFreeEffects) {
            $jobs = array_merge(
                $jobs,
                $this->buildVariantApprovedJobs(self::VARIANT_FREE, $customer, $eventContext, $transaction, $pricing, $input)
            );
        }

        $jobs = array_merge(
            $jobs,
            $this->buildVariantApprovedJobs(self::VARIANT_VIP, $customer, $eventContext, $transaction, $pricing, $input)
        );

        return $jobs;
    }

    private function buildVariantApprovedJobs(
        string $variant,
        array $customer,
        array $eventContext,
        array $transaction,
        array $pricing,
        array $input
    ): array {
        $userSnapshot = $this->buildUserSnapshot($variant, $customer, $eventContext, $transaction, $pricing, $input);
        $eventType = $this->resolveEventType($variant);
        $aggregateType = 'checkout_transaction';
        $aggregateId = (int) $transaction['id'];

        return [
            [
                'event_type' => $eventType,
                'job_type' => 'email.send',
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'idempotency_key' => $eventType . ':' . $aggregateId . ':email.send',
                'payload' => ['user' => $userSnapshot, 'subject' => $userSnapshot['subject']],
            ],
            [
                'event_type' => $eventType,
                'job_type' => 'spreadsheet.save',
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'idempotency_key' => $eventType . ':' . $aggregateId . ':spreadsheet.save',
                'payload' => ['user' => $userSnapshot, 'spreadsheetId' => $this->resolveSpreadsheetId($variant, $eventContext)],
            ],
            [
                'event_type' => $eventType,
                'job_type' => 'doppler_list.add',
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'idempotency_key' => $eventType . ':' . $aggregateId . ':doppler_list.add',
                'payload' => ['user' => $userSnapshot],
            ],
        ];
    }

    private function buildUserSnapshot(
        string $variant,
        array $customer,
        array $eventContext,
        array $transaction,
        array $pricing,
        array $input
    ): array {
        $vipFlag = $eventContext['eventType'] === 'ecommerce' ? 'ecommerce' : 'digital_trends';
        $eventsData = ['ecommerce' => 0, 'digital_trends' => 0];
        $eventsData[$vipFlag] = 1;

        $snapshot = [
            'register' => $customer['register'],
            'firstname' => $customer['firstname'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'company' => $customer['company'],
            'emailPlatform' => $customer['emailPlatform'],
            'jobPosition' => $customer['jobPosition'],
            'website' => $customer['website'],
            'ecommerce' => $eventsData['ecommerce'],
            'digital_trends' => $eventsData['digital_trends'],
            'encode_email' => bin2hex(json_encode(['userEmail' => $customer['email']], JSON_UNESCAPED_SLASHES)),
            'privacy' => $customer['privacy'],
            'promotions' => $customer['promotions'],
            'ip' => $customer['ip'] ?? '',
            'country_ip' => $customer['country'],
            'source_utm' => $customer['source_utm'],
            'medium_utm' => $customer['medium_utm'],
            'campaign_utm' => $customer['campaign_utm'],
            'content_utm' => $customer['content_utm'],
            'term_utm' => $customer['term_utm'],
            'emms_ref' => $customer['emms_ref'],
            'origin' => $input['checkout']['origin'] ?? 'checkout',
            'type' => $eventContext['eventFreeId'],
            'form_id' => $eventContext['eventPhase'],
            'list' => $this->resolveDopplerListId($variant, $eventContext),
            'subject' => EmailService::resolveDynamicSubject($variant, $eventContext['eventFreeId']),
            'formOrigin' => $input['checkout']['origin'] ?? 'checkout',
            'payment_status' => $variant === self::VARIANT_FREE
                ? 'Registro FREE'
                : ($pricing['requiresPayment'] ? 'Tarjeta de Crédito' : 'Cupón 100%'),
            'final_price' => $variant === self::VARIANT_FREE
                ? '0.00'
                : $this->decimal($transaction['final_amount']),
        ];

        if ($variant === self::VARIANT_VIP) {
            $snapshot['ticketType'] = $this->resolveTicketType($eventContext);
            $snapshot['payment'] = [
                'price' => $this->decimal($transaction['amount']),
                'discount' => $this->decimal($transaction['discount_amount']),
                'final_price' => $this->decimal($transaction['final_amount']),
                'customer_name' => $customer['firstname'],
                'customer_email' => $customer['email'],
                'customer_country' => $customer['country'],
                'tax_id' => '',
                'payment_status' => $transaction['status'],
                'coupon_id' => $transaction['coupon_code'],
                'coupon_name' => $transaction['coupon_code'],
                'event_name' => $eventContext['eventDisplayName'],
                'event_phase' => $eventContext['eventPhase'],
                'ticket_name' => $transaction['ticket_name'],
                'ticket_price_id' => $transaction['ticket_id'],
            ];
        }

        return $snapshot;
    }

    private function resolveEventType(string $variant): string
    {
        if ($variant === self::VARIANT_FREE) {
            return 'checkout_free_approved';
        }

        return 'checkout_vip_approved';
    }

    private function resolveTicketType(array $eventContext): string
    {
        $prefix = $eventContext['eventType'] === 'ecommerce' ? 'ecommerceVip' : 'digitalTrendsVip';
        $phase = ucfirst($eventContext['eventPhase']);
        return $prefix . $phase;
    }

    private function resolveDopplerListId(string $variant, array $eventContext): int
    {
        if ($variant === self::VARIANT_FREE) {
            if ($eventContext['eventType'] === 'ecommerce') {
                return (int) LIST_LANDING_ECOMMERCE;
            }

            return (int) LIST_LANDING_DIGITALT;
        }

        if ($eventContext['eventType'] === 'ecommerce') {
            return (int) LIST_LANDING_ECOMMERCE_VIP;
        }

        return (int) LIST_LANDING_DIGITALT_VIP;
    }

    private function resolveSpreadsheetId(string $variant, array $eventContext): string
    {
        if ($variant === self::VARIANT_FREE) {
            return (string) ID_SPREADSHEET;
        }

        if ($eventContext['eventType'] === 'ecommerce') {
            return (string) ID_SPREADSHEET_VIP;
        }

        return (string) ID_SPREADSHEET_DT_VIP;
    }

    private function decimal($value): string
    {
        $decimal = trim((string) $value);
        if (preg_match('/^\d+\.\d{2}$/D', $decimal) !== 1) {
            throw new InvalidArgumentException('invalid_checkout_decimal');
        }
        return $decimal;
    }
}
