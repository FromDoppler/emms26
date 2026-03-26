<?php
  $currentUrl = $_SERVER['REQUEST_URI'];
  $buttonLink = ($currentUrl === '/checkout-lp-landing') ? '/checkout-lp' : '/checkout';
?>
<div class="popup-modal__vip-inner">
  <div class="popup-modal__vip-copy">
    <h3 id="<?= $titleId ?>" class="popup-modal__title">⭐ ¡Tu entrada VIP te espera! ⭐</h3>
    <p class="popup-modal__text">
      Vive el EMMS con beneficios exclusivos solo para asistentes VIP:
    </p>
    <ul>
      <li>Workshops con referentes internacionales.</li>
      <li>Cuenta gratuita en Doppler por 6 meses (válido para cuentas nuevas).</li>
      <li>Estrategias y herramientas que transformarán tu negocio.</li>
    </ul>
    <a href="<?= $buttonLink ?>" class="emms__cta emms__cta--terciary emms__cta--sm">
      ADQUIERE TU ENTRADA
    </a>
  </div>

  <img class="popup-modal__vip-img" src="/src/img/modals/persona-cupones.png" alt="Mujer feliz con tickets" aria-hidden="true" />
</div>
