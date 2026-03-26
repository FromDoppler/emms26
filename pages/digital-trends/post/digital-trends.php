<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/modal.php');
$isPost = $digitalTrendsStates['isPost']
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/digital-trends/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
  <script type="module">
    import {
      hiddenOrShowUserUI
    } from '/src/<?= VERSION ?>/js/user.js';
    hiddenOrShowUserUI(window.APP.EVENTS.EVENTCODES.ECOMMERCE);
  </script>
  <script type="module">
    import {
      toggleVipDigitalTrendsElements
    } from '/src/<?= VERSION ?>/js/toggleVipElements.js';
    toggleVipDigitalTrendsElements();
  </script>
</head>

<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'); ?>


  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-unreg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>

  <main>
    <div class="register-form__container eventHiddenElements">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/register-form.php') ?>
    </div>
    <div class="register-noform__container  eventShowElements">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/register-withoutform.php') ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/premium-content.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/central-video.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
