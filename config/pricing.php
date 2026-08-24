<?php

// Solo para el copy que se muestra: el precio que realmente se cobra vive en la tabla `tickets`.
// Cuando termine la oferta, igualar VIP_PRICE_CURRENT a VIP_PRICE_LIST y todo el copy de la fase PRE lo sigue.
if (!defined('VIP_PRICE_LIST')) define('VIP_PRICE_LIST', '9,99');
if (!defined('VIP_PRICE_CURRENT')) define('VIP_PRICE_CURRENT', '7,99');
if (!defined('VIP_OFFER_DEADLINE_LABEL')) define('VIP_OFFER_DEADLINE_LABEL', '2 de septiembre');
if (!defined('VIP_OFFER_DISCOUNT_LABEL')) define('VIP_OFFER_DISCOUNT_LABEL', '20%');

// Separa un precio como '7,99' en [enteros, centavos] para que las plantillas puedan
// mostrar los decimales en un tamaño menor sin hardcodear el importe.
if (!function_exists('vipPriceUnits')) {
    function vipPriceUnits(string $price): array
    {
        $parts = explode(',', $price, 2);
        return [$parts[0], isset($parts[1]) ? $parts[1] : '00'];
    }
}
