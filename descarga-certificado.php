<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/app-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
// If the user accesses this page without the email parameter or workshop, they will automatically be redirected to the home
if ((!isset($_GET['email']))) {
  header('Location: ' . 'index');
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/pre/home/head.php'); ?>


  <?php if (defined('SECRET_REFRESH') && !empty(constant('SECRET_REFRESH'))): ?>
  <script src='/src/<?= VERSION ?>/js/vendors/socket.io.min.js?version=<?= VERSION ?>'></script>
  <script>
    const socket = io("wss://<?= URL_REFRESH ?>", {
      path: "/<?= PATH_REFRESH ?>/socket.io"
    });
    socket.on("state", (args) => {
      location.reload();
    });
  </script>
  <?php endif; ?>
</head>

<body class="emms__previous-editions">
  <div class="loader-page--new" id="spinner">
    <img src="/src/img/logoemms-nobg.png" class="loader-goemms" alt="Loader goemms">
  </div>
  <!-- Header -->
  <header class="emms__header">
    <div class="emms__container--lg emms__fade-in">
      <div class="emms__header__logo">
        <a href="/"><img src="/src/img/logos/logo-emms.png" alt="Emms 2025"></a>
      </div>
      <a class="emms__header__nav--mb" id="btn-burger"></a>
      <nav class="emms__header__nav emms__header__nav--hidden" id="nav-mb">
        <ul class="emms__header__nav__menu">
          <li><a href="/">home</a></li>
          <li><a href="/digital-trends-registrado">DIGITAL TRENDS</a>
          </li>
          <li><a href="/sponsors">biblioteca de recursos</a></li>
          <li class="emms__header__nav__menu__dropdown"><a href="./ediciones-anteriores">Qué es el EMMS</a>
            <ul class="emms__header__nav__submenu">
              <li><a href="./ediciones-anteriores#sobre-emms">Sobre el EMMS</a></li>
              <li><a href="./ediciones-anteriores#ediciones-anteriores">Revive ediciones anteriores</a></li>
            </ul>
          </li>
          <li><a href="/sponsors-promo">sponsors</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Share -->
  <div class="emms__share">
    <a id="btn-share" class="emms__share__open-list"><img src="/src/img/icons/icon-share.svg" alt="Share"></a>
    <ul id="list-share" class="emms__share__list">
      <li>
        <a href="javascript: void(0);" onclick="window.open ('https://www.facebook.com/sharer/sharer.php?u=https%3A//goemms.com/digital-trends-registrado', 'Facebook', 'toolbar=0, status=0, width=550, height=350');">
          <img src="/src/img/Facebook-w.svg" alt="Facebook">
        </a>
      </li>
      <li>
        <a href="javascript: void(0);" onclick="window.open ('https://twitter.com/intent/tweet?text=¡Vuelve%20el%20EMMS%20Digital%20Trends!%20El%20evento%20online%20y%20gratuito%20de%20Marketing%20Digital%20más%20importante%20de%20España%20y%20Latinoamérica.%20Es%20gratis%20y%20online%20:%29%20Reserva%20tu%20lugar%20ahora:%20https://goemms.com/digital-trends', 'Twitter', 'toolbar=0, status=0, width=550, height=350');">
          <img src="/src/img/Twitter-w.svg" alt="Twitter">
        </a>
      </li>
      <li>
        <a href="javascript: void(0);" onclick="window.open ('https://www.linkedin.com/shareArticle?url=https%3A%2F%2Fgoemms.com%2Fdigital-trends-registrado&title=%C2%A1Cuenta%20regresiva%20para%20una%20nueva%20edici%C3%B3n%20de%20EMMS%20Digital%20Trends!&summary=%C2%A1Cuenta%20regresiva%20para%20una%20nueva%20edici%C3%B3n%20de%20EMMS%20Digital%20Trends!%20Llega%20el%20evento%20online%20y%20gratuito%20de%20Marketing%20Digital%20m%C3%A1s%20importante%20de%20Espa%C3%B1a%20y%20Latinoam%C3%A9rica.%20Conferencias%2C%20Entrevistas%2C%20Casos%20de%20%C3%A9xito%2C%20Workshops%2C%20Networking%20%C2%A1y%20mucho%20m%C3%A1s!%20Cada%20a%C3%B1o%2C%20miles%20de%20profesionales%20y%20referentes%20en%20la%20industria%20eligen%20este%20evento%20para%20capacitarse.%20Es%20gratis%20y%20online%20%3A)%20Reserva%20tu%20lugar%20ahora%3A%20goemms.com%2Fdigital-trends&mini=true', 'Linkedin', 'toolbar=0, status=0, width=550, height=550');">
          <img src="/src/img/LinkedIn-w.svg" alt="LinkedIn">
        </a>
      </li>
    </ul>
  </div>

  <main>

    <!-- Hero -->
    <section class="emms__certificate-download">
      <div class="emms__container--md">
        <h1 class="emms__fade-top">¡Tu Certificado de Asistencia ya está disponible!</h1>
        <p class="emms__fade-in">Ingresa tu nombre y apellido para obtenerlo ahora 🙂</p>
        <form id="certificateForm">
          <input type="text" placeholder="Ingresa aquí tu Nombre y Apellido" name="fullname" class="emms__fade-in">
          <span class="certificateError">¡Ouch! Debes ingresar al menos 2 caracteres.</span>
          <a class="emms__cta emms__fade-in" type="button" id="certificateWorkshop"><span class="button__text">DESCARGA TU CERTIFICADO</span></a>
        </form>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
  <script src="/components/digital-trends/during/digital-trends/certificate/certificateWorkshop.js?version=<?= VERSION ?>" type="module"></script>

</body>

</html>
