<section class="hero-registration hero-registration--digitaltrends-pre">
  <?php
  $heroRegistrationChecklist = [
    'Conferencias de e-commerce y marketing digital',
    'Los mejores Speakers de la historia del EMMS.',
    'WORKSHOPS PRÁCTICOS',
    'Gratis y online.',
    'Premios, recursos exclusivos y más.',
  ];
  ?>
  <div class="hero-registration__columns">

    <div class="hero-registration__text emms__fade-in">
      <h1><em>EVENTO ONLINE Y GRATUITO - 14, 15 y 16 de Julio</em><span class="main">EMMS </span> <span class="main">2026 :</span></h1>
      <p class="hero-registration__text__subtitle">
        La Edición especial que celebra 20 años de Doppler
      </p>
      <ul class="hero-registration__text__checklist dk">
        <?php foreach ($heroRegistrationChecklist as $heroRegistrationChecklistItem): ?>
          <li><?= $heroRegistrationChecklistItem ?></li>
        <?php endforeach; ?>
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
        <?php foreach ($heroRegistrationChecklist as $heroRegistrationChecklistItem): ?>
          <li><?= $heroRegistrationChecklistItem ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
