<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/modal.php');
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
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/hello-module.php') ?>
    <div class="gold-schedule gold-schedule--landing">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/entry-plans.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/video-ticketing.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/vip-features.php') ?>
    <?php
    $gridVariant = 'long';
    include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/grid-event-types.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/pages/checkout-lp/components/faqs.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
    <?php
    render_modal('modalVipLanding', 'vipmodal',  'vip', true);
    ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
