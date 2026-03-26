<?php
$media_partner_benefits = [
    "Conferencia on-demand de 15 minutos en el sitio",
    "Logo de tu marca en el sitio web y menciones de tu negocio durante el EMMS",
    "Participación en un Email que se envía a todos los registrados",
    "Posibilidad de ofrecer beneficios a miles de potenciales clientes"
];
?>

<section class="emms__sponsor-promo__media-partner emms__bg-section-5 " id="conviertete-en-media-partner">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
    
    <div class="emms__container--md emms__fade-in">
        <div class="emms__sponsor-promo__media-partner__text">
            <h2>¿Sin inversión este año?<br>¡Conviértete en Media Partner!</h2>
            <p>Obtén visibilidad sin coste alguno, a cambio de compartir nuestro evento con tu audiencia. ¡Otra alternativa para multiplicar el impacto de tu marca!</p>
            <p>¿Qué obtendrás como Media Partner?</p>
            <ul>
                <?php foreach ($media_partner_benefits as $benefit): ?>
                    <li>
                        <img src="/src/img/tick-success.png" alt="Beneficio asegurado">
                        <span><?= $benefit ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button class="emms__cta" data-target="modalRegister" data-toggle="emms__register-modal" data-type="mediaPartner">
                CONVIÉRTETE EN MEDIA PARTNER
            </button>
        </div>
        <div class="emms__sponsor-promo__media-partner__picture">
            <img src="/src/img/rompecabez-asset.png" alt="Ilustración de un rompecabezas">
        </div>
    </div>
</section>
