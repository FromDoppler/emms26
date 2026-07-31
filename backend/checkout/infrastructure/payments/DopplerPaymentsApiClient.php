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
        $remoteAttempted = false;
        $authorizationCode = null;
        $transactionLinkId = null;
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
        } catch (Throwable $e) {
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
            'payment_id' => $request->paymentId,
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
            $authorizationResponse = $this->jsonRequest(
                DOPPLER_PAYMENTS_API_URL . '/authorization',
                $authorizationPayload,
                $jwt,
                $request,
                $remoteAttempted
            );
            if ($this->isProviderErrorResponse($authorizationResponse)) {
                return $this->buildResultFromProviderErrorResponse(
                    $request,
                    $startedAt,
                    $authorizationResponse,
                    'authorization',
                    null,
                    null
                );
            }

            $authorizationData = $this->decodeResponse($authorizationResponse['body']);
            $authorizationCode = $this->normalizeResponseCode($authorizationData['responseCode'] ?? null);

            if ($authorizationCode === null) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::UNKNOWN,
                    'provider' => 'doppler-payments-api',
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Authorization response did not include ResponseCode.',
                    'rawResponse' => [],
                ]));
            }

            if ($authorizationCode !== '000') {
                $status = CheckoutProviderRejectionCatalog::isBusinessRejection($authorizationCode)
                    ? ProviderPaymentResult::REJECTED
                    : ProviderPaymentResult::UNKNOWN;
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => $status,
                    'provider' => 'doppler-payments-api',
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => $authorizationCode ?: (string) $authorizationResponse['status'],
                    'responseMessage' => $status === ProviderPaymentResult::REJECTED
                        ? 'Authorization rejected.'
                        : 'Authorization outcome is ambiguous.',
                    'rawResponse' => [],
                ]));
            }

            $tokenizedPan = $authorizationData['tokenizedPan'] ?? null;
            $transactionLinkId = $authorizationData['transactionLinkID'] ?? null;

            if (!is_string($tokenizedPan) || trim($tokenizedPan) === '') {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::UNKNOWN,
                    'provider' => 'doppler-payments-api',
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => 'missing_tokenized_pan',
                    'responseMessage' => 'Authorization did not return TokenizedPan.',
                    'rawResponse' => [],
                ]));
            }
            if ($transactionLinkId !== null && !is_string($transactionLinkId)) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::UNKNOWN,
                    'provider' => 'doppler-payments-api',
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Authorization returned an invalid TransactionLinkID.',
                    'rawResponse' => [],
                ]));
            }
            $tokenizedPan = trim($tokenizedPan);
            $transactionLinkId = is_string($transactionLinkId) && trim($transactionLinkId) !== ''
                ? trim($transactionLinkId)
                : null;

            $purchasePayload = [
                'paymentToken' => $tokenizedPan,
                'amount' => $request->finalAmount,
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

            $purchaseResponse = $this->jsonRequest(
                DOPPLER_PAYMENTS_API_URL . '/purchase',
                $purchasePayload,
                $jwt,
                $request,
                $remoteAttempted
            );
            if ($this->isProviderErrorResponse($purchaseResponse)) {
                return $this->buildResultFromProviderErrorResponse(
                    $request,
                    $startedAt,
                    $purchaseResponse,
                    'purchase',
                    $authorizationCode,
                    $transactionLinkId
                );
            }

            $purchaseData = $this->decodeResponse($purchaseResponse['body']);
            $purchaseCode = $this->normalizeResponseCode($purchaseData['responseCode'] ?? null);

            if ($purchaseCode === null) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::UNKNOWN,
                    'provider' => 'doppler-payments-api',
                    'transactionLinkId' => $transactionLinkId,
                    'authorizationResponseCode' => $authorizationCode,
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Purchase response did not include ResponseCode.',
                    'rawResponse' => [],
                ]));
            }

            $status = $purchaseCode === '000'
                ? ProviderPaymentResult::APPROVED
                : (CheckoutProviderRejectionCatalog::isBusinessRejection($purchaseCode)
                    ? ProviderPaymentResult::REJECTED
                    : ProviderPaymentResult::UNKNOWN);
            $authorizationNumber = $purchaseData['authorizationNumber'] ?? null;
            if ($status === ProviderPaymentResult::APPROVED
                && (!is_string($authorizationNumber) || trim($authorizationNumber) === '')) {
                return $this->finish($request, $startedAt, new ProviderPaymentResult([
                    'status' => ProviderPaymentResult::UNKNOWN,
                    'provider' => 'doppler-payments-api',
                    'transactionLinkId' => $transactionLinkId,
                    'authorizationResponseCode' => $authorizationCode,
                    'purchaseResponseCode' => $purchaseCode,
                    'responseCode' => 'provider_invalid_response',
                    'responseMessage' => 'Purchase did not return AuthorizationNumber.',
                    'rawResponse' => [],
                ]));
            }
            $authorizationNumber = is_string($authorizationNumber) && trim($authorizationNumber) !== ''
                ? trim($authorizationNumber)
                : null;

            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => $status,
                'provider' => 'doppler-payments-api',
                'authorizationNumber' => $authorizationNumber,
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'purchaseResponseCode' => $purchaseCode,
                'responseCode' => $purchaseCode ?: (string) $purchaseResponse['status'],
                'responseMessage' => null,
                'rawResponse' => [],
            ]));
        } catch (Throwable $e) {
            $status = $remoteAttempted
                ? ProviderPaymentResult::UNKNOWN
                : ProviderPaymentResult::ERROR;

            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => $status,
                'provider' => 'doppler-payments-api',
                'authorizationResponseCode' => $authorizationCode,
                'transactionLinkId' => $transactionLinkId,
                'responseCode' => 'provider_exception',
                'responseMessage' => $e->getMessage(),
                'rawResponse' => [],
            ]));
        }
    }

    private function finish(ProviderPaymentRequest $request, float $startedAt, ProviderPaymentResult $result): ProviderPaymentResult
    {
        Logger::event('payment_provider_call_finished', [
            'correlation_id' => $request->correlationId,
            'checkout_transaction_id' => $request->checkoutTransactionId,
            'payment_id' => $request->paymentId,
            'provider' => $result->provider,
            'status' => $result->status,
            'response_code' => $result->responseCode,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ], 'PAYMENTS', in_array($result->status, [ProviderPaymentResult::ERROR, ProviderPaymentResult::UNKNOWN], true) ? Logger::ERROR : Logger::INFO);

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
        if (!is_string($value)) {
            return null;
        }

        $code = trim((string) $value);
        return $code === '' ? null : $code;
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
        ?string $authorizationCode,
        ?string $transactionLinkId
    ): ProviderPaymentResult {
        $statusCode = (int) ($providerResponse['status'] ?? 0);
        $body = (string) ($providerResponse['body'] ?? '');
        if ($statusCode >= 300 && $statusCode < 400) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'provider_redirect_response',
                'responseMessage' => 'Provider returned an unexpected redirect.',
                'rawResponse' => [],
            ]));
        }

        if ($providerStep !== 'purchase' || $statusCode !== 400) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'provider_unexpected_http_status',
                'responseMessage' => 'Provider returned unexpected HTTP ' . $statusCode . '.',
                'rawResponse' => [],
            ]));
        }

        $paymentError = $this->tryDecodeResponse($body);
        $rawResponseKey = $providerStep . '_error';

        if ($paymentError !== null && $this->looksLikePaymentError($paymentError)) {
            return $this->buildResultFromPaymentError(
                $request,
                $startedAt,
                $paymentError,
                $authorizationCode,
                $transactionLinkId,
                $rawResponseKey
            );
        }

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => ProviderPaymentResult::UNKNOWN,
            'provider' => 'doppler-payments-api',
            'transactionLinkId' => $transactionLinkId,
            'authorizationResponseCode' => $authorizationCode,
            'responseCode' => 'provider_invalid_response',
            'responseMessage' => 'Provider returned HTTP ' . $statusCode . ' with an unexpected body.',
            'rawResponse' => [],
        ]));
    }

    private function jsonRequest(
        string $url,
        array $payload,
        string $jwt,
        ProviderPaymentRequest $request,
        bool &$remoteAttempted
    ): array
    {
        $body = $this->encodePayload($payload);
        if ($body === false) {
            throw new Exception('Could not encode provider payload.');
        }

        $headers = [
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (!empty($request->correlationId)) {
            $headers[] = 'X-Correlation-Id: ' . $request->correlationId;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Could not initialize curl.');
        }

        $configured = curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => (int) DOPPLER_PAYMENTS_API_CONNECT_TIMEOUT_SECONDS,
            CURLOPT_TIMEOUT => (int) DOPPLER_PAYMENTS_API_TIMEOUT_SECONDS,
        ]);
        if ($configured === false) {
            curl_close($ch);
            throw new Exception('Could not configure provider request.');
        }

        $remoteAttempted = true;
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
        if (isset($data['errorCode']) && trim((string) $data['errorCode']) !== '') {
            return trim((string) $data['errorCode']);
        }

        return null;
    }

    private function extractPaymentErrorMessage(array $data): ?string
    {
        if (isset($data['errorMessage']) && trim((string) $data['errorMessage']) !== '') {
            return trim((string) $data['errorMessage']);
        }

        return null;
    }

    private function encodePayload(array $payload)
    {
        $amount = $payload['amount'] ?? null;
        if ($amount !== null && (!is_string($amount) || preg_match('/^\d+\.\d{2}$/D', $amount) !== 1)) {
            return false;
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($body === false || $amount === null) {
            return $body;
        }

        $encodedAmount = json_encode($amount);
        if ($encodedAmount === false) {
            return false;
        }

        $encoded = preg_replace(
            '/"amount":' . preg_quote($encodedAmount, '/') . '/',
            '"amount":' . $amount,
            $body,
            1
        );
        return is_string($encoded) ? $encoded : false;
    }

    private function buildResultFromPaymentError(
        ProviderPaymentRequest $request,
        float $startedAt,
        array $paymentError,
        ?string $authorizationCode,
        ?string $transactionLinkId,
        string $rawResponseKey
    ): ProviderPaymentResult {
        $extractedErrorCode = $this->extractPaymentErrorCode($paymentError);
        $isBusinessRejection = CheckoutProviderRejectionCatalog::isBusinessRejection($extractedErrorCode);
        $status = $isBusinessRejection ? ProviderPaymentResult::REJECTED : ProviderPaymentResult::UNKNOWN;
        $errorCode = $extractedErrorCode ?: ($status === ProviderPaymentResult::REJECTED ? 'payment_rejected' : 'payment_error');
        $errorMessage = $this->extractPaymentErrorMessage($paymentError) ?: ($status === ProviderPaymentResult::REJECTED ? 'Payment rejected.' : 'Payment error.');
        $responseCode = $isBusinessRejection
            ? $errorCode
            : ($this->isKnownTechnicalProviderError($errorCode) ? $errorCode : 'provider_payment_error_ambiguous');

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => $status,
            'provider' => 'doppler-payments-api',
            'transactionLinkId' => $transactionLinkId,
            'authorizationResponseCode' => $authorizationCode ?: ($rawResponseKey === 'authorization_error' ? $errorCode : null),
            'purchaseResponseCode' => $rawResponseKey === 'purchase_error' ? $errorCode : null,
            'responseCode' => $responseCode,
            'responseMessage' => $errorMessage,
            'rawResponse' => [],
        ]));
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
