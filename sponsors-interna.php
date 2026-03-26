
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/app-config.php');

function getSponsorBySlug($slug) {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sponsors = $db->getSponsorsBySlug($slug);
    return !empty($sponsors) ? $sponsors[0] : null;
}

$sponsor = isset($_GET['slug']) ? getSponsorBySlug($_GET['slug']) : null;

if (!$sponsor) {
    header('Location: ' . 'sponsors');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>


    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/ecommerce/pre/ecommerce/head.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="emms__internal-sponsors">
    <?php if (PRODUCTION) include $_SERVER['DOCUMENT_ROOT'] . '/components/gtm.php'; ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/date-counter.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/navbar-reg.php') ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php') ?>
    <main>
        <?php if (!empty($sponsor['youtube'])): ?>
            <section class="emms__internal-sponsors__hero emms__bg-section-2">
                <div class="emms__container--lg emms__fade-top">
                    <div class="emms__internal-sponsors__hero__content">
                        <h1><?= htmlspecialchars($sponsor['title']) ?></h1>
                        <p><?= htmlspecialchars($sponsor['description']) ?></p>
                    </div>
                    <div class="emms__internal-sponsors__hero__video">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($sponsor['youtube']) ?>"></iframe>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Resource Section -->
        <section class="emms__internal-sponsors__resource <?= empty($sponsor['youtube']) ? 'mt' : '' ?>">
            <div class="emms__container--md emms__fade-in">
                <div class="emms__internal-sponsors__resource__picture">
                    <img src="<?= empty($sponsor['youtube']) ? '/src/img/download--locked-24.png' : '/src/img/sponsor-asset.png' ?>" alt="download">
                </div>
                <div class="emms__internal-sponsors__resource__text">
                    <h2><?= htmlspecialchars($sponsor['title_magnet']) ?></h2>
                    <p><?= htmlspecialchars($sponsor['description_magnet']) ?></p>
                    <a href="<?= htmlspecialchars($sponsor['link_magnet']) ?>" class="emms__cta" target="_blank">ACCEDE</a>
                </div>
            </div>
        </section>

        <!-- Description Section -->
        <section class="emms__internal-sponsors__description">
            <div class="emms__container--md emms__fade-in">
                <h2>Conoce más sobre <?= htmlspecialchars($sponsor['title_promo_company']) ?></h2>
                <p><?= htmlspecialchars($sponsor['description_promo_company']) ?></p>
                <a href="<?= htmlspecialchars($sponsor['link_promo_company']) ?>" class="emms__cta" target="_blank">CONOCE MÁS</a>
            </div>
        </section>
    </main>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>
</body>

</html>
