<section class="hero-registration hero-registration--digitaltrends-pre">
  <?php
  $heroRegistrationDescription = 'Inspírate y aprende en un solo evento todas las tendencias del Marketing Digital.';
  ?>
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>EVENTO ONLINE Y GRATUITO ·<br>Del 29 de septiembre al 1 de octubre</em><span class="top">LLEGA EL</span><span class="main">EMMS'26</span></h1>
      <p class="hero-registration__text__description dk"><?= $heroRegistrationDescription ?></p>
      <p class="dk"><strong>REGÍSTRATE GRATIS Y GUARDA TU LUGAR</strong></p>
      <ul class="hero-registration__text__checklist dk">
        <li>SPEAKERS INTERNACIONALES</li>
        <li>WORKSHOPS PRÁCTICOS</li>
        <li>SORTEOS Y BENEFICIOS EXCLUSIVOS</li>
      </ul>
    </div>
    <!-- Form -->
    <?php
    $formTitle = '';
    $formSubTitle = '';
    $eventType = DIGITALTRENDS;
    ?>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/register-form-two-step.php'); ?>
    <!-- End form -->

    <div class="hero-registration__text emms__fade-in mb">
      <p class="hero-registration__text__description"><?= $heroRegistrationDescription ?></p>
      <p><strong>REGÍSTRATE GRATIS Y GUARDA TU LUGAR</strong></p>
      <ul class="hero-registration__text__checklist">
        <li>SPEAKERS INTERNACIONALES</li>
        <li>WORKSHOPS PRÁCTICOS</li>
        <li>SORTEOS Y BENEFICIOS EXCLUSIVOS</li>
      </ul>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
