<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SubscriberDopplerList.php');

class DopplerListAddJobHandler implements UserEventJobHandler
{
    public function jobType(): string
    {
        return 'doppler_list.add';
    }

    public function handle(array $job): void
    {
        $payload = $job['payload'];

        if (empty($payload['user']) || !is_array($payload['user'])) {
            throw UserEventJobException::terminal('Missing or invalid user in doppler_list job payload');
        }
        if (empty($payload['user']['email'])) {
            throw UserEventJobException::terminal('Missing user.email in doppler_list job payload');
        }
        if (!filter_var($payload['user']['email'], FILTER_VALIDATE_EMAIL)) {
            throw UserEventJobException::terminal('Invalid user.email in doppler_list job payload');
        }
        if (empty($payload['user']['list'])) {
            throw UserEventJobException::terminal('Missing user.list in doppler_list job payload');
        }

        $dopplerHandler = new SubscriberDopplerList();
        $result = $dopplerHandler->saveSubscription($payload['user']);

        $accepted = [
            SubscriberDopplerList::RESULT_SUCCESS,
            SubscriberDopplerList::RESULT_SUCCESS_DOUBLE_OPTIN,
            SubscriberDopplerList::RESULT_ALREADY_SUBSCRIBED,
        ];
        if (!in_array($result, $accepted, true)) {
            $lastError = $dopplerHandler->getLastError();
            $message = 'Doppler list handler returned unexpected result: ' . $result;
            if ($lastError) {
                $message .= ' - ' . $lastError;
            }

            if ($this->isTerminalDopplerFailure($dopplerHandler->getLastApiException())) {
                throw UserEventJobException::terminal($message);
            }

            throw UserEventJobException::retryable($message);
        }
    }

    private function isTerminalDopplerFailure(?DopplerApiException $error): bool
    {
        if ($error === null) {
            return false;
        }

        if ($error->getErrorCode() === 12) {
            return true;
        }

        return $error->getErrorCode() === 9
            && $error->getDetail() !== null
            && stripos($error->getDetail(), 'Internal Policies') !== false;
    }
}
