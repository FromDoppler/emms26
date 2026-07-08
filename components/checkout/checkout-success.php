<?php
$successBackPath = '/registrado';
?>
<div class="emms__checkout emms__checkout__page emms__checkout__page--success">
  <div class="loader-page--new visible" id="spinner">
    <img src="/src/img/logoemms-nobg.png" class="loader-goemms" alt="Loader goemms">
  </div>

  <div class="emms__checkout__container emms__fade-in emms__checkout__container--hidden" id="checkout-container">
    <div class="emms__checkout__card emms__checkout__card--success-shell">
      <img src="/src/img/logos/logo-emms-gray.png" alt="EMMS" class="emms__checkout__success-logo">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/states/success-card.php'); ?>
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/states/error-state.php'); ?>
    </div>
  </div>
</div>

<script type="module" src="/src/<?= VERSION ?>/js/checkout/checkout-success.js"></script>
