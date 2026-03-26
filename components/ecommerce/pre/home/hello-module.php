<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contents = [
    '/' => [
        'heading' => 'DESCUBRE LA EXPERIENCIA <br> EMMS 2025',
        'DTCardButton' => 'REGÍSTRATE GRATIS'
    ],
    '/registrado' => [
        'heading' => 'DESCUBRE LA EXPERIENCIA <br> EMMS 2025',
        'DTCardButton' => 'INGRESA AHORA'
    ],
    '/*' => [
        'heading' => 'DESCUBRE LA EXPERIENCIA <br> EMMS 2025',
        'DTCardButton' => 'REGÍSTRATE GRATIS'
    ],
];


include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];
?>

<!-- Hero -->
<section class="home-hero">
    <div class="home-hero__title emms__fade-top">
        <h1><em>TODAS LAS TENDENCIAS EN MARKETING DIGITAL, EN UN SOLO LUGAR</em> <?php echo ($content['heading']); ?>
        </h1>
        <h2>EVENTOS ONLINE Y GRATUITOS</h2>
        <p>Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento de Latam y España. Disfruta de <span>2 ediciones exclusivas </span> al año para capacitarte e inspirarte con los líderes de tu industria.
        </p>
    </div>
    <div id="eventos"></div>
    <!-- Event cards -->
    <div class="emms__eventCards">
        <div class="emms__container--lg">
            <ul class="emms__eventCards__list emms__eventCards__list--dk emms__fade-in">
                <?php
                if ($isRegistered) {
                    echo renderEventCard(array(
                        'imageSrc' => '/src/img/home/card-image-ecommerce-early.png',
                        'imageAlt' => 'Ecommerce image',
                        'title' => 'EMMS E-commerce',
                        'description' => 'Referentes internacionales de la industria te contarán qué <b>tendencias y estrategias emplean en sus Tiendas Online</b> para captar nuevos clientes
y aumentar sus ingresos. Regístrate ahora y asegura tu lugar.',
                        'buttonText' => 'ACCEDE',
                        'buttonLink' => '/ecommerce-registrado',
                        'ribbonText' => '',
                        'isRegistered' => true,
                        'isSecondaryButton' => false
                    ), $ecommerceStates);


                    echo renderEventCard(array(
                        'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
                        'imageAlt' => 'Image Digital Trends',
                        'title' => 'EMMS Digital Trends',
                        'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. Conferencias, Entrevistas, Casos de éxito, Workshops, Networking ¡y mucho más! Revive la última edición',
                        'buttonText' => 'REVÍVELO AHORA',
                        'buttonLink' => '/ediciones-anteriores-registrado#ediciones-anteriores',
                        'ribbonText' => '',
                        'isRegistered' => false,
                        'isSecondaryButton' => true
                    ), $digitalTrendsStates);
                } else {

                    echo renderEventCard(array(
                        'imageSrc' => '/src/img/home/card-image-ecommerce-early.png',
                        'imageAlt' => 'Ecommerce image',
                        'title' => 'EMMS E-commerce',
                        'description' => 'Referentes internacionales de la industria te contarán qué <b>tendencias y estrategias emplean en sus Tiendas Online</b> para captar nuevos clientes
y aumentar sus ingresos. Regístrate ahora y asegura tu lugar.',
                        'buttonText' => 'REGÍSTRATE GRATIS',
                        'buttonLink' => '/ecommerce',
                        'ribbonText' => '',
                        'isRegistered' => false,
                        'isSecondaryButton' => false
                    ), $ecommerceStates);


                    echo renderEventCard(array(
                        'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
                        'imageAlt' => 'Image Digital Trends',
                        'title' => 'EMMS Digital Trends',
                        'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. Conferencias, Entrevistas, Casos de éxito, Workshops, Networking ¡y mucho más! Revive la última edición',
                        'buttonText' => 'REVÍVELO AHORA',
                        'buttonLink' => '/ediciones-anteriores#ediciones-anteriores',
                        'ribbonText' => '',
                        'isRegistered' => false,
                        'isSecondaryButton' => true
                    ), $digitalTrendsStates);
                }

                ?>
            </ul>
            <ul class="emms__eventCards__list emms__eventCards__list--mb emms__fade-in main-carousel" data-flickity>
                <?php
                echo renderEventCard(array(
                    'imageSrc' => '/src/img/home/card-image-ecommerce-early.png',
                    'imageAlt' => 'Ecommerce image',
                    'title' => 'EMMS E-commerce',
                    'description' => 'Referentes internacionales de la industria te contarán qué <b>tendencias y estrategias emplean en sus Tiendas Online</b> para captar nuevos clientes
y aumentar sus ingresos. Regístrate ahora y asegura tu lugar.',
                    'buttonText' => 'REGÍSTRATE GRATIS',
                    'buttonLink' => '/ecommerce',
                    'ribbonText' => '',
                    'isRegistered' => $content['isRegistered'] ?? null,
                    'isSecondaryButton' => false
                ), $ecommerceStates);


                echo renderEventCard(array(
                    'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
                    'imageAlt' => 'Image Digital Trends',
                    'title' => 'EMMS Digital Trends',
                    'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. Conferencias, Entrevistas, Casos de éxito, Workshops, Networking ¡y mucho más! Revive la última edición',
                    'buttonText' => 'REVÍVELO AHORA',
                    'buttonLink' => '/ediciones-anteriores',
                    'ribbonText' => '',
                    'isRegistered' => false,
                    'isSecondaryButton' => true
                ), $digitalTrendsStates);
                ?>
            </ul>
        </div>
    </div>
    </div>
</section>
