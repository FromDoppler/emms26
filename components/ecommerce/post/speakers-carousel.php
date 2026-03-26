<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
function getBlock($url)
{
    $blocks = [
        '/' => [
            'block' => 'home',
        ],
        '/registrado' => [
            'block' => 'registerHome',
        ],
        '/ecommerce' => [
            'block' => 'ecommerce',
        ],
        '/*' => [
            'block' => 'home',
        ],
    ];

    return $blocks[$url] ?? $blocks['/*'];
}
$block = getBlock($normalizedUrl);

function renderSpeakersList($speakers, $carouselId = "carousel-default")
{
?>
    <div class="speakerslist emms__fade-in">
        <ul class="main-carousel" id="<?= htmlspecialchars($carouselId) ?>" data-flickity='{ "initialIndex": ".is-initial-select", "wrapAround": "true" }'>
            <?php foreach ($speakers as $index => $speaker): ?>
                <li class="speakerslist__item <?= $index === 0 ? 'is-initial-select' : '' ?>">
                    <div class="speakerslist__item__content">
                        <img src="<?= htmlspecialchars($speaker['photo']) ?>" alt="<?= htmlspecialchars($speaker['name']) ?>" class="speakerslist__item__photo">
                        <p><?= htmlspecialchars($speaker['name']) ?><span><?= htmlspecialchars($speaker['title']) ?></span></p>
                        <img src="<?= htmlspecialchars($speaker['logo']) ?>" alt="<?= htmlspecialchars($speaker['company']) ?>" class="speakerslist__item__logo">
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php
}
$speakersHome = [
    [
        'name' => 'Neil Patel',
        'title' => 'Co-Founder Neil Patel Digital',
        'photo' => '/src/img/people--gradient/neil.png',
        'logo' => '/src/img/logos--white/logo-neil.png',
        'company' => 'Neil Patel Digital'
    ],
    [
        'name' => 'Fernando D’Acunto',
        'title' => 'Sr. Product Marketing Manager',
        'photo' => '/src/img/people--gradient/facundo-dacunto.png',
        'logo' => '/src/img/logos--white/logo-youtube.png',
        'company' => 'YouTube'
    ],
    [
        'name' => 'Tim Ash',
        'title' => 'Founder',
        'photo' => '/src/img/people--gradient/tim-ash.png',
        'logo' => '/src/img/logos--white/logo-tim-ash.png',
        'company' => 'Tim Ash'
    ],
    [
        'name' => 'Julia Rayeb',
        'title' => 'Directora Creativa de Marketing de negocios para Latinoamérica en Meta',
        'photo' => '/src/img/people--gradient/julia-rayeb.png',
        'logo' => '/src/img/logos--white/logo-meta.png',
        'company' => 'Meta'
    ],
    [
        'name' => 'Vedant Misra',
        'title' => 'Investigador de IA',
        'photo' => '/src/img/people--gradient/vedant-misra.png',
        'logo' => '/src/img/logos--white/logo-vedant.png',
        'company' => 'Vedant Misra'
    ],
    [
        'name' => 'Vilma Nuñez',
        'title' => 'Founder',
        'photo' => '/src/img/people--gradient/vilma.png',
        'logo' => '/src/img/logos--white/logo-vilma.png',
        'company' => 'Vilma Nuñez'
    ],
    [
        'name' => 'Marcos Pueryrredón',
        'title' => 'Co-Founder & Global Executive SVP at VTEX',
        'photo' => '/src/img/people--gradient/marcos.png',
        'logo' => '/src/img/logos--white/logo-vtex.png',
        'company' => 'VTEX'
    ],
    [
        'name' => 'Diego Dagnino',
        'title' => 'Country Community Manager',
        'photo' => '/src/img/people--gradient/diego-dagnino.png',
        'logo' => '/src/img/logos--white/logo-canva.png',
        'company' => 'Canva'
    ],
    [
        'name' => 'Diana Ramirez',
        'title' => 'Head of Latam Spotify Advertising',
        'photo' => '/src/img/people--gradient/diana.png',
        'logo' => '/src/img/logos--white/logo-spotify.png',
        'company' => 'Spotify'
    ],
    [
        'name' => 'Juan Lombana',
        'title' => 'Founder',
        'photo' => '/src/img/people--gradient/juan-lombana.png',
        'logo' => '/src/img/logos--white/logo-mercatitlan.png',
        'company' => 'Mercatitlán'
    ],
];
$speakersEcommerce = [
    [
        'name' => 'Federico Muñoz Villavicencio',
        'title' => 'Client Solutions Manager en Meta',
        'photo' => '/src/img/people--gradient/federico-munoz.png',
        'logo' => '/src/img/logos--white/logo-meta.png',
        'company' => 'Meta'
    ],
    [
        'name' => 'Manuel García Cuerva',
        'title' => 'Head Global Profit Pools en VTEX',
        'photo' => '/src/img/people--gradient/manuel-garcia-cuerva.png',
        'logo' => '/src/img/logos--white/logo-vtex.png',
        'company' => 'Vtex'
    ],
    [
        'name' => 'Ana Laura Fleba',
        'title' => 'Digital Selling & Commerce Director en Unilever',
        'photo' => '/src/img/people--gradient/ana-laura-fleba.png',
        'logo' => '/src/img/logos--white/logo-unilever.png',
        'company' => 'Uniliver'
    ],
    [
        'name' => 'Alina Pineda',
        'title' => 'Community Manager Latam en Canva',
        'photo' => '/src/img/people--gradient/alina-pineda.png',
        'logo' => '/src/img/logos--white/logo-canva.png',
        'company' => 'Canva'
    ],
    [
        'name' => 'Alicia Macías Hernández',
        'title' => 'Fundadora de eCommerce efectivo',
        'photo' => '/src/img/people--gradient/alicia.png',
        'logo' => '/src/img/logos--white/logo-ecommerce-efectivo.png',
        'company' => 'eCommerce efectivo'
    ],
    [
        'name' => 'Ricardo Tayar',
        'title' => 'CEO y Fundador de Flat 101',
        'photo' => '/src/img/people--gradient/ricardo-tayer.png',
        'logo' => '/src/img/logos--white/logo-flat-101.png',
        'company' => 'Flat 101'
    ],
    [
        'name' => 'Ana Victoria Odonel',
        'title' => 'Digital Planner en Tramontina México',
        'photo' => '/src/img/people--gradient/ana-victoria-odonel.png',
        'logo' => '/src/img/logos--white/logo-tramontina.png',
        'company' => 'Tramontina'
    ],
    [
        'name' => 'Ana Ivars',
        'title' => 'Founder & CEO en Dinamiza Digital',
        'photo' => '/src/img/people--gradient/ana-ivars.png',
        'logo' => '/src/img/logos--white/logo-ana-ivars.png',
        'company' => 'Ana Ivars'
    ],
    [
        'name' => 'Marcos Westphalen',
        'title' => 'Director en Google',
        'photo' => '/src/img/people--gradient/marcos-westphalen.png',
        'logo' => '/src/img/logos--white/logo-google.png',
        'company' => 'Google'
    ]
];

