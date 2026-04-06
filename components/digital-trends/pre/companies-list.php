<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();

function getGridBlock($url)
{
    $blocks = [
        '/sponsors-promo' => [
            'block' => 'none',
        ],
        '/*' => [
            'block' => 'CtaBlock',
        ],
    ];

    return $blocks[$url] ??  $blocks['/*'];
}

$block = getGridBlock($normalizedUrl);
?>

<section class="companies">
    <div class="emms__container--lg">
        <h2 class="emms__fade-in">Nos han acompañado en ediciones anteriores</h2>
        <ul class="companies-list emms__fade-in">
            <li class="companies-list__item"><img src="/src/img/logos/logo-metricool.png" alt="Metricool"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-asociacion-marketing-espana.png" alt="Asociación de Marketing de España"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-capece.png" alt="Capece"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-amvo.png" alt="AMVO"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-linkedin.png" alt="LinkedIn"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-bigbox.png" alt="Bigbox"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-semrush.png" alt="Semrush"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-crehana.png" alt="Crehana"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-marketing-4ecommerce.png" alt="Marketing 4 Ecommerce"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-vtex.png" alt="VTEX"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-banco-frances.png" alt="BBVA Francés"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-airbnb.png" alt="Airbnb"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-woocomerce.png" alt="Woocommerce"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-doofinder.png" alt="Doofinder"></li>
            <li class="companies-list__item"><img src="/src/img/logos/logo-easycommerce.png" alt="Easycommerce"></li>
        </ul>

        <?php if ($block['block'] === 'none') : ?>
        <?php elseif ($block['block'] === 'CtaBlock') : ?>
          <p class="companies__body">
            ¿Quieres ser aliado del EMMS 2026? ¡Hablemos! <br>
            Escríbenos a <a href="mailto:partners@fromdoppler.com" class="companies__body-link">partners@fromdoppler.com </a> y te contamos cómo sumarte al evento
          </p>
          <a href="/sponsors-promo" class="emms__cta emms__cta--secondary">QUIERO SER ALIADO</a>
        <?php endif; ?>


    </div>

</section>
