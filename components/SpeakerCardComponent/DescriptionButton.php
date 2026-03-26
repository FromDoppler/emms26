<?php

$contents = [
    '/digital-trends' => [
        'buttonNoVip' => '',
    ],
    '/digital-trends-registrado' => [
        'buttonNoVip' => '<a href="#entradas" class="emms__cta hidden--vip">HAZTE VIP</a>',
    ],
    '/*' => [
        'buttonNoVip' => '',
    ],
];

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];

if (!function_exists('generateAttributes')) {
    /**
     * Genera los atributos del botón según su estado.
     *
     * @param bool $isActive Indica si el botón está activo.
     * @return string Atributos generados.
     */
    function generateAttributes($isActive)
    {
        return $isActive
            ? 'target="_blank"'
            : 'aria-disabled="true" data-tooltip="El enlace aún no está disponible" title="El enlace aún no está disponible" disabled';
    }
}

if (!function_exists('generateHref')) {
    /**
     * Genera el atributo href del botón.
     *
     * @param string $speaker Información del speaker
     * @param string $type Tipo del speaker
     * @return string Atributo href generado.
     */
    function generateHref($speaker, $type)
    {
        $url =  '';
        switch ($type) {
            case 'workshop':
                $url = $speaker['youtube'];
                break;
            case 'conference':
            case 'interview':
            case 'debate':
                $url = '/speaker-interna?slug=' . $speaker['slug'];
                break;
            default:
                $url = '/speaker-interna?slug=' . $speaker['slug'];
                break;
        }

        return !empty($url) ? "href=\"{$url}\"" : '';
    }
}

if (!function_exists('generateButtonClass')) {
    /**
     * Genera las clases del botón según su tipo y estado.
     *
     * @param string $type Tipo del evento.
     * @param bool $isActive Indica si el botón está activo.
     * @return string Clases CSS generadas.
     */
    function generateButtonClass($type, $isActive)
    {
        $baseClass = $isActive ? 'emms__cta--nd' : 'inactive--button-card';
        $vipClass = ($type === 'workshop') ? 'show--vip' : '';
        return "{$baseClass} {$vipClass}";
    }
}

if (!function_exists('generateAdditionalContent')) {
    /**
     * Genera contenido adicional según el tipo de evento.
     *
     * @param string $type Tipo del evento.
     * @param array $content Contenido asociado.
     * @return string Contenido adicional generado.
     */
    function generateAdditionalContent($type, $content)
    {
        return ($type === 'workshop') ? ($content['buttonNoVip'] ?? '') : '';
    }
}

if (!function_exists('getDescriptionButton')) {
    /**
     * Genera un botón de redirección para la card de speaker.
     *
     * @param string $type El tipo de evento.
     * @param string $speakerUrl URL a donde dirige el botón.
     * @param array $content Contenido adicional asociado.
     * @return string HTML del botón generado.
     */
    function getDescriptionButton($type, $speaker, $content)
    {
        if (!in_array($type, ['workshop', 'conference', 'interview', 'debate'])) {
            return '';
        }

        $isActive = !empty($speaker['youtube']);
        $attributes = generateAttributes($isActive);
        $href = generateHref($speaker, $type);
        $buttonClass = generateButtonClass($type, $isActive);
        $additionalContent = generateAdditionalContent($type, $content);

        $buttonHtml = <<<HTML
        <a {$href} class="emms__cta {$buttonClass} speaker-button" {$attributes}>ACCEDE AHORA</a>
        HTML;

        return $buttonHtml . $additionalContent;
    }
}
?>

<?= getDescriptionButton($type, $speaker, $content); ?>
