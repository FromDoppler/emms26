<div id="customer-card" class="emms__checkout__steps" data-customer-mode="email">
    <article class="emms__checkout__step-card" data-checkout-step-card="identification" data-step-state="active">
        <header class="emms__checkout__step-card-head">
            <span class="emms__checkout__step-card-marker" data-step-card-marker data-step-icon="identification" aria-hidden="true">1</span>
            <div class="emms__checkout__step-card-copy">
                <h3>Identificación</h3>
                <p data-step-summary="identification">Ingresá tu email para continuar.</p>
            </div>
            <button type="button" class="emms__checkout__step-edit" data-step-edit="identification" hidden>
                Editar
            </button>
        </header>

        <div class="emms__checkout__step-card-body" data-step-card-body>
            <section id="customer-email-step" class="emms__checkout__customer-panel">

                <div class="emms__checkout__fields">
                    <div class="emms__checkout__field">
                        <input
                            id="customer-email"
                            type="email"
                            aria-label="Email"
                            inputmode="email"
                            placeholder="nombre@empresa.com"
                            class="emms__checkout__input"
                            autocomplete="email"
                            aria-describedby="customer-email-status"
                        />

                        <small id="customer-email-status" class="emms__checkout__status"></small>
                    </div>

                    <div class="emms__checkout__actions-row">
                        <button id="resolve-customer" type="button" class="emms__checkout__secondary-button">
                            Continuar
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </article>

    <article class="emms__checkout__step-card" data-checkout-step-card="customer-data" data-step-state="locked">
        <header class="emms__checkout__step-card-head">
            <span class="emms__checkout__step-card-marker" data-step-card-marker data-step-icon="customer" aria-hidden="true">2</span>
            <div class="emms__checkout__step-card-copy">
                <h3>Datos personales</h3>
                <p data-step-summary="customer-data">Completá tus datos para continuar.</p>
            </div>
            <button type="button" class="emms__checkout__step-edit" data-step-edit="customer-data" hidden>
                Editar
            </button>
        </header>

        <div class="emms__checkout__step-card-body" data-step-card-body>
            <section id="customer-profile-summary" class="emms__checkout__customer-panel emms__checkout__customer-summary emms__checkout__customer-summary--resolved" hidden>
                <p class="emms__checkout__customer-summary-title" id="customer-summary-title">Encontramos tu registro</p>
                <p class="emms__checkout__customer-summary-copy" id="customer-summary-copy">
                    Estos son los datos asociados a tu correo.
                </p>

                <dl class="emms__checkout__customer-summary-list">
                    <div>
                        <dt>Nombre</dt>
                        <dd id="customer-summary-name">-</dd>
                    </div>
                    <div>
                        <dt>Teléfono</dt>
                        <dd id="customer-summary-phone">-</dd>
                    </div>
                </dl>
            </section>

            <section id="customer-fields" class="emms__checkout__customer-panel emms__checkout__customer-form" hidden>
                <div class="emms__checkout__fields">
                    <div class="emms__checkout__field">
                        <input id="customer-name" type="text" aria-label="Nombre" placeholder="Nombre" class="emms__checkout__input" autocomplete="name" aria-describedby="customer-name-status" />
                        <small id="customer-name-status" class="emms__checkout__status"></small>
                    </div>

                    <div class="emms__checkout__field">
                        <input id="customer-phone" type="tel" aria-label="Teléfono" placeholder="Teléfono" class="emms__checkout__input emms__checkout__phone-input js-checkout-phone" inputmode="tel" autocomplete="tel" aria-describedby="customer-phone-status" />
                        <small id="customer-phone-status" class="emms__checkout__status"></small>
                    </div>
                </div>

                <div id="checkout-consents" class="emms__checkout__checkboxes emms__checkout__customer-consents" hidden>
                    <div class="emms__checkout__checkbox-field">
                        <label class="emms__checkout__checkbox">
                            <input id="accept-policies" type="checkbox" aria-describedby="customer-policies-status" />
                            Acepto la
                            <a href="https://www.fromdoppler.com/es/legal/privacidad/" target="_blank" rel="noopener noreferrer" class="emms__checkout__privacy-link">política de privacidad</a>
                        </label>
                        <small id="customer-policies-status" class="emms__checkout__status"></small>
                    </div>

                    <label class="emms__checkout__checkbox">
                        <input id="accept-promotions" type="checkbox" />
                        Acepto recibir novedades
                    </label>
                </div>
            </section>

            <div id="customer-step-actions" class="emms__checkout__step-actions emms__checkout__step-actions--single" hidden>
                <button id="customer-next-step" type="button" class="emms__checkout__secondary-button">
                    Siguiente
                </button>
            </div>
        </div>
    </article>
</div>
