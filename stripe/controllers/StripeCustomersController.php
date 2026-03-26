<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php';
require_once 'models/StripeCustomersDatabase.php';
require_once 'models/RegisteredDatabase.php';
require_once 'utils/sendEmail.php';
require_once 'utils/toHex.php';
require_once 'models/StripeCustomersJobsDatabase.php';
require_once 'workers/core/RedisManager.php';
require_once 'utils/SpreadSheetGoogle.php';

class StripeCustomersController
{
    private $db;

    public function __construct()
    {
        $this->db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 'utf8mb4');
    }

    public function handleRequest()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                Logger::error("invalid_method", ['method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'], 'STRIPE');
                http_response_code(405);
                throw new Exception('Metodo no permitido');
            }

            $jsonData = $this->getJsonDataFromRequest();
            $email = $jsonData['customer_email'] ?? 'unknown';

            Logger::info("subscription_received", [
                'session_id' => $jsonData['session_id'] ?? $jsonData['stripe_session_id'] ?? 'unknown',
                'email' => $email,
                'final_price' => (float)($jsonData['final_price'] ?? 0),
                'payment_status' => $jsonData['payment_status'] ?? 'unknown'
            ], 'STRIPE');

            $result = $this->processAndSaveSubscription($jsonData);

            return ['message' => 'Subscription saved successfully', 'data' => $result];
        } catch (Exception $e) {
            Logger::error("controller_failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'STRIPE');
            http_response_code(500);
            throw $e;
        }
    }

    private function getJsonDataFromRequest()
    {
        $json = file_get_contents('php://input');
        $data = json_decode(mb_convert_encoding($json, 'UTF-8'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error("json_invalid", ['error' => json_last_error_msg()], 'STRIPE');
            http_response_code(400);
            throw new Exception('JSON incorrecto');
        }

        return $data;
    }

    private function updateRegisteredUser($UserData)
    {
        $email = $UserData['customer_email'] ?? 'unknown';
        $registeredModel = new RegisteredDatabase($this->db);
        $existingUser = $registeredModel->getRegisteredByEmail($email);

        if ($existingUser) {
            $registeredModel->updateDTVIPByEmail($email);
            return ['id' => $existingUser['id'], 'isNew' => false];
        }

        $newId = $registeredModel->insertAutomatedRegistered($UserData);
        return ['id' => $newId, 'isNew' => true];
    }

    private function processAndSaveSubscription($UserData)
    {
        $email = $UserData['customer_email'] ?? 'unknown';

        $userContext = $this->saveCustomerAndPrepareUser($UserData);

        if (!$userContext) {
            return 'readonly';
        }

        return $this->enqueueOrFallback(
            $userContext['user'],
            $userContext['registeredId'],
            $userContext['stripeCustomerId'],
            $email
        );
    }

    private function saveCustomerAndPrepareUser($UserData)
    {
        $email = $UserData['customer_email'] ?? 'unknown';
        $stripeModel = new StripeCustomersDatabase($this->db);

        $existingCustomer = $stripeModel->getCustomerBySessionId($UserData['session_id']);
        if ($existingCustomer) {
            Logger::duplicate("customer_duplicate", ['email' => $email, 'session_id' => $UserData['session_id'] ?? null], 'STRIPE');
            return false;
        }

        $stripeModel->insertCustomer($UserData);
        $stripeCustomerId = $this->db->lastInsertID();

        $registeredResult = $this->updateRegisteredUser($UserData);
        $registeredId = $registeredResult['id'];
        $isNew = $registeredResult['isNew'];

        Logger::info("customer_upserted", [
            'registered_id' => $registeredId,
            'stripe_customer_id' => $stripeCustomerId,
            'email' => $email,
            'is_new_user' => $isNew
        ], 'STRIPE');

        if ($isNew) {
            // FREE → procesar secuencial
            $user = $this->prepareUserDataFree($UserData, LIST_LANDING_DIGITALT);
            $this->processAutomatedFreeUser($user, $email, ID_SPREADSHEET);
        }

        // VIP → devolver datos para encolado
        $user = $this->prepareUserDataVip($UserData, LIST_LANDING_DIGITALT_VIP);
        return compact('registeredId', 'stripeCustomerId', 'user');
    }

    private function enqueueOrFallback($user, $registeredId, $stripeCustomerId, $email)
    {
        $useQueue = defined('USE_JOB_QUEUE') && USE_JOB_QUEUE && class_exists('Redis');

        if ($useQueue) {
            $jobModel = new StripeCustomersJobsDatabase($this->db);
            $jobId = $jobModel->createJob($registeredId, $stripeCustomerId, $user);

            Logger::info("job_created", [
                'job_id' => $jobId,
                'registered_id' => $registeredId,
                'stripe_customer_id' => $stripeCustomerId,
                'email' => $email
            ], 'STRIPE');

            $publishResults = $this->publishJobToStreams($jobId, $email);
            $allStreams = RedisManager::getAllStreams();

            if (count($publishResults) === count($allStreams)) {
                Logger::success("subscription_queued", [
                    'email' => $email,
                    'job_id' => $jobId,
                    'registered_id' => $registeredId,
                    'stripe_customer_id' => $stripeCustomerId,
                    'streams' => $publishResults
                ], 'STRIPE');
                return true;
            }

            Logger::warning("queue_fallback_triggered", [
                'job_id' => $jobId,
                'email' => $email,
                'reason' => empty($publishResults) ? 'redis_failed' : 'redis_partial'
            ], 'STRIPE');
        } else {
            Logger::info('queue_unavailable', ['email' => $email], 'STRIPE');
        }

        return $this->processFallback($user, $email);
    }

    private function processFallback($user, $email)
    {
        require_once 'models/SubscriberDopplerList.php';
        $ok = true;

        try {
            sendEmail($user, $user['subject']);
            Logger::info("email_sent", ['email' => $email], 'STRIPE');
        } catch (Exception $e) {
            Logger::error("email_failed_direct", ['email' => $email, 'error' => $e->getMessage()], 'STRIPE');
            $ok = false;
        }

        try {
            saveSubscriptionSpreadSheet($user, ID_SPREADSHEET_DT_VIP);
            Logger::info("spreadsheet_saved", ['email' => $email], 'STRIPE');
        } catch (Exception $e) {
            Logger::error("spreadsheet_failed_direct", ['email' => $email, 'error' => $e->getMessage()], 'STRIPE');
            $ok = false;
        }

        try {
            $doppler = new SubscriberDopplerList();
            $result = $doppler->saveSubscription($user);
            Logger::info("doppler_added", ['email' => $email, 'result' => $result], 'STRIPE');
        } catch (Exception $e) {
            Logger::error("doppler_failed_direct", ['email' => $email, 'error' => $e->getMessage()], 'STRIPE');
            $ok = false;
        }

        if ($ok) {
            Logger::success("subscription_completed_direct", ['email' => $email], 'STRIPE');
        } else {
            Logger::warning("subscription_partial_direct", ['email' => $email], 'STRIPE');
        }

        return $ok;
    }

    private function processAutomatedFreeUser($user, $email, $spreadsheetId)
    {
        require_once 'models/SubscriberDopplerList.php';
        $ok = true;

        try {
            saveSubscriptionSpreadSheet($user, $spreadsheetId);
            Logger::info("spreadsheet_saved", ['email' => $email], 'AUTOMATED_FREE_USER');
        } catch (Exception $e) {
            Logger::error("spreadsheet_failed_direct", ['email' => $email, 'error' => $e->getMessage()], 'AUTOMATED_FREE_USER');
            $ok = false;
        }

        try {
            $doppler = new SubscriberDopplerList();
            $result = $doppler->saveSubscription($user);
            Logger::info("doppler_added", ['email' => $email, 'result' => $result], 'AUTOMATED_FREE_USER');
        } catch (Exception $e) {
            Logger::error("doppler_failed_direct", ['email' => $email, 'error' => $e->getMessage()], 'AUTOMATED_FREE_USER');
            $ok = false;
        }

        if ($ok) {
            Logger::success("subscription_completed_direct", ['email' => $email], 'AUTOMATED_FREE_USER');
        } else {
            Logger::warning("subscription_partial_direct", ['email' => $email], 'AUTOMATED_FREE_USER');
        }

        return $ok;
    }

    private function publishJobToStreams($jobId, $email)
    {
        if (!class_exists('Redis')) return [];

        try {
            $redisManager = RedisManager::getInstance();
            $streams = RedisManager::getAllStreams();
            $results = [];

            foreach ($streams as $stream) {
                $results[$stream] = $redisManager->addToStream($stream, $jobId);
                Logger::info('job_published', ['stream'=>$stream,'job_id'=>$jobId,'message_id'=>$results[$stream]], 'STRIPE');
            }

            return $results;
        } catch (Exception $e) {
            Logger::error("job_publish_failed", ['email'=>$email,'job_id'=>$jobId,'error'=>$e->getMessage()], 'STRIPE');
            return [];
        }
    }

    private function resolveTicketType(string $event): string
    {
        $eventTicketMaps = [
            DIGITALTRENDS => [
                'pre' => 'digitalTrendsVipPre',
                'during' => 'digitalTrendsVipDuring',
                'post' => 'digitalTrendsVipPost',
            ],
            ECOMMERCE => [
                'pre' => 'ecommerceVipPre',
                'during' => 'ecommerceVipDuring',
                'post' => 'ecommerceVipPost',
            ],
        ];

        if (!isset($eventTicketMaps[$event])) {
            throw new LogicException("Evento no válido: {$event}");
        }

        $tiketTypeMap = $eventTicketMaps[$event];

        $phaseData = processPhaseToShow($event);
        $phaseToShow = $phaseData['phaseToShow'] ?? null;

        if (!isset($tiketTypeMap[$phaseToShow])) {
            throw new LogicException("Fase no válida para el evento: {$phaseToShow}");
        }

        return $tiketTypeMap[$phaseToShow];
    }

    private function prepareUserDataVip($UserData, $listId)
    {
        $user = $this->CreateUserObj($UserData);
        $user['list'] = $listId;
        $user['subject'] = EmailService::resolveDynamicSubject('vip', DIGITALTRENDS);
        $user['ticketType'] = $this->resolveTicketType(DIGITALTRENDS);
        $user['final_price'] = $UserData['final_price'] ?? 0;
        $user['payment_status'] = $UserData['payment_status'] ?? '';
        $user['stripe'] = $UserData;
        return $user;
    }

       private function prepareUserDataFree($UserData, $listId)
    {
        $user = $this->CreateUserObj($UserData);
        $user['list'] = $listId;
        $user['subject'] = EmailService::resolveDynamicSubject('free', DIGITALTRENDS);
        $user['emms_ref'] = "AUTOMATED_FREE_USER";
        return $user;
    }

    private function CreateUserObj($UserData)
    {
        $email = $UserData['customer_email'] ?? 'unknown';
        $currentEvent = getCurrentEvent();

        $encode_email = toHex(json_encode([
            'userEmail' => $email,
            'userEvents' => json_encode([$currentEvent['freeId'], $currentEvent['vipId']])
        ]));

        $userObj = [
            'register' => date("Y-m-d h:i:s A"),
            'firstname' => $UserData['customer_name'],
            'email' => $email,
            'company' => '',
            'jobPosition' => '',
            'phone' => '',
            'ecommerce' => 0,
            'digital_trends' => 1,
            'encode_email' => $encode_email,
            'privacy' => true,
            'promotions' => false,
            'ip' => '',
            'country_ip' => '',
            'source_utm' => $UserData['utm_source'] ?? '',
            'medium_utm' => $UserData['utm_medium'] ?? '',
            'campaign_utm' => $UserData['utm_campaign'] ?? '',
            'content_utm' => $UserData['utm_content'] ?? '',
            'term_utm' => $UserData['utm_term'] ?? '',
            'origin' => $UserData['origin'] ?? '',
            'type' => $currentEvent['freeId'],
            'form_id' => "pre",
        ];

        return $userObj;
    }
}
