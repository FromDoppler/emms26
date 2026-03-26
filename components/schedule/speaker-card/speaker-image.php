<div class="speaker-card__image">
    <?php if ($speaker['exposes'] === 'networking'): ?>
        <img src="/src/img/networking-template-image.png" alt="networking" class="speaker-card__photo">
    <?php else: ?>
        <img src="./admin/speakers/uploads/<?= $speaker['image'] ?>" alt="<?= $speaker['alt_image'] ?>" class="speaker-card__photo">
        <p class="speaker-card__image-name">
            <?= $speaker['name'] ?>
        </p>

        <p class="speaker-card__image-title">
            <?= $speaker['job'] ?>
        </p>
        <img src="./admin/speakers/uploads/<?= $speaker['image_company'] ?>" alt="<?= $speaker['alt_image_company'] ?>" class="speaker-card__logo">
    <?php endif; ?>
    <p class="speaker-card__more-info">VER MÁS INFO</p>

</div>
