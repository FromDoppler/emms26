<section class="emms__hero-conference emms__hero-conference--recorded">
    <div class="emms__container--lg">
        <h1 class="emms__fade-in"><?= $speaker['title'] ?></h1>
        <div class="emms__hero-conference__video emms__fade-in">
            <!--Video -->
            <div class="emms__cropper-cont-16-9" id="speakerVideo">
                <div class="emms__cropper-cont ">
                    <div class="emms__cropper-cont-interno">
                        <iframe
                            src="https://www.youtube.com/embed/<?= $speaker['youtube'] ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        <div class="emms__hero-conference__aside emms__fade-in">
            <h2><?= $speaker['name'] ?></h2>
            <p><?= $speaker['bio'] ?></p>
            <ul>
                <?php if ($speaker['sm_linkedin']) : ?>
                    <li><a href="<?= $speaker['sm_linkedin'] ?>"><img src="/src/img/icons/icono-linkedin-b.svg" alt="LinkedIn"></a></li>
                <?php endif; ?>
                <?php if ($speaker['sm_twitter']) : ?>
                    <li><a href="<?= $speaker['sm_twitter'] ?>"><img src="/src/img/icons/icono-twitter-b.svg" alt="Twitter"></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>
