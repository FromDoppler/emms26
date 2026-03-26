<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contentsDuring = [
    '/' => [
        'subheading' => 'COMENZÓ EL EVENTO MÁS GRANDE EN LATAM Y ESPAÑA',
        'heading' => '¡Únete al EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Ya comenzó! </a>',
        'ecommerceUrl' => '/ecommerce',
    ],
    '/registrado' => [
        'subheading' => 'ONLINE Y GRATUITO | <span>¡COMENZÓ LA TRANSMISIÓN!</span>',
        'heading' => '¡SÚMATE AL EMMS E-COMMERCE 2025!',
        'body' => 'Domina el Marketing de tu Tienda Online. Capacítate con los mayores referentes del mundo. <a href="/ecommerce-registrado">¡Estamos en vivo! </a> Disfruta ahora de una nueva
edición con Conferencias, Workshops, sorteos y ¡mucho más!.',
        'ecommerceUrl' => '/ecommerce-registrado',
    ],
    '/*' => [
        'subheading' => 'COMENZÓ EL EVENTO MÁS GRANDE EN LATAM Y ESPAÑA',
        'heading' => '¡Únete al EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Ya comenzó! </a>',
        'ecommerceUrl' => '/ecommerce',
    ],
];


$contentsTransition = [
    '/' => [
        'subheading' => 'COMENZÓ EL EVENTO MÁS GRANDE EN LATAM Y ESPAÑA',
        'heading' => '¡Únete al EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Ya comenzó! </a>',
        'ecommerceUrl' => '/ecommerce',
    ],
    '/registrado' => [
        'subheading' => 'ONLINE Y GRATUITO | <span>¡COMENZÓ LA TRANSMISIÓN!</span>',
        'heading' => '¡SÚMATE AL EMMS E-COMMERCE 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce-registrado">¡Ya comenzó! </a>',
        'ecommerceUrl' => '/ecommerce-registrado',
    ],
    '/*' => [
        'subheading' => 'COMENZÓ EL EVENTO MÁS GRANDE EN LATAM Y ESPAÑA',
        'heading' => '¡Únete al EMMS E-commerce 2025!',
        'body' => 'Te damos la bienvenida al evento de comercio electrónico que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y con los mejores speakers internacionales. <a href="/ecommerce">¡Ya comenzó! </a>',
        'ecommerceUrl' => '/ecommerce',
    ],
];


include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();

if ($ecommerceStates['isTransition']) {
    $content = $contentsTransition[$normalizedUrl] ?? $contentsTransition['/*'];
} else {
    $content = $contentsDuring[$normalizedUrl] ?? $contentsDuring['/*'];
}


// TODO: Se podria hacer un helper para generar los contenidos y las cards en un solo lugar y que este componente sea dummy solo para UX
$cards = [];

$cards[] = renderEventCard([
    'imageSrc' => '/src/img/home/card-image-ecommerce-early.png',
    'imageAlt' => 'Ecommerce image',
    'title' => 'EMMS E-commerce',
    'description' => 'Referentes internacionales de la industria te revelan las <b>tendencias y estrategias que emplean en sus Tiendas Online</b> para captar nuevos clientes y aumentar sus ingresos. ¡No te lo pierdas!',
    'buttonText' => $isRegistered ? 'ÚNETE AL VIVO' : 'ÚNETE AL VIVO',
    'buttonLink' => $isRegistered ? '/ecommerce-registrado' : '/ecommerce',
    'ribbonText' => 'EN VIVO',
    'isShortRibbon' => true,
    'isRegistered' => $isRegistered,
    'spanText' => 'ONLINE Y GRATUITO',
    'spanExtraClass' => '',
    'isSecondaryButton' => false,
], $ecommerceStates);

$cards[] = renderEventCard([
    'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
    'imageAlt' => 'Image Digital Trends',
    'title' => 'EMMS Digital Trends',
    'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. Revive la última edición para nutrirte de ideas para tu negocio.',
    'buttonText' => 'REVÍVELE LA EDICIÓN 2024',
    'buttonLink' => $isRegistered ? '/ediciones-anteriores-registrado#ediciones-anteriores' : '/ediciones-anteriores#ediciones-anteriores',
    'ribbonText' => 'PRÓXIMAMENTE',
    'isShortRibbon' => false,
    'isRegistered' => false,
    'spanText' => $isRegistered ? 'ONLINE Y GRATUITO' : '',
    'spanExtraClass' => 'ribbon--coming-soon',
    'isSecondaryButton' => true,
], $digitalTrendsStates);
?>

<!-- Hero -->
<section class="home-hero">
    <div class="home-hero__title emms__fade-top">
        <h1><em> <?php echo ($content['subheading']); ?></em> <?php echo ($content['heading']); ?>
        </h1>
        <h2>ONLINE Y GRATUITO</h2>
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
