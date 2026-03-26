<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
function getScheduleBlock($url)
{
    $blocks = [
        '/ecommerce' => [
            'block' => 'dt',
        ],
        '/ecommerce-registrado' => [
            'block' => 'dt-registrado',
        ],
        '/*' => [
            'block' => 'digital-trend',
        ],
    ];

    return $blocks[$url] ?? $blocks['/*'];
}
$block = getScheduleBlock($normalizedUrl);
?>

<section class="emms__calendar" id="agenda">
    <div class="emms__container--lg">
        <div class="emms__calendar__title emms__fade-in">
            <h2>AGENDA EMMS E-COMMERCE 2025</h2>
            <p>Descubre las Conferencias de speakers internacionales, los Workshops y los espacios de Networking diseñados para que conectes con especialistas de la industria
            </p>
        </div>
        <?php include('speakers.php') ?>

        <?php if ($block['block'] === 'dt') : ?>
            <div class="emms__calendar__bottom emms__fade-in  eventHiddenElements">
                <a href="#registro" class="emms__cta">REGÍSTRATE GRATIS</a>
            </div>
            <div class="emms__calendar__bottom  eventShowElements">
                <a href="#registro" class="emms__cta alreadyRegisterForm"><span class="button__text">REGÍSTRATE GRATIS</span></a>
            </div>
        <?php elseif ($block['block'] === 'dt-registrado') : ?>
            <div class="emms__calendar__bottom emms__fade-in hidden--vip">
                <a href="./checkout" class="emms__cta">COMPRA TU ENTRADA VIP</a>
            </div>
        <?php endif; ?>
    </div>
</section>
