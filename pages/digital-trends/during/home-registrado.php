<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
$isTransition = $digitalTrendsStates['isTransition'] && $digitalTrendsStates['isDuring'];
$isLive = $digitalTrendsStates['isLive'] &&  $digitalTrendsStates['isDuring'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/home/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__home">
  <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/home/hello-module.php') ?>
    <?php
    if ($isTransition) {
      include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/central-video.php');
    }
    ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/event-numbers.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/speakers-carousel.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/premium-content.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/users-comments.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/faqs.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
