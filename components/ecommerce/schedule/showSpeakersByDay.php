<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');

function showEventDatetimeByDay($day, $digitalTrendsStates)
{
  if ($day === 1) {
?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <?php if ($digitalTrendsStates['isPre']) : ?>
          <p> La transmisión comienza a las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00</span>.
          <p>Si estás en otro país o zona horaria, consulta tu horario</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí.</a>
        <?php endif ?>
        <?php if ($digitalTrendsStates['isLive']) : ?>
          <p> A partir de las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">
            (ARG) 11:00</p>
          </span>
          <p>. Si no eres de allí o estarás en otro lado
          </p>
          <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+E-commerce+D%C3%8DA+1&iso=20250428T11&p1=51&ah=6" target="_blank">mira el horario de tu país</a> <?php endif ?>
        <?php if ($digitalTrendsStates['isTransition']) : ?>
          <!-- <p>El primer día a finalizado. Pronto accederás a las grabaciones</p> -->
        <?php endif ?>
      </div>
    </div>
  <?php
  } else if ($day === 2) {
  ?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <?php if (DAY_DURING > 2) : ?>
          <p> La transmisión comienza a las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00</span>.
          <p>Si estás en otro país o zona horaria, consulta tu horario</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí.</a>
        <?php else: ?>
          <p> La transmisión comienza a las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00</span>.
          <p>Si estás en otro país o zona horaria, consulta tu horario</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí.</a>
        <?php endif ?>
      </div>
    </div>
  <?php
  } else if ($day === 3) {
  ?>
    <div class="emms__calendar__date emms__fade-in">
      <div class="emms__calendar__date__country">
        <?php if (DAY_DURING > 2) : ?>
          <p> La transmisión comienza a las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00</span>.
          <p>Si estás en otro país o zona horaria, consulta tu horario</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí.</a>
        <?php else: ?>
          <p> La transmisión comienza a las</p>
          <span><img src="/src/img/flag-argentina.png" alt="Argentina">(ARG) 11:00</span>.
          <p>Si estás en otro país o zona horaria, consulta tu horario</p> <a href="https://www.timeanddate.com/worldclock/fixedtime.html?msg=EMMS+Digital+Trends+2025+-+D%C3%ADa+1&iso=20251028T11&p1=51&ah=6" target="_blank">aquí.</a>
        <?php endif ?>
      </div>
    </div>
<?php
  }
}
?>

<?php
function showSpeakersByDay($day, $digitalTrendsStates)
{
  $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $speakers = $db->getSpeakersByDay($day); ?>

  <div class="emms__container--lg" role="tabpanel" aria-labelledby="day<?= $day ?>">
    <?php
    showEventDatetimeByDay($day, $digitalTrendsStates);
    ?>
    <!-- List -->
    <ul class="emms__calendar__list emms__calendar__list--dk emms__fade-in">
      <?php
      foreach ($speakers as $speaker) :
        $isSpeakerEcommerce = $speaker['event'] === "ecommerce";
        $isSpeakerExposeDebate = $speaker['exposes'] === "debate";
        $allowedExposesTypes = ["conference", "workshop", "networking", "successStory", "interview"];
        $isSpeakerExposesType = in_array($speaker['exposes'], $allowedExposesTypes) || $isSpeakerExposeDebate;
      ?>
        <?php if (($isSpeakerExposesType) && $isSpeakerEcommerce) : ?>
          <li class="emms__calendar__list__item">
            <?php
            $type = $speaker['exposes'] ?? 'default';
            include($_SERVER['DOCUMENT_ROOT'] . '/components/SpeakerCard.php');
            ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
    <?php
    include('mobile-carousel.php')
    ?>
  </div>
<?php
}
showSpeakersByDay(1, $digitalTrendsStates);
showSpeakersByDay(2, $digitalTrendsStates);
showSpeakersByDay(3, $digitalTrendsStates);

?>
