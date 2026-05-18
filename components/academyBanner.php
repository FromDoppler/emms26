<?php
$academyBannerVariant = $academyBannerVariant ?? 'default';
$isGreenAcademyBannerVariant = $academyBannerVariant === true || $academyBannerVariant === 'green';
$academyBannerSectionClass = $isGreenAcademyBannerVariant ? ' academy-banner--green-accent' : '';
$academyBannerIcon = $isGreenAcademyBannerVariant ? '/src/img/icons/icon-check--soft-green.svg' : '/src/img/icons/icon-check--purple.svg';
?>
        <!-- Doppler Academy Banner -->
        <section class="academy-banner academy-banner--aniversario emms__bg-section-6 sponsors__hero--blue-bg<?= $academyBannerSectionClass ?>" id="aprende-con-doppler">
            <div class="emms__container--sm">
                <div class="academy-banner__description">
                    <h2>Lleva tu conocimiento a otro nivel con Doppler Academy</h2>
                    <p>Además de ayudar al crecimiento de tu negocio, formamos profesionales de Marketing a lo largo de todo el mundo <strong> de forma online y 100% gratuita.</strong>
                    <br> Cursa a tu ritmo, donde quieras y cuando quieras 🙂</p>
                    <div class="text-with-icon-container">
                        <div class="text-with-icon">
                            <img src="<?= $academyBannerIcon ?>" alt="Check icon">
                            <span>Gratuito</span>
                        </div>
                        <div class="text-with-icon">
                            <img src="<?= $academyBannerIcon ?>" alt="Check icon">
                            <span>on demand</span>
                        </div>
                        <div class="text-with-icon">
                            <img src="<?= $academyBannerIcon ?>" alt="Check icon">
                            <span>A TU RITMO</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="emms__container--md">
                <div class="academy-banner__carousel academy-carousel" data-flickity>
                    <a class="academy-banner__carousel__item">
                        <div class="academy-banner__carousel__item__text">
                            <h3>Cursos</h3>
                            <ul>
                                <li>Estructurados en módulos prácticos y tests</li>
                                <li>A tu ritmo, sin presiones</li>
                                <li>Certificación oficial</li>
                            </ul>
                        </div>
                        <div class="academy-banner__carousel__item__image">
                            <img src="/src/img/academy-certificaciones.png" alt="Cursos">
                        </div>
                    </a>
                    <a class="academy-banner__carousel__item">
                        <div class="academy-banner__carousel__item__text">
                            <h3>Webinars</h3>
                            <ul>
                                <li>Speakers destacados</li>
                                <li>Tendencias digitales</li>
                                <li>Prácticas en vivo</li>
                            </ul>
                        </div>
                        <div class="academy-banner__carousel__item__image">
                            <img src="/src/img/academy-webinars.png" alt="Webinars">
                        </div>
                    </a>
                    <a class="academy-banner__carousel__item">
                        <div class="academy-banner__carousel__item__text">
                            <h3>Cápsulas</h3>
                            <ul>
                                <li>Tutoriales de 3 minutos</li>
                                <li>Categorías teóricas y prácticas</li>
                                <li>Estrategias de Email Marketing</li>
                            </ul>
                        </div>
                        <div class="academy-banner__carousel__item__image">
                            <img src="/src/img/academy-podcasts.png" alt="Cápsulas">
                        </div>
                    </a>
                    <a class="academy-banner__carousel__item">
                        <div class="academy-banner__carousel__item__text">
                            <h3>Doppler Demo</h3>
                            <ul>
                                <li>Tutoriales sobre Doppler</li>
                                <li>Entrenamiento online y gratuito</li>
                                <li>Técnicas para Email, WhatsApp, Chatbots y más canales</li>
                            </ul>
                        </div>
                        <div class="academy-banner__carousel__item__image">
                            <img src="/src/img/academy-doppler-demo.png" alt="Doppler Demo">
                        </div>
                    </a>
                </div>
            </div>
            <div class="emms__container--sm">
                <div class="academy-banner__description--button">
                    <a href="https://academy.fromdoppler.com/" target="_blank" class="emms__cta emms__fade-in">COMIENZA AHORA</a>
                </div>
            </div>
        </section>
