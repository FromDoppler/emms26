<div class="modal__social-icons">
    <?php if (!empty($speaker['sm_twitter'])): ?>
        <a href="<?= $speaker['sm_twitter'] ?>" class="modal__social-link" target="_blank" rel="noopener">
            <img src="/src/img/icons/icono-twitter.svg" alt="Twitter" />
        </a>
    <?php endif; ?>
    <?php if (!empty($speaker['sm_linkedin'])): ?>
        <a href="<?= $speaker['sm_linkedin'] ?>" class="modal__social-link" target="_blank" rel="noopener">
            <img src="/src/img/icons/icono-linkedin.svg" alt="LinkedIn" />
        </a>
    <?php endif; ?>
    <?php if (!empty($speaker['sm_facebook'])): ?>
        <a href="<?= $speaker['sm_facebook'] ?>" class="modal__social-link" target="_blank" rel="noopener">
            <img src="/src/img/icons/icono-facebook.svg" alt="Facebook" />
        </a>
    <?php endif; ?>
    <?php if (!empty($speaker['sm_instagram'])): ?>
        <a href="<?= $speaker['sm_instagram'] ?>" class="modal__social-link" target="_blank" rel="noopener">
            <img  src="/src/img/icons/icono-instagram.svg" alt="Instagram" />
        </a>
    <?php endif; ?>
</div>
