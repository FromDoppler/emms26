<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$gridBlocks = [
  '/digital-trends' => ['block' => 'CtaBlock'],
  '/digital-trends-registrado' => ['block' => 'TextBlock'],
  '/*' => ['block' => 'CtaBlock'],
];
$block = $gridBlocks[$normalizedUrl] ?? $gridBlocks['/*'];

if (!isset($gridTitle)) {
  $gridTitle = 'DESCUBRE LA EXPERIENCIA COMPLETA CON TU ENTRADA VIP';
}

if (!isset($gridColumns)) {
  $gridColumns = 2;
}

if (!isset($gridItems)) {
  $gridItems = [
    [
      'img' => '/src/img/conferencias.png',
      'alt' => 'Conferencias',
      'title' => 'Conferencias',
      'text' => 'Escucha a referentes internacionales y conoce las últimas tendencias digitales.',
    ],
    [
      'img' => '/src/img/entrevistas.png',
      'alt' => 'Entrevistas',
      'title' => 'Entrevistas',
      'text' => 'Aprende de influencers y creadores que marcan el rumbo de la industria.',
    ],
    [
      'img' => '/src/img/casos-de-exito.png',
      'alt' => 'Casos de Éxito',
      'title' => 'Casos de Éxito',
      'text' => 'Descubre cómo las marcas más reconocidas están transformando el sector.',
    ],
    [
      'img' => '/src/img/gifs.png',
      'alt' => 'Regalos y beneficios',
      'title' => 'Regalos y beneficios',
      'text' => 'Accede a descuentos especiales y premios exclusivos para asistentes VIP.',
    ],
  ];
}

$gridClass = $gridColumns === 3 ? 'emms__grid--3' : 'emms__grid--2';
?>

<section class="emms__grid emms__grid--compact-cards <?= $gridClass ?>">
  <div class="emms__container--md">
    <div class="emms__grid__title emms__fade-in">
      <h2><?= $gridTitle ?></h2>
    </div>
    <ul class="emms__grid__content emms__fade-in">
      <?php foreach ($gridItems as $item) : ?>
        <li class="emms__grid__item">
          <div class="emms__grid__item__image">
            <img src="<?= $item['img'] ?>" alt="<?= $item['alt'] ?? $item['title'] ?>">
          </div>
          <div class="emms__grid__item__text">
            <h3><?= $item['title'] ?></h3>
            <p><?= $item['text'] ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="grid__footer">
      <?php if ($block['block'] === 'CtaBlock') : ?>
        <a href="#registro" class="emms__cta emms__fade-in-animation eventHiddenElements">RESERVA TU LUGAR</a>
        <button class="emms__cta emms__fade-in-animation eventShowElements alreadyRegisterForm">
          <span class="button__text">RESERVA TU LUGAR</span>
        </button>
      <?php elseif ($block['block'] === 'TextBlock') : ?>
        <div class="hidden--vip">
          <p><strong>¡No te lo pierdas! Vive la experiencia EMMS completa con tu pase VIP.</strong></p>
          <a href="#entradas" class="emms__cta emms__fade-in-animation emms__cta--xl">COMPRA TU ENTRADA</a>
        </div>
      <?php endif; ?>
    </div>
    <?php if ($block['block'] === 'TextBlock') : ?>
      <div class="emms__separator emms__separator--white"></div>
    <?php endif; ?>
  </div>
</section>
