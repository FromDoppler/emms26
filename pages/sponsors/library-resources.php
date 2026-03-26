<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/ecommerce/pre/ecommerce/head.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__ecommerce">
    <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/hello-bar.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . ($isRegistered ? '/components/navbar-reg.php' : '/components/navbar-unreg.php')); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
    <main>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors/libraryResources/hero.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors/libraryResources/sponsorsList.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors/libraryResources/registerModal.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors/libraryResources/conferences.php') ?>
    </main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors/libraryResources/resources/resourceGrid.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/academyBanner.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
    <script src="/src/<?= VERSION ?>/js/sponsors.js"></script>
</body>

</html>
