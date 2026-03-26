<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

// Cache TTL configuration
$CACHE_TTL_COMPLETE = 300;  // 5 minutos para sesiones completas
$CACHE_TTL_PENDING = 60;    // 1 minuto para sesiones pendientes/abiertas
$CURL_TIMEOUT = 10;         // Timeout para request a Node server

try {
    $session_id = $_GET['session_id'] ?? '';
    if (!$session_id) {
        http_response_code(400);
        echo json_encode(['error' => 'session_id requerido']);
        exit;
    }

    $cacheEnabled = class_exists('Memcached');
    $cached = false;

    if ($cacheEnabled) {
        try {
            $mem = new Memcached();
            $mem->addServer(MEMCACHED_SERVER, 11211);
            $cacheKey = "stripe_session_status_" . $session_id;
            $cached = $mem->get($cacheKey);

            if ($cached !== false) {
                Logger::info("session_status_cache_hit", ['session_id' => $session_id], 'STRIPE');
                http_response_code($cached['code']);
                echo $cached['body'];
                exit;
            }
        } catch (Exception $e) {
            Logger::warning("memcached_error", ['error' => $e->getMessage()], 'STRIPE');
            $cacheEnabled = false;
        }
    }

    $params = $_GET;
    $queryString = http_build_query($params);

    $node_url = STRIPE_URL_SERVER . "/session-status?" . $queryString;

    $ch = curl_init();
    if (!$ch) {
        throw new Exception("No se pudo inicializar curl");
    }

    curl_setopt($ch, CURLOPT_URL, $node_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $CURL_TIMEOUT);
    

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Error en curl: $error");
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($cacheEnabled && $http_code === 200) {
        try {
            $responseData = json_decode($response, true);

            if (is_array($responseData)) {
                $isFirstTime = $responseData['is_first_time'] ?? null;

                // Cachear UNICAMENTE si is_first_time === false
                if ($isFirstTime === false) {
                    $status = $responseData['status'] ?? null;
                    $ttl = ($status === 'complete') ? $CACHE_TTL_COMPLETE : $CACHE_TTL_PENDING;

                    $mem->set($cacheKey, [
                        'code' => $http_code,
                        'body' => $response
                    ], $ttl);
                }
            }
        } catch (Exception $e) {
            Logger::warning("cache_set_error", ['error' => $e->getMessage()], 'STRIPE');
        }
    }

    http_response_code($http_code);
    header('Content-Type: application/json');
    echo $response;

} catch (Exception $e) {
    Logger::error("session_status_error", [
        'error' => $e->getMessage(),
        'session_id' => $_GET['session_id'] ?? 'unknown'
    ], 'STRIPE');

    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno al obtener el estado de la sesión',
        'message' => $e->getMessage()
    ]);
}
