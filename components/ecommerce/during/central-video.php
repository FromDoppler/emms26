<?php

$contents = [
    '/' => [
        'heading' => 'Súmate ahora al EMMS E-commerce',
        'body' => 'Descubre en este video por qué el EMMS E-commerce es el evento clave para aprender a escalar tu tienda, de la mano de los líderes del sector.',
        'button' => 'ACCEDE AL VIVO',
        'link' => 'ecommerce#registro',
        'youtubeCode' => 'ZGS-lmiXHFE',
    ],
    '/ecommerce' => [
        'heading' => 'Súmate ahora al EMMS E-commerce',
        'body' => 'Descubre en este video por qué el EMMS E-commerce es el evento clave para aprender a escalar tu tienda, de la mano de los líderes del sector.',
        'button' => 'REGÍSTRATE AHORA',
        'link' => '#registro',
        'youtubeCode' => 'T8Crntnfgak',
    ],
    '/ecommerce-registrado' => [
        'heading' => 'Inspírate en el evento más grande de E-commerce',
        'body' => 'Descubre en este video por qué el EMMS E-commerce es el evento clave para aprender a escalar tu tienda, de la mano de los líderes del sector.',
        'youtubeCode' => 'T8Crntnfgak',
    ],
    '/*' => [
        'heading' => 'Súmate ahora al EMMS E-commerce',
        'body' => 'Descubre en este video por qué el EMMS E-commerce es el evento clave para aprender a escalar tu tienda, de la mano de los líderes del sector.',
        'button' => 'ACCEDE AL VIVO',
        'link' => 'ecommerce#registro',
        'youtubeCode' => 'ZGS-lmiXHFE',
    ],
];

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = $contents[$normalizedUrl] ?? $contents['/*'];

$youtubeBaseUrl = "https://www.youtube.com/embed/";
$youtubeParams = "?controls=0&modestbranding=1&rel=0&fs=0&disablekb=1&autoplay=0&loop=1";
$videoUrl = $youtubeBaseUrl . $content['youtubeCode'] . $youtubeParams;

?>

<section class="centralvideo">
    <div class="emms__container--lg emms__container--lg--column">
        <?php if (!empty($content['heading']) || !empty($content['body'])): ?>
            <div class="centralvideo__title emms__fade-in">
                <?php if (!empty($content['heading'])): ?>
                    <h2><?php echo $content['heading']; ?></h2>
                <?php endif; ?>
                <?php if (!empty($content['body'])): ?>
                    <p><?php echo $content['body']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="centralvideo__video emms__fade-in">
            <iframe width="560" height="315" src="<?php echo $videoUrl ?>" frameborder="0" allowfullscreen></iframe>
        </div>

        <?php if (!empty($content['link']) || !empty($content['button'])): ?>
            <a href="<?php echo $content['link']; ?>" class="emms__cta">
                <?php echo !empty($content['button']) ? $content['button'] : ''; ?>
            </a>
        <?php endif; ?>
    </div>
</section>
