<?php
function getContentForUrl($url)
{

    $contentMap = [
        'default' => [
            'heading' => 'Accede a la Biblioteca de Recursos ¡gratis!',
            'body' => 'Descubre contenidos descargables, herramientas y conferencias on-demand que te traen nuestros aliados para potenciar al máximo tu Tienda Online.
',
        ],
        'group1' => [
            'heading' => 'Accede a la Biblioteca de Recursos ¡gratis!',
            'body' => 'Descubre contenidos descargables, herramientas y conferencias on-demand que te traen nuestros aliados para potenciar al máximo tu Tienda Online.
',
        ],
        'group2' => [
            'heading' => 'Potencia tu negocio con la Biblioteca de Recursos',
            'body' => 'Accede a descuentos, materiales de consulta y herramientas que traen nuestros aliados para potenciar al máximo tu negocio.
Ingresa a la Biblioteca de Recursos ¡gratis!'
        ],
    ];

    $urlToGroupMap = [
        '/' => 'group1',
        '/registrado' => 'group1',
        '/ecommerce' => 'group2',
        '/ecommerce-registrado' => 'group2',
    ];


    $group = $urlToGroupMap[$url] ?? 'default';

    return $contentMap[$group];
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = getContentForUrl($normalizedUrl);
?>
<section class="emms__premium-content">
    <div class="emms__container--lg">
        <div class="emms__premium-content__picture emms__fade-in">
            <img src="/src/img/biblioteca-recursos.png" alt="Biblioteca de recursos">
        </div>
        <div class="emms__premium-content__text emms__fade-in">
            <h2><?php echo ($content['heading']); ?></h2>
            <p><?php echo ($content['body']); ?></p>
            <a href="./sponsors" class="emms__cta sm emms__cta--nd emms__fade-in">REGÍSTRATE AHORA</a>
        </div>
    </div>
</section>
