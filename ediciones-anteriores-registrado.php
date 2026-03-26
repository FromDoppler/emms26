<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/app-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/digital-trends/post/home/head.php'); ?>

  <?php if ((defined('SECRET_REFRESH') && !empty(constant('SECRET_REFRESH')))): ?>
  <script src='/src/<?= VERSION ?>/js/vendors/socket.io.min.js?version=<?= VERSION ?>'></script>
  <script>
    const socket = io("wss://<?= URL_REFRESH ?>", {
      path: "/<?= PATH_REFRESH ?>/socket.io"
    });
    socket.on("state", (args) => {
      location.reload();
    });
  </script>
  <?php endif; ?>
  <script type="module">
    import {
      isUserLogged
    } from '/src/<?= VERSION ?>/js/common/index.js';

    if (!isUserLogged()) {
      window.location.href = window.APP.utils.addParams('/ediciones-anteriores');
    }
  </script>
</head>

<body class="emms__previous-editions">

  <!-- Header -->
  <header class="emms__header">
    <div class="emms__container--lg emms__fade-in">
      <div class="emms__header__logo">
        <a href="/"><img src="/src/img/logos/logo-emms.png" alt="Emms 2025"></a>
      </div>
      <?php if ($digitalTrendsStates['isLive']) : ?>
        <div class="emms__header__live">
          <p>¡ESTAMOS EN VIVO EN EMMS DIGITAL TRENDS!</p>
        </div>
      <?php endif ?>
      <a class="emms__header__nav--mb" id="btn-burger"></a>
      <nav class="emms__header__nav emms__header__nav--hidden" id="nav-mb">
        <ul class="emms__header__nav__menu">
          <li><a href="/registrado">home</a></li>
          <li><a href="/digital-trends-registrado">DIGITAL TREND</a></li>
          </li>
          <li><a href="./sponsors-registrado">biblioteca de recursos</a></li>
          <li class="emms__header__nav__menu__dropdown"><a href="#" class="active">Qué es el EMMS</a>
            <ul class="emms__header__nav__submenu">
              <li><a href="#sobre-emms">Sobre el EMMS</a></li>
              <li><a href="#ediciones-anteriores">Revive ediciones anteriores</a></li>
            </ul>
          </li>
          <li><a href="/sponsors-promo">sponsors</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Share -->
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/share.php'); ?>


  <main>

    <!-- Hero -->
    <section class="emms__previous-editions__hero emms__bg-section-4" id="sobre-emms">
      <div class="emms__container--lg emms__previous-editions__hero__row">
        <div class="emms__previous-editions__hero__column-text">
          <h1 class="emms__fade-top">Acerca del EMMS</h1>
          <p class="emms__fade-in">El EMMS es el <strong>evento online de Marketing Digital más convocante en Latinoamérica y España</strong>. Se desarrolla de forma <strong>100% virtual</strong> y es organizado por <a href="https://www.fromdoppler.com/es/" target="_blank">Doppler</a>, la <strong>herramienta de Marketing Automation</strong> líder entre el público hispanohablante, hace <strong>más de 16 años</strong>. <br><br>Cada edición cuenta con los referentes y marcas más destacados en la industria, abordando las temáticas más resonantes de los últimos meses ante más de 50 mil registrados. <br>Además, actualmente el EMMS ofrece dos ediciones: una exclusiva para la industria E-commerce y otra sobre tendencias globales de marketing digital.
          </p>
        </div>
        <div class="emms__previous-editions__hero__column-img">
          <img src="/src/img/ediciones-anteriores/ediciones-anteriores.png" alt="Equipo de Doppler" class="emms__fade-in">
        </div>
      </div>
    </section>

    <!-- Editions list -->
    <section class="emms__previous-editions__list" id="ediciones-anteriores">
      <div class="emms__container--md">
        <h2>Revive las ediciones anteriores</h2>
        <div class="emms__previous-editions__list__container emms__previous-editions__list__container--xl emms__fade-in">
          <div class="emms__previous-editions__list__item emms__previous-editions__list__item--xl">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8rXMsoQVihjCL9cSjEvRs-_" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/edicion2024-dt-xl.png" alt="EMMS 2024 E-commerce" class="desktop">
                <img src="/src/img/editions/emms2023-dt.png" alt="EMMS 2024 Digital Trends" class="mobile">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h4>EMMS E-commerce 2025</h4>
                <p>
                  Te invitamos a revivir el evento más importante de Comercio Electrónico en Latinoamérica y España. Fueron dos días de puro aprendizaje junto a las mentes más brillantes de la industria, quienes revelaron Estrategias simples y efectivas para atraer clientes y escalar las ventas de tu Tienda Online. Si no pudiste asistir
                  o quieres reactivar todo lo aprendido, ¡accede ahora!
                </p>
                <span>REVIVE ESTA EDICIÓN</span>
              </div>
            </a>
          </div>
        </div>

        <ul class="emms__previous-editions__list__container emms__previous-editions__list__container--lg">
          <li class="emms__previous-editions__list__item emms__previous-editions__list__item--lg emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8pmtMbwaW-CORRkIu-0p8xN" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2023-dt.png" alt="EMMS 2024 Digital Trends">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS Digital Trends 2024</h3>
                <p> Revive tres días épicos en el evento online que reunió a más de 30,000 personas de todo el mundo para descubrir las últimas tendencias del Marketing Digital. Durante esta edición, exploramos qué están haciendo las empresas más influyentes del mundo y disfrutamos de entrevistas exclusivas con líderes
                  de la industria. Si te lo perdiste o querés volver a inspirarte con ideas innovadoras para tu negocio, ¡accede ahora!</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__previous-editions__list__item--lg emms__fade-in">
            <a href="https://youtube.com/playlist?list=PLHE_SVtQOB8rfVH-su2RHsEZzh06kePw3&feature=shared" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2023-ecommerce.png" alt="EMMS 2023 E-commerce">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS E-commerce 2024</h3>
                <p>Más de 40 mil personas se capacitaron en la última edición del EMMS E-commerce 2024. Aprovecha esta oportunidad y revive de manera gratuita entrevistas exclusivas con especialistas, Casos de Éxito y Conferencias, como así también
                  los mejores insights en Inteligencia Artificial aplicada a este mercado.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__previous-editions__list__item--lg emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qMW0FxjjOYdNpLyX0x3_4z" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2023-ecommerce.png" alt="EMMS 2023 E-commerce">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS E-COMMERCE 2023</h3>
                <p>Más de 13 mil personas se unieron a la primera edición especializada en la industria del Retail e E-commerce del EMMS. Contamos con entrevistas exclusivas con especialistas, casos de éxitos y conferencias, así como también los mejores insights en Inteligencia Artificial aplicada a este mercado. </p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
        </ul>
        <ul class="emms__previous-editions__list__container">
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qPIWeiFxsj1HdRpjHPN71R" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2022.png" alt="EMMS 2023">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS Digital Trends 2023</h3>
                <p>Líderes en el ámbito del marketing digital
                  y referentes de las compañías más competitivas del mercado exploraron
                  las últimas tendencias y estrategias
                  en el mundo digital. Se abordó desde
                  la experiencia del usuario hasta innovaciones en inteligencia artificial.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://youtube.com/playlist?list=PLHE_SVtQOB8rm4R9Dn55TfQSABmWQoaRK" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2022.png" alt="EMMS 2022">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2022</h3>
                <p>A lo largo de 3 días, 21 speakers internacionales de las compañías más vanguardistas del mercado revolucionaron el evento de Marketing Digital más esperado del año. ¡Revívelo y descubre por qué 55.000 personas no quisieron perdérselo!</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8oeQSWPozYXCwdC9PKRNPiM" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2021.png" alt="EMMS 2021">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2021</h3>
                <p>Los líderes de la industria se reunieron para dar respuesta y soluciones a los desafíos actuales.
                  Revive esta edición especial, pensada para todos aquellos que toman decisiones de negocio en su día a día y llevan sus empresas al próximo nivel.
                </p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8pcO6n-OHDedWgmicdFuj_p" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2020.png" alt="EMMS 2020">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2020</h3>
                <p>A lo largo de 5 días, 18 oradores de primer nivel compartieron su conocimiento sobre Marketing Digital enfocado en 5 industrias clave. ¡Las sesiones virtuales de Networking fueron el complemento clave!</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qoW8HGYDWUF1V6c0taJGJk" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2019.png" alt="EMMS 2019">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2019</h3>
                <p>Las temáticas más votadas por el público y los especialistas que están cambiando el Marketing Digital en el mundo. ¡Revive la jornada que se convirtió en el evento online del año!</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qJGZdZ8UiUZA9N0BqQ_ePf" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2018.png" alt="EMMS 2018">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2018</h3>
                <p>Las conferencias en inglés con traducción en simultáneo marcaron un antes y un después para los eventos de Marketing del mercado hispano. Hubo speakers de primer nivel y miles de asistentes.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8rVbcM84J2HdtU9Ko1N2wOW" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2017.png" alt="EMMS 2017">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2017</h3>
                <p>¡La décima edición tuvo récord de registros! Fueron 8 conferencias organizadas en nivel inicial y avanzado para que cada uno pudiera capacitarse en base a su experiencia y necesidades.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8olvfTQWIY-K12xmfh-083T" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2016.png" alt="EMMS 2016">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2016</h3>
                <p>Se sumaron novedosos formatos como charlas motivacionales, entrevistas a expertos, debates en vivo y más. Esta vez fue la audiencia quien eligió de qué manera aprender.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8rSRLFPeeXwpbFwXglMAjw6" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2015.png" alt="EMMS 2015">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2015</h3>
                <p>Como cada edición, el EMMS se renovó. Las conferencias se convirtieron en un evento de dos días con 10 oradores destacados dentro de 2 temáticas: motivación y acción.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8rfXIJu1cFWY8LjPe6wQQ4b" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2014.png" alt="EMMS 2014">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2014</h3>
                <p>El evento se transformó volviéndose 100% online, internacional y gratis. Con una duración de 10 horas ininterrumpidas, 10 increíbles speakers y más de 10.000 asistentes.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qr7C4nts3AwCXRP3fPi1y1" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2013.png" alt="EMMS 2013">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2013</h3>
                <p>Por primera vez el evento viajó por 5 países: Ecuador, España, República Dominicana, México y Argentina. Los influencers del sector se lucieron con charlas magníficas.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8pFFK1-Tg8o1uDOWKUGBaoM" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2012.png" alt="EMMS 2012">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2012</h3>
                <p>Inspirado en el “fin del mundo”, volvió el EMMS para salvar a aquellos que no pensaban actualizarse con las últimas tendencias del Marketing. Más de 2.000 participantes.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8pCjMuMVOwrataaoQUyaFvo" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2011.png" alt="EMMS 2011">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2011</h3>
                <p>El evento más relevante de Marketing Online llegó a México. Se discutieron temas como el Mobile Marketing, tendencias del mercado y se inauguró el panel de casos de éxito.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8oQe0h6OLhb1QOwMHSfoI3P" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2010.png" alt="EMMS 2010">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2010</h3>
                <p>Los asistentes aprendieron sobre el análisis de Métricas, Social Email Marketing, Diseño y Conversión, en el reconocido seminario del Email Marketing Made Simple.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
          <li class="emms__previous-editions__list__item emms__fade-in">
            <a href="https://www.youtube.com/playlist?list=PLHE_SVtQOB8qZykZGtv66ITP3zxHPUhfV" target="_blank">
              <div class="emms__previous-editions__list__item__image">
                <img src="/src/img/editions/emms2009.png" alt="EMMS 2009">
              </div>
              <div class="emms__previous-editions__list__item__description">
                <h3>EMMS 2009</h3>
                <p>Solo 500 personas tuvieron la posibilidad de vivir este evento en Buenos Aires, Argentina. Tendencias en Social Media, Content Marketing, SEO y mucho más.</p>
                <span>Revive esta edición</span>
              </div>
            </a>
          </li>
        </ul>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'); ?>



</body>

</html>
