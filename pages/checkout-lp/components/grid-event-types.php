<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();

if (!isset($gridVariant)) {
  $gridVariant = 'short';
}

if (!function_exists('renderGridItems')) {

  function renderGridItems($gridVariant = 'long')
  {
    $items = [
      ["img" => "/src/img/conferencias.png", "title" => "Conferencias", "text" => "Escucha a referentes internacionales y conoce las últimas tendencias digitales."],
      ["img" => "/src/img/entrevistas.png", "title" => "Entrevistas", "text" => "Aprende de influencers y creadores que marcan el rumbo de la industria."],
      ["img" => "/src/img/casos-de-exito.png", "title" => "Casos de Éxito", "text" => "Descubre cómo las marcas más reconocidas están transformando el sector."],
      ["img" => "/src/img/gifs.png", "title" => "Regalos y beneficios", "text" => "Accede a descuentos especiales y premios exclusivos para asistentes VIP."],
      ["img" => "/src/img/workshop.png", "title" => "Workshops", "text" => "Participa en talleres prácticos y aplica lo aprendido al instante."],
      ["img" => "/src/img/recursos.png", "title" => "Recursos", "text" => "Accede a materiales exclusivos: guías, plantillas, e-books, ¡y mucho más!"],
    ];

    if ($gridVariant === 'short') {
      $items = array_slice($items, 0, 3);
    }

    foreach ($items as $item) {
      echo '<li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="' . $item['img'] . '" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>' . $item['title'] . '</h3>
                    <p>' . $item['text'] . '</p>
                </div>
              </li>';
    }
  }
}

?>

<section class="emms__grid emms__grid--3">
  <div class="emms__container--md">
    <div class="emms__grid__title emms__fade-in">
      <h2>VIVE LA EXPERIENCIA EMMS COMPLETA CON TU PASE VIP</h2>
    </div>
    <ul class="emms__grid__content emms__fade-in">
      <?php renderGridItems($gridVariant); ?>
    </ul>
    <div class="grid__footer">
        <a href="/checkout-lp" class="emms__cta emms__fade-in-animation eventHiddenElements">COMPRA TU ENTRADA</a>
    </div>
  </div>
</section>
