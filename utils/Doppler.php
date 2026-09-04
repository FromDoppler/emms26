<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DopplerApiException.php');

class Doppler
{

    private static $apiKey;
    private static $account;

    private const urlBase = 'https://restapi.fromdoppler.com/accounts/';

    private static function executeCurl($url, $data, $headers, $method): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $body = curl_exec($ch);

        if ($body === false) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            throw new DopplerApiException('Doppler: cURL error ' . $errno . ' - ' . $error);
        }

        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['body' => (string) $body, 'httpStatus' => $httpStatus];
    }

    public static function init($account, $apiKey)
    {
        self::$apiKey = $apiKey;
        self::$account = $account;
    }

    private static function throwIfErrorResponse($response, int $httpStatus, string $rawBody): void
    {
        if ($response === null) {
            if (trim($rawBody) === '' && $httpStatus >= 200 && $httpStatus < 300) {
                return;
            }
            throw new DopplerApiException(
                'Doppler: HTTP ' . $httpStatus . ' | invalid response: ' . substr($rawBody, 0, 200),
                $httpStatus
            );
        }

        if (!is_object($response) && !is_array($response)) {
            throw new DopplerApiException(
                'Doppler: HTTP ' . $httpStatus . ' | unexpected response type: ' . substr($rawBody, 0, 200),
                $httpStatus
            );
        }

        if (is_array($response)) {
            if ($httpStatus >= 400) {
                $messages = [];
                foreach ($response as $item) {
                    if (is_object($item)) {
                        $key = isset($item->key) ? $item->key : (isset($item->title) ? $item->title : 'error');
                        $detail = isset($item->detail) ? $item->detail : (isset($item->message) ? $item->message : json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                        $messages[] = $key . '->' . $detail;
                    }
                }
                $detail = !empty($messages) ? implode('; ', $messages) : substr($rawBody, 0, 200);
                throw new DopplerApiException('Doppler: HTTP ' . $httpStatus . ' | ' . $detail, $httpStatus);
            }
            return;
        }

        if (isset($response->errors)) {
            $errors = is_array($response->errors) ? $response->errors : [$response->errors];
            $messages = [];
            foreach ($errors as $error) {
                $key = isset($error->key) ? $error->key : (isset($error->title) ? $error->title : 'unknown');
                if (isset($error->detail)) {
                    $detail = $error->detail;
                } elseif (isset($error->message)) {
                    $detail = $error->message;
                } else {
                    $detail = json_encode($error, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                $messages[] = $key . '->' . $detail;
            }
            throw new DopplerApiException('Doppler: Error ' . implode('; ', $messages), $httpStatus);
        }

        if (isset($response->errorCode)) {
            $errorCode = (int) $response->errorCode;
            $detail = isset($response->detail) ? $response->detail : 'Unknown error';
            throw new DopplerApiException(
                'Doppler: Error ' . $detail . ' | errorCode= ' . $errorCode,
                $httpStatus,
                $errorCode,
                $errorCode !== 9
            );
        }

        if (isset($response->status) && (int) $response->status >= 400) {
            $detail = isset($response->detail) ? $response->detail : 'Unknown error';
            throw new DopplerApiException('Doppler: Error ' . $detail, $httpStatus);
        }

        if ($httpStatus >= 400) {
            $detail = 'Unknown error';
            if (isset($response->detail)) {
                $detail = $response->detail;
            } elseif (isset($response->message)) {
                $detail = $response->message;
            } elseif (isset($response->title)) {
                $detail = $response->title;
            }
            throw new DopplerApiException('Doppler: HTTP ' . $httpStatus . ' | ' . $detail, $httpStatus);
        }
    }

    private static function getCustomFields($data)
    {
        $customFields = [];

        // field data name => custom doppler name
        $fieldMappings = [
            'firstname' => 'FIRSTNAME',
            'encode_email' => 'EmmsEncodeEmail',
            'privacy' => 'AceptoPoliticaPrivacidad',
            'promotions' => 'AceptoPromocionesDopplerAliados',
            'ip' => 'IP',
            'country_ip' => 'PaisIP',
            'source_utm' => 'utmsource',
            'medium_utm' => 'utmmedium',
            'campaign_utm' => 'utmcampaign',
            'content_utm' => 'utmcontent',
            'term_utm' => 'utmterm',
            'join_url' => 'academyGTW',
            'origin' => 'DOrigin',
            'phone' => 'tel',
        ];

        foreach ($fieldMappings as $dataKey => $customFieldName) {
            if (isset($data[$dataKey]) && trim($data[$dataKey]) !== '') {
                $customFields[] = ['name' => $customFieldName, 'Value' => $data[$dataKey]];
            }
        }

        return $customFields;
    }

    public static function subscriber($data)
    {
        $endPointSubscriber = self::urlBase . urlencode(self::$account) . '/lists/' . $data['list'] . '/subscribers?api_key=' . self::$apiKey;
        $customFields = self::getCustomFields($data);
        $dataSubscriber = array(
            "email" => $data['email'],
            "fields" => $customFields
        );
        $dataJson = json_encode($dataSubscriber);
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataJson)
        );
        $result = self::executeCurl($endPointSubscriber, $dataJson, $headers, 'POST');
        $response = json_decode($result['body']);
        self::throwIfErrorResponse($response, $result['httpStatus'], $result['body']);
    }

    public static function dobleOptin($data)
    {
        $endPointSubscriber = self::urlBase . urlencode(self::$account) . '/lists/' . $data['list'] . '/subscribers/doble-optin/471?api_key=' . self::$apiKey;
        $dataSubscriber = array(
            "email" => $data['email']
        );
        $dataJson = json_encode($dataSubscriber);
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataJson)
        );
        $result = self::executeCurl($endPointSubscriber, $dataJson, $headers, 'POST');
        $response = json_decode($result['body']);
        self::throwIfErrorResponse($response, $result['httpStatus'], $result['body']);
    }
}
