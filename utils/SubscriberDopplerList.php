<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Doppler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SubscriptionErrors.php');

class SubscriberDopplerList
{
    const RESULT_SUCCESS = 'success';
    const RESULT_ALREADY_SUBSCRIBED = 'already_subscribed';
    const RESULT_FAIL = 'fail';
    const RESULT_SUCCESS_DOUBLE_OPTIN = 'success-doble-optin';
    const RESULT_FAIL_DOUBLE_OPTIN = 'fail-doble-optin';

    private $lastError = null;
    private $lastApiException = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function getLastApiException(): ?DopplerApiException
    {
        return $this->lastApiException;
    }

    public function saveSubscription($user)
    {
        $this->lastError = null;
        $this->lastApiException = null;
        $email = $user['email'] ?? 'unknown';
        $list = $user['list'] ?? 'unknown';

        if (empty($user['email']) || empty($user['list'])) {
            $this->lastError = 'Missing email or list';
            Logger::error('doppler_subscription_invalid_user', [
                'email' => $email,
                'list' => $list,
            ], 'USER_EVENT');

            return self::RESULT_FAIL;
        }

        try {
            Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
            Doppler::subscriber($user);

            return self::RESULT_SUCCESS;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->lastError = $errorMessage;
            $this->lastApiException = $e instanceof DopplerApiException ? $e : null;

            $detail = $this->lastApiException ? $this->lastApiException->getDetail() : null;
            if (stripos($detail ?? $errorMessage, 'Unsubscribed') !== false) {
                Logger::info('doppler_user_unsubscribed', ['email' => $email], 'USER_EVENT');
                return $this->doubleOptin($user);
            }

            if ($this->isAlreadySubscribedError($errorMessage)) {
                Logger::info('doppler_user_already_subscribed', [
                    'email' => $email,
                    'list' => $list,
                ], 'USER_EVENT');

                return self::RESULT_ALREADY_SUBSCRIBED;
            }

            Logger::error('doppler_subscription_failed', [
                'email' => $email,
                'list' => $list,
                'error' => $errorMessage,
            ], 'USER_EVENT');

            $subscriptionErrors = new SubscriptionErrors();
            $subscriptionErrors->saveSubscriptionErrors($email, $list, $errorMessage);

            return self::RESULT_FAIL;
        }
    }

    private function doubleOptin($user)
    {
        try {
            Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
            Doppler::dobleOptin($user);
            return self::RESULT_SUCCESS_DOUBLE_OPTIN;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->lastError = $errorMessage;
            $this->lastApiException = $e instanceof DopplerApiException ? $e : null;
            Logger::error('doppler_double_optin_failed', [
                'email' => $user['email'] ?? 'unknown',
                'list' => $user['list'] ?? 'unknown',
                'error' => $errorMessage,
            ], 'USER_EVENT');

            $subscriptionErrors = new SubscriptionErrors();
            $subscriptionErrors->saveSubscriptionErrors(
                $user['email'] ?? 'unknown',
                $user['list'] ?? 'unknown',
                $errorMessage
            );

            return self::RESULT_FAIL_DOUBLE_OPTIN;
        }
    }

    private function isAlreadySubscribedError(string $errorMessage): bool
    {
        if (!preg_match('/already subscribed|subscriber already exists|email already exists|duplicated subscriber/i', $errorMessage)) {
            return false;
        }

        // Exclude only when the error clearly refers to a list or account not found/closed, not the subscriber
        if (preg_match('/list.*(not found|does not exist|closed)|account.*(not found|does not exist)/i', $errorMessage)) {
            return false;
        }

        return true;
    }
}
