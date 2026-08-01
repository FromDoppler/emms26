<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/CheckoutModule.php');

class GetCheckoutController
{
    public static function handle(): void
    {
        Logger::newRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Allow: GET');
            self::json(405, ['success' => false, 'error' => 'method_not_allowed']);
            return;
        }

        if (!array_key_exists('payment_id', $_GET)) {
            self::json(400, ['success' => false, 'error' => 'payment_id_required']);
            return;
        }

        $rawPaymentId = $_GET['payment_id'];
        if (!is_string($rawPaymentId)) {
            self::json(422, ['success' => false, 'error' => 'validation_error']);
            return;
        }

        if (trim($rawPaymentId) === '') {
            self::json(400, ['success' => false, 'error' => 'payment_id_required']);
            return;
        }

        $paymentId = trim($rawPaymentId);

        try {
            $result = CheckoutModule::createGetCheckoutService()->execute($paymentId);
            self::json($result['httpStatus'], $result['payload']);
        } catch (Throwable $e) {
            $correlationId = 'corr_' . bin2hex(random_bytes(16));
            Logger::event('payment_error', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ], 'PAYMENTS', Logger::ERROR);
            self::json(500, [
                'success' => false,
                'error' => 'internal_error',
                'correlationId' => $correlationId,
            ]);
        }
    }

    private static function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
