<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
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
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <main>
        <?php
    $digitalTrendsVipGridTitle = 'EMMS 2026: anticípate al futuro del Marketing';
    $digitalTrendsVipGridColumns = 2;
    $digitalTrendsVipGridItems = [
      [
        'img' => '/src/img/grid-event-types/ia.png',
        'alt' => 'Inteligencia Artificial',
        'title' => 'Inteligencia Artificial',
        'text' => 'Descubre cómo usar IA para automatizar Campañas, personalizar experiencias y mejorar tus resultados.',
      ],
      [
        'img' => '/src/img/grid-event-types/marketingyecommerce.png',
        'alt' => 'Marketing y E-commerce',
        'title' => 'Marketing y E-commerce',
        'text' => 'Aprende estrategias para atraer tráfico, convertir clientes y fidelizar audiencias en mercados cada vez más competitivos.',
      ],
      [
        'img' => '/src/img/grid-event-types/casosdeexito.png',
        'alt' => 'Casos de Éxito',
        'title' => 'Casos de Éxito',
        'text' => 'Conoce a las empresas que están cambiando las reglas del juego y replica sus tácticas en 
tu negocio.',
      ],
      [
        'img' => '/src/img/grid-event-types/regalos.png',
        'alt' => 'Sorpresas por Aniversario',
        'title' => 'Sorpresas por Aniversario',
        'text' => 'Descuentos exclusivos, premios y beneficios únicos por los 20 años Doppler',
      ],
    ];
    ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/hello-module.php') ?>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/referral.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/premium-content.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/academyBanner.php'); ?>
    <?php
    $gridTitle = $digitalTrendsVipGridTitle;
    $gridColumns = $digitalTrendsVipGridColumns;
    $gridItems = $digitalTrendsVipGridItems;
    include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/grid-event-types.php');
    ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
  <script src="src/<?= VERSION ?>/js/newDate.js" type="module"></script>

</body>


</html>
