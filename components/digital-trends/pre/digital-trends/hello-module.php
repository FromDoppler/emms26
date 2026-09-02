<section class="hero-registration--registered hero-registration--withoutform hero-registration--registered--digitaltrends-pre">
  <div class="emms__container--md">
    <h1 class="emms__fade-top">
      <em>ONLINE Y GRATUITO • Del 29 de septiembre al 1 de octubre</em>
      <span>Súmate a la experiencia completa con el pase VIP</span>
    </h1>
    <p class="hero-registration__vip-copy emms__fade-in">
      Más de 10 Workshops, certificado y 6 meses gratis en Doppler, por <strong>solo USD <?= VIP_PRICE_CURRENT ?></strong>.
    </p>

    <?php
    $counterTitle = 'NOS VEMOS EN:';
    include($_SERVER['DOCUMENT_ROOT'] . '/components/date-counter.php');
    unset($counterTitle);
    ?>

    <div class="hero-registration__vip-actions emms__fade-in">
      <a class="emms__cta emms__cta--terciary" href="./checkout">ACCEDE A TU VIP A USD <?= str_replace(',', '.', VIP_PRICE_CURRENT) ?></a>
      <a class="emms__cta emms__cta--primary-light" href="#entradas">CONOCE LOS BENEFICIOS</a>
    </div>
  </div>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
