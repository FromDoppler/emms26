<?php
define('REGISTERFORM_URL', '#registro');
define('CHECKOUT_URL', '/checkout');

/**
 * Retorna toda la configuración de botones por tipo y fase,
 * incluyendo la diferenciación CSS para usuarios VIP (show--vip / hidden--vip)
 */
function getButtonConfig(): array
{
  static $config = null;
  if ($config !== null) return $config;

  $base = [
    'pre' => [
      'registered' => null,
      'guest' => ['text' => 'REGÍSTRATE GRATIS', 'href' => REGISTERFORM_URL],
    ],
    'during' => [
      'registered' => ['text' => 'ACCEDE AHORA', 'href' => 'speaker-interna?slug={slug}'],
      'guest' => ['text' => 'REGÍSTRATE GRATIS', 'href' => REGISTERFORM_URL],
    ],
    'transition' => [
      'registered' => null,
      'guest' => ['text' => 'REGÍSTRATE GRATIS', 'href' => REGISTERFORM_URL],
    ],
    'post' => [
      'registered' => ['text' => 'ACCEDE A LA CONFERENCIA', 'href' => 'speaker-interna?slug={slug}'],
      'guest' => ['text' => 'REGÍSTRATE GRATIS', 'href' => REGISTERFORM_URL],
    ],
  ];

  $conference = $base;

  $successStory = array_replace_recursive($base, [
    'post' => [
      'registered' => ['text' => 'VER HISTORIA', 'href' => '{youtube}']
    ]
  ]);

  // === WORKSHOPS ===
  //  Los workshops tienen dos "sub-estados" dentro de registered:
  //  1️ Botón visible para NO VIP (hidden--vip): invita a comprar (checkout)
  //  2️ Botón visible para VIP (show--vip): acceso o visualización de video
  $workshop = [
    'pre' => [
      'registered' => [
        // No VIP → se muestra con hidden--vip
        ['class' => 'hidden--vip', 'text' => 'HAZTE VIP', 'href' => CHECKOUT_URL],
        // VIP → no debería ver ningún botón (queda solo la clase, sin texto)
        ['class' => 'show--vip', 'text' => null, 'href' => '#'],
      ],
      'guest' => ['text' => 'REGÍSTRATE Y HAZTE VIP', 'href' => REGISTERFORM_URL],
    ],
    'during' => [
      'registered' => [
        // No VIP → se muestra con hidden--vip
        ['class' => 'hidden--vip', 'text' => 'COMPRA TU ENTRADA VIP', 'href' => CHECKOUT_URL],
        // VIP → no debería ver ningún botón (queda solo la clase, sin texto)
        ['class' => 'show--vip', 'text' => 'INGRESA AHORA', 'href' => '{youtube}'],
      ],
      'guest' => ['text' => 'REGÍSTRATE Y HAZTE VIP', 'href' => REGISTERFORM_URL],
    ],
    'transition' => [
      'registered' => [
        ['class' => 'hidden--vip', 'text' => 'COMPRA TU ENTRADA VIP', 'href' => CHECKOUT_URL],
        // TODO: A donde manda este link?
        ['class' => 'show--vip', 'text' => 'ACCEDE AHORA', 'href' => '#'],
      ],
      'guest' => ['text' => 'REGÍSTRATE Y HAZTE VIP', 'href' => REGISTERFORM_URL],
    ],
    'post' => [
      'registered' => [
        ['class' => 'hidden--vip', 'text' => 'COMPRA TU ENTRADA VIP', 'href' => CHECKOUT_URL],
        ['class' => 'show--vip', 'text' => 'ACCEDE AHORA', 'href' => '{youtube}'],
      ],
      'guest' => ['text' => 'REGÍSTRATE Y HAZTE VIP', 'href' => REGISTERFORM_URL],
    ],
  ];

  $config = [
    'conference' => $conference,
    'successStory' => $successStory,
    'workshop' => $workshop,
  ];

  return $config;
}
