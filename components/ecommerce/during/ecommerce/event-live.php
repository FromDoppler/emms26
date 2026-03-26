<!-- TODO: Abstraer placas a componentes dummy y mejorar las logicas compartidas -->

<?php if (($settings_phase['event'] === ECOMMERCE) && ($settings_phase['during'] === 1) && ($settings_phase['transition'] === "live-on") && ($settings_phase['transmission'] === "youtube")) : ?>
    <p class="live-advice">EN VIVO </p>
    <h1 class="emms__fade-in">Estamos en vivo en el #EMMSBYDOPPLER</h1>
    <div class="emms__hero-conference__video emms__fade-in">
        <div class="emms__cropper-cont-16-9">
            <div class="emms__cropper-cont ">
                <div class="emms__cropper-cont-interno">
                    <iframe src="https://www.youtube.com/embed/<?= $duringDaysArray[$dayDuring]['youtube'] ?>?rel=0&autoplay=1&mute=1&enablejsapi=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="emms__hero-conference__aside emms__fade-in emms__hero-conference__video--chat">
        <iframe src="https://www.youtube.com/live_chat?v=<?= $duringDaysArray[$dayDuring]['youtube'] ?>&embed_domain=<?= $_SERVER['HTTP_HOST'] ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
<?php elseif (($settings_phase['event'] === ECOMMERCE) && ($settings_phase['during'] === 1) && ($settings_phase['transition'] === "live-on") && ($settings_phase['transmission'] === "twitch")) : ?>
    <h1 class="emms__fade-in">Estamos en vivo en el #EMMSBYDOPPLER</h1>
    <div class="emms__hero-conference__video emms__fade-in">
        <div class="emms__cropper-cont-16-9">
            <div class="emms__cropper-cont ">
                <div class="emms__cropper-cont-interno">
                    <iframe src="https://player.twitch.tv/?channel=<?= $duringDaysArray[$dayDuring]['twitch'] ?>&parent=<?= $_SERVER['HTTP_HOST'] ?>"></iframe>
                </div>
            </div>
        </div>
    </div>
<?php elseif (($settings_phase['event'] === ECOMMERCE) && ($settings_phase['during'] === 1) && ($settings_phase['transition'] === "live-on") && ($settings_phase['transmission'] === "twitch-migrate")) : ?>
    <img src="src/img/placas/migrate-twitch.png" alt="Se migró a Twitch" class="banner">
<?php elseif (($settings_phase['event'] === ECOMMERCE) && ($settings_phase['during'] === 1) && ($settings_phase['transition'] === "live-on") && ($settings_phase['transmission'] === "technical-problems")) : ?>
    <img src="src/img/placas/technical-error.png" alt="Errores técnicos" class="banner">
