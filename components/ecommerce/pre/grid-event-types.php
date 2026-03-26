<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/helpers/urlHelper.php');
$normalizedUrl = getNormalizeUrl();

function getGridBlock($url)
{
    $blocks = [
        '/ecommerce' => [
            'block' => 'CtaBlock',
        ],
        '/ecommerce-registrado' => [
            'block' => 'TextBlock',
        ],
        '/*' => [
            'block' => 'CtaBlock',
        ],
    ];

    return $blocks[$url] ??  $blocks['/*'];
}

$block = getGridBlock($normalizedUrl);
?>
<section class="emms__grid emms__grid--3">
    <div class="emms__container--md">
        <div class="emms__grid__title emms__fade-in">
            <h2>Vive la experiencia completa en EMMS E-commerce</h2>
        </div>
        <ul class="emms__grid__content emms__fade-in">
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/conferencias.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Conferencias</h3>
                    <p>Descubre las estrategias que están implementando los líderes globales del Marketing para E-commerce</p>
                </div>
            </li>
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/entrevistas.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Entrevistas</h3>
                    <p>Escucha a expertos de grandes marcas compartir secretos, tendencias y claves para el éxito.</p>
                </div>
            </li>
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/casos-de-exito.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Casos de Éxito</h3>
                    <p>Descubre cómo empresas líderes escalaron su negocio y aplica sus tácticas en el tuyo.</p>
                </div>
            </li>
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/networking.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Networking</h3>
                    <p>Conéctate con profesionales del sector, crea alianzas y amplía tu red de contactos.</p>
                </div>
            </li>
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/workshop.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Workshops</h3>
                    <p>Participa en sesiones prácticas con especialistas y aplica lo aprendido en tiempo real.
                    </p>
                </div>
            </li>
            <li class="emms__grid__item">
                <div class="emms__grid__item__image">
                    <img src="/src/img/recursos.png" alt="Image">
                </div>
                <div class="emms__grid__item__text">
                    <h3>Recursos</h3>
                    <p>Accede a guías, plantillas y contenido exclusivo para potenciar tu
                        estrategia digital.</p>
                </div>
            </li>
        </ul>
        <div class="grid__footer">
            <?php if ($block['block'] === 'CtaBlock') : ?>
                <a href="#registro" class="emms__cta emms__fade-in-animation eventHiddenElements">REGÍSTRATE GRATIS</a>
                <button class="emms__cta emms__fade-in-animation eventShowElements alreadyRegisterForm"><span class="button__text">Regístrate gratis</span></button>
            <?php elseif ($block['block'] === 'TextBlock') : ?>
                <p> <strong>Pronto podrás comprar tus entradas VIP para acceder a los Workshops y el Networking. ¡Mantente pendiente de tu casilla de Email!</strong></p>
            <?php endif; ?>
        </div>
        <?php if ($block['block'] === 'TextBlock') : ?>
            <div class="emms__separator emms__separator--white">
            </div>
        <?php endif; ?>
    </div>
</section>
