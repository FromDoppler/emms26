<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/EmailTemplateManager.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Relay.php');

class EmailService
{
    public static function sendEmailRegister($user, $subject, $service = 'REGISTER')
    {
        Logger::debug("email_service_started", ['email' => $user['email']], $service);

        try {
            $html = EmailTemplateManager::getTemplateForUser($user);
            Logger::debug("template_loaded", ['email' => $user['email'], 'length' => strlen($html)], $service);

            Relay::sendEmail($user['email'], $subject, $html);
            Logger::debug("relay_sent", ['email' => $user['email']], $service);
        } catch (Exception $e) {
            Logger::error("email_service_failed", [
                'email' => $user['email'],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], $service);
            throw $e;
        }
    }

    public static function sendEmailSponsor($sponsor, $service = 'SPONSOR')
    {
        Logger::debug("sponsor_email_started", ['email' => EMAIL_SPONSORS], $service);

        try {
            $html = EmailTemplateManager::getTemplateForSponsor($sponsor);
            $currentYear = date("Y");
            $subject = 'NUEVA SOLICITUD DE SPONSOR PARA EMMS -' . $currentYear;
            Relay::sendEmail(EMAIL_SPONSORS, $subject, $html);

            Logger::debug("sponsor_email_sent", ['email' => EMAIL_SPONSORS], $service);
        } catch (Exception $e) {
            Logger::error("sponsor_email_failed", [
                'email' => EMAIL_SPONSORS,
                'error' => $e->getMessage()
            ], $service);
            throw $e;
        }
    }

    public static function sendEmailPartner($partner, $service = 'PARTNER')
    {
        Logger::debug("partner_email_started", ['email' => EMAIL_PARTNERS], $service);

        try {
            $html = EmailTemplateManager::getTemplateForPartner($partner);
            $currentYear = date("Y");
            $subject = 'NUEVA SOLICITUD DE PARTNER PARA EMMS -' . $currentYear;
            Relay::sendEmail(EMAIL_PARTNERS, $subject, $html);

            Logger::debug("partner_email_sent", ['email' => EMAIL_PARTNERS], $service);
        } catch (Exception $e) {
            Logger::error("partner_email_failed", [
                'email' => EMAIL_PARTNERS,
                'error' => $e->getMessage()
            ], $service);
            throw $e;
        }
    }

     public static function resolveDynamicSubject(string $userType, string $eventType = DIGITALTRENDS): string
    {
        $phaseData = processPhaseToShow($eventType);
        $phaseToShow = $phaseData['phaseToShow'] ?? 'pre';

        $subjects = [
            'free' => [
                ECOMMERCE => [
                    'pre' => SUBJECT_FREE_PRE_ECOMMERCE,
                    'during' => SUBJECT_FREE_DURING_ECOMMERCE,
                    'post' => SUBJECT_FREE_POST_ECOMMERCE,
                ],
                DIGITALTRENDS => [
                    'pre' => SUBJECT_FREE_PRE_DIGITALT,
                    'during' => SUBJECT_FREE_DURING_DIGITALT,
                    'post' => SUBJECT_FREE_POST_DIGITALT,
                ]
            ],
            'vip' => [
                ECOMMERCE => [
                    'pre' => SUBJECT_VIP_PRE_ECOMMERCE,
                    'during' => SUBJECT_VIP_DURING_ECOMMERCE,
                    'post' => SUBJECT_VIP_POST_ECOMMERCE,
                ],
                DIGITALTRENDS => [
                    'pre' => SUBJECT_VIP_PRE_DIGITALT,
                    'during' => SUBJECT_VIP_DURING_DIGITALT,
                    'post' => SUBJECT_VIP_POST_DIGITALT,
                ]
            ]
        ];

        $selected = $subjects[$userType][$eventType][$phaseToShow];

        return $selected;
    }

}
