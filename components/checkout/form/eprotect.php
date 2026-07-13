<article class="emms__checkout__step-card" data-checkout-step-card="payment" data-step-state="locked">
    <header class="emms__checkout__step-card-head">
        <span class="emms__checkout__step-card-marker" data-step-card-marker data-step-icon="payment" aria-hidden="true">3</span>
        <div class="emms__checkout__step-card-copy">
            <h3>Pago</h3>
            <p data-step-summary="payment">Completá los datos de pago.</p>
        </div>
        <img
            class="emms__checkout__accepted-cards"
            src="/src/img/credit-cards.svg"
            alt="Tarjetas aceptadas: Visa, Mastercard y Amex"
        />
    </header>

    <div class="emms__checkout__step-card-body" data-step-card-body>
        <div id="eprotect-container" class="emms__checkout__eprotect" hidden>
            <div class="emms__checkout__payment-card">
                <div class="emms__checkout__payment-frame" data-eprotect-frame>
                    <div id="eprotect-loading" class="emms__checkout__payment-loading" role="status" aria-live="polite" hidden>
                        <span class="emms__checkout__payment-loading-spinner" aria-hidden="true"></span>
                        <span>Cargando formulario seguro de pago...</span>
                    </div>
                    <div id="eprotect-payframe" class="emms__checkout__payframe"></div>
                </div>
                <small id="payment-method-status" class="emms__checkout__status"></small>
            </div>
        </div>

    </div>
</article>
