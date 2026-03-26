<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/components/cacheSettings.php');
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="ecommerce">
    <main>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/schedule/schedule.php') ?>
    </main>
    <header class="emms__header"></header>
    <script src="/src/<?= VERSION ?>/js/commonAnimations.js"></script>
    <script src="/src/<?= VERSION ?>/flickity/flickity.pkgd.min.js"></script>

</body>

</html>
