<?php

$contentsDuring = [
    '/' => [
        'content' => '🚨EMMS E-commerce: ¡mira las conferencias gratuitas! 🚨 El evento terminó. Pero de todos modos, puedes registrarte sin costo. ',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '/ecommerce#registro',
    ],
    '/registrado' => [
        'content' => '🎆 ¡Llegó el EMMS E-commerce! 🎆 Súmate al vivo ahora',
        'buttonText' => ' MIRA LA TRANSMISIÓN',
        'buttonLink' => '/ecommerce-registrado',
    ],
    '/ecommerce' => [
        'content' => '📢 ¡Ya estamos en vivo! 📢 ¿Todavía no te has registrado? Súmate gratis.',
        'buttonText' => 'ÚNETE AHORA',
        'buttonLink' => '#registro',
    ],
    '/ecommerce-registrado' => [
        'content' => '#preguntas-frecuentes',
        'buttonText' => '#preguntas-frecuentes',
        'buttonLink' => '#preguntas-frecuentes',
    ],
    '/*' => [
        'content' => '🚨EMMS E-commerce: ¡mira las conferencias gratuitas! 🚨 El evento terminó. Pero de todos modos, puedes registrarte sin costo. ',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '/ecommerce#registro',
    ],
];

$contentsTransition = [
    '/' => [
        'content' => '🚨¡Ya llegó el EMMS E-commerce 2025! 🚨 Únete a otra jornada con más Conferencias gratuitas, Workshops y Networking',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '/ecommerce#registro',
    ],
    '/registrado' => [
        'content' => '¡Ya llegó el EMMS E-commerce 2025! Únete a otra jornada con más Conferencias gratuitas, Workshops y Networking',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '/ecommerce-registrado',
    ],
    '/ecommerce' => [
        'content' => '¡Queda más EMMS E-commerce! ¿Aún no te has registrado? Súmate gratis para unirte a una nueva jornada.',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '#registro',
    ],
    '/*' => [
        'content' => '🚨¡Ya llegó el EMMS E-commerce 2025! 🚨 Únete a otra jornada con más Conferencias gratuitas, Workshops y Networking',
        'buttonText' => 'REGÍSTRATE GRATIS',
        'buttonLink' => '/ecommerce#registro',
    ],
];




include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
if ($ecommerceStates['isTransition']) {
    $content = $contentsTransition[$normalizedUrl] ?? $contentsTransition['/*'];
} else {
    $content = $contentsDuring[$normalizedUrl] ?? $contentsDuring['/*'];
}
?>


<div class="hellobar hellobar--counter">
    <div class="hellobar__container hellobar__container--during emms__fade-in">
        <p><strong><?= $content['content'] ?></strong><a href="<?= $content['buttonLink'] ?>"><?= $content['buttonText'] ?></a></p>
    </div>
</div>
