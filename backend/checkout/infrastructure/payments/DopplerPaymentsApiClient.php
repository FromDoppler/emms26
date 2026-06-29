<?php

class DopplerPaymentsApiClient implements PaymentProviderClient
{
    private $jwtService;

    public function __construct(SuperUserJwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function purchase(ProviderPaymentRequest $request): ProviderPaymentResult
    {
        $startedAt = microtime(true);
        $authorizationData = null;
        $authorizationCode = null;
        $transactionLinkId = null;
        $authorizationProviderTransactionId = null;
        $providerTransactionId = null;

        if (DOPPLER_PAYMENTS_API_URL === '') {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::ERROR,
                'provider' => 'doppler-payments-api',
                'responseCode' => 'provider_config_missing',
                'responseMessage' => 'DOPPLER_PAYMENTS_API_URL is required.',
                'rawResponse' => [],
            ]));
        }

        try {
            $jwt = $this->jwtService->getBearerToken();
        } catch (Exception $e) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::ERROR,
                'provider' => 'doppler-payments-api',
                'responseCode' => 'provider_auth_config_error',
                'responseMessage' => $e->getMessage(),
                'rawResponse' => [
                    'exception' => $e->getMessage(),
                ],
            ]));
        }

        Logger::event('payment_provider_call_started', [
            'correlation_id' => $request->correlationId,
            'checkout_transaction_id' => $request->checkoutTransactionId,
            'checkout_public_id' => $request->publicId,
            'provider' => 'doppler-payments-api',
        ], 'PAYMENTS', Logger::INFO);

        if ($request->customerId > 2147483647) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::ERROR,
                'provider' => 'doppler-payments-api',
                'responseCode' => 'customer_id_too_large',
                'responseMessage' => 'CustomerId must be <= 2147483647.',
                'rawResponse' => [],
            ]));
        }

        $authorizationPayload = [
            'worldPayLowValueToken' => $request->worldPayLowValueToken,
            'encryptedCreditCard' => [
                'expirationMonth' => (int) $request->ccExpMonth,
                'expirationYear' => (int) $request->ccExpYear,
                'holderName' => $request->customerName ?: 'EMMS VIP',
                'cardType' => (int) $request->ccType,
            ],
        ];

        try {
            $authorizationResponse = $this->jsonRequest(DOPPLER_PAYMENTS_API_URL . '/authorization', $authorizationPayload, $jwt, $request);
            if ($this->isProviderErrorResponse($authorizationResponse)) {
                return $this->buildResultFromProviderErrorResponse(
                    $request,
                    $startedAt,
                    $authorizationResponse,
                    'authorization',
                    null,
                    null,
                    null,
                    null
                );
            }

            $authorizationData = $this->decodeResponse($authorizationResponse['body']);
            $authorizationCode = $this->normalizeResponseCode($authorizationData['ResponseCode'] ?? $authorizationData['responseCode'] ?? null);
            $authorizationProviderTransactionId = $this->extractProviderTransactionId($authorizationData);

            if ($authorizationCode === null) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::ERROR,
                    'provider' => 'doppler-payments-api',
                    'providerTransactionId' => $authorizationProviderTransactionId,
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Authorization response did not include ResponseCode.',
                    'rawResponse' => ['authorization' => $authorizationData],
                ]));
            }

            if ($authorizationCode !== '000') {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::REJECTED,
                    'provider' => 'doppler-payments-api',
                    'providerTransactionId' => $authorizationProviderTransactionId,
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => $authorizationCode ?: (string) $authorizationResponse['status'],
                    'responseMessage' => $authorizationData['Message'] ?? $authorizationData['message'] ?? 'Authorization rejected.',
                    'rawResponse' => ['authorization' => $authorizationData],
                ]));
            }

            $tokenizedPan = $authorizationData['TokenizedPan'] ?? $authorizationData['tokenizedPan'] ?? null;
            $transactionLinkId = $authorizationData['TransactionLinkID'] ?? $authorizationData['TransactionLinkId'] ?? $authorizationData['transactionLinkID'] ?? $authorizationData['transactionLinkId'] ?? null;

            if (!is_string($tokenizedPan) || $tokenizedPan === '') {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::ERROR,
                    'provider' => 'doppler-payments-api',
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => 'missing_tokenized_pan',
                    'responseMessage' => 'Authorization did not return TokenizedPan.',
                    'rawResponse' => ['authorization' => $authorizationData],
                ]));
            }

            $purchasePayload = [
                'paymentToken' => $tokenizedPan,
                'amount' => (float) $request->finalAmount,
                'customerId' => (int) $request->customerId,
                'customerEmail' => $request->customerEmail,
                'encryptedCreditCard' => [
                    'expirationMonth' => (int) $request->ccExpMonth,
                    'expirationYear' => (int) $request->ccExpYear,
                    'holderName' => $request->customerName ?: 'EMMS VIP',
                    'cardType' => (int) $request->ccType,
                ],
            ];

            if ($transactionLinkId !== null && $transactionLinkId !== '') {
                $purchasePayload['transactionLinkId'] = $transactionLinkId;
            }

            $purchaseResponse = $this->jsonRequest(DOPPLER_PAYMENTS_API_URL . '/purchase', $purchasePayload, $jwt, $request);
            if ($this->isProviderErrorResponse($purchaseResponse)) {
                return $this->buildResultFromProviderErrorResponse(
                    $request,
                    $startedAt,
                    $purchaseResponse,
                    'purchase',
                    $authorizationData,
                    $authorizationCode,
                    $transactionLinkId,
                    $authorizationProviderTransactionId
                );
            }

            $purchaseData = $this->decodeResponse($purchaseResponse['body']);
            $purchaseCode = $this->normalizeResponseCode($purchaseData['ResponseCode'] ?? $purchaseData['responseCode'] ?? null);
            $providerTransactionId = $this->extractProviderTransactionId($purchaseData) ?? $authorizationProviderTransactionId;

            if ($purchaseCode === null) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::ERROR,
                    'provider' => 'doppler-payments-api',
                    'providerTransactionId' => $providerTransactionId,
                    'transactionLinkId' => $transactionLinkId,
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Purchase response did not include ResponseCode.',
                    'rawResponse' => [
                        'authorization' => $authorizationData,
                        'purchase' => $purchaseData,
                    ],
                ]));
            }

            $status = $purchaseCode === '000' ? ProviderPaymentResult::APPROVED : ProviderPaymentResult::REJECTED;

            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => $status,
                'provider' => 'doppler-payments-api',
                'providerTransactionId' => $providerTransactionId,
                'authorizationNumber' => $purchaseData['AuthorizationNumber'] ?? $purchaseData['authorizationNumber'] ?? null,
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'purchaseResponseCode' => $purchaseCode,
                'responseCode' => $purchaseCode ?: (string) $purchaseResponse['status'],
                'responseMessage' => $purchaseData['Message'] ?? $purchaseData['message'] ?? null,
                'rawResponse' => [
                    'authorization' => $authorizationData,
                    'purchase' => $purchaseData,
                ],
            ]));
        } catch (Exception $e) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::ERROR,
                'provider' => 'doppler-payments-api',
                'providerTransactionId' => $providerTransactionId ?? $authorizationProviderTransactionId,
                'authorizationResponseCode' => $authorizationCode,
                'transactionLinkId' => $transactionLinkId,
                'responseCode' => 'provider_exception',
                'responseMessage' => $e->getMessage(),
                'rawResponse' => [
                    'authorization' => $authorizationData,
                    'exception' => $e->getMessage(),
                ],
            ]));
        }
    }

    private function finish(ProviderPaymentRequest $request, float $startedAt, ProviderPaymentResult $result): ProviderPaymentResult
    {
        Logger::event('payment_provider_call_finished', [
            'correlation_id' => $request->correlationId,
            'checkout_transaction_id' => $request->checkoutTransactionId,
            'checkout_public_id' => $request->publicId,
            'provider' => $result->provider,
            'status' => $result->status,
            'response_code' => $result->responseCode,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ], 'PAYMENTS', $result->status === ProviderPaymentResult::ERROR ? Logger::ERROR : Logger::INFO);

        return $result;
    }

    private function decodeResponse(string $body): array
    {
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new Exception('Provider returned invalid JSON.');
        }

        return $decoded;
    }

    private function normalizeResponseCode($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $code = trim((string) $value);
        return $code === '' ? null : $code;
    }

    private function summarizeProviderErrorBody(string $body, string $providerStep, int $statusCode): array
    {
        return [
            'provider_step' => $providerStep,
            'status_code' => $statusCode,
            'body_hash' => hash('sha256', $body),
            'body_length' => strlen($body),
        ];
    }

    private function isProviderErrorResponse(array $response): bool
    {
        $statusCode = (int) ($response['status'] ?? 0);
        return $statusCode < 200 || $statusCode >= 300;
    }

    private function buildResultFromProviderErrorResponse(
        ProviderPaymentRequest $request,
        float $startedAt,
        array $providerResponse,
        string $providerStep,
        ?array $authorizationData,
        ?string $authorizationCode,
        ?string $transactionLinkId,
        ?string $fallbackProviderTransactionId
    ): ProviderPaymentResult {
        $statusCode = (int) ($providerResponse['status'] ?? 0);
        $body = (string) ($providerResponse['body'] ?? '');
        $paymentError = $this->tryDecodeResponse($body);
        $rawResponseKey = $providerStep . '_error';

        if ($paymentError !== null && $this->looksLikePaymentError($paymentError)) {
            return $this->buildResultFromPaymentError(
                $request,
                $startedAt,
                $paymentError,
                $authorizationData,
                $authorizationCode,
                $transactionLinkId,
                $this->extractProviderTransactionId($paymentError) ?? $fallbackProviderTransactionId,
                $rawResponseKey
            );
        }

        $providerTransactionId = $paymentError !== null
            ? ($this->extractProviderTransactionId($paymentError) ?? $fallbackProviderTransactionId)
            : $fallbackProviderTransactionId;

        $rawResponse = [];

        if ($authorizationData !== null) {
            $rawResponse['authorization'] = $authorizationData;
        }

        $rawResponse[$rawResponseKey . '_raw'] = $this->summarizeProviderErrorBody($body, $providerStep, $statusCode);

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => ProviderPaymentResult::ERROR,
            'provider' => 'doppler-payments-api',
            'providerTransactionId' => $providerTransactionId,
            'transactionLinkId' => $transactionLinkId,
            'authorizationResponseCode' => $authorizationCode,
            'responseCode' => 'provider_invalid_response',
            'responseMessage' => 'Provider returned HTTP ' . $statusCode . ' with an unexpected body.',
            'rawResponse' => $rawResponse,
        ]));
    }

    private function jsonRequest(string $url, array $payload, string $jwt, ProviderPaymentRequest $request): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new Exception('Could not encode provider payload.');
        }

        // Propagate checkout correlation/idempotency headers to Doppler Payments API.
        // Today these headers are used for traceability unless Payments API explicitly honors Idempotency-Key.
        $headers = [
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (!empty($request->idempotencyKey)) {
            $headers[] = 'Idempotency-Key: ' . $request->idempotencyKey;
        }

        if (!empty($request->correlationId)) {
            $headers[] = 'X-Correlation-Id: ' . $request->correlationId;
        }

        if (!empty($request->publicId)) {
            $headers[] = 'X-Checkout-Public-Id: ' . $request->publicId;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Could not initialize curl.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => (int) DOPPLER_PAYMENTS_API_CONNECT_TIMEOUT_SECONDS,
            CURLOPT_TIMEOUT => (int) DOPPLER_PAYMENTS_API_TIMEOUT_SECONDS,
        ]);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Provider request failed: ' . $error);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $statusCode,
            'body' => $responseBody,
        ];
    }

    private function tryDecodeResponse(string $body): ?array
    {
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function looksLikePaymentError(array $data): bool
    {
        return $this->extractPaymentErrorCode($data) !== null
            || $this->extractPaymentErrorMessage($data) !== null;
    }

    private function extractPaymentErrorCode(array $data): ?string
    {
        foreach (['ErrorCode', 'errorCode', 'code', 'ResponseCode', 'responseCode'] as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return null;
    }

    private function extractPaymentErrorMessage(array $data): ?string
    {
        foreach (['ErrorMessage', 'errorMessage', 'Message', 'message'] as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return null;
    }

    private function extractProviderTransactionId(?array $data): ?string
    {
        if ($data === null) {
            return null;
        }

        foreach (['APITransactionID', 'APITransactionId', 'APItransactionID', 'APItransactionId', 'apiTransactionID', 'apiTransactionId', 'transactionId'] as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return null;
    }

    private function buildResultFromPaymentError(
        ProviderPaymentRequest $request,
        float $startedAt,
        array $paymentError,
        ?array $authorizationData,
        ?string $authorizationCode,
        ?string $transactionLinkId,
        ?string $providerTransactionId,
        string $rawResponseKey
    ): ProviderPaymentResult {
        $extractedErrorCode = $this->extractPaymentErrorCode($paymentError);
        $isBusinessRejection = $extractedErrorCode !== null && $this->isBusinessPaymentRejection($extractedErrorCode, $rawResponseKey);
        $status = $isBusinessRejection ? ProviderPaymentResult::REJECTED : ProviderPaymentResult::ERROR;
        $errorCode = $extractedErrorCode ?: ($status === ProviderPaymentResult::REJECTED ? 'payment_rejected' : 'payment_error');
        $errorMessage = $this->extractPaymentErrorMessage($paymentError) ?: ($status === ProviderPaymentResult::REJECTED ? 'Payment rejected.' : 'Payment error.');
        $responseCode = $isBusinessRejection
            ? $errorCode
            : ($this->isKnownTechnicalProviderError($errorCode) ? $errorCode : 'provider_payment_error_ambiguous');

        $rawResponse = [];

        if ($authorizationData !== null) {
            $rawResponse['authorization'] = $authorizationData;
        }

        $rawResponse[$rawResponseKey] = $paymentError;

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => $status,
            'provider' => 'doppler-payments-api',
            'providerTransactionId' => $providerTransactionId,
            'transactionLinkId' => $transactionLinkId,
            'authorizationResponseCode' => $authorizationCode ?: ($rawResponseKey === 'authorization_error' ? $errorCode : null),
            'purchaseResponseCode' => $rawResponseKey === 'purchase_error' ? $errorCode : null,
            'responseCode' => $responseCode,
            'responseMessage' => $errorMessage,
            'rawResponse' => $rawResponse,
        ]));
    }

    private function isBusinessPaymentRejection(string $errorCode, string $rawResponseKey): bool
    {
        if (in_array($errorCode, [
            'DeclinedPaymentTransaction',
            'DoNotHonorPaymentResponse',
            'FraudPaymentTransaction',
        ], true)) {
            return true;
        }

        if ($this->isKnownTechnicalProviderError($errorCode)) {
            return false;
        }

        if ($rawResponseKey === 'purchase_error' && preg_match('/^[0-9]{3}$/', $errorCode) === 1 && $errorCode !== '000') {
            return true;
        }

        if ($rawResponseKey === 'authorization_error' && preg_match('/^[0-9]{3}$/', $errorCode) === 1 && $errorCode !== '000') {
            return true;
        }

        return false;
    }

    private function isKnownTechnicalProviderError(string $errorCode): bool
    {
        return in_array($errorCode, [
            'ServerPaymentTransactionError',
            'ClientPaymentTransactionError',
            'DuplicatedPaymentTransaction',
        ], true);
    }
}
