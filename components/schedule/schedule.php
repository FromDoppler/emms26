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
      <h2>AGENDA EMMS DIGITAL TRENDS 2025</h2>
      <?php if ($normalizedUrl === '/checkout-lp-landing'): ?>
        <p>
          Conoce a las figuras internacionales que se sumarán a los Workshops privados del 30 de octubre <br>
          y el line up de todas las Conferencias gratuitas que también podrás disfrutar los días 28 y 29 de octubre.
        </p>
      <?php elseif ($normalizedUrl && $isPost): ?>
        <p>
          Figuras internacionales de marcas líderes compartieron las últimas </br>
          tendencias en Marketing Digital. ¡Conócelas aquí!
        </p>
      <?php else: ?>
        <p>
          Conoce a las figuras internacionales que participan del evento de Marketing Digital más esperado del año.
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
