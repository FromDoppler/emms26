<?php

$contents = [
  '/' => [
    'heading' => '¡Vuelve el EMMS Digital Trends!',
    'body' => '¿Buscas inspiración real, aprendizajes concretos y un espacio para potenciar tu Estrategia? Este evento reúne a las empresas y figuras que están transformando el presente y futuro del Marketing Digital. ¡No te lo pierdas!
',
    'button' => 'ASEGURA TU LUGAR GRATIS',
    'link' => 'digital-trends#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
  '/digital-trends' => [
    'heading' => '¿Qué esperas para inscribirte?',
    'body' => 'Mira este video y descubre por qué el EMMS Digital Trends es el evento clave para transformar tus Estrategias de Marketing Digital, de la mano de especialistas del sector.',
    'button' => 'INSCRÍBETE AHORA',
    'link' => '#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
  '/registrado' => [
    'heading' => '¡Vuelve el EMMS Digital Trends!',
    'body' => '¿Buscas inspiración real, aprendizajes concretos y un espacio para potenciar tu Estrategia? Este evento reúne a las empresas y figuras que están transformando el presente y futuro del Marketing Digital. ¡No te lo pierdas!',
    'youtubeCode' => 'isDPHOi2mAs',
  ],
  '/*' => [
    'heading' => '¡Vuelve el EMMS Digital Trends!',
    'body' => '¿Buscas inspiración real, aprendizajes concretos y un espacio para potenciar tu Estrategia? Este evento reúne a las empresas y figuras que están transformando el presente y futuro del Marketing Digital. ¡No te lo pierdas!
',
    'button' => 'ASEGURA TU LUGAR GRATIS',
    'link' => 'digital-trends#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
];


include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];

$youtubeBaseUrl = "https://www.youtube.com/embed/";
$youtubeParams = "?controls=0&modestbranding=1&rel=0&fs=0&disablekb=1&autoplay=0&loop=1";
$videoUrl = $youtubeBaseUrl . $content['youtubeCode'] . $youtubeParams;

?>

<section class="centralvideo">
  <div class="emms__container--lg emms__container--lg--column">
    <?php if (!empty($content['heading']) || !empty($content['body'])): ?>
      <div class="centralvideo__title emms__fade-in">
        <?php if (!empty($content['heading'])): ?>
          <h2><?php echo $content['heading']; ?></h2>
        <?php endif; ?>
        <?php if (!empty($content['body'])): ?>
          <p><?php echo $content['body']; ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="centralvideo__video emms__fade-in">
      <iframe width="560" height="315" src="<?php echo $videoUrl ?>" frameborder="0" allowfullscreen></iframe>
    </div>

    <?php if (!empty($content['link']) || !empty($content['button'])): ?>
      <a href="<?php echo $content['link']; ?>" class="emms__cta emms__cta--md emms__fade-in">
        <?php echo !empty($content['button']) ? $content['button'] : ''; ?>
      </a>
    <?php endif; ?>
  </div>
</section>
