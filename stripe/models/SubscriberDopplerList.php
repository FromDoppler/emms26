<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Doppler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SubscriptionErrors.php');

class SubscriberDopplerList
{
    public function saveSubscription($user)
    {
        $email = $user['email'] ?? 'unknown';

        try {
            Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
            Doppler::subscriber($user);

            return 'success';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (stripos($errorMessage, "Unsubscribed") !== false) {
                Logger::info("doppler_user_unsubscribed", ['email' => $email], 'STRIPE');
                return $this->dobleOptin($user);
            } else {
                Logger::error("doppler_subscription_failed", [
                    'email' => $email,
                    'list' => $user['list'] ?? 'unknown',
                    'error' => $e->getMessage()
                ], 'STRIPE');

                $subscriptionErrors = new SubscriptionErrors();
                $subscriptionErrors->saveSubscriptionErrors($user['email'], $user['list'], $errorMessage);
                return 'fail';
            }
        }
    }

    private function dobleOptin($user)
    {
        try {
            Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
            Doppler::dobleOptin($user);
            return 'success-doble-optin';
        } catch (Exception $e) {
            $errorMessage = json_encode(["dobleOptinDoppler", $e->getMessage(), ['user' => $user]]);
            echo $errorMessage;
            return 'fail-doble-optin';
        }
    }
}
