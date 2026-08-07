<?php

class SuperUserJwtService
{
    public function getBearerToken(): string
    {
        $authMode = strtolower((string) DOPPLER_PAYMENTS_API_AUTH_MODE);

        if ($authMode === 'static_bearer') {
            $token = DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN ?: getenv('DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN');
            if (!$token) {
                throw new Exception('DOPPLER_PAYMENTS_API_STATIC_BEARER_TOKEN is required for static_bearer auth mode.');
            }
            return $token;
        }

        if ($authMode !== 'jwt_superuser') {
            throw new Exception('Invalid DOPPLER_PAYMENTS_API_AUTH_MODE.');
        }

        $privateKey = $this->loadPrivateKey();
        $issuedAt = time();
        $payload = [
            'isSU' => true,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 1800,
        ];

        if (DOPPLER_PAYMENTS_API_JWT_AUDIENCE !== '') {
            $payload['aud'] = DOPPLER_PAYMENTS_API_JWT_AUDIENCE;
        }

        if (DOPPLER_PAYMENTS_API_JWT_ISSUER !== '') {
            $payload['iss'] = DOPPLER_PAYMENTS_API_JWT_ISSUER;
        }

        return $this->signJwt(['alg' => 'RS256', 'typ' => 'JWT'], $payload, $privateKey);
    }

    private function loadPrivateKey(): string
    {
        $privateKeyFile = trim((string) DOPPLER_PAYMENTS_API_PRIVATE_KEY_FILE);
        if ($privateKeyFile !== '') {
            return $this->loadPrivateKeyFromFile($privateKeyFile);
        }

        if (DOPPLER_PAYMENTS_API_PRIVATE_KEY !== '') {
            return $this->normalizePrivateKey(DOPPLER_PAYMENTS_API_PRIVATE_KEY);
        }

        throw new Exception('Missing Doppler payments private key configuration.');
    }

    private function loadPrivateKeyFromFile(string $privateKeyFile): string
    {
        if (!is_file($privateKeyFile)) {
            throw new Exception('Doppler payments private key file does not exist.');
        }

        if (!is_readable($privateKeyFile)) {
            throw new Exception('Doppler payments private key file is not readable.');
        }

        $privateKey = file_get_contents($privateKeyFile);
        if ($privateKey === false) {
            throw new Exception('Could not read Doppler payments private key file.');
        }

        if (trim($privateKey) === '') {
            throw new Exception('Doppler payments private key file is empty.');
        }

        return $privateKey;
    }

    private function normalizePrivateKey(string $privateKey): string
    {
        return str_replace('\\n', "\n", $privateKey);
    }

    private function signJwt(array $header, array $payload, string $privateKey): string
    {
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';
        $result = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if ($result !== true) {
            throw new Exception('Could not sign Doppler payments JWT.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