?>


<section class="speakers emms__bg-section-3">
    <div class="emms__container--lg">
        <?php if ($block['block'] === 'home') : ?>
            <h2 class="emms__fade-in">Algunos de los conferencistas que nos han acompañado en las últimas ediciones:</h2>
        <?php elseif ($block['block'] === 'registerHome') : ?>
            <h2 class="emms__fade-in">Algunos de los conferencistas que nos han acompañado en las últimas ediciones:</h2>
        <?php elseif ($block['block'] === 'ecommerce') : ?>
            <h2 class="emms__fade-in speakers__title">Speakers destacados en ediciones anteriores</h2>
            <P class="emms__fade-in speakers__sub-title">Aprende de referentes globales que han marcado la diferencia en empresas líderes como Google, Meta, Unilever y más.</P>
        <?php endif; ?>

        <?php if ($block['block'] === 'home' || $block['block'] === 'registerHome') :
            renderSpeakersList($speakersHome, "carousel-1");
        elseif ($block['block'] === 'ecommerce') :
            renderSpeakersList($speakersEcommerce, "carousel-2");
        endif; ?>

        <?php if ($block['block'] === 'home') : ?>
            <p class="emms__fade-in speakers__sub-title">Pronto conocerás a los Speakers del EMMS Digital Trends 2025
            </p>
            <p class="speakers__body ">Las marcas con más trayectoria en la industria y los especialistas más reconocidos están preparándose
                para ser parte del evento en que aprenderás las últimas tendencias y estrategias sobre el Marketing Digital.
                Mantente pendiente a tu correo electrónico para descubrir quiénes nos acompañarán en la próxima edición y, mientras tanto, recuerda las mejores charlas de ediciones pasadas..  
            </p>
            <a href="/ecommerce" class="emms__cta emms__fade-in">REVIVE EDICIONES ANTERIORES</a>
        <?php elseif ($block['block'] === 'registerHome') : ?>
            <p class="emms__fade-in  speakers__sub-title">Pronto conocerás a los Speakers del EMMS Digital Trends 2025
            </p>
            <p class="emms__fade-in  speakers__body">Las marcas que son tendencia en la industria y los especialistas más reconocidos están preparándose
                para ser parte del mayor evento hispano de Marketing Digital. Mantente pendiente a tu correo electrónico
                para descubrir quiénes nos acompañarán en esta edición.
            </p>
        <?php elseif ($block['block'] === 'ecommerce') : ?>
            <p class="emms__fade-in speakers__sub-title">✨ ¡Pronto revelaremos los speakers 2025! Regístrate gratis y descúbrelos antes que nadie.
            </p>
            <a href="#registro" class="emms__cta emms__fade-in">REGÍSTRATE GRATIS</a>
        <?php endif; ?>

    </div>
</section>
