<?php
$modalId = 'modal-' . $speaker['id'] . ($isMobile ? '-mobile' : '');
?>
<div class="modal-overlay" id="<?= $modalId ?>">
    <div class="modal <?= 'modal--' . $speaker['exposes'] ?>">
        <div class="modal__image">
            <?php if ($speaker['exposes'] === 'networking'): ?>
                <img src="/src/img/networking-template-image.png" alt="networking" class="speaker-card__photo">
            <?php else: ?>
                <img src="./admin/speakers/uploads/<?= $speaker['image'] ?>" alt="<?= $speaker['alt_image'] ?>" class="speaker-card__photo">
            <?php endif; ?>
        </div>

        <div class="modal__content">
            <span class="modal__close-btn">&times;</span>
            <p class="modal__label">Speaker</p>
            <p class="modal__name"><?= $speaker['name'] ?></p>

            <?php include('speaker-modal-social.php'); ?>

            <p class="modal__description">
                <?= $speaker['description'] ?>
            </p>
            <!-- TODO: definir si va boton y re utilizar el componente que ya hay -->
            <!-- <button class="modal__btn">HAZTE VIP</button> -->
        </div>
    </div>
</div>
