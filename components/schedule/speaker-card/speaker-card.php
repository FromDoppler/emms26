<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
$speakerId = $speaker['id'] . ($isMobile ? '-mobile' : '');
?>
<div class="speaker-card <?= 'speaker-card--' . $speaker['exposes'] ?>" data-target-speaker="modal-<?= $speakerId ?>">

    <!-- Ribon del speaker vip -->
    <?php render_speaker_ribon($speaker, $isMobile); ?>

    <!-- Imagen del speaker -->
    <?php render_speaker_image($speaker); ?>

    <!-- Información del speaker -->
    <?php render_speaker_info($speaker,  $isRegistered, $digitalTrendsStates); ?>

</div>
