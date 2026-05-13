<div class="hero-registration__form emms__fade-in" id="registro">
  <!-- Form -->
  <form class="emms__form emms__form--two-step emms__fade-in" novalidate autocomplete="off" id="commonForm" data-step="1" eventType='<?= $eventType ?>'>
    <div class="emms__form__step" data-step="1">
      <img class="emms__form__badge" src="/src/img/icons/icon-form-mail.png" alt="">
      <h2>Ingresa tu Email</h2>
      <ul class="emms__form__field-group">
        <li class="emms__form__field-item">
          <div class="holder">
            <label class="required-label" for="email">*Email empresarial</label>
            <input type="email" name="email" id="email" placeholder="¡No te olvides de usar @!" class="email required" autocomplete="off">
          </div>
        </li>
      </ul>
      <div class="emms__form__btn">
        <button class="emms__cta" type="button"><span class="button__text">REGÍSTRATE GRATIS</span></button>
      </div>
      <p class="emms__form__reassurance"><em>Tu información está protegida.</em></p>
    </div>

    <div class="emms__form__step" data-step="2" hidden>
      <img class="emms__form__badge" src="/src/img/icons/icon-form-user.png" alt="">
      <h2>Completa tus datos</h2>
      <p class="emms__form__subtitle">Prepárate para disfrutar el EMMS 2026.</p>
      <ul class="emms__form__field-group">
        <li class="emms__form__field-item">
          <div class="holder">
            <label class="required-label" for="name">*Nombre</label>
            <input type="text" name="name" id="name" placeholder="Nombre y apellido" class="required error-name nameLength" autocomplete="off">
          </div>
        </li>
        <li class="emms__form__field-item">
          <div class="holder">
            <label class="required-label" for="phone">*Whatsapp</label>
            <input type="tel" name="phone" id="phone" placeholder="011-15 65954489" class="phone phone-number required" autocomplete="off">
          </div>
        </li>
      </ul>
      <ul class="emms__form__field-group">
        <li class="emms__form__field-item emms__form__field-item__checkbox">
          <div class="holder">
            <input name="privacy" type="checkbox" id="acepto-politicas" value="true" class="required check acept-politic"><span class="checkmark"></span><label for="acepto-politicas">
              Acepto la Pol&iacute;tica de Privacidad de Doppler *
            </label>
          </div>
        </li>
        <li class="emms__form__field-item emms__form__field-item__checkbox">
          <div class="holder">
            <input name="promotions" type="checkbox" id="acepto-promociones" value="true"><span class="checkmark"></span><label for="acepto-promociones">
              Acepto recibir promociones de Doppler y sus aliados
            </label>
          </div>
        </li>
      </ul>
      <div class="emms__form__btn">
        <button class="emms__cta" id="register-button" type="button"><span class="button__text">REGÍSTRATE GRATIS</span></button>
      </div>
    </div>
  </form>
</div>

<script src="/src/<?= VERSION ?>/js/autoCompleteUserForm.js" type="module"></script>

<?php if (!defined('EMMS_COMMONFORM_JS_INCLUDED')) {
  define('EMMS_COMMONFORM_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/commonForm.js"></script>
<?php } ?>

<?php if (!defined('EMMS_INTELL_INPUT_JS_INCLUDED')) {
  define('EMMS_INTELL_INPUT_JS_INCLUDED', true); ?>
  <script type="module" src="/src/<?= VERSION ?>/js/intell-input/intell-input.js"></script>
<?php } ?>
