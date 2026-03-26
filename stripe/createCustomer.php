<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once 'controllers/StripeCustomersController.php';

Logger::newRequest(); // Initialize new request correlation
Logger::info("webhook_received", [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
], 'STRIPE');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    $StripeCustomersController = new StripeCustomersController();

    $result = $StripeCustomersController->handleRequest();

    $response['success'] = true;
    $response['message'] = 'Subscription processed successfully';
    $response['data'] = $result;

    Logger::info("webhook_completed", [], 'STRIPE');
    echo json_encode($response);
} catch (Exception $e) {
    $errorDetails = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'stack_trace' => $e->getTraceAsString(),
        'request_data' => file_get_contents('php://input'),
        'server_info' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]
    ];

    Logger::error("webhook_failed", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 'STRIPE');

    http_response_code(500);
    // Don't expose internal error details to client in production
    if (defined('PRODUCTION') && PRODUCTION) {
        $response['message'] = 'Internal server error occurred';
    } else {
        $response['message'] = 'Internal server error: ' . $e->getMessage();
        $response['error_details'] = $errorDetails;
    }

    echo json_encode($response);
}
