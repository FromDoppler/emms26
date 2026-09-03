<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');

$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$isRegistered = true;
$isPost = false;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview agenda — Digital Trends</title>
    <link rel="stylesheet" href="/src/<?= VERSION ?>/css/styles.css">
</head>

<body class="digital-trends">
    <main>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php'); ?>
    </main>

    <script src="/src/<?= VERSION ?>/flickity/flickity.pkgd.min.js"></script>
    <script src="/src/<?= VERSION ?>/js/commonAnimations.js"></script>
    <script src="/src/<?= VERSION ?>/js/speakerCarrousel.js"></script>
</body>

</html>
