<?php
$benefits = [
    "Destaca tu marca en el evento líder de Marketing en LATAM y España",
    "Brinda una conferencia main stage en el evento",
    "Ten tu propia Landing Page en el sitio del evento y capta leads de calidad",
    "Llega con un envío exclusivo a toda la base de registrados al EMMS",
    "Únete a las marcas más influyentes de la industria y potencia tu networking"
];
?>

<section class="emms__sponsor-promo__resource">
    <div class="emms__container--md emms__fade-in">
        <div class="emms__sponsor-promo__resource__picture">
            <img src="/src/img/sponsor-promo.png" alt="Promoción para sponsors del evento">
        </div>
        <div class="emms__sponsor-promo__resource__text">
            <h2>¿Por qué ser Sponsor?</h2>
            <p>Súmate como Sponsor al EMMS y amplifica el impacto de tu marca en el mercado del Marketing Digital y E-commerce.</p>
            <ul>
                <?php foreach ($benefits as $benefit): ?>
                    <li>
                        <img src="/src/img/asset-estrella.png" alt="Ícono de beneficio">
                        <span><?= htmlspecialchars($benefit) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
