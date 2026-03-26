<?php
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$sponsors = $db->getSponsorsCards('SPONSOR');

if (!empty($sponsors)) {
?>
    <section class="emms__sponsors__list">
        <div class="emms__container--lg">
            <div class="emms__sponsors__list__title">
                <h2 class="emms__fade-in">Inspírate y capacítate con contenido exclusivo <br> de nuestros Media Partners:
                </h2>
            </div>
            <ul class="emms__sponsors__list__content emms__fade-in">
                <?php
                $index = 0;
                $texts = array(0 => "RECURSO EXCLUSIVO", 1 => "¡NO TE LO PIERDAS!", 2 => "SOLO PARA TI", 3 => "¡HAZ CLIC AHORA!");

                foreach ($sponsors as $sponsor) :
                ?>
                    <li class="emms__sponsors__list__item">
                        <div class="emms__sponsors__list__item__ribon">
                            <img src="/src/img/emoji-book.svg" alt="Book emoji">
                            <?= $texts[$index % count($texts)] ?>
                        </div>

                        <h3><?= htmlspecialchars($sponsor['title']) ?></h3>
                        <p><?= htmlspecialchars($sponsor['description_card']) ?></p>
                        <?php if (empty($sponsor['slug'])) : ?>
                            <a class="inactive">Accede →</a>
                        <?php else : ?>
                            <?php if ($isRegistered) : ?>
                                <a href="/sponsors-interna?slug=<?= urlencode($sponsor['slug']) ?>">Accede ahora</a>
                            <?php else : ?>
                                <a data-target="modalRegister" data-toggle="emms__register-modal" href="#">Accede ahora</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="emms__sponsors__list__item__logo">
                            <img src="./adm24/server/modules/sponsors/uploads/<?= htmlspecialchars($sponsor['logo_company']) ?>" alt="<?= htmlspecialchars($sponsor['alt_logo_company']) ?>">
                        </div>
                    </li>
                <?php
                    $index++;
                endforeach;
                ?>
            </ul>
        </div>
    </section>
<?php } ?>
