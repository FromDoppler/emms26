<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contents = [
  '/' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span class="text--transparent"> Capacítate e inspírate </br> con el EMMS Digital Trends</span>',
    'body' => 'Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento de Marketing Digital de Latam y España. Mientras esperas por la edición
de E-commerce, revive el EMMS Digital Trends.',
    'ecommerceUrl' => '/ecommerce',
  ],
  '/registrado' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span class="text--transparent">¡Revive la experiencia EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento que reúne a miles de profesionales en Latinoamérica y España. Aprende las últimas tendencias con speakers internacionales.<br> ¡Revive gratis las conferencias!',
    'ecommerceUrl' => '/ecommerce-registrado',
  ],
  '/*' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span class="text--transparent">Capacítate e inspírate </br> con el EMMS Digital Trends</span>',
    'body' => 'Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento de Marketing Digital de Latam y España. Mientras esperas por la edición
de E-commerce, revive el EMMS Digital Trends. ',
    'ecommerceUrl' => '/ecommerce',
  ],
];



include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];


// TODO: Se podria hacer un helper para generar los contenidos y las cards en un solo lugar y que este componente sea dummy solo para UX
$cards = [];

$cards[] = renderEventCard([
  'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
  'imageAlt' => 'Image Digital Trends',
  'title' => 'EMMS Digital Trends',
  'description' => 'Las principales figuras del Marketing Digital compartieron tendencias y estrategias que aplican las empresas líderes de la industria. Revive gratis todas las Conferencias de la última edición.',
  'buttonText' => $isRegistered ?  'REVIVELO AHORA' : 'REVIVELO AHORA',
  'buttonLink' => $isRegistered ? '/digital-trends-registrado' : '/digital-trends',
  'ribbonText' => 'EVENTO FINALIZADO',
  'isShortRibbon' => false,
  'isRegistered' => $isRegistered,
  'spanText' =>  'ONLINE Y GRATUITO',
  'spanExtraClass' => '',
  'buttonType' => 'primary',
], $digitalTrendsStates);

$cards[] = renderEventCard([
  'imageSrc' => '/src/img/home/card-image-ecommerce-post.png',
  'imageAlt' => 'Ecommerce image',
  'title' => 'EMMS E-commerce',
  'description' => 'Referentes internacionales de la industria te contarán las tendencias y estrategias que emplean en sus Tiendas Online para captar nuevos clientes y aumentar sus ingresos.',
  'buttonText' => $isRegistered ? 'PRÓXIMAMENTE' : 'PRÓXIMAMENTE',
  'buttonLink' => $isRegistered ? '/ediciones-anteriores-registrado#ediciones-anteriores' : '/ediciones-anteriores#ediciones-anteriores',
  'ribbonText' => 'MUY PRONTO',
  'isShortRibbon' => true,
  'isRegistered' => false,
  'spanText' => 'ONLINE Y GRATUITO',
  'spanExtraClass' => '',
  'buttonType' => 'disabled',
], $ecommerceStates);

?>

<!-- Hero -->
<section class="home-hero home-hero--digitaltrends">
  <div class="home-hero__title home-hero__title--post emms__fade-top">
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
