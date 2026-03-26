<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/ecommerce/pre/ecommerce/head.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__sponsor-promo">
    <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-unreg.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
    <main>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors-promo/hero.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors-promo/register-modal.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors-promo/benefits.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/ecommerce/pre/companies-list.php') ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsors-promo/media-partner.php'); ?>
    </main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
    <script src="/src/<?= VERSION ?>/js/sponsorsPromo.js" type="module"></script>
    <script src="/src/<?= VERSION ?>/js/intell-input/intell-input.js" type="module"></script>

</body>

</html>
