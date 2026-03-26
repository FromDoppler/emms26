<?php

include_once '../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $config = new Config();
    $config->VPNMiddleware();
    $con = $config->getCon();
    $config->sendRefresh();
} catch (Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
