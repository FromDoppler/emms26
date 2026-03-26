<?php
$labelClasses = [
    'conference' => 'emms__calendar__list__item__card__label--free',
    'workshop' => 'emms__calendar__list__item__card__label--vip',
    'networking' => 'emms__calendar__list__item__card__label--vip',
    'debate' => 'emms__calendar__list__item__card__label--free',
    'interview' => 'emms__calendar__list__item__card__label--free',
];

$labelClass = $labelClasses[$type] ?? '';

$labelText = match ($type) {
    'conference' => 'Conferencia',
    'workshop' => 'Workshop - exclusivo VIP',
    'networking' => 'Networking - exclusivo VIP',
    'debate' => 'Mesa de debate',
    'interview' => 'Entrevista',
    default => '',
};
?>

<div class="emms__calendar__list__item__card__label <?= $labelClass ?>">
    <p><?= $labelText ?></p>
</div>
