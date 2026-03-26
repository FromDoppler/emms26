<div class="popup-modal__form-inner">
  <div class="popup-modal__form-head">
    <h3 class="popup-modal__title">
      ¡Estás a un clic de quedarte afuera! 🚀
    </h3>
    <p class="popup-modal__text">
      Miles de profesionales ya aseguraron su lugar en el evento más grande de Marketing Digital de habla hispana.
      El registro es gratuito y quedan pocos lugares. ¡No te lo pierdas!
    </p>
  </div>

  <form class="popup-modal__form-fields" id="modalForm" novalidate>
    <!-- Email -->
    <label class="popup-modal__f-group holder">
      <span class="popup-modal__f-label">*Email</span>
      <input
        class="popup-modal__input required"
        type="email"
        name="email"
        placeholder="¡No te olvides de usar @!"
        required
        autocomplete="email"
        inputmode="email" />
    </label>

    <label class="popup-modal__f-group holder">
      <span class="popup-modal__f-label">*Nombre</span>
      <input
        class="popup-modal__input required"
        type="text"
        name="name"
        placeholder="Tu nombre"
        required
        autocomplete="name" />
    </label>

    <div class="popup-modal__f-group">
      <span class="popup-modal__f-label holder">WhatsApp</span>
      <div class="popup-modal__phone holder">
        <div class="popup-modal__select-wrap popup-modal__phone-country">
          <input type="tel" name="phone" id="phone2" class="phone phone-number required popup-modal__input" autocomplete="off">
        </div>
      </div>
    </div>

    <div class="popup-modal__form-actions">
      <button class="emms__cta emms__cta--terciary"><span class="button__text">REGÍSTRATE GRATIS</span></button>

      <label class="popup-modal__check holder">
        <input name="privacy" type="checkbox" id="acepto-politicas" value="true" class="required check acept-politic">
        <span class="checkmark"></span>
        <label for="acepto-politicas">
          Acepto la
          <a href="https://www.fromdoppler.com/es/legal/privacidad/" target="_blank">Política de Doppler </a> *</span>
        </label>
      </label>
      <label class="popup-modal__check holder">
        <input name="promotions" type="checkbox" id="acepto-promociones" value="true">
        <span class="checkmark"></span>
        <label for="acepto-promociones">
          Acepto recibir promociones de Doppler y sus aliados
        </label>
      </label>
    </div>
  </form>

  <img
    class="popup-modal__form-hero"
    src="/src/img/modals/computer-men.png"
    alt="Hombre con computadora"
    aria-hidden="true" />
</div>

<?php if (!defined('EMMS_COMMONFORM_JS_INCLUDED')) {
  define('EMMS_COMMONFORM_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/commonForm.js"></script>
<?php } ?>

<?php if (!defined('EMMS_COLLAPSIBLES_JS_INCLUDED')) {
  define('EMMS_COLLAPSIBLES_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/collapsibles.js"></script>
<?php } ?>

<?php if (!defined('EMMS_INTELL_INPUT_JS_INCLUDED')) {
  define('EMMS_INTELL_INPUT_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/intell-input/intell-input.js"></script>
<?php } ?>
>
