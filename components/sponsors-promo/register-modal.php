<div id="modalRegister" class="emms__register-modal">
    <div class="emms__register-modal__window emms__register-modal--sponsor-promo">
        <!-- Form -->
        <form class="emms__form" id="sponsorsPromoForm" novalidate autocomplete="off">
            <h3>Déjanos tus datos de contacto para ser parte del EMMS 2025 :)</h3>
            <h4>En breve nuestro equipo te escribirá para conversar sobre la mejor forma de sumarte como <span id="sponsorType"></span> al EMMS</h4>
            <ul class="emms__form__field-group">
                <li class="emms__form__field-item">
                    <div class="holder">
                        <label class="required-label" for="name">Nombre *</label>
                        <input type="text" name="name" id="name" placeholder="Tu nombre" class="required error-name nameLength" autocomplete="off">
                    </div>
                </li>
                <li class="emms__form__field-item">
                    <div class="holder">
                        <label class="required-label" for="email">Email Empresarial*</label>
                        <input type="email" name="email" id="email" placeholder="ejemplo@miempresa.com" class="email required" autocomplete="off">
                    </div>
                </li>
                <li class="emms__form__field-item">
                    <div class="holder">
                        <label class="required-label" for="telefono">Teléfono</label>
                        <input type="tel" name="phone" id="phone" class="phone phone-number" autocomplete="off">
                    </div>
                </li>
                <li class="emms__form__field-item">
                    <div class="holder">
                        <label class="required-label" for="company">Empresa *</label>
                        <input type="text" name="company" id="company" placeholder="Nombre de tu empresa o negocio" class="email required" autocomplete="off">
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
                            Acepto recibir promociones de Doppler</label>
                    </div>
                </li>
            </ul>
            <div class="emms__form__btn">
                <button class="emms__cta" id="register-button" type="submit"><span class="button__text"> HABLEMOS</span></button>
            </div>
            <div class="emms__form__legal close">
                <a class="emms__form__legal__btn" id="legalBtn">Información básica sobre privacidad </a>
                <p>Doppler te informa que los datos de car&aacute;cter personal que nos proporciones al rellenar el presente formulario ser&aacute;n tratados por Doppler LLC como responsable de esta Web.<br>
                    <strong>Finalidad: </strong>Gestionar el alta de registro a la capacitación, enviarte material vinculado a la misma e información sobre Doppler así como nuestros futuros eventos o capacitaciones.<br>
                    <strong>Legitimaci&oacute;n: </strong>Consentimiento del interesado. <br>
                    <strong>Destinatarios: </strong>Tus datos ser&aacute;n guardados por Doppler y los co-organizadores del evento, Unbounce como empresa de creaci&oacute;n de Landing Pages, DigitalOcean como empresa de hosting y Zapier como herramienta de integraci&oacute;n de apps.<br>
                    <strong>Informaci&oacute;n adicional: </strong>En la <a href="https://www.fromdoppler.com/es/legal/privacidad/" target="_blank" rel="noopener">Pol&iacute;tica de Privacidad</a> de Doppler encontrar&aacute;s informaci&oacute;n adicional
                    sobre la recopilaci&oacute;n y el uso de su informaci&oacute;n personal por parte de Doppler, incluida
                    informaci&oacute;n sobre acceso, conservaci&oacute;n, rectificaci&oacute;n, eliminaci&oacute;n, seguridad,
                    transferencias
                    transfronterizas y otros temas. <br>
                </p>
            </div>
        </form>
        <!-- End form -->
        <button class="emms__register-modal__window__close" data-dismiss="emms__register-modal"></button>
    </div>
    <div class="emms__register-modal__window emms__register-modal__window--success-message">
        <h5>¡Gracias por tu interés en ser parte del EMMS!</h5>
        <p>Pronto te estaremos contactando vía Email para enviarte
            más información.</p>
        <button class="emms__register-modal__window__close" data-dismiss="emms__register-modal"></button>
    </div>
</div>
