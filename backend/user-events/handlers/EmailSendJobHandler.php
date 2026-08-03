<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');

class EmailSendJobHandler implements UserEventJobHandler
{
    private const CHECKOUT_FREE_EVENT_TYPE = 'checkout_free_approved';
    private const CHECKOUT_VIP_EVENT_TYPE = 'checkout_vip_approved';
    private const CHECKOUT_PHASES = ['pre', 'during', 'post'];

    public function jobType(): string
    {
        return 'email.send';
    }

    public function handle(array $job): void
    {
        $payload = $job['payload'];

        if (empty($payload['user']) || !is_array($payload['user'])) {
            throw new InvalidArgumentException('Missing or invalid user in email job payload');
        }
        if (empty($payload['user']['email'])) {
            throw new InvalidArgumentException('Missing user.email in email job payload');
        }
        if (empty($payload['subject'])) {
            throw new InvalidArgumentException('Missing subject in email job payload');
        }
        if (!filter_var($payload['user']['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid user.email in email job payload');
        }

        $this->assertRequiredUserFields($payload['user'], ['type', 'encode_email']);
        $this->assertCheckoutEmailSnapshot($job, $payload['user']);

        EmailService::sendEmailRegister($payload['user'], $payload['subject'], 'USER_EVENT_EMAIL');
    }

    private function assertCheckoutEmailSnapshot(array $job, array $user): void
    {
        $eventType = $job['event_type'] ?? null;
        if ($eventType !== self::CHECKOUT_FREE_EVENT_TYPE
            && $eventType !== self::CHECKOUT_VIP_EVENT_TYPE) {
            return;
        }

        $phase = $user['form_id'] ?? null;
        if (!is_string($phase) || !in_array($phase, self::CHECKOUT_PHASES, true)) {
            throw new InvalidArgumentException(
                'Missing or invalid user.form_id in checkout email job payload'
            );
        }

        $ticketType = $user['ticketType'] ?? null;
        if ($eventType === self::CHECKOUT_FREE_EVENT_TYPE) {
            if ($ticketType !== null && $ticketType !== '') {
                throw new InvalidArgumentException(
                    'Unexpected user.ticketType in checkout FREE email job payload'
                );
            }
            return;
        }

        $expectedTicketType = $this->resolveExpectedCheckoutVipTicketType($user['type'], $phase);
        if ($expectedTicketType === null || $ticketType !== $expectedTicketType) {
            throw new InvalidArgumentException(
                'Missing or inconsistent user.ticketType in checkout VIP email job payload'
            );
        }
    }

    private function resolveExpectedCheckoutVipTicketType($type, string $phase): ?string
    {
        if (!is_string($type)) {
            return null;
        }

        $ticketTypes = [
            ECOMMERCE => [
                'pre' => 'ecommerceVipPre',
                'during' => 'ecommerceVipDuring',
                'post' => 'ecommerceVipPost',
            ],
            DIGITALTRENDS => [
                'pre' => 'digitalTrendsVipPre',
                'during' => 'digitalTrendsVipDuring',
                'post' => 'digitalTrendsVipPost',
            ],
        ];

        return $ticketTypes[$type][$phase] ?? null;
    }

    private function assertRequiredUserFields(array $user, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $user) || $user[$field] === null || $user[$field] === '') {
                throw new InvalidArgumentException('Missing user.' . $field . ' in email job payload');
            }
        }
    }
}

