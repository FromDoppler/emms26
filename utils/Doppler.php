<?php
class Doppler
{

    private static $apiKey;
    private static $account;

    private const urlBase = 'https://restapi.fromdoppler.com/accounts/';

    private static function executeCurl($url, $data, $headers, $method)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return curl_exec($ch);
    }

    public static function init($account, $apiKey)
    {
        self::$apiKey = $apiKey;
        self::$account = $account;
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
            'Content: ' . strlen($dataJson)
        );
        $response = json_decode(self::executeCurl($endPointSubscriber, $dataJson, $headers, 'POST'));
        if (isset($response->errors)) :
            foreach ($response->errors as $error) :
                throw new Exception('Doppler: Error ' . $error->key . '->' . $error->detail);
            endforeach;
        endif;
        if (isset($response->errorCode)) :
            throw new Exception('Doppler: Error ' . $response->detail . ' | errorCode= ' . $response->errorCode);
        endif;
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
            'Content: ' . strlen($dataJson)
        );
        $response = json_decode(self::executeCurl($endPointSubscriber, $dataJson, $headers, 'POST'));
        if (isset($response->errors)) :
            foreach ($response->errors as $error) :
                throw new Exception('Doppler: Error ' . $error->key . '->' . $error->detail);
            endforeach;
        endif;
        if (isset($response->errorCode)) :
            throw new Exception('Doppler: Error ' . $response->detail . ' | errorCode= ' . $response->errorCode);
        endif;
    }
}
