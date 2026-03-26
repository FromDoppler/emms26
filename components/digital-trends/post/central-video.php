<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');

$normalizedUrl = getNormalizeUrl();

$contents = [
  '/' => [
    'heading' => '¿No has asistido? Aquí se reunieron las principales figuras del Marketing Digital',
    'body' => 'Descubre por qué miles de profesionales y las principales figuras de la industria eligen
este evento para compartir sus estrategias de negocio.',
    'button' => 'REVÍVELO GRATIS',
    'link' => 'digital-trends#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
  '/digital-trends' => [
    'heading' => 'Súmate al evento más convocante de Marketing Digital',
    'body' => 'Mira este video y conoce por qué miles de profesionales de habla hispana eligen capacitarse en el EMMS Digital Trends.',
    'button' => 'REGÍSTRATE GRATIS',
    'link' => '#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
  '/registrado' => [
    'heading' => 'El EMMS, un clásico de cada año para aprender las tendencias en Marketing Digital',
    'body' => 'Descubre por qué miles de profesionales y las principales figuras de la industria eligen
este evento para compartir sus estrategias de negocio.',
    'youtubeCode' => 'isDPHOi2mAs',
  ],
  '/*' => [
    'heading' => '¿No has asistido? Aquí se reunieron las principales figuras del Marketing Digital',
    'body' => 'Descubre por qué miles de profesionales y las principales figuras de la industria eligen
este evento para compartir sus estrategias de negocio.',
    'button' => 'REVÍVELO GRATIS',
    'link' => 'digital-trends#registro',
    'youtubeCode' => 'rTImzuky-LE',
  ],
];

$content = $contents[$normalizedUrl] ?? $contents['/*'];

$youtubeBaseUrl = "https://www.youtube.com/embed/";
$youtubeParams = "?controls=0&modestbranding=1&rel=0&fs=0&disablekb=1&autoplay=0&loop=1";
$videoUrl = $youtubeBaseUrl . $content['youtubeCode'] . $youtubeParams;

$sectionClasses = ['centralvideo'];

if ($normalizedUrl === '/digital-trends') {
  $sectionClasses[] = 'centralvideo--background';
}

$sectionClassString = implode(' ', $sectionClasses);
?>

<section class="<?php echo $sectionClassString; ?>">
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
      <iframe width="560" height="315" src="<?php echo $videoUrl; ?>" frameborder="0" allowfullscreen></iframe>
    </div>

    <?php if (!empty($content['link']) || !empty($content['button'])): ?>
      <a href="<?php echo $content['link']; ?>" class="emms__cta emms__cta--md emms__fade-in">
        <?php echo !empty($content['button']) ? $content['button'] : ''; ?>
      </a>
    <?php endif; ?>
  </div>
</section>
