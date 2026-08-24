<?php
$entryPlanBenefits = [
  ["text" => "Cuenta gratuita en Doppler 6 meses valorada en hasta USD 500", "free" => false],
  ["text" => "Hasta 10 Workshops prácticos con cupos limitados", "free" => false],
  ["text" => "Acceso de por vida a los Workshops.", "free" => false],
  ["text" => "Certificado de asistencia a Workshops.", "free" => false],
  ["text" => "Guías con herramientas y tips exclusivos", "free" => false],
  ["text" => "Sesiones de preguntas y respuestas con referentes", "free" => false],
  ["text" => "Acceso a todas las Conferencias con speakers internacionales.", "free" => true],
  ["text" => "Volver a verlas todas las veces que quieras", "free" => true],
  ["text" => "Ingreso a la Biblioteca de Recursos (E-books, plantillas, audiovisual)", "free" => true],
  ["text" => "Participación en sorteos", "free" => true],
  ["text" => "Descuentos en herramientas y capacitaciones", "free" => true],
];
?>
<div class="emms__bg-dark-gradient--2 ">
  <!-- Prices plans -->
  <div class="emms__plans  hidden--vip" id="entradas">
    <div class="emms__container--lg">
      <div class="emms__plans__title">
        <h2>Hazte VIP y acelera el crecimiento de tu negocio</h2>
        <p>Compra tu entrada y accede a una gran variedad de beneficios. Por tiempo limitado, tu pase tiene un <span><?= VIP_OFFER_DISCOUNT_LABEL ?> de descuento</span>. Consíguelo por sólo <span>USD <?= VIP_PRICE_CURRENT ?></span>. ¡Se paga solo!</p>
      </div>
      <table class="emms__plans__table">
        <thead>
          <tr>
            <th class="emms__plans__table__legend" scope="col"><span>Beneficios</span></th>
            <th class="emms__plans__table__plan emms__plans__table__plan--vip" scope="col">
              <span class="emms__plans__table__plan__tag">Asistente VIP</span>
              <span class="emms__plans__table__plan__body">
                <span class="emms__plans__table__plan__label">Precio entrada VIP</span>
                <span class="emms__plans__table__plan__price">USD<?= VIP_PRICE_CURRENT ?>*</span>
                <span class="emms__plans__table__plan__note">*antes del <?= VIP_OFFER_DEADLINE_LABEL ?></span>
              </span>
            </th>
            <th class="emms__plans__table__plan emms__plans__table__plan--free" scope="col">
              <span class="emms__plans__table__plan__tag">Asistente FREE</span>
              <span class="emms__plans__table__plan__body">
                <span class="emms__plans__table__plan__label">Precio entrada FREE</span>
                <span class="emms__plans__table__plan__price">Gratis</span>
                <span class="emms__plans__table__plan__note">(tu Plan actual)</span>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entryPlanBenefits as $benefit): ?>
            <tr>
              <th scope="row"><?= $benefit["text"] ?></th>
              <td class="emms__plans__table__value"><img src="/src/img/icons/icon-check--green.svg" alt="Incluido" width="24" height="24"></td>
              <td class="emms__plans__table__value"><img src="/src/img/icons/<?= $benefit["free"] ? "icon-check--green.svg" : "icon-exclude.svg" ?>" alt="<?= $benefit["free"] ? "Incluido" : "No incluido" ?>" width="24" height="24"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="emms__plans__actions">
        <div class="emms__plans__actions__cell"></div>
        <div class="emms__plans__actions__cell"><a href="./checkout" class="emms__cta emms__cta--green">Hazte VIP ahora</a></div>
        <div class="emms__plans__actions__cell"><a class="emms__cta inactive">Accede ahora</a></div>
      </div>
    </div>
  </div>
  <div class="hidden--vip">
    <div class="emms__separator emms__separator--white">
    </div>
  </div>

</div>
