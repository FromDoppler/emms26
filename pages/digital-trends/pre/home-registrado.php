<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/home/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__home">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/home/hello-module.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/event-numbers.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/speakers-carousel.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/premium-content.php') ?>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/academyBanner.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/faqs.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
