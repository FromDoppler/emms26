<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

// Leer body JSON (email opcional)
$input = json_decode(file_get_contents('php://input'), true) ?: [];

// URL interna al Node
$node_url = STRIPE_URL_SERVER . "/create-checkout-session";

// CURL simple con manejo básico de error
$ch = curl_init($node_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['error' => 'Error en CURL: ' . $error]);
    exit;
}

curl_close($ch);

// Devolver respuesta al frontend
http_response_code($http_code);
echo $response;
