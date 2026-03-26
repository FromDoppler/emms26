<?php
$contentsPre = [
  '/' => [
    'helloBarText' => '⏳ ¡Cuenta regresiva para el EMMS Digital Trends! Del 28 al 30 de octubre: conferencias, workshops y beneficios especiales.',
    'helloBarCtaTxt' => 'REGÍSTRATE GRATIS',
    'helloBarCtaLink' => '/digital-trends',
  ],
  '/registrado' => [
    'helloBarText' => '⏳ ¡Cuenta regresiva para el EMMS Digital Trends! Del 28 al 30 de octubre: conferencias, workshops y beneficios especiales.',
    'helloBarCtaTxt' => 'REGÍSTRATE GRATIS',
    'helloBarCtaLink' => '/digital-trends-registrado',
  ],
  '/digital-trends' => [
    'helloBarText' => 'Regístrate ahora y obtén tu entrada VIP  de regalo',
    'helloBarCtaTxt' => 'Reserva tu lugar',
    'helloBarCtaLink' => '#registro',
  ],
  '/digital-trends-registrado' => [
    'helloBarText' => '¡Accede a tu entrada VIP por solo 9.99 USD! Conferencias, Workshops y beneficios especiales.',
    'helloBarCtaTxt' => 'COMPRA TU ENTRADA',
    'helloBarCtaLink' => '#entradas',
  ],
  '/sponsors' => [
    'helloBarText' => 'Regístrate ahora y obtén tu entrada VIP  de regalo',
    'helloBarCtaTxt' => 'Reserva tu lugar',
    'helloBarCtaLink' => '#registro',
  ],
  '/*' => [
    'helloBarText' => '⏳ ¡Cuenta regresiva para el EMMS Digital Trends! Del 28 al 30 de octubre: conferencias, workshops y beneficios especiales.',
    'helloBarCtaTxt' => 'REGÍSTRATE GRATIS',
    'helloBarCtaLink' => '/digital-trends',
  ],
];
$contentsLive = [
  '/' => [
    'helloBarText' => '🚨EMMS Digital Trends: ¡ya estamos en vivo! 🚨 Conferencias gratuitas, Workshops, Entrevistas, ¡y mucho más!',
    'helloBarCtaTxt' => 'SÚMATE GRATIS',
    'helloBarCtaLink' => '/digital-trends',
  ],
  '/registrado' => [
    'helloBarText' => '🎆¡Llegó el EMMS Digital Trends!🎆 Súmate al vivo ahora',
    'helloBarCtaTxt' => ' MIRA LA TRANSMISIÓN',
    'helloBarCtaLink' => '/digital-trends-registrado',
  ],
  '/digital-trends' => [
    'helloBarText' => '📢 ¡Ya estamos en vivo! 📢 ¿Aún no te has registrado? Súmate gratis.',
    'helloBarCtaTxt' => 'ÚNETE AHORA',
    'helloBarCtaLink' => '#registro',
  ],
  '/digital-trends-registrado' => [
    'helloBarText' => '🎫¡Quedan pocas! Compra tu entrada VIP y accede a beneficios exclusivos.',
    'helloBarCtaTxt' => 'HAZTE VIP',
    'helloBarCtaLink' => '#entradas',
  ],
  '/sponsors-registrado' => [
    'helloBarText' => '¡Aprovecha 25% OFF en la compra de entradas VIP por tiempo limitado !',
    'helloBarCtaTxt' => 'adquiere tu entrada vip',
    'helloBarCtaLink' => '/checkout',
  ],
  '/*' => [
    'helloBarText' => '🚨EMMS Digital Trends: ¡ya estamos en vivo! 🚨 Conferencias gratuitas, Workshops, Entrevistas, ¡y mucho más!',
    'helloBarCtaTxt' => 'SÚMATE GRATIS',
    'helloBarCtaLink' => '/digital-trends',
  ],
];
$contentsDuring = [
  '/' => [
    'helloBarText' => '¡Comenzó el EMMS Digital Trends 2025! Conferencias gratuitas, Workshops, ¡y mucho más!',
    'helloBarCtaTxt' => 'REGÍSTRATE AHORA',
    'helloBarCtaLink' => '/digital-trends',
  ],
  '/registrado' => [
    'helloBarText' => '¡Comenzó el EMMS Digital Trends 2025! Conferencias gratuitas, Workshops, ¡y mucho más!',
    'helloBarCtaTxt' => 'SÚMATE AHORA',
    'helloBarCtaLink' => '/digital-trends-registrado',
  ],
  '/digital-trends' => [
    'helloBarText' => '¡Queda más EMMS Digital Trends! ¿Aún no te has registrado?',
    'helloBarCtaTxt' => 'Reserva tu lugar',
    'helloBarCtaLink' => '#registro',
  ],
  '/digital-trends-registrado' => [
    'helloBarText' => '🎫¡Quedan pocas! Compra tu entrada VIP y accede a beneficios exclusivos.',
    'helloBarCtaTxt' => 'HAZTE VIP',
    'helloBarCtaLink' => '#entradas',
  ],
  '/sponsors' => [
    'helloBarText' => '¡Queda más EMMS Digital Trends! ¿Aún no te has registrado?',
    'helloBarCtaTxt' => 'Reserva tu lugar',
    'helloBarCtaLink' => '#registro',
  ],
  '/sponsors-registrado' => [
    'helloBarText' => '¡Aprovecha 25% OFF en la compra de entradas VIP por tiempo limitado !',
    'helloBarCtaTxt' => 'adquiere tu entrada vip',
    'helloBarCtaLink' => '/checkout',
  ],
  '/*' => [
    'helloBarText' => '¡Comenzó el EMMS Digital Trends 2025! Conferencias gratuitas, Workshops, ¡y mucho más!',
    'helloBarCtaTxt' => 'REGÍSTRATE AHORA',
    'helloBarCtaLink' => '/digital-trends',
  ],
];

