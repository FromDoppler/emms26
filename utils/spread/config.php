<?php
require_once 'vendor/autoload.php';
require($_SERVER['DOCUMENT_ROOT'] . "/config.php");

$config = [
    'callback' => GOOGLE_SPREAD_CALLBACK,
    'keys'     => [
        'id' => $GOOGLE_CLIENT_ID,
        'secret' => $GOOGLE_CLIENT_SECRET
    ],
    'scope'    => 'https://www.googleapis.com/auth/spreadsheets',
    'authorize_url_parameters' => [
        'approval_prompt' => 'force', // to pass only when you need to acquire a new refresh token.
        'access_type' => 'offline'
    ]
];

$adapter = new Hybridauth\Provider\Google($config);
