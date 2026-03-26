<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
?>

<div class="emms__calendar__list__item__card__description">
    <h3 class="title-<?= $type ?>"><?= $speaker['title'] ?></h3>
    <p><?= $speaker['description'] ?></p>

    <?php if (!empty($speaker['time']) & !$ecommerceStates['isPost']): ?>
        <div class="emms__calendar__list__item__country">
            <span><img src="src/img/flags/arg.png" alt="">(ARG) <?= $speaker['time'] ?></span>
            <a href="<?= $speaker['link_time'] ?>" target="_blank">Mira el horario de tu país</a>
        </div>
    <?php endif; ?>

    <?php
    if (!$ecommerceStates['isPre'] && $normalizedUrl === '/digital-trends-registrado') {
        include 'DescriptionButton.php';
    }
    ?>
</div>
