<?php include($_SERVER['DOCUMENT_ROOT'] . '/components/event-card.php'); ?>

<?php

$contentsLive = [
  '/' => [
    'subheading' => 'COMENZÓ EL EVENTO MÁS ESPERADO POR MARKETERS Y EMPRENDEDORES',
    'heading' => '<span class="text--transparent"> ¡Únete al EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y en vivo con speakers internacionales de renombre. <a href="/digital-trends">¡Ya comenzó!</a>  ',
    'digital-trendsUrl' => '/digital-trends',
  ],
  '/registrado' => [
    'subheading' => 'ONLINE Y GRATUITO |  <span>¡COMENZÓ LA TRANSMISIÓN!<span>',
    'heading' => '<span class="text--transparent">¡YA ERES PARTE DEL EMMS 2025!</span>',
    'body' => 'Conoce las tendencias del presente y futuro del Marketing Digital. Capacítate con referentes internacionales de la industria. <a href="./digital-trends-registrado">¡Estamos en vivo!</a> Disfruta ahora de una nueva edición con Conferencias, Workshops, Entrevistas, sorteos, ¡y mucho más!',
    'digital-trendsUrl' => '/digital-trends-registrado',
  ],
  '/*' => [
    'subheading' => 'COMENZÓ EL EVENTO MÁS ESPERADO POR MARKETERS Y EMPRENDEDORES',
    'heading' => '<span class="text--transparent"> ¡Únete al EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y en vivo con speakers internacionales de renombre. <a href="/digital-trends">¡Ya comenzó!</a> ',
    'digital-trendsUrl' => '/digital-trends',
  ],
];

$contentsTrans = [
  '/' => [
    'subheading' => 'COMENZÓ EL EVENTO MÁS ESPERADO POR MARKETERS Y EMPRENDEDORES</span>',
    'heading' => '<span class="text--transparent"> ¡Únete al EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento de Marketing Digital que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y en vivo con speakers internacionales de renombre. <a href="/digital-trends">¡Ya comenzó!</a> ',
    'digital-trendsUrl' => '/digital-trends',
  ],
  '/registrado' => [
    'subheading' => 'COMENZÓ EL EVENTO MÁS ESPERADO POR MARKETERS Y EMPRENDEDORES</span>',
    'heading' => '<span class="text--transparent"> ¡Únete al EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento de Marketing Digital que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y en vivo con speakers internacionales de renombre. <a href="/digital-trends-registrado">¡Ya comenzó!</a>',
    'digital-trendsUrl' => '/digital-trends-registrado',
  ],
  '/*' => [
    'subheading' => 'COMENZÓ EL EVENTO MÁS ESPERADO POR MARKETERS Y EMPRENDEDORES</span>',
    'heading' => '<span class="text--transparent"> ¡Únete al EMMS Digital Trends 2025!</span>',
    'body' => 'Te damos la bienvenida al evento de Marketing Digital que reúne a miles de personas en Latinoamérica y España. Capacítate gratis y en vivo con speakers internacionales de renombre. <a href="/digital-trends">¡Ya comenzó!</a> ',
    'digital-trendsUrl' => '/digital-trends',
  ],
];

function getDigitalTrendsButtonText($isLive, $isTransition, $isRegistered)
{
  if ($isLive) {
    return $isRegistered ? 'VER TRANSMISIÓN' : 'ÚNETE AL VIVO';
  }
  if ($isTransition) {
    return $isRegistered ? 'ACCEDE AHORA' : 'REGÍSTRATE GRATIS';
  }
  return $isRegistered ? 'INGRESA AL EVENTO' : 'REGÍSTRATE GRATIS';
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
if ($isLive) {
  $content = $contentsLive[$normalizedUrl] ?? $contentsLive['/*'];
} elseif ($isTransition) {
  $content = $contentsTrans[$normalizedUrl] ?? $contentsTrans['/*'];
} else {
  $content = $contentsLive['/*'];
}


// TODO: Se podria hacer un helper para generar los contenidos y las cards en un solo lugar y que este componente sea dummy solo para UX
$cards = [];

$cards[] = renderEventCard([
  'imageSrc' => '/src/img/home/card-image-digitaltrends-post.png',
  'imageAlt' => 'Image Digital Trends',
  'title' => 'EMMS Digital Trends',
  'description' => 'Descubre las últimas innovaciones en Marketing Digital aplicadas por las empresas que marcan tendencia en la industria. ¡No te lo pierdas! Es gratis y online.',
  'buttonText' => getDigitalTrendsButtonText($isLive, $isTransition, $isRegistered),
  'buttonLink' => $isRegistered ? '/digital-trends-registrado' : '/digital-trends',
  'ribbonText' => $isLive ? 'EN VIVO' : '',
  'isShortRibbon' => true,
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
    <h1><em><?= $content['subheading']; ?></em> <?= $content['heading']; ?></h1>
    <h2>ONLINE Y GRATUITO</h2>
    <p><?= $content['body']; ?></p>
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
