<?php
$quotes = [
  [
    'text' => 'Asistí al EMMS y la magnitud de los speakers y de todo el evento me sorprendió muchísimo. Lo que más destaco es el hecho de poder interactuar y hacer preguntas a estos referentes.',
    'name' => 'Andrea',
    'flag' => '/src/img/flag-colombia.png',
    'image' => '/src/img/quotes/quote-andrea.png',
    'country' => 'Colombia',
  ],
  [
    'text' => '¡Ver las conferencias de expertos de todo el mundo y poder hacerlo online es increíble! Cada año me apunto para saber qué conviene aplicar en mi negocio.',
    'name' => 'Sergio',
    'flag' => '/src/img/flag-espana.png',
    'image' => '/src/img/quotes/quote-sergio.png',
    'country' => 'España',
  ],
  [
    'text' => '¡No puedo recomendar este evento lo suficiente! Su contenido gratuito es de una calidad excepcional, superando a muchos eventos pagos.',
    'name' => 'Ricardo',
    'flag' => '/src/img/flag-mexico.png',
    'image' => '/src/img/quotes/quote-ricardo.png',
    'country' => 'México',
  ],
];
?>

<section class="emms__userscomments">
  <div class="emms__container--lg">
    <h2 class="emms__fade-in">Nuestros asistentes dicen:</h2>

    <ul class="emms__userscomments__list emms__userscomments__list--dk emms__fade-in">
      <?php foreach ($quotes as $quote) : ?>
        <li class="emms__userscomments__list__item">
          <div class="emms__userscomments__list__item__content">
            <p class="emms__userscomments__list__item__text">“<?= $quote['text'] ?>”</p>
            <div class="emms__userscomments__list__item__author">
              <img class="emms__userscomments__list__item__author--photo" src="<?= $quote['image'] ?>" alt="<?= $quote['name'] ?>">
              <div class="emms__userscomments__list__item__author--name">
                <p><?= $quote['name'] ?></p>
                <img src="<?= $quote['flag'] ?>" alt="<?= $quote['country'] ?>">
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <ul class="emms__userscomments__list emms__userscomments__list--mb main-carousel" data-flickity>
      <?php foreach ($quotes as $quote) : ?>
        <li class="emms__userscomments__list__item">
          <div class="emms__userscomments__list__item__content">
            <p class="emms__userscomments__list__item__text">“<?= $quote['text'] ?>”</p>
            <div class="emms__userscomments__list__item__author">
              <img class="emms__userscomments__list__item__author--photo" src="<?= $quote['image'] ?>" alt="<?= $quote['name'] ?>">
              <div class="emms__userscomments__list__item__author--name">
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
