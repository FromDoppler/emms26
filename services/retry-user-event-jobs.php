<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobsRetryService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobHandlerRegistry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/EmailSendJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/SpreadsheetSaveJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/DopplerListAddJobHandler.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

$expectedToken = trim((string) SALES_REPORT_TRIGGER_TOKEN);
$providedToken = trim((string) ($_SERVER['HTTP_X_USER_EVENT_RETRY_TOKEN'] ?? ''));

if ($providedToken === '') {
    $authorization = trim((string) (
        $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? ''
    ));
    if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
        $providedToken = trim($matches[1]);
    }
}

if ($expectedToken === '') {
    Logger::event('user_event_retry_config_missing', [], 'USER_EVENT_RETRY', Logger::ERROR);
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'user_event_retry_unavailable']);
    exit;
}

if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

$db = null;

try {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $jobsRepository = new UserEventJobsRepository($db);
    $runnerFactory = static function () use ($jobsRepository): InlineUserEventJobRunner {
        return new InlineUserEventJobRunner(
            $jobsRepository,
            new UserEventJobHandlerRegistry([
                new EmailSendJobHandler(),
                new SpreadsheetSaveJobHandler(),
                new DopplerListAddJobHandler(),
            ])
        );
    };

    $service = new UserEventJobsRetryService($jobsRepository, $runnerFactory);
    $result = $service->run();

    Logger::event('user_event_retry_finished', $result, 'USER_EVENT_RETRY', Logger::INFO);

    echo json_encode(['success' => true] + $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    Logger::event('user_event_retry_failed', [
        'error' => substr($e->getMessage(), 0, 500),
    ], 'USER_EVENT_RETRY', Logger::ERROR);

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'user_event_retry_failed']);
} finally {
    if ($db instanceof DB) {
        try {
            $db->close();
        } catch (Throwable $ignored) {
        }
    }
}
