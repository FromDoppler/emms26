<?php
function renderResourceCard($resource, $isRegistered) {
?>
    <li class="resource-card">
        <div class="resource-card__image">
            <img src="<?= $resource['image'] ?>" alt="<?= $resource['title'] ?>">
        </div>
        <div class="resource-card__body">
            <div class="resource-card__ribbon">
                <img src="/src/img/emoji-book.svg" alt="Book emoji">
                <?= $resource['tag'] ?>
            </div>
            <h3 class="resource-card__title"><?= $resource['title'] ?></h3>
            <p class="resource-card__description"><?= $resource['description'] ?></p>

            <?php if ($isRegistered) : ?>
                <a class="resource-card__link" href="<?= $resource['downloadUrl'] ?>" target="_blank"><?= $resource['downloadText'] ?></a>
            <?php else : ?>
                <a class="resource-card__link" data-target="modalRegister" data-toggle="emms__register-modal" href="#"><?= $resource['downloadText'] ?></a>
            <?php endif; ?>
        </div>
    </li>
<?php
}
?>
