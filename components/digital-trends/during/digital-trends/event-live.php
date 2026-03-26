<!-- TODO: Abstraer placas a componentes dummy y mejorar las logicas compartidas -->
<?php if (($settings_phase_DT['event'] === DIGITALTRENDS) && ($settings_phase_DT['during'] === 1) && ($settings_phase_DT['transition'] === "live-on") && ($settings_phase_DT['transmission'] === "youtube")) : ?>
  <p class="live-advice">EN VIVO </p>
  <h1 class="emms__fade-in">Estamos en vivo en el #EMMSBYDOPPLER</h1>
  <div class="emms__hero-conference__video emms__fade-in">
    <div class="emms__cropper-cont-16-9">
      <div class="emms__cropper-cont ">
        <div class="emms__cropper-cont-interno">
          <iframe src="https://www.youtube.com/embed/<?= $duringDaysArray[$dayDuring]['youtube'] ?>?rel=0&autoplay=1&mute=1&enablejsapi=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
  <div class="emms__hero-conference__aside emms__fade-in emms__hero-conference__video--chat">
    <iframe src="https://www.youtube.com/live_chat?v=<?= $duringDaysArray[$dayDuring]['youtube'] ?>&embed_domain=<?= $_SERVER['HTTP_HOST'] ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
  </div>
<?php elseif (($settings_phase_DT['event'] === DIGITALTRENDS) && ($settings_phase_DT['during'] === 1) && ($settings_phase_DT['transition'] === "live-on") && ($settings_phase_DT['transmission'] === "twitch")) : ?>
  <h1 class="emms__fade-in">Estamos en vivo en el #EMMSBYDOPPLER</h1>
  <div class="emms__hero-conference__video emms__fade-in">
    <div class="emms__cropper-cont-16-9">
      <div class="emms__cropper-cont ">
        <div class="emms__cropper-cont-interno">
          <iframe src="https://player.twitch.tv/?channel=<?= $duringDaysArray[$dayDuring]['twitch'] ?>&parent=<?= $_SERVER['HTTP_HOST'] ?>"></iframe>
        </div>
      </div>
    </div>
  </div>
<?php elseif (($settings_phase_DT['event'] === DIGITALTRENDS) && ($settings_phase_DT['during'] === 1) && ($settings_phase_DT['transition'] === "live-on") && ($settings_phase_DT['transmission'] === "twitch-migrate")) : ?>
  <img src="src/img/placas/migrate-twitch.png" alt="Se migró a Twitch" class="banner">
<?php elseif (($settings_phase_DT['event'] === DIGITALTRENDS) && ($settings_phase_DT['during'] === 1) && ($settings_phase_DT['transition'] === "live-on") && ($settings_phase_DT['transmission'] === "technical-problems")) : ?>
  <img src="src/img/placas/technical-error.png" alt="Errores técnicos" class="banner">
<?php elseif (($settings_phase_DT['event'] === DIGITALTRENDS) && ($settings_phase_DT['during'] === 1) && ($settings_phase_DT['transition'] === "live-off")) : ?>
  <h1 class="emms__fade-in">Seguimos con más EMMS Digital Trends</h1>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/slides/register-free-slide.php') ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/slides/register-vip-slide.php') ?>

  <div class="emms__hero-conference__aside emms__hero-conference__aside--transition emms__fade-in">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/during/digital-trends/certificate/certificate.php') ?>
  </div>
<?php endif; ?>
