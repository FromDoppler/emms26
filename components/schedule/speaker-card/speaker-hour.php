<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
?>

<?php if ($speaker['link_time'] && $speaker['time']): ?>
    <div class="speaker-card__hour">
        <?php
        $time = $speaker['time'];
        $date = DateTime::createFromFormat('H:i', $time);
        $amPm = $date ? $date->format('a') : '';
        ?>
        <div class="speaker-card__hour">
            <span>(ARG) <?= $time ?> <?= $amPm ?></span>
            <a href="<?= $speaker['link_time'] ?>" target="_blank">Mira el horario de tu país</a>
        </div>
    </div>
<?php endif; ?>
