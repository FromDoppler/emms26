<section class="hero-registration hero-registration--digitaltrends-pre">
  <?php
  $heroRegistrationDescription = 'Los mejores Speakers que pasaron por el EMMS, juntos en una misma edición. Conferencias y Workshops prácticos sobre E-commerce y Marketing.';
  ?>
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>ONLINE Y GRATUITO - 15, 16 Y 17 DE SEPTIEMBRE</em><span class="main">EMMS'26</span></h1>
      <p class="hero-registration__text__subtitle">
       EDICIÓN ANIVERSARIO <br> Doppler 20 años
      </p>
      <p class="hero-registration__text__description dk"><?= $heroRegistrationDescription ?></p>
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
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
