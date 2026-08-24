<section class="hero-registration--registered hero-registration--withoutform hero-registration--registered--digitaltrends-pre">
  <div class="emms__container--md">
    <h1 class="emms__fade-top">
      <em>ONLINE Y GRATUITO - Del 29 de septiembre al 1 de octubre</em>
      <span>Gracias por sumarte al EMMS 2026</span>
    </h1>
    <p class="hero-registration__vip-copy emms__fade-in">
      El pase VIP nunca fue tan accesible.<br>
      Ya tienes acceso a las Conferencias gratuitas. Es momento de dar el salto y acceder a tu pase VIP:<br>
      más de 10 Workshops, certificado oficial y 6 meses gratis en Doppler —<br>
      <strong>por solo USD <?= VIP_PRICE_CURRENT ?> si te sumas antes del <?= VIP_OFFER_DEADLINE_LABEL ?>.</strong>
    </p>

    <?php
    $counterTitle = 'NOS VEMOS EN:';
    include($_SERVER['DOCUMENT_ROOT'] . '/components/date-counter.php');
    unset($counterTitle);
    ?>

    <div class="hero-registration__vip-actions emms__fade-in">
      <a class="emms__cta emms__cta--terciary" href="./checkout">ACCEDE A TU VIP A USD <?= VIP_PRICE_CURRENT ?></a>
      <a class="emms__cta emms__cta--primary-light" href="#entradas">CONOCE LOS BENEFICIOS</a>
    </div>
  </div>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
