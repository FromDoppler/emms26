<?php
require_once 'config.php';
require_once("../utils/DB.php");

try {
    $adapter->authenticate();
    $token = $adapter->getAccessToken();
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->google_oauth_update_access_token(json_encode($token));
    $db->close();
    echo "Access token inserted successfully.";
} catch (Exception $e) {
    echo $e->getMessage();
}
