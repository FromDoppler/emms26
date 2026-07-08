<?php
$checkoutOrigin = 'checkout';
$checkoutSuccessPath = '/checkout-success';
$checkoutBackPath = '/';
$checkoutPhoneCountry = 'AR';
$checkoutItiFlags1x = '/src/' . VERSION . '/vendor/intl-tel-input/29.1.1/img/flags.webp';
$checkoutItiFlags2x = '/src/' . VERSION . '/vendor/intl-tel-input/29.1.1/img/flags@2x.webp';
if (class_exists('GeoIp') && method_exists('GeoIp', 'getGeoLocalitationCountryNameAndCode')) {
    $checkoutGeoCountry = GeoIp::getGeoLocalitationCountryNameAndCode();
    if (!empty($checkoutGeoCountry['countryCode'])) {
        $checkoutPhoneCountry = $checkoutGeoCountry['countryCode'];
    }
}
?>
<div
    class="emms__checkout emms__checkout__page"
    data-checkout
    data-origin="<?= htmlspecialchars($checkoutOrigin, ENT_QUOTES, 'UTF-8'); ?>"
    data-success-path="<?= htmlspecialchars($checkoutSuccessPath, ENT_QUOTES, 'UTF-8'); ?>"
    data-phone-country="<?= htmlspecialchars($checkoutPhoneCountry, ENT_QUOTES, 'UTF-8'); ?>"
    style="--checkout-iti-path-flags-1x: url('<?= htmlspecialchars($checkoutItiFlags1x, ENT_QUOTES, 'UTF-8'); ?>'); --checkout-iti-path-flags-2x: url('<?= htmlspecialchars($checkoutItiFlags2x, ENT_QUOTES, 'UTF-8'); ?>');"
>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/states/spinner.php'); ?>

    <div class="emms__checkout__container emms__checkout__card__container--form emms__fade-in">
        <div class="emms__checkout__card emms__checkout__card--form">
            <header class="emms__checkout__header" aria-labelledby="checkout-title">
                <span class="emms__checkout__eyebrow">Checkout seguro</span>
                <h1 id="checkout-title">Finalizá tu acceso VIP</h1>
                <p>Completá los pasos para confirmar tu pase al EMMS 2026.</p>
            </header>

            <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/form/stepper.php'); ?>
            <div class="emms__checkout__layout">
                <section class="emms__checkout__steps-column">
                    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/form/customer.php'); ?>
                    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/form/eprotect.php'); ?>
                </section>
                <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/checkout/sidebar/summary.php'); ?>
            </div>
        </div>
        <a href="<?= htmlspecialchars($checkoutBackPath, ENT_QUOTES, 'UTF-8'); ?>" class="emms__checkout__back">← Volver al sitio</a>
    </div>
</div>

<script src="/src/<?= VERSION ?>/vendor/intl-tel-input/29.1.1/js/intlTelInputWithUtils.min.js" defer></script>
<script type="module" src="/src/<?= VERSION ?>/js/checkout/checkout.js"></script>
