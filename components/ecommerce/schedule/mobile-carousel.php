<ul class="emms__calendar__list emms__calendar__list--mb main-carousel emms__fade-in" data-flickity>
    <?php
    foreach ($speakers as $speaker) :
        $isSpeakerEcommerce = $speaker['event'] === "ecommerce";
        $isSpeakerExposeDebate = $speaker['exposes'] === "debate";
        $allowedExposesTypes = ["conference", "workshop", "networking", "successStory", "interview"];
        $isSpeakerExposesType = in_array($speaker['exposes'], $allowedExposesTypes) || $isSpeakerExposeDebate;
    ?>
        <?php if (($isSpeakerExposesType) && $isSpeakerEcommerce) : ?>
            <li class="emms__calendar__list__item">
                <?php
                $type = $speaker['exposes'] ?? 'default';
                include($_SERVER['DOCUMENT_ROOT'] . '/components/SpeakerCard.php');
                ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
