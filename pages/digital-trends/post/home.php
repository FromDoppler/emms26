<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/home/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__home">
  <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-unreg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php');
  ?>

  <main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/home/hello-module.php');   ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/central-video.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/premium-content.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/event-numbers.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/speakers-carousel.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/users-comments.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/faqs.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/companies-list.php') ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>

</body>

</html>
