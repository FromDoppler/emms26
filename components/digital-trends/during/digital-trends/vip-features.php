  <!-- Features -->
  <div class="features hidden--vip">
    <div class="features__item emms__fade-in features__item--reverse">
      <div class="emms__container--lg">
        <div class="features__item__image">
          <img src="src/img/pasevip.png" alt="Imagen pase VIP">
        </div>
        <div class="features__item__text">
          <h3>Invierte en tu futuro por sólo <?= VIP_PRICE_LIST ?> USD
          </h3>
          <p>Accede a:</p>
          <ul class="features__item__text__list">
            <li>Workshops con referentes internacionales.</li>
            <li>Cuenta gratuita en Doppler por 6 meses (válido para cuentas nuevas).</li>
            <li>Estrategias y herramientas que transformarán tu negocio.</li>
          </ul>
          <?php [$vipUnits, $vipCents] = vipPriceUnits(VIP_PRICE_LIST); ?>
          <p class="features__item__text__price features__item__text__price--live">
            <em>¡POR SOLO</em><small>USD</small><?= $vipUnits ?><small>,<?= $vipCents ?>!</small>
          </p>
          <a href="#entradas" class="emms__cta emms__cta--terciary"> COMPRA TU ENTRADA AHORA
          </a>
        </div>
      </div>
    </div>
  </div>
