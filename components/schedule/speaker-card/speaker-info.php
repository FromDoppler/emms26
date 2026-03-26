<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
?>

<div class="speaker-card__info">
  <p class="speaker-card__type"><?= translateExposes($speaker['exposes']) ?></p>
  <p class="speaker-card__title"><?= $speaker['title'] ?></p>
  <?php
  $eventPhase = resolveEventPhase($digitalTrendsStates);
  if ($eventPhase !== 'post') {
    render_speaker_hour($speaker);
  }
  ?>
  <!-- CTA -->
  <?php render_speaker_button($speaker,  $isRegistered, $digitalTrendsStates); ?>
</div>
