<?php

$contents = [
    '/' => [
        'link' => '#preguntas-frecuentes',
    ],
    '/registrado' => [
        'link' => '#preguntas-frecuentes',
    ],
    '/ecommerce' => [
        'link' => '/#preguntas-frecuentes',
    ],
    '/ecommerce-registrado' => [
        'link' => '/registrado#preguntas-frecuentes',
    ],
    '/*' => [
        'link' => '#preguntas-frecuentes',
    ],
];

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];
?>

<div class="faq-banner">
    <div class="faq-banner__content">
        <p class="faq-banner__text">
            ¿Tienes dudas sobre el EMMS?
            <a href="<?= $content['link'] ?>" class="faq-banner__link">Haz clic aquí</a>
            y encuentra las preguntas más frecuentes sobre el evento
        </p>
    </div>
</div>