include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();
$contentPre = $contentsPre[$normalizedUrl] ?? $contentsPre['/*'];
$contentLive = $contentsLive[$normalizedUrl] ?? $contentsLive['/*'];
$contentDuring = $contentsDuring[$normalizedUrl] ?? $contentsDuring['/*'];





?>

<?php if ($digitalTrendsStates['isPre']) : ?>
  <div class="hellobar hellobar--pre">
    <div class="hellobar__container  emms__fade-in">
      <p><strong><?= $contentPre['helloBarText'] ?></strong><a href="<?= $contentPre['helloBarCtaLink'] ?>"><?= $contentPre['helloBarCtaTxt'] ?></a></p>
    </div>
  </div>
<?php elseif ($digitalTrendsStates['isLive']) : ?>
  <div class="hidden--vip">
    <div class="hellobar">
      <div class="hellobar__container hellobar__container--during emms__fade-in">
        <p><strong><?= $contentLive['helloBarText'] ?></strong><a href="<?= $contentLive['helloBarCtaLink'] ?>"><?= $contentLive['helloBarCtaTxt'] ?></a></p>
      </div>
    </div>
  </div>
  <div class="show--vip">
    <div class="hellobar">
      <div class="hellobar__container hellobar__container--during emms__fade-in">
        <p><strong>⭐ ¡No te pierdas los workshops! Busca los links en la agenda para unirte a las salas.</strong><a href="#agenda">MIRA LA AGENDA</a></p>
      </div>
    </div>
  </div>
<?php elseif ($digitalTrendsStates['isDuring']) : ?>
  <div class="hidden--vip">
    <div class="hellobar">
      <div class="hellobar__container hellobar__container--during emms__fade-in">
        <p><strong><?= $contentDuring['helloBarText'] ?></strong><a href="<?= $contentDuring['helloBarCtaLink'] ?>"><?= $contentDuring['helloBarCtaTxt'] ?></a></p>
      </div>
    </div>
  </div>
  <div class="show--vip">
    <div class="hellobar">
      <div class="hellobar__container hellobar__container--during emms__fade-in">
        <p><strong>⭐ ¡No te pierdas los workshops! Busca los links en la agenda para unirte a las salas.</strong><a href="#agenda">MIRA LA AGENDA</a></p>
      </div>
    </div>
  </div>
<?php endif; ?>
