<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/CheckoutModule.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/EmailAddressValidator.php');

class CalculateCheckoutController
{
    public static function handle(): void
    {
        Logger::newRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            self::json(405, ['success' => false, 'error' => 'method_not_allowed']);
            return;
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if ($raw !== '' && json_last_error() !== JSON_ERROR_NONE) {
            self::json(400, ['success' => false, 'error' => 'invalid_json']);
            return;
        }

        $input = CheckoutRequestNormalizer::normalizeCalculate(is_array($input) ? $input : []);
        if ($input === null) {
            self::json(422, ['success' => false, 'error' => 'validation_error']);
            return;
        }

        $customerEmail = $input['customerEmail'] ?? '';
        if ($customerEmail !== '' && !EmailAddressValidator::isValid($customerEmail)) {
            self::json(422, ['success' => false, 'error' => 'validation_error']);
            return;
        }

        try {
            $result = CheckoutModule::createCalculateCheckoutService()->execute($input);
            self::json($result['httpStatus'], $result['payload']);
        } catch (Throwable $e) {
            $correlationId = 'corr_' . bin2hex(random_bytes(16));
            Logger::event('checkout_calculate_error', [
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
