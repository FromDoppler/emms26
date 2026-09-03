<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/domain/CheckoutTransactionStatus.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/sales-report/SalesReportRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/sales-report/SlackChatClient.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/sales-report/SalesReportService.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

$expectedToken = trim((string) SALES_REPORT_TRIGGER_TOKEN);
$slackBotToken = trim((string) SLACK_SALES_BOT_TOKEN);
$slackChannelId = trim((string) SLACK_SALES_CHANNEL_ID);

if ($expectedToken === '' || $slackBotToken === '' || $slackChannelId === '') {
    Logger::event('sales_report_config_missing', [], 'SALES_REPORT', Logger::ERROR);
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'sales_report_unavailable']);
    exit;
}

$providedToken = trim((string) ($_SERVER['HTTP_X_SALES_REPORT_TOKEN'] ?? ''));

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

if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'unauthorized']);
    exit;
}

$db = null;

try {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $service = new SalesReportService(
        new SalesReportRepository($db),
        new SlackChatClient($slackBotToken, $slackChannelId)
    );

    $result = $service->publish();

    Logger::event('sales_report_publish_finished', [
        'status' => $result['status'],
        'period_start' => $result['period_start'],
        'period_end' => $result['period_end'],
        'sales_count' => $result['sales_count'],
        'displayed_sales_count' => $result['displayed_sales_count'] ?? 0,
    ], 'SALES_REPORT', Logger::INFO);

    echo json_encode(['success' => true] + $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    Logger::event('sales_report_publish_failed', [
        'error' => substr($e->getMessage(), 0, 500),
    ], 'SALES_REPORT', Logger::ERROR);

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'sales_report_failed']);
} finally {
    if ($db instanceof DB) {
        try {
            $db->close();
        } catch (Throwable $ignored) {
        }
    }
}
