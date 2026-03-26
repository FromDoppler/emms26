<?php foreach (['twitter', 'linkedin', 'instagram', 'facebook'] as $social): ?>
    <?php if (!empty($speaker["sm_$social"])): ?>
        <li><a href="<?= $speaker["sm_$social"] ?>" target="_blank"><img src="src/img/icons/icono-<?= $social ?>.png" alt="<?= ucfirst($social) ?>"></a></li>
    <?php endif; ?>
<?php endforeach; ?>
