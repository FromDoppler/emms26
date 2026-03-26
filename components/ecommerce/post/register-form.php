<section class="hero-registration post">
    <div class="hero-registration__columns">

        <div class="hero-registration__text emms__fade-in">
            <h1><em>REVIVE EL EVENTO GRATUITO</em> !Capacítate gratis</span> con el EMMS <br> E-commerce 2025!</h1>
            <p>Disfruta del evento que reúne speakers internacionales de las empresas más importantes de Latam y España. ¡Inspírate y capacítate gratis junto a líderes de la industria!
            </p>
            <ul class="hero-registration__text__checklist dk">
                <li>SPEAKERS INTERNACIONALES</li>
                <li>HERRAMIENTAS Y RECURSOS</li>
                <li>CONFERENCIAS ON-DEMAND</li>
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
                <li>HERRAMIENTAS Y RECURSOS</li>
                <li>CONFERENCIAS ON-DEMAND</li>
            </ul>
        </div>
    </div>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/marquee.php') ?>
</section>
