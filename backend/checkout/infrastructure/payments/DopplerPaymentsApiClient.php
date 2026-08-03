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
                'authorization'
            );
        } catch (Throwable $e) {
            return $this->handlePreTransportFailure($request, $startedAt, 'authorization', $e, null, null);
        }

        if (!empty($authorizationResponse['transport_error'])) {
            return $this->handleTransportFailure(
                $request,
                $startedAt,
                'authorization',
                (int) ($authorizationResponse['curl_errno'] ?? 0),
                (string) ($authorizationResponse['curl_error'] ?? ''),
                null,
                null
            );
        }

        $authorizationStatus = (int) ($authorizationResponse['status'] ?? 0);

        if ($authorizationStatus !== 200) {
            return $this->buildResultFromProviderErrorResponse(
                $request,
                $startedAt,
                $authorizationResponse,
                'authorization',
                null,
                null
            );
        }

        if (!isset($authorizationResponse['body']) || !is_string($authorizationResponse['body']) || trim($authorizationResponse['body']) === '') {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'responseCode' => 'provider_invalid_response',
                'responseMessage' => 'Authorization response body was missing or empty.',
                'rawResponse' => [],
            ]));
        }

        try {
            $authorizationData = $this->decodeResponse($authorizationResponse['body']);
        } catch (Throwable $e) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'responseCode' => 'provider_invalid_response',
                'responseMessage' => 'Authorization response body was invalid JSON.',
                'rawResponse' => [],
            ]));
        }
        $authorizationCode = $this->normalizeResponseCode($authorizationData['responseCode'] ?? null);
        $rawTransactionLinkId = $authorizationData['transactionLinkID'] ?? null;
        $transactionLinkId = is_string($rawTransactionLinkId) && trim($rawTransactionLinkId) !== ''
            ? trim($rawTransactionLinkId)
            : null;

        if ($authorizationCode === null) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
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
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => $authorizationCode ?: (string) $authorizationResponse['status'],
                'responseMessage' => $status === ProviderPaymentResult::REJECTED
                    ? 'Authorization rejected.'
                    : 'Authorization outcome is ambiguous.',
                'rawResponse' => [],
            ]));
        }

        $tokenizedPan = $authorizationData['tokenizedPan'] ?? null;

        if (!is_string($tokenizedPan) || trim($tokenizedPan) === '') {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'missing_tokenized_pan',
                'responseMessage' => 'Authorization did not return TokenizedPan.',
                'rawResponse' => [],
            ]));
        }
        if ($rawTransactionLinkId !== null && !is_string($rawTransactionLinkId)) {
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

        try {
            $purchaseResponse = $this->jsonRequest(
                DOPPLER_PAYMENTS_API_URL . '/purchase',
                $purchasePayload,
                $jwt,
                $request,
                'purchase'
            );
        } catch (Throwable $e) {
            return $this->handlePreTransportFailure($request, $startedAt, 'purchase', $e, $authorizationCode, $transactionLinkId);
        }

        if (!empty($purchaseResponse['transport_error'])) {
            return $this->handleTransportFailure(
                $request,
                $startedAt,
                'purchase',
                (int) ($purchaseResponse['curl_errno'] ?? 0),
                (string) ($purchaseResponse['curl_error'] ?? ''),
                $authorizationCode,
                $transactionLinkId
            );
        }

        $purchaseStatus = (int) ($purchaseResponse['status'] ?? 0);

        if ($purchaseStatus !== 200) {
            return $this->buildResultFromProviderErrorResponse(
                $request,
                $startedAt,
                $purchaseResponse,
                'purchase',
                $authorizationCode,
                $transactionLinkId
            );
        }

        if (!isset($purchaseResponse['body']) || !is_string($purchaseResponse['body']) || trim($purchaseResponse['body']) === '') {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'provider_invalid_response',
                'responseMessage' => 'Purchase response body was missing or empty.',
                'rawResponse' => [],
            ]));
        }

        try {
            $purchaseData = $this->decodeResponse($purchaseResponse['body']);
        } catch (Throwable $e) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::UNKNOWN,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'provider_invalid_response',
                'responseMessage' => 'Purchase response body was invalid JSON.',
                'rawResponse' => [],
            ]));
        }
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
        $authorizationNumber = null;
        if ($status === ProviderPaymentResult::APPROVED) {
            $authorizationNumber = $purchaseData['authorizationNumber'] ?? null;
        }
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
            'authorization_response_code' => $result->authorizationResponseCode,
            'purchase_response_code' => $result->purchaseResponseCode,
            'transaction_link_id' => $result->transactionLinkId,
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
        if ($providerStep === 'authorization' && in_array($statusCode, [401, 403], true)) {
            return $this->finish($request, $startedAt, new ProviderPaymentResult([
                'status' => ProviderPaymentResult::ERROR,
                'provider' => 'doppler-payments-api',
                'transactionLinkId' => $transactionLinkId,
                'authorizationResponseCode' => $authorizationCode,
                'responseCode' => 'provider_unauthorized',
                'responseMessage' => 'Authorization was rejected before the handler ran.',
                'rawResponse' => [],
            ]));
        }
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

        if (
            !in_array($providerStep, ['authorization', 'purchase'], true)
            || $statusCode !== 400
        ) {
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
        string $providerStep
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

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $curlErrno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'transport_error' => true,
                'curl_errno' => $curlErrno,
                'curl_error' => $error,
                'provider_step' => $providerStep,
            ];
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $statusCode,
            'body' => $responseBody,
        ];
    }

    private function handlePreTransportFailure(
        ProviderPaymentRequest $request,
        float $startedAt,
        string $providerStep,
        Throwable $error,
        ?string $authorizationCode,
        ?string $transactionLinkId
    ): ProviderPaymentResult {
        $status = $providerStep === 'authorization'
            ? ProviderPaymentResult::ERROR
            : ProviderPaymentResult::UNKNOWN;

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => $status,
            'provider' => 'doppler-payments-api',
            'authorizationResponseCode' => $authorizationCode,
            'transactionLinkId' => $transactionLinkId,
            'responseCode' => $providerStep === 'authorization'
                ? 'provider_pre_authorization_error'
                : 'provider_purchase_error',
            'responseMessage' => $error->getMessage(),
            'rawResponse' => [],
        ]));
    }

    private function handleTransportFailure(
        ProviderPaymentRequest $request,
        float $startedAt,
        string $providerStep,
        int $curlErrno,
        string $curlError,
        ?string $authorizationCode,
        ?string $transactionLinkId
    ): ProviderPaymentResult {
        $status = $providerStep === 'authorization' && $this->isDefinitelyNotProcessedCurlError($curlErrno)
            ? ProviderPaymentResult::ERROR
            : ProviderPaymentResult::UNKNOWN;

        return $this->finish($request, $startedAt, new ProviderPaymentResult([
            'status' => $status,
            'provider' => 'doppler-payments-api',
            'authorizationResponseCode' => $authorizationCode,
            'transactionLinkId' => $transactionLinkId,
            'responseCode' => $status === ProviderPaymentResult::ERROR
                ? 'provider_transport_error'
                : 'provider_transport_ambiguous',
            'responseMessage' => $curlError !== '' ? $curlError : 'Provider transport failed.',
            'rawResponse' => [],
        ]));
    }

    private function isDefinitelyNotProcessedCurlError(int $curlErrno): bool
    {
        return in_array($curlErrno, [
            CURLE_URL_MALFORMAT,
            CURLE_COULDNT_RESOLVE_PROXY,
            CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_CONNECT,
            CURLE_SSL_CONNECT_ERROR,
            CURLE_PEER_FAILED_VERIFICATION,
        ], true);
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
