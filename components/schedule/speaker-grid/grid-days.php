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

// Fecha y horario publicados de cada dia, en hora de Argentina (UTC-3).
// TODO: unificar con los labels de speaker-grid.php y con showSpeakersByDay.php.
function getEventDay($day)
{
  $days = [
    1 => ['date' => '2026-09-29', 'time' => '11:00'],
    2 => ['date' => '2026-09-30', 'time' => '11:00'],
    3 => ['date' => '2026-10-01', 'time' => '11:00'],
  ];

  return $days[$day] ?? null;
}

function getTimezoneLink($day, $eventDay)
{
  $iso = str_replace('-', '', $eventDay['date']) . 'T' . substr($eventDay['time'], 0, 2);

  return 'https://www.timeanddate.com/worldclock/fixedtime.html'
    . '?msg=' . rawurlencode("EMMS 2026 - Día {$day}")
    . '&iso=' . $iso
    . '&p1=51&ah=6';
}

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

  $eventDay = getEventDay($day);

  if ($eventDay === null) {
    return;
  }

  // TODO: Revisar logica y contenido al cambiar estado de eventos.
  // Esta bajada esta escrita para isPre; en isLive el evento ya empezo.
  $timezoneLink = htmlspecialchars(getTimezoneLink($day, $eventDay), ENT_QUOTES);
  // newDate.js lee data-event-date y data-event-time, y reescribe los slots
  // [data-flag], [data-country-code] y [data-local-time] con la zona del visitante.
  ?>
  <div class="emms__calendar__date emms__fade-in">
    <div class="emms__calendar__date__country emms__calendar__date__country--live"
      data-event-date="<?= $eventDay['date'] ?>"
      data-event-time="<?= $eventDay['time'] ?>">
      <span class="emms__calendar__date__country__flag" data-flag><img src="/src/img/flags/AR.png" alt="Argentina" title="Argentina"></span>
      <span data-country-code>ARG</span>
      <p>| Transmisión en vivo: el evento comienza a las <span data-local-time><?= $eventDay['time'] ?> a.m.</span></p>
      <a href="<?= $timezoneLink ?>" target="_blank" rel="noopener noreferrer">Consulta el horario según tu país.</a>
    </div>
  </div>
<?php
}
?>
