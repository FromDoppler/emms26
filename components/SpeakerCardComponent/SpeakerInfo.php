<?php if (in_array($type, ['conference', 'workshop', 'debate', 'interview'])): ?>
    <div class="emms__calendar__list__item__card__speaker">
        <div class="emms__calendar__list__item__card__speaker__image<?= ($type === 'debate') ? '--debate' : '' ?>">
            <img src="./admin/speakers/uploads/<?= $speaker['image'] ?>" alt="<?= $speaker['alt_image'] ?>">
        </div>
        <?php if (in_array($type, ['conference', 'workshop', 'interview'])): ?>
            <div class="emms__calendar__list__item__card__speaker__text">
                <h4><?= $speaker['name'] ?></h4>
                <h5><?= $speaker['job'] ?></h5>
                <ul>
                    <?php include 'SocialLinks.php'; ?>
                    <?php include 'SpeakerBio.php'; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
