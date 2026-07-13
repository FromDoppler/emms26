<section id="error-state" hidden class="emms__checkout__error-state" role="alert">
    <h2 id="error-title">No pudimos validar la compra</h2>
    <p id="error-message">Intentá nuevamente en unos minutos.</p>
    <a href="<?= htmlspecialchars($successBackPath ?? '/', ENT_QUOTES, 'UTF-8'); ?>" class="emms__checkout__back emms__checkout__error-back">
        ← Volver al sitio
    </a>
</section>