<?php elseif (($settings_phase['event'] === ECOMMERCE) && ($settings_phase['during'] === 1) && ($settings_phase['transition'] === "live-off")) : ?>
    <h1 class="emms__fade-in">Seguimos con más EMMS E-commerce</h1>
    <div class="emms__hero-conference__video emms__hero-conference__video--transition emms__fade-in hidden--vip">
        <div class="broadcast-free__container emms__container--md">
            <div class="broadcast-free__content">
                <h3 class="broadcast-free__title">¡Prepárate para <br> los Workshops!</h3>
                <p class="broadcast-free__text">
                    Después de las conferencias gratuitas, llegan los workshops con especialistas en la industria. <br>
                    Con tu pase VIP, accederás a talleres interactivos, una cuenta gratuita en Doppler por seis meses, una guía exclusiva de Prompts de IA y, ¡muchos beneficios más!
                </p>
                <a href="/checkout" class="emms__cta emms__cta--golden">HAZTE VIP</a>
            </div>

            <!-- Imágenes decorativas -->
            <img src="src/img/placas/ticket.png" alt="VIP Ticket" class="broadcast-free__image broadcast-free__image--ticket" />
            <img src="src/img/placas/carrito.png" alt="Carrito de compras" class="broadcast-free__image broadcast-free__image--cart" />
        </div>
    </div>
    <div class="emms__hero-conference__video emms__hero-conference__video--transition emms__fade-in  show--vip emms__vip">
        <div class="speaker-card__ribbon dk">EXCLUSIVO ASISTENTE VIP </div>
        <div class="speaker-card__ribbon mb">VIP </div>
        <div class="broadcast-vip__container emms__container--md">
            <div class="broadcast-vip__content">
                <h3 class="broadcast-vip__title">Accede a los links para ingresar a los Workshops 🤩</h3>
                <?php
                    $workshops = [
                        '29 DE ABRIL' => [
                            [
                                'title' => 'Antes de vender online: dominios, hosting y seguridad',
                                'url' => 'https://us06web.zoom.us/j/85498048337?pwd=aFD3DxapKTLXMbpRJKDr1fAh0GOIy0.1',
                                'hour' => '(ARG) 15:00 p.m.',
                                'hourLink' => 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Xavier+Idevik+%7C+Workshop+EMMS+E-commerce+&iso=20250429T15&p1=51&am=40'
                            ],
                            [
                                'title' => 'Análisis de E-commerce en WordPress',
                                'url' => 'https://us06web.zoom.us/j/88598752825?pwd=G7bTVCSdbM6D9eDC45r469ZGq45zqK.1',
                                'hour' => '(ARG) 15:45 p.m.',
                                'hourLink' => 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Pablo+Moratinos+%7C+EMMS+E-commerce+2025&iso=20250429T1545&p1=51&am=40'
                            ],
                            [
                                'title' => 'Tendencias 2025 de IA para tener más impacto en Marketing',
                                'url' => 'https://us06web.zoom.us/j/87981563138?pwd=ZxLu5rUr5UGVPxQezgzPWnbP8gR5bF.1',
                                'hour' => '(ARG) 16:30 p.m.',
                                'hourLink' => 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Matias+Carrera+%7C+EMMS+E-commerce+2025&iso=20250429T1630&p1=51&am=40'
                            ]
                        ],
                        '&nbsp;' => [
                            [
                                'title' => 'Growth Hacks & Quick Wins para E-commerce en 2025',
                                'url' => 'https://us06web.zoom.us/j/86387365095?pwd=Zt0VAXkfN5Ms6bAiqrZTk1iHcfVIbi.1',
                                'hour' => '(ARG) 15:00 p.m.',
                                'hourLink' => 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Josean+Mu%C3%B1oz+%7C+EMMS+E-commerce+2025&iso=20250429T15&p1=51&am=40'
                            ],
                            [
                                'title' => 'Checklist de Sitios Web efectivos para E-commerce',
                                'url' => 'https://us06web.zoom.us/j/85344715275?pwd=sFtaN4ZRmEVa4fcaPnboEXV9IulGVu.1',
                                'hour' => '(ARG) 15:45 p.m.',
                                'hourLink' => 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Luis+Betancourt+%7C+Workshop+EMMS+E-commerce+&iso=20250429T1545&p1=51&am=40'
                            ]
                        ]
                    ];
                ?>


                <div class="workshops-links__columns">
                    <?php foreach ($workshops as $day => $sessions): ?>
                        <div class="workshops-links__day">
                            <h4><?= $day ?></h4>
                            <hr>
                            <ul>
                                <?php foreach ($sessions as $session): ?>
                                    <li>
                                        <a href="<?= $session['url'] ?>" target="_blank"><?= $session['title'] ?></a>
                                        <?php if (isset($session['alert']) && !empty($session['alert'])): ?>
                                            <br>
                                            <span class="alert"><?= $session['alert'] ?></span>
                                        <?php endif; ?>
                                        <br>
                                        <span><?= $session['hour'] ?></span>
                                        <?php if (isset($session['hourLink']) && !empty($session['hourLink'])): ?>
                                            <br>
                                            <a class="hour" href="<?= $session['hourLink'] ?>">Mira el horario en tu país</a>
                                        <?php endif; ?>

                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <img src="src/img/placas/tuerca-c.png" alt="Icono de tuerca" class="broadcast-free__image broadcast-vip__image--tool" />

        </div>

    </div>
    <div class="emms__hero-conference__aside emms__hero-conference__aside--transition emms__fade-in">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/ecommerce/during/ecommerce/certificate/certificate.php') ?>
    </div>
<?php endif; ?>
