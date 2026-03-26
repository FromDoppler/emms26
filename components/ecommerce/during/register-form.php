<?php
$state = $ecommerceStates['isTransition'] ? '<span class="green">PREPÁRATE PARA OTRA JORNADA </span><br> EVENTO ONLINE Y GRATUITO - 28 Y 29 DE ABRIL' : '<span class="green">EN VIVO</span> | ¡COMENZÓ LA TRANSMISIÓN!';
?>
<section class="hero-registration">
    <div class="hero-registration__columns">

        <div class="hero-registration__text emms__fade-in">
            <h1><em><?= $state; ?></em><span>¡SÚMATE AL</span>
                <span class="top">EMMS</span>
                E-COMMERCE<span class="bottom">2025!</span>
            </h1>
            <?php if (!$ecommerceStates['isTransition']) { ?>
                <p>
                    Domina el Marketing de tu Tienda Online. Capacítate con los mayores referentes del mundo.
                    <a href="#registro">¡Estamos en vivo!</a> Disfruta ahora
                    de una nueva edición con Conferencias, Workshops, sorteos y ¡mucho más!
                </p>
            <?php } else { ?>
                <p>Domina el Marketing de tu Tienda Online con los mejores referentes del mundo. ¡Regístrate gratis ahora y asegura tu acceso!</p>
                <ul class="hero-registration__text__checklist dk">
                    <li>SPEAKERS INTERNACIONALES</li>
                    <li>WORKSHOPS Y NETWORKING</li>
                    <li>REGALOS Y PREMIOS</li>
                </ul>
            <?php } ?>

        </div>
        <!-- Form -->
        <?php
        $formTitle = '';
        $formSubTitle = '';
        $eventType = ECOMMERCE;
        ?>
        <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/register-form-component.php'); ?>
        <!-- End form -->

        <div class="hero-registration__text emms__fade-in mb">
            <ul class="hero-registration__text__checklist">
                <li>SPEAKERS INTERNACIONALES</li>
                <li>WORKSHOPS Y NETWORKING</li>
                <li>REGALOS Y PREMIOS</li>
            </ul>
        </div>
    </div>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
