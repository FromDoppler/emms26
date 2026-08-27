<?php

class SlackWebhookClient
{
    private $webhookUrl;

    public function __construct(string $webhookUrl)
    {
        $this->webhookUrl = trim($webhookUrl);
    }

    public function send(array $payload): void
    {
        if ($this->webhookUrl === '') {
            throw new RuntimeException('slack_sales_webhook_missing');
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('slack_sales_payload_encode_failed');
        }

        $curl = curl_init($this->webhookUrl);
        if ($curl === false) {
            throw new RuntimeException('slack_sales_curl_init_failed');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $errno !== 0) {
            throw new RuntimeException('slack_sales_transport_error: ' . substr($error, 0, 300));
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('slack_sales_http_error_' . $status);
        }
    }
}
