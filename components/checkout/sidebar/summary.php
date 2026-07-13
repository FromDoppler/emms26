<?php
$summaryBackPath = $checkoutBackPath ?? '/';
?>
<aside class="emms__checkout__summary-panel" data-summary-mode="checkout">
    <h3 class="emms__checkout__section-title emms__checkout__section-title--summary">Resumen</h3>
    <div id="summary-vip-notice" class="emms__checkout__vip-summary" hidden>
        <strong>Tu acceso VIP ya está activo.</strong>
        <span>No necesitás comprar nuevamente para este evento.</span>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/form/ticket-and-coupon.php'); ?>

    <div class="emms__checkout__summary">
        <div class="emms__checkout__summary-row emms__checkout__summary-row--ticket">
            <span>Pase seleccionado</span>
            <strong id="summary-ticket">-</strong>
        </div>
        <div class="emms__checkout__summary-row emms__checkout__summary-row--subtotal">
            <span>Subtotal</span>
            <strong id="summary-amount">USD 0.00</strong>
        </div>
        <div id="summary-discount-row" class="emms__checkout__summary-row emms__checkout__summary-row--discount" hidden>
            <span>Descuento</span>
            <strong id="summary-discount">USD 0.00</strong>
        </div>
        <div class="emms__checkout__summary-row emms__checkout__summary-row--total">
            <span>Total</span>
            <strong id="summary-total">USD 0.00</strong>
        </div>
    </div>

    <button id="submit-payment" type="button" class="emms__checkout__submit" hidden>
        Completar acceso VIP
    </button>

    <small class="emms__checkout__secure-note">
        Transacción segura y encriptada.
    </small>

    <a id="summary-secondary-action" href="<?= htmlspecialchars($summaryBackPath, ENT_QUOTES, 'UTF-8'); ?>" class="emms__checkout__summary-secondary-action" hidden>
        Volver al evento
    </a>

    <small id="checkout-status" class="emms__checkout__status"></small>
</aside>
