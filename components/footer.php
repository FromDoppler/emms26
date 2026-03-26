 <footer class="emms__footer">
    <div class="emms__footer__event emms__fade-in">
    <p><strong>EMMS <?= date("Y") ?></strong> - Un evento creado por <a href="https://www.fromdoppler.com/es/" target="_blank"><img src="/src/img/logos/logo-doppler--neg.svg" alt="Doppler"></a></p>
    </div>
    <div class="emms__footer__social emms__fade-in">
    <ul>
        <li><a href="https://www.instagram.com/fromdoppler/" target="_blank"><img src="/src/img/icons/icono-instagram.svg" alt="Instagram"></a></li>
        <li><a href="https://www.linkedin.com/company/228261" target="_blank"><img src="/src/img/icons/icono-linkedin.svg" alt="LinkedIn"></a></li>
        <li><a href="https://www.youtube.com/user/FromDoppler" target="_blank"><img src="/src/img/icons/icono-youtube.svg" alt="Youtube"></a>
        <li><a href="https://www.tiktok.com/@fromdoppler" target="_blank"><img src="/src/img/icons/icono-tiktok.svg" alt="TikTok"></a>
        <li><a href="https://twitter.com/fromDoppler" target="_blank"><img src="/src/img/icons/icono-twitter.svg" alt="Twitter"></a></li>
        <li><a href="https://www.facebook.com/DopplerEmailMarketing" target="_blank"><img src="/src/img/icons/icono-facebook.svg" alt="Facebook"></a></li>
        </li>
    </ul>
    </div>
    <div class="emms__footer__actions emms__fade-in">
    <a onclick="OneTrust.ToggleInfoDisplay()" id="ot-sdk-btn" class="ot-sdk-show-settings">Configuración de Cookies.</a>
    <a href="https://www.fromdoppler.com/es/legal/privacidad/" target="_blank">Políticas de privacidad y legales</a>
    </div>
 </footer>
 <span class="emms-hidden" id="version"><?= VERSION ?></span>
 <?php
  if (!(PRODUCTION)) {
  ?>
    <script src="/src/<?= VERSION ?>/js/autoComplete.js"></script>
 <?php
  }
  ?>
 <script src="/src/<?= VERSION ?>/flickity/flickity.pkgd.min.js"></script>
 <script src="/src/<?= VERSION ?>/js/commonAnimations.js"></script>
 <script src="/src/<?= VERSION ?>/js/speakerCarrousel.js"></script>
 <script src="https://www.fromdoppler.com/wp-content/themes/doppler_site/utm/utm.js?v=<?= VERSION ?>"></script>
 <script src="/src/<?= VERSION ?>/js/global.js"></script>
