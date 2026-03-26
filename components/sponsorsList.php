<!-- Companies list -->
<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
function getSponsorsContent($url, $isPost)
{
  if ($isPost) {
    return [
      'title' => 'Nuestros aliados en el EMMS Digital Trends',
    ];
  }


  $blocks = [
    '/digital-trends' => [
      'title' => 'Nos acompañan en esta edición',
    ],
    '/digital-trends-registrado' => [
      'title' => 'Apoyan el EMMS Digital Trends',
    ],
    '/*' => [
      'title' => 'Nos acompañan en esta edición',
    ],
  ];

  return $blocks[$url] ??  $blocks['/*'];
}

$content = getSponsorsContent($normalizedUrl, $isPost);
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!$db->hasActiveSponsor()) {
  $db->close();
  return;
}

$uploadsPath = './adm25/server/modules/sponsors/uploads/';
$faqLink = $isRegistered ? '/registrado#preguntas-frecuentes' : './#preguntas-frecuentes';

$sponsorTypes = [
  'SPONSOR' => ['title' => 'SPONSORS', 'class' => 'companies-list companies-list--lg'],
  'PREMIUM' => ['title' => 'MEDIA PARTNERS EXCLUSIVE', 'class' => 'companies-list'],
  'STARTER' => ['title' => 'MEDIA PARTNERS STARTERS', 'class' => 'companies-list']
];

$sponsorsByType = [];
foreach ($sponsorTypes as $type => $config) {
  $sponsorsByType[$type] = $db->getSponsorsByType($type);
}
?>

<section class="companies companies--categories" id="aliados">
  <div class="emms__container--lg">
    <h2 class="emms__fade-in"><?= $content['title'] ?></h2>

    <?php foreach ($sponsorTypes as $type => $config): ?>
      <?php if (!empty($sponsorsByType[$type])): ?>
        <h3><?= htmlspecialchars($config['title']) ?></h3>
        <ul class="<?= htmlspecialchars($config['class']) ?> emms__fade-in">
          <?php foreach ($sponsorsByType[$type] as $sponsor): ?>
            <li class="companies-list__item companies-list__item--animated">
              <?php if (!empty($sponsor['link_site'])): ?>
                <a href="<?= htmlspecialchars($sponsor['link_site']) ?>" target="_blank" rel="noopener noreferrer">
                <?php endif; ?>
                <img src="<?= htmlspecialchars($uploadsPath . $sponsor['logo_company']) ?>"
                  alt="<?= htmlspecialchars($sponsor['alt_logo_company']) ?>"
                  loading="lazy">
                <?php if (!empty($sponsor['link_site'])): ?>
                </a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="companies__divisor"></div>
      <?php endif; ?>
    <?php endforeach; ?>

    <!-- FAQ Link -->
    <?php if ($isPost): ?>
      <p class="companies__body">
        ¿Tienes preguntas sobre el EMMS? <a href="<?= htmlspecialchars($faqLink) ?>" class="companies__body-link">Haz clic aquí</a> y
        encuentra las <br>  preguntas más frecuentes sobre el evento.
      </p>
    <?php else: ?>
      <p class="companies__body">
        ¿Quieres ser aliado del EMMS 2025? ¡Hablemos! <br>
        Escríbenos a <a href="mailto:partners@fromdoppler.com" class="companies__body-link">partners@fromdoppler.com </a> y te contamos cómo sumarte al evento
        <a href="/sponsors-promo" class="emms__cta emms__cta--secondary">QUIERO SER ALIADO</a>
      </p>
    <?php endif; ?>
  </div>

</section>

<script>
  const sponsorObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const section = entry.target;
        const items = section.querySelectorAll('.companies-list__item--animated');

        items.forEach((item, index) => {
          item.style.transitionDelay = `${index * 80}ms`;
          item.classList.add('visible');
        });

        sponsorObserver.unobserve(section);
      }
    });
  }, {
    rootMargin: '100px 0px -20px 0px',
    threshold: 0.1
  });

  document.querySelectorAll('.companies-list').forEach(list => {
    sponsorObserver.observe(list);
  });
</script>

<?php
$db->close();
?>
