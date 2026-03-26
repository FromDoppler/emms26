<?php
$footerClass = '';
$companyImage = true;

switch ($type) {
    case 'conference':
    case 'interview':
        $footerClass = 'emms__calendar__list__item__card__business';
        break;
    case 'debate':
        $footerClass = 'emms__calendar__list__item__card__business--debate';
        break;
    case 'networking':
    case 'workshop':
        $footerClass = 'emms__calendar__list__item__card__business vip';
        $companyImage = ($type === 'workshop');
        break;
    default:
        $footerClass = 'emms__calendar__list__item__card__business';
        $companyImage = false;
}

?>

<div class="<?= $footerClass ?>">
    <?php if ($companyImage): ?>
        <img src="./admin/speakers/uploads/<?= $speaker['image_company'] ?>" alt="<?= $speaker['alt_image_company'] ?>">
    <?php else: ?>
        &nbsp;
    <?php endif; ?>
</div>
