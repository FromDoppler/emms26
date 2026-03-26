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
    '/digital-trends' => [
      'block' => 'digital-trends',
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
  $primarySpeaker = 'Federico Muñoz Villavicencio';
  $secondarySpeaker = 'Vedant Misra';

  $initialIndexClass = ''; // class selector for Flickity
  foreach ($speakers as $speaker) {
    if ($speaker['name'] === $primarySpeaker) {
      $initialIndexClass = $primarySpeaker;
      break;
    }
  }

  if (!$initialIndexClass) {
    foreach ($speakers as $speaker) {
      if ($speaker['name'] === $secondarySpeaker) {
        $initialIndexClass = $secondarySpeaker;
        break;
      }
    }
  }

?>
  <div class="speakerslist emms__fade-in ">
    <ul
      class="main-carousel"
      data-flickity='{ "initialIndex": ".is-initial-select", "wrapAround": "true" }'
      id="<?= htmlspecialchars($carouselId) ?>">
      <?php foreach ($speakers as $speaker): ?>
        <?php
        $isInitial =
          $speaker['name'] === $primarySpeaker ||
          ($initialIndexClass === $secondarySpeaker && $speaker['name'] === $secondarySpeaker);
        ?>
        <li class="speakerslist__item <?= $isInitial ? 'is-initial-select' : '' ?>">
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


<section class="speakers emms__bg-section-10">
  <div class="emms__container--lg">
    <div class="spealers__header">
      <?php if ($block['block'] === 'home') : ?>
        <h2 class="emms__fade-in">Speakers de primer nivel que marcaron tendencia en ediciones anteriores:</h2>
      <?php elseif ($block['block'] === 'registerHome') : ?>
        <h2 class="emms__fade-in">Speakers de primer nivel que marcaron tendencia en ediciones anteriores:</h2>
      <?php elseif ($block['block'] === 'digital-trends') : ?>
        <h2 class="emms__fade-in speakers__title">Speakers que brillaron en el EMMS</h2>
      <?php endif; ?>
    </div>

    <?php if ($block['block'] === 'home' || $block['block'] === 'registerHome') :
      renderSpeakersList($speakersHome, "carousel-1");
    elseif ($block['block'] === 'digital-trends') :
      renderSpeakersList($speakersEcommerce, "carousel-2");
    endif; ?>

    <?php if ($block['block'] === 'home' || $block['block'] === 'registerHome') : ?>
      <p class="emms__fade-in speakers__sub-title">Pronto conocerás la agenda del EMMS E-commerce 2026</p>
      <p class="emms__fade-in">Mantente pendiente a tu Email para descubrir a los Speakers que nos acompañarán <br>
en el próximo evento. Mientras tanto, disfruta de las mejores
Conferencias de ediciones pasadas.
</p>
      <a href="/digital-trends" class="emms__cta emms__cta--md  emms__fade-in">REVIVE EDICIONES ANTERIORES</a>
    <?php elseif ($block['block'] === 'digital-trends') : ?>
      <p class="emms__fade-in speakers__sub-title">¡Muy pronto conocerás la agenda 2025! Regístrate gratis y descúbrela antes que nadie.
      </p>
      <a href="#registro" class="emms__cta emms__cta--md emms__fade-in">REGÍSTRATE GRATIS</a>
    <?php endif; ?>

  </div>
</section>
