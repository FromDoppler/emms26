<section class="hero-registration">
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>EVENTO ONLINE Y GRATUITO - 28, 29 y 30 de Octubre</em><span class="top">¡Vuelve el </span> <span class="main">EMMS </span> <span class="bottom">Digital Trends!</span></h1>
      <p>Inspírate y aprende en un solo lugar todas las tendencias del Marketing Digital. <br>
        ¡Regístrate gratis y vive una experiencia única!</p>
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
        <li>SPEAKERS INTERNACIONALES</li>
        <li>WORKSHOPS PRÁCTICOS</li>
        <li>SORTEOS Y BENEFICIOS</li>
      </ul>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
