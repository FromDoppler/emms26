<div class="emms__checkout__summary-access">
    <h4 class="emms__checkout__summary-access-title">Tu acceso</h4>
    <div class="emms__checkout__field-group emms__checkout__field-group--first">
        <select id="ticket-code" class="emms__checkout__input" aria-label="Pase seleccionado" disabled>
            <option value="">Cargando...</option>
        </select>
        <small id="ticket-status" class="emms__checkout__status"></small>
    </div>

    <div id="coupon-section" class="emms__checkout__coupon-section" data-coupon-mode="closed">
        <button id="coupon-toggle" type="button" class="emms__checkout__coupon-toggle">
            <span>¿Tenés un código?</span>
            <strong>Agregar</strong>
        </button>

        <div id="coupon-editor" class="emms__checkout__coupon-card" hidden>
            <div class="emms__checkout__coupon-row">
                <input id="coupon-code" type="text" aria-label="Código de cupón" placeholder="Código de cupón" maxlength="18" autocapitalize="characters" autocomplete="off" spellcheck="false" class="emms__checkout__input emms__checkout__coupon-input" />
                <button id="apply-coupon" type="button" class="emms__checkout__coupon-button">Aplicar</button>
            </div>
        </div>

        <div id="coupon-applied" class="emms__checkout__coupon-applied" hidden>
            <div class="emms__checkout__coupon-applied-copy">
                <p id="coupon-applied-label" class="emms__checkout__coupon-applied-label">
                    <span>Cupón aplicado</span>
                    <strong id="coupon-applied-source" class="emms__checkout__coupon-applied-source" hidden>desde el enlace</strong>
                </p>
                <strong id="coupon-applied-code" class="emms__checkout__coupon-applied-code">-</strong>
            </div>
            <button id="remove-coupon" type="button" class="emms__checkout__link-button emms__checkout__link-button--icon emms__checkout__link-button--remove" aria-label="Quitar cupón"></button>
        </div>

        <div id="coupon-status" class="emms__checkout__status emms__checkout__message emms__checkout__coupon-status" role="alert" hidden>
            <span id="coupon-status-text" data-coupon-status-text></span>
            <button id="coupon-status-dismiss" type="button" class="emms__checkout__coupon-status-dismiss" data-coupon-status-dismiss aria-label="Cerrar mensaje de cupón"></button>
        </div>
    </div>
</div>
