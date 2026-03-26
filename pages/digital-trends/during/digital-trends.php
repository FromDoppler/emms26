<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/modal.php');

$isTransition = $digitalTrendsStates['isTransition'] && $digitalTrendsStates['isDuring'];
$isLive = $digitalTrendsStates['isLive'] &&  $digitalTrendsStates['isDuring'];
$isPost = $digitalTrendsStates['isPost'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/home/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
    <script type="module">
        import {
            hiddenOrShowUserUI
        } from '/src/<?= VERSION ?>/js/user.js';
        import {
            eventsType
        } from '/src/<?= VERSION ?>/js/enums/eventsType.enum.js';
        hiddenOrShowUserUI(eventsType.DIGITALTRENDS);
    </script>
    <script type="module">
        import {
            toggleVipDigitalTrendsElements
        } from '/src/<?= VERSION ?>/js/toggleVipElements.js';
        toggleVipDigitalTrendsElements();
    </script>
</head>

<body>
    <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-unreg.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>

    <main>
        <div class="register-form__container eventHiddenElements">
            <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/register-form.php') ?>
        </div>
        <div class="register-noform__container  eventShowElements">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/register-withoutform.php') ?>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/premium-content.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/central-video.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
    </main>
    <?php
    // Form captador
    render_modal('form-modal', 'form',  'form', true);
    ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
