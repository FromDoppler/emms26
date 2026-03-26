<section class="hero-registration">
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>MÁS DE 30.000 PERSONAS ELIGIERON ESTE EVENTO</em>Capacítate gratis en el EMMS Digital  Trends 2025</h1>
      <p>Accede al evento más importante de Marketing Digital en Latam y España. Descubrirás las últimas tendencias y estrategias de negocio junto a líderes de la industria.</p>
      <ul class="hero-registration__text__checklist dk">
        <li>SPEAKERS INTERNACIONALES</li>
        <li>herramientas y recursos</li>
        <li>conferencias on-demand</li>
      </ul>
    </div>
    <!-- Form -->
    <?php
    $formTitle = '';
    $formSubTitle = '';
    $eventType = DIGITALTRENDS;
    ?>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/register-form-component.php'); ?>
    <!-- End form -->

    <div class="hero-registration__text emms__fade-in mb">
      <ul class="hero-registration__text__checklist">
        <li>SPEAKERS INTERNACIONALES</li>
        <li>herramientas y recursos</li>
        <li>conferencias on-demand</li>
      </ul>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
