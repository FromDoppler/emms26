<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/modal.php');
$isPost = false;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
  <script type="module">
    import {
      hiddenOrShowUserUI
    } from '/src/<?= VERSION ?>/js/user.js';
    hiddenOrShowUserUI(window.APP.EVENTS.CURRENT.freeId);
  </script>
  <script type="module">
    import {
      toggleVipDigitalTrendsElements
    } from '/src/<?= VERSION ?>/js/toggleVipElements.js';
    toggleVipDigitalTrendsElements();
  </script>
</head>

<body>
  <div class="register-form__container hidden--vip">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php'); ?>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <main>
    <div class="show--vip">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/hello-vip-module.php') ?>
    </div>
    <div class="hidden--vip">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/hello-module.php') ?>
    </div>
    <div class="gold-schedule">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
    </div>
    <div class="hidden--vip">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/entry-plans.php') ?>
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/video-ticketing.php') ?>
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/vip-features.php') ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/referral.php') ?>

    <div class="show--vip">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/premium-content.php') ?>
    </div>
    <div class="hidden--vip">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/premium-content.php') ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/academyBanner.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php
  require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/extraDataCaptor.php');
  render_modal('modalVip', 'vipmodal',  'vip', true);
  ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
  <script src="src/<?= VERSION ?>/js/newDate.js" type="module"></script>

</body>


</html>
