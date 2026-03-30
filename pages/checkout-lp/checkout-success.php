<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/digital-trends/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
  <script type="module">
    const getLocalStorageEvents = () => {
      let localStorageEvents = localStorage.getItem("events");
      return localStorageEvents ? JSON.parse(localStorageEvents) : [];
    };

    const isVipUser = (eventType) => {
      const events = getLocalStorageEvents();
      return events.some((event) => event === eventType);
    };
    const toggleVipElements = () => {
      const vipElements = document.querySelectorAll(".hidden--vip, .show--vip");
      vipElements.forEach((element) => {
        element.classList.add("toogle");
      });
    };
    const toggleVipDigitalTrendsElements = () => {
      const isDTVip = isVipUser('digital-trends25-vip');
      if (isDTVip) {
        toggleVipElements();
      }
    };
    toggleVipDigitalTrendsElements();
  </script>
</head>

<body class="emms__ecommerce emms__ecommerce-logueado">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
  <main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout-lp/checkout-success.php') ?>
    <div class="gold-schedule gold-schedule--landing">
      <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/schedule.php') ?>
    </div>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/academyBanner.php'); ?>
  </main>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
