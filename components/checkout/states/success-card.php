<section id="success" hidden>
    <div class="emms__checkout__success-card">
        <div class="emms__checkout__card__main emms__checkout__success-main">
            <span class="emms__checkout__success-badge">Confirmado</span>
            <h2>Tu acceso VIP quedó confirmado</h2>
            <p>Revisá tu correo: en instantes vas a recibir la confirmación y el detalle de la compra.</p>

            <h4>Con tu pase premium accedés a estos beneficios:</h4>
            <ul class="emms__checkout__success-points">
                <li>Workshops en vivo con referentes internacionales.</li>
                <li>Talleres prácticos on-demand de ediciones anteriores.</li>
                <li>Cuenta gratuita en Doppler por 6 meses para cuentas nuevas.</li>
                <li>Recursos y materiales exclusivos para tu negocio.</li>
            </ul>
        </div>

        <div class="emms__checkout__card__aside emms__checkout__success-aside">
            <h3>Detalle de tu compra</h3>
            <dl class="emms__checkout__success-details">
                <div>
                    <dt>Pase</dt>
                    <dd id="ticketName"></dd>
                </div>
                <div>
                    <dt>Titular</dt>
                    <dd id="customerName"></dd>
                </div>
                <div>
                    <dt>Medio de pago</dt>
                    <dd id="paymentMethod"></dd>
                </div>
                <div>
                    <dt>Fecha de compra</dt>
                    <dd id="date"></dd>
                </div>
                <div>
                    <dt>Monto</dt>
                    <dd id="amount"></dd>
                </div>
            </dl>
        </div>
    </div>
    <a href="<?= htmlspecialchars($successBackPath ?? '/', ENT_QUOTES, 'UTF-8'); ?>" class="emms__checkout__back">← Volver al sitio</a>
</section>
