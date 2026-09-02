  <!-- Features -->
  <div class="features features--launch-price hidden--vip">
    <div class="features__launch-price emms__fade-in">
      <div class="features__title">
        <h2>Invierte en tu futuro por sólo <?= VIP_PRICE_CURRENT ?> USD</h2>
      </div>
      <div class="features__counter">
        <?php
        $counterTitle = '';
        include($_SERVER['DOCUMENT_ROOT'] . '/components/date-counter.php');
        unset($counterTitle);
        ?>
      </div>
    </div>
    <div class="features__item emms__fade-in features__item--reverse">
      <div class="emms__container--lg">
        <div class="features__item__image">
          <img src="src/img/pasevip.png" alt="Imagen pase VIP">
        </div>
        <div class="features__item__text">
          <p><strong>Accede a:</strong></p>
          <ul class="features__item__text__list">
            <li>Más de 10 Workshops en salas reducidas.</li>
            <li>Cuenta gratis en Doppler por 6 meses, valorada en hasta 500 USD. (¡Puedes cancelar cuando quieras!)</li>
            <li>Sección de preguntas y respuestas con tus referentes.</li>
          </ul>
          <?php [$vipUnits, $vipCents] = vipPriceUnits(VIP_PRICE_CURRENT); ?>
          <p class="features__item__text__price features__item__text__price--live">
            <em>¡POR SOLO</em><small>USD</small><?= $vipUnits ?><small>,<?= $vipCents ?>!</small>
          </p>
          <a href="#entradas" class="emms__cta emms__cta--terciary">ASEGURA TU LUGAR</a>
        </div>
      </div>
    </div>
  </div>
