<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contents = [
    '/' => [
        'subheading' => '¡MÁS DE 20.000 PERSONAS ELIGIERON ESTE EVENTO!',
        'heading' => '&iexcl;Capacítate gratis con <br> el EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Regístrate gratis para acceder a las conferencias! </a> ',
        'ecommerceUrl' => '/ecommerce',
    ],
    '/registrado' => [
        'subheading' => '¡MÁS DE 20.000 PERSONAS ELIGIERON ESTE EVENTO!',
        'heading' => '¡Revive la experiencia <br>
EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne
a miles de personas en Latinoamérica y España. Capacítate sin costo sobre las últimas tendencias con speakers internacionales.<a href="/ecommerce-registrado"> ¡Revive gratis las conferencias!</a>',
        'ecommerceUrl' => '/ecommerce-registrado',
    ],
    '/*' => [
        'subheading' => '¡MÁS DE 20.000 PERSONAS ELIGIERON ESTE EVENTO!',
        'heading' => '&iexcl;Capacítate gratis con <br> el EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Regístrate gratis para acceder a las conferencias! </a> ',
        'ecommerceUrl' => '/ecommerce',
    ],
];



include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];


// TODO: Se podria hacer un helper para generar los contenidos y las cards en un solo lugar y que este componente sea dummy solo para UX
$cards = [];

$cards[] = renderEventCard([
    'imageSrc' => '/src/img/home/card-image-ecommerce-early.png',
    'imageAlt' => 'Ecommerce image',
    'title' => 'EMMS E-commerce',
    'description' => 'Referentes internacionales de la industria te revelan las tendencias y estrategias que emplean en
sus Tiendas Online para captar nuevos clientes
y aumentar sus ingresos. ¡Revive la edición 2025!
Es gratis y online.',
    'buttonText' => $isRegistered ? 'REVÍVELO AHORA' : 'REVÍVELO AHORA',
    'buttonLink' => $isRegistered ? '/ecommerce-registrado' : '/ecommerce',
    'ribbonText' => 'EVENTO FINALIZADO',
    'isShortRibbon' => false,
    'isRegistered' => $isRegistered,
    'spanText' => 'ONLINE Y GRATUITO',
    'spanExtraClass' => 'ribbon--coming-soon',
    'buttonType' => 'primary',
], $ecommerceStates);

$cards[] = renderEventCard([
    'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
    'imageAlt' => 'Image Digital Trends',
    'title' => 'EMMS Digital Trends',
    'description' => 'Speakers internacionales líderes en Marketing Digital comparten tendencias, estrategias y herramientas, que aplican las empresas más destacadas en la industria.',
    'buttonText' => 'PRÓXIMAMENTE',
    'buttonLink' => $isRegistered ? '/ediciones-anteriores-registrado#ediciones-anteriores' : '/ediciones-anteriores#ediciones-anteriores',
    'ribbonText' => 'MUY PRONTO',
    'isShortRibbon' => false,
    'isRegistered' => false,
    'spanText' => $isRegistered ? 'ONLINE Y GRATUITO' : '',
    'spanExtraClass' => '',
    'buttonType' => 'disabled',
], $digitalTrendsStates);
?>

<!-- Hero -->
<section class="home-hero">
    <div class="home-hero__title emms__fade-top">
        <h1><em> <?php echo ($content['subheading']); ?></em> <?php echo ($content['heading']); ?>
        </h1>
        <h2>EVENTOS ONLINE Y GRATUITOS</h2>
        <p>
            <?php echo ($content['body']); ?>
        </p>
    </div>
    <div id="eventos"></div>
    <!-- Event cards -->
    <div class="emms__eventCards">
        <div class="emms__container--lg">
            <ul class="emms__eventCards__list emms__eventCards__list--dk emms__fade-in">
                <?php echo implode('', $cards); ?>
            </ul>
            <ul class="emms__eventCards__list emms__eventCards__list--mb emms__fade-in main-carousel" data-flickity>
                <?php echo implode('', $cards); ?>
            </ul>
        </div>
    </div>
    </div>
</section>
