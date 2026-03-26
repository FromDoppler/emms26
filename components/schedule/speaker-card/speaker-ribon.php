<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
?>

<?php if (isSpeakerWithRibbon($speaker)): ?>
  <?php
    $text = ($isMobile ? 'VIP' : 'EXCLUSIVO ASISTENTE VIP');
  ?>
  <div class="speaker-card__ribbon"><?= $text ?> </div>
<?php else: ?>
  <?php
    $text = ($isMobile ? 'BONUS' : 'BONUS POR SER VIP');
  ?>
  <div class="speaker-card__ribbon ribbon-else"><?= $text ?></div>
<?php endif; ?>
