<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SpreadSheetGoogle.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

class SpreadsheetSaveJobHandler implements UserEventJobHandler
{
    public function jobType(): string
    {
        return 'spreadsheet.save';
    }

    public function handle(array $job): void
    {
        $payload = $job['payload'];

        if (empty($payload['spreadsheetId'])) {
            throw UserEventJobException::terminal('Missing spreadsheetId in spreadsheet job payload');
        }
        if (empty($payload['user']) || !is_array($payload['user'])) {
            throw UserEventJobException::terminal('Missing or invalid user in spreadsheet job payload');
        }

        $requiredUserFields = ['promotions', 'privacy'];
        foreach ($requiredUserFields as $field) {
            if (!array_key_exists($field, $payload['user'])) {
                throw UserEventJobException::terminal('Missing user.' . $field . ' in spreadsheet job payload');
            }
        }

        $this->assertRequiredNonEmptyUserFields($payload['user'], ['firstname', 'email']);
        if (!filter_var($payload['user']['email'], FILTER_VALIDATE_EMAIL)) {
            throw UserEventJobException::terminal('Invalid user.email in spreadsheet job payload');
        }

        $context = [
            'email' => $payload['user']['email'] ?? null,
            'spreadsheet_id' => $payload['spreadsheetId'] ?? null,
        ];

        try {
            Logger::debug('spreadsheet_save_started', $context, 'USER_EVENT_SPREADSHEET');
            SpreadSheetGoogle::write($payload['spreadsheetId'], $payload['user'], null);
            Logger::debug('spreadsheet_save_completed', $context, 'USER_EVENT_SPREADSHEET');
        } catch (Throwable $e) {
            Logger::error('spreadsheet_save_failed', array_merge($context, ['error' => $e->getMessage(), 'code' => $e->getCode()]), 'USER_EVENT_SPREADSHEET');
            throw $e;
        }
    }

    private function assertRequiredNonEmptyUserFields(array $user, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $user) || $user[$field] === null || $user[$field] === '') {
                throw UserEventJobException::terminal('Missing user.' . $field . ' in spreadsheet job payload');
            }
        }
    }
}
