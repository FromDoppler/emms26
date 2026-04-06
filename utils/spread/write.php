<?php


function write_to_sheet($spreadsheetId, $range, $values, $db, $googleClientId = null, $googleClientSecret = null) {
    // Use global constants if parameters not provided
    if (!$googleClientId) {
        global $GOOGLE_CLIENT_ID, $GOOGLE_CLIENT_SECRET;
        $googleClientId = $GOOGLE_CLIENT_ID;
        $googleClientSecret = $GOOGLE_CLIENT_SECRET;
    }

    // Load only the vendor autoload, not the full config
    require_once ('vendor/autoload.php');

    if (!defined('GOOGLE_REFRESH_TOKEN') || !GOOGLE_REFRESH_TOKEN) {
        throw new Exception('GOOGLE_REFRESH_TOKEN is required');
    }

    $refresh_token = trim(GOOGLE_REFRESH_TOKEN, " \t\n\r\0\x0B'\"");

    $response = (new GuzzleHttp\Client(['base_uri' => 'https://accounts.google.com']))->request('POST', '/o/oauth2/token', [
        'form_params' => [
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token,
            "client_id" => $googleClientId,
            "client_secret" => $googleClientSecret,
        ],
    ]);
    $arr_token = (array) json_decode($response->getBody(), true);

    $accessToken = array(
        'access_token' => $arr_token['access_token'],
        'expires_in' => $arr_token['expires_in'],
    );

    $client = new Google_Client();
    $client->setAccessToken($accessToken);
    $service = new Google_Service_Sheets($client);

    try {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
    } catch (Exception $e) {
        if (401 == $e->getCode()) {
            write_to_sheet($spreadsheetId, $range, $values, $db, $googleClientId, $googleClientSecret);
        } else {
            $error = json_decode($e->getMessage()); //print the error just in case your data is not added.
            throw new Exception($error->error->message);

        }
    }
}