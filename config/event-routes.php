<?php

# EVENT ROUTES CONFIGURATION

// Todas las páginas base compartidas por todos los eventos
$sharedPages = [
    'home' => [
        'unregistered' => ['url' => '/', 'page' => 'home.php'],
        'registered'   => ['url' => 'registrado', 'page' => 'home-registrado.php'],
    ],
    'sponsors' => [
        'unregistered' => ['url' => 'sponsors', 'page' => 'library-resources.php'],
        'registered'   => ['url' => 'sponsors-registrado', 'page' => 'library-resources.php'],
        'folder'       => 'sponsors',
    ],
    'ediciones-anteriores' => [
        'unregistered' => ['url' => 'ediciones-anteriores', 'page' => 'ediciones-anteriores.php'],
        'registered'   => ['url' => 'ediciones-anteriores-registrado', 'page' => 'ediciones-anteriores.php'],
        'folder'       => 'ediciones-anteriores',
    ],
    'sponsors-promo' => [
        'url' => 'sponsors-promo',
        'page' => 'sponsors-promo.php',
        'folder' => 'sponsors-promo',
    ],
    'sponsors-interna' => [
        'url' => 'sponsors-interna',
        'page' => 'sponsors-interna.php',
        'folder' => 'sponsors-interna',
    ],
    'checkout' => [
        'url'  => 'checkout',
        'page' => 'checkout.php',
        'folder' => 'checkout',
    ],
    'checkout-success' => [
        'url'  => 'checkout-success',
        'page' => 'checkout-success.php',
        'folder' => 'checkout',
    ],
    'checkout-lp' => [
        'url'  => 'checkout-lp',
        'page' => 'checkout.php',
        'folder' => 'checkout-lp',
    ],
    'checkout-lp-success' => [
        'url'  => 'checkout-lp-success',
        'page' => 'checkout-success.php',
        'folder' => 'checkout-lp',
    ],
    'checkout-lp-landing' => [
        'url'  => 'checkout-lp-landing',
        'page' => 'landing.php',
        'folder' => 'checkout-lp',
    ],


    'speaker' => [
        'url' => 'speaker-interna',
        'page' => 'speaker-interna.php',
        'folder' => 'speaker-interna',
    ],
];

// Configuración específica por evento
$events = [
    'ECOMMERCE' => [
        'freeId' => 'ecommerce25',
        'vipId'  => 'ecommerce25-vip',
        'name'   => 'E-commerce',
        'folder' => 'ecommerce',
        'pages'  => [
            'unregistered' => ['url' => 'ecommerce', 'page' => 'ecommerce.php'],
            'registered'   => ['url' => 'ecommerce-registrado', 'page' => 'ecommerce-registrado.php'],
        ],
    ],
    'DIGITALTRENDS' => [
        'freeId' => 'digital-trends25',
        'vipId'  => 'digital-trends25-vip',
        'name'   => 'Digital Trends',
        'folder' => 'digital-trends',
        'pages'  => [
            'unregistered' => ['url' => 'digital-trends', 'page' => 'digital-trends.php'],
            'registered'   => ['url' => 'digital-trends-registrado', 'page' => 'digital-trends-registrado.php'],
        ],
    ],
];

// Helper para generar redirects basado en la nueva estructura
function getRedirectsForEvent($event, $sharedPages) {
    return [
        'registered' => [
            '' => 'registrado', // Home redirect for registered users
            'digital-trends' => $event['pages']['registered']['url'],
            'sponsors' => $sharedPages['sponsors']['registered']['url'],
            'ediciones-anteriores' => $sharedPages['ediciones-anteriores']['registered']['url'],
        ],
        'unregistered' => [
            'registrado' => '', // Home redirect for unregistered users (root)
            'digital-trends-registrado' => $event['pages']['unregistered']['url'],
            'sponsors-registrado' => $sharedPages['sponsors']['unregistered']['url'],
            'ediciones-anteriores-registrado' => $sharedPages['ediciones-anteriores']['unregistered']['url'],
            'checkout' => $event['pages']['unregistered']['url'],
            'checkout-success' => $event['pages']['unregistered']['url'],
            'speaker-interna' => $event['pages']['unregistered']['url'],
            'sponsors-interna' => $sharedPages['sponsors-interna']['url'],
        ],
    ];
}

return [
    'sharedPages' => $sharedPages,
    'events' => $events,
    'getRedirectsForEvent' => 'getRedirectsForEvent'
];
