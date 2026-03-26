<section class="hero-registration">
    <div class="hero-registration__columns">

        <div class="hero-registration__text emms__fade-in">
            <h1><em>EVENTO ONLINE Y GRATUITO - 28 y 29 DE ABRIL</em><span class="top">EMMS</span>E-commerce<span class="bottom">2025</span></h1>
            <p>Domina el Marketing de tu Tienda Online con los mejores referentes del mundo. ¡Regístrate gratis ahora y asegura tu acceso!</p>
            <ul class="hero-registration__text__checklist dk">
                <li>SPEAKERS INTERNACIONALES</li>
                <li>WORKSHOPS Y NETWORKING</li>
                <li>REGALOS Y PREMIOS</li>
            </ul>
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
