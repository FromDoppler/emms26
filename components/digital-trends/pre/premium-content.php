<?php
if (!function_exists('getContentForUrl')) {
  function getContentForUrl($url)
  {
    $resourceList = [
      'Contenidos descargables',
      'Conferencias on-demand',
      'Herramientas que potencian tu negocio',
    ];

    $contentMap = [
      'default' => [
        'heading' => 'Accede a la Biblioteca de Recursos <br> ¡Es gratis y súper completa!',
        'body' => null,
        'list' => $resourceList,
      ],
      'group1' => [
        'heading' => 'Accede a la Biblioteca de Recursos <br> ¡Es gratis y súper completa!',
        'body' => null,
        'list' => $resourceList,
      ],
      'group2' => [
        'heading' => 'Accede a la Biblioteca de Recursos <br> ¡Es gratis y súper completa!',
        'body' => null,
        'list' => $resourceList,
      ],
      'with-list' => [
        'heading' => 'Accede a la Biblioteca de Recursos <br> ¡Es gratis y súper completa!',
        'body' => null,
        'list' => $resourceList,
      ],
    ];

    $urlToGroupMap = [
      '/' => 'group1',
      '/registrado' => 'group1',
      '/digital-trends' => 'group2',
      '/digital-trends-registrado' => 'group2',
    ];


    $group = $urlToGroupMap[$url] ?? 'default';

    return $contentMap[$group];
  }
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$content = getContentForUrl($normalizedUrl);
$isDigitalTrends = in_array($normalizedUrl, [
  '/digital-trends',
  '/digital-trends-registrado'
]);

?>
<section class="premium-content">
  <div class="emms__container--lg">
    <div class="premium-content__picture emms__fade-in">
      <img src="/src/img/biblioteca-recursos-2.png" alt="Biblioteca de recursos">
    </div>
    <div class="premium-content__text emms__fade-in">
      <h2><?php echo ($content['heading']); ?></h2>
      <?php if (!empty($content['body'])) { ?>
        <p><?php echo ($content['body']); ?></p>
      <?php } ?>

      <?php if (!empty($content['list'])) { ?>
        <ul class="premium-content__list emms__fade-in">
          <?php foreach ($content['list'] as $item) { ?>
            <li>
              <img src="/src/img/icons/icon-check--strong-purple.svg" alt="Check">
              <span><?php echo ($item); ?></span>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>

      <a href="<?= $isRegistered ?  '/sponsors-registrado' : '/sponsors' ?>" class="emms__cta sm emms__cta--secondary emms__fade-in">ACCEDE AHORA</a>
    </div>
  </div>
</section>
