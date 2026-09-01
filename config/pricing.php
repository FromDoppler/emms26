<?php

// Solo para el copy que se muestra: el precio que realmente se cobra vive en la tabla `payment_tickets`.
if (!defined('VIP_PRICE_LIST')) define('VIP_PRICE_LIST', '9,99');
if (!defined('VIP_PRICE_CURRENT')) define('VIP_PRICE_CURRENT', '9,99');

// Separa un precio como '9,99' en [enteros, centavos] para que las plantillas puedan
// mostrar los decimales en un tamaño menor sin hardcodear el importe.
if (!function_exists('vipPriceUnits')) {
    function vipPriceUnits(string $price): array
    {
        $parts = explode(',', $price, 2);
        return [$parts[0], isset($parts[1]) ? $parts[1] : '00'];
    }
}
