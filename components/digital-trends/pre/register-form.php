<section class="hero-registration hero-registration--digitaltrends-pre">
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>EVENTO ONLINE Y GRATUITO - 14, 15 y 16 de Julio</em><span class="main">EMMS </span> <span class="main">2026 :</span></h1>
      <p class="hero-registration__text__subtitle">
        <span class="hero-registration__text__subtitle--lg">La Edición especial</span>
        <span class="hero-registration__text__subtitle--md">que</span>
        <span class="hero-registration__text__subtitle--sm">celebra 20 años de Doppler</span>
      </p>
      <p>Súmate a Conferencias, Workshops y Networking para capacitarte e inspirarte con las últimas tendencias en Marketing e E-commerce.</p>
      <ul class="hero-registration__text__checklist dk">
        <li>SPEAKERS INTERNACIONALES</li>
        <li>WORKSHOPS Y NETWORKING</li>
        <li>SORTEOS Y BENEFICIOS</li>
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
        <li>Los mejores Speakers de la historia del EMMS.</li>
        <li>WORKSHOPS PRÁCTICOS</li>
        <li>Gratis y online.</li>
        <li>Premios, recursos exclusivos y más.</li>
      </ul>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
