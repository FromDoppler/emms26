<?php

class SlackChatClient
{
    private const API_URL = 'https://slack.com/api/chat.postMessage';
    private const MAX_RATE_LIMIT_RETRIES = 1;

    private $botToken;
    private $channelId;

    public function __construct(string $botToken, string $channelId)
    {
        $this->botToken = trim($botToken);
        $this->channelId = trim($channelId);
    }

    public function postMessage(array $message, ?string $threadTs = null): string
    {
        if ($this->botToken === '' || $this->channelId === '') {
            throw new RuntimeException('slack_sales_chat_config_missing');
        }

        $payload = ['channel' => $this->channelId] + $message;
        if ($threadTs !== null && $threadTs !== '') {
            $payload['thread_ts'] = $threadTs;
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('slack_sales_payload_encode_failed');
        }

        for ($attempt = 0; $attempt <= self::MAX_RATE_LIMIT_RETRIES; $attempt++) {
            $result = $this->sendRequest($json);

            if ($result['status'] !== 429) {
                return $this->messageTs($result['response'], $result['status']);
            }

            $retryAfter = $result['retry_after'];
            if ($attempt === self::MAX_RATE_LIMIT_RETRIES || $retryAfter === null) {
                throw new RuntimeException('slack_sales_http_error_429');
            }

            sleep($retryAfter);
        }

        throw new RuntimeException('slack_sales_http_error_429');
    }

    private function sendRequest(string $json): array
    {
        $retryAfter = null;
        $curl = curl_init(self::API_URL);
        if ($curl === false) {
            throw new RuntimeException('slack_sales_curl_init_failed');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->botToken,
                'Content-Type: application/json; charset=utf-8',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $header) use (&$retryAfter): int {
                if (stripos($header, 'Retry-After:') === 0) {
                    $value = trim(substr($header, strlen('Retry-After:')));
                    if (ctype_digit($value) && (int) $value > 0) {
                        $retryAfter = (int) $value;
                    }
                }

                return strlen($header);
            },
        ]);

        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $errno !== 0) {
            throw new RuntimeException('slack_sales_transport_error: ' . substr($error, 0, 300));
        }

        return [
            'response' => (string) $response,
            'status' => $status,
            'retry_after' => $retryAfter,
        ];
    }

    private function messageTs(string $response, int $status): string
    {
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('slack_sales_http_error_' . $status);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('slack_sales_invalid_response');
        }

        if (($decoded['ok'] ?? false) !== true) {
            $slackError = trim((string) ($decoded['error'] ?? 'unknown_error'));
            throw new RuntimeException('slack_sales_api_error: ' . substr($slackError, 0, 200));
        }

        $ts = trim((string) ($decoded['ts'] ?? ''));
        if ($ts === '') {
            throw new RuntimeException('slack_sales_missing_message_ts');
        }

        return $ts;
    }
}
