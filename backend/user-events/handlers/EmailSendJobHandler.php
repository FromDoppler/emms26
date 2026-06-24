<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');

class EmailSendJobHandler implements UserEventJobHandler
{
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

        EmailService::sendEmailRegister($payload['user'], $payload['subject'], 'USER_EVENT_EMAIL');
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

