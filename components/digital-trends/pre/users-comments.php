<?php
$quotes = [
  [
    'name' => 'Martín',
    'text' => '“Asistir al EMMS me ayudó a tomar decisiones más estratégicas en mi trabajo. Las conferencias están enfocadas en casos reales y tendencias actuales. ¡Esto sin dudas marca la diferencia!”',
    'image' => '/src/img/quotes/quote-martin.png',
    'flag' => '/src/img/flag-mexico.png',
    'country' => 'México',
  ],
  [
    'name' => 'Camila',
    'text' => '“Lo mejor del evento es la calidad de los contenidos y la posibilidad de acceder a ellos en todo momento y desde cualquier lugar. ¡Es una oportunidad única para seguir aprendiendo sin barreras!”',
    'image' => '/src/img/quotes/quote-camila.png',
    'flag' => '/src/img/flags/CL.png',
    'country' => 'Chile',
  ],
  [
    'name' => 'Luis',
    'text' => '“Participar del EMMS fue como hacer una mini certificación intensiva. Aprendí, tomé notas, conecté con otros profesionales y me fui con muchas ideas para aplicar en mi negocio”',
    'image' => '/src/img/quotes/quote-luis.png',
    'flag' => '/src/img/flags/PE.png',
    'country' => 'Perú',
  ]
];

$quotesDT = [
  [
    'name' => 'Andrea',
    'text' => '“El EMMS fue una experiencia transformadora y muy enriquecedora.  No solo aprendí estrategias que pude implementar al instante, sino que también hice alianzas con otros colegas de toda la región. ¡Totalmente recomendado!”.',
    'image' => '/src/img/quotes/quote-andrea.png',
    'flag' => '/src/img/flags/CO.png',
    'country' => 'Colombia',
  ],
  [
    'name' => 'Beatriz',
    'text' => '“Nunca imaginé que un evento digital me proporcionaría tanto valor.  Los workshops prácticos fueron claves para mejorar nuestras campañas. ¡Definitivamente, un must para cualquier profesional del marketing!”.',
    'image' => '/src/img/quotes/quote-beatriz.png',
    'flag' => '/src/img/flags/ES.png',
    'country' => 'España',
  ],
  [
    'name' => 'Juan Pablo',
    'text' => '“El EMMS me brindó las herramientas exactas que necesitaba para optimizar mi estrategia de marketing. Los casos de éxito compartidos fueron inspiradores. Ahora mi negocio está escalando como nunca”.',
    'image' => '/src/img/quotes/quote-juan.png',
    'flag' => '/src/img/flag-mexico.png',
    'country' => 'México',
  ]
];
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();


$usersCommentsConfig = [
  '/digital-trends' => [
    'title' => 'Esto opinan quienes <br> ya fueron parte del EMMS:',
    'class' => 'userscomments',
    'quotes' => $quotes,
  ],
  '/digital-trends' => [
    'title' => 'Nuestros asistentes dicen:',
    'class' => 'userscomments userscomments--digitaltrends',
    'quotes' =>  $quotesDT,
  ],
  '/digital-trends-registrado' => [
    'title' => 'Nuestros asistentes dicen:',
    'class' => 'userscomments userscomments--digitaltrends',
    'quotes' => $quotesDT,
  ],
  '/*' => [
    'title' => 'Esto opinan quienes <br> ya fueron parte del EMMS:',
    'class' => 'userscomments',
    'quotes' => $quotes,
  ]
];

$config = $usersCommentsConfig[$normalizedUrl] ?? $usersCommentsConfig['/*'];
?>

<section class="<?= $config['class'] ?>">
  <div class="emms__container--lg">
    <h2 class="emms__fade-in"><?= $config['title'] ?></h2>

    <ul class="userscomments__list userscomments__list--dk emms__fade-in">
      <?php foreach  ($config['quotes'] as $quote):  ?>
        <li class="userscomments__list-item">
          <div class="userscomments__list-item__content">
            <p class="userscomments__list-item__text"><?= $quote['text'] ?></p>
            <div class="userscomments__list-item__author">
              <img class="userscomments__list-item__author--photo" src="<?= $quote['image'] ?>" alt="<?= $quote['name'] ?>">
              <div class="userscomments__list-item__author--name">
                <p><?= $quote['name'] ?></p>
                <img src="<?= $quote['flag'] ?>" alt="<?= $quote['country'] ?>">
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <ul class="userscomments__list userscomments__list--mb main-carousel" data-flickity>
      <?php  foreach  ($config['quotes'] as $quote):  ?>
        <li class="userscomments__list-item">
          <div class="userscomments__list-item__content">
            <p class="userscomments__list-item__text"><?= $quote['text'] ?></p>
            <div class="userscomments__list-item__author">
              <img class="userscomments__list-item__author--photo" src="<?= $quote['image'] ?>" alt="<?= $quote['name'] ?>">
              <div class="userscomments__list-item__author--name">
                <p><?= $quote['name'] ?></p>
                <img src="<?= $quote['flag'] ?>" alt="<?= $quote['country'] ?>">
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php
$structuredQuotes = [
  '@context' => 'https://schema.org',
  '@graph' => [],
];

foreach ($quotes as $quote) {
  $structuredQuotes['@graph'][] = [
    '@type' => 'Quotation',
    'text' => $quote['text'],
    'spokenByCharacter' => [
      '@type' => 'Person',
      'name' => $quote['name'],
      'nationality' => $quote['country'],
    ]
  ];
}
?>

<script type="application/ld+json">
  <?= json_encode($structuredQuotes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
