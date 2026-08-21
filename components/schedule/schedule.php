<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-grid-helper.php');

$normalizedUrl = getNormalizeUrl();
function getScheduleBlock2($url)
{
  $blocks = [
    '/digital-trends' => [
      'block' => 'digital-trends',
    ],
    '/digital-trends-registrado' => [
      'block' => 'digital-trends-registrado',
    ],
    '/*' => [
      'block' => 'digital-trend',
    ],
  ];

  return $blocks[$url] ?? $blocks['/*'];
}
$block = getScheduleBlock2($normalizedUrl);
?>

<section class="emms__calendar" id="agenda">
  <div class="emms__container--lg">
    <div class="emms__calendar__title emms__fade-in">
      <h2>AGENDA EMMS 2026</h2>
      <?php if ($normalizedUrl && $isPost): ?>
        <p>
          Figuras internacionales de marcas líderes compartieron las últimas <br>
          tendencias en Marketing Digital. ¡Conócelas aquí!
        </p>
      <?php elseif ($isRegistered): ?>
        <p>
          Conoce las Conferencias y Workshops de figuras internacionales del Marketing Digital
        </p>
      <?php else: ?>
        <p>
          Conoce a las figuras internacionales que ya confirmaron su presencia en el evento de Marketing Digital <br>
          más esperado del año. ¡Y prepárate! Muy pronto anunciaremos el resto de la agenda.
        </p>
      <?php endif; ?>





    </div>

    <?php
    //TODO: Abstraer ecommerceStates a un getter que pase el state del currentEvent para volver agnostica la genda de eventos
    render_speaker_grid($digitalTrendsStates, $isRegistered, $isPost); ?>
    <?php if ($block['block'] === 'digital-trends') : ?>
      <div class="emms__calendar__bottom emms__fade-in  eventHiddenElements">
        <a href="#registro" class="emms__cta">
          REGÍSTRATE GRATIS
        </a>
      </div>
      <div class="emms__calendar__bottom  eventShowElements">
        <a href="#registro" class="emms__cta alreadyRegisterForm"><span class="button__text">SÚMATE GRATIS</span></a>
      </div>
    <?php endif; ?>
  </div>
</section>
