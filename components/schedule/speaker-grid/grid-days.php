<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-grid-helper.php');

$normalizedUrl = getNormalizeUrl();
function getScheduleBlock($url)
{
  $blocks = [
    '/ecommerce' => [
      'block' => 'dt',
    ],
    '/ecommerce-registrado' => [
      'block' => 'dt-registrado',
    ],
    '/*' => [
      'block' => 'digital-trend',
    ],
  ];

  return $blocks[$url] ?? $blocks['/*'];
}
$block = getScheduleBlock($normalizedUrl);

function render_event_day($day, $eventState)
{

  if ($eventState['isPost']) {
?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <p class="hidden--vip">El evento en vivo ha finalizado. Revive todas las Conferencias y pon en práctica las últimas tendencias de Marketing Digital en tu negocio</p>
      </div>
    </div>
  <?php
    return;
  }

  if ($day === 1) {
    // TODO: Revisar logica y contenido al cambiar estado de eventos
  ?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <p> La transmisión comienza a las</p>
        <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00 hs.</span>
        <p>Si estás en otro país o zona horaria, consulta tu horario,</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí</a>
      </div>
    </div>
  <?php
  } else if ($day === 2) {
  ?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <p> La transmisión comienza a las</p>
        <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00 hs.</span>
        <p>Si estás en otro país o zona horaria, consulta tu horario,</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+2&iso=20251029T11&p1=51&ah=6" target="_blank">aquí</a>
      </div>
    </div>
  <?php
  } else if ($day === 3) {
  ?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <p> La transmisión comienza a las</p>
        <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00 hs.</span>
        <p>Si estás en otro país o zona horaria, consulta tu horario,</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+3&iso=20251030T11&p1=51&ah=6" target="_blank">aquí</a>
      </div>
    </div>
<?php
  }
}
?>
