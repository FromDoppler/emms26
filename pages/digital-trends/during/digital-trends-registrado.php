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
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/head.php'); ?>
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
  <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <?php
  if ($isLive) {
    include($_SERVER['DOCUMENT_ROOT'] . '/pages/digital-trends/during/combinations/dtr-during.php');
  } else {
    include($_SERVER['DOCUMENT_ROOT'] . '/pages/digital-trends/during/combinations/dtr-transition.php');
  }
  ?>
  <?php
  require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/extraDataCaptor.php');
  render_modal('modalVip', 'vipmodal',  'vip', true);
  ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
  <script src="src/<?= VERSION ?>/js/newDate.js" type="module"></script>

</body>

</html>
