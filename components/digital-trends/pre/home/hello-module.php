<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contents = [
  '/' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span> Digital Trends 2025: ¡Está llegando!</span>',
    'body' => 'Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento
de Latam y España. Disfruta de dos ediciones exclusivas al año para capacitarte e inspirarte con referentes de tu industria.</a> ',
    'ecommerceUrl' => '/ecommerce',
  ],
  '/registrado' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span>¡YA ERES PARTE DEL EMMS 2025!</span>',
    'body' => 'Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento
de Latam y España. Disfruta de dos ediciones exclusivas al año para capacitarte e inspirarte con referentes de tu industria.',
    'ecommerceUrl' => '/ecommerce-registrado',
  ],
  '/*' => [
    'subheading' => 'TODAS LAS TENDENCIAS DE MARKETING DIGITAL EN UN SOLO LUGAR',
    'heading' => '<span> Digital Trends 2025: ¡Está llegando!</span>',
    'body' => 'Revoluciona tu forma de hacer negocios y potencia tus resultados con el mayor evento
de Latam y España. Disfruta de dos ediciones exclusivas al año para capacitarte e inspirarte con referentes de tu industria.</a> ',
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
  'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. Conferencias, Entrevistas, Casos de Éxito, Workshops y mucho más. ¡Reserva tu lugar ahora!',
  'buttonText' => $isRegistered ?  'ACCEDE AL EVENTO' : 'REGÍSTRATE GRATIS',
  'buttonLink' => $isRegistered ? '/digital-trends-registrado' : '/digital-trends',
  'ribbonText' => '',
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
  'description' => 'Referentes internacionales de la industria
te contarán las tendencias y Estrategias que emplean en sus Tiendas Online para captar nuevos clientes y aumentar sus ingresos. ¡Revive la última edición!',
  'buttonText' => $isRegistered ? 'REVÍVELO AHORA' : 'REVÍVELO AHORA',
  'buttonLink' => $isRegistered ? '/ediciones-anteriores-registrado#ediciones-anteriores' : '/ediciones-anteriores#ediciones-anteriores',
  'ribbonText' => '',
  'isShortRibbon' => false,
  'isRegistered' => false,
  'spanText' => 'ONLINE Y GRATUITO',
  'spanExtraClass' => 'ribbon--coming-soon',
  'buttonType' => 'secondary',
], $ecommerceStates);

?>

<!-- Hero -->
<section class="home-hero home-hero--digitaltrends">
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
