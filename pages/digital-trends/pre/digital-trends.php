<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/modal/modal.php');
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
    <div class="register-form__container eventHiddenElements">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/register-form.php') ?>
    </div>
    <div class="register-noform__container  eventShowElements">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/register-withoutform.php') ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/vip-offer.php') ?>
    <?php
    $gridTitle = $digitalTrendsVipGridTitle;
    $gridColumns = $digitalTrendsVipGridColumns;
    $gridItems = $digitalTrendsVipGridItems;
    include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/grid-event-types.php');
    ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/event-numbers.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/speakers-carousel.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/central-video.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/premium-content.php') ?>
    <div class="register-noform__container  eventShowElements">
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/users-comments.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/sponsorsList.php') ?>
  </main>
  <?php
  // Form captador
  render_modal('form-modal', 'form',  'form', true);
  ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
