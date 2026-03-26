<?php
$isPost = $state === 'post';
$selected = $isSelected ? 'true' : 'false';
$shouldShowFinalized = ($isFinalized ?? false) && !$isPost && empty($suppressFinalizedLabels) === true;
$finalized = $shouldShowFinalized ? ' - finalizado' : '';
$id = "day{$day}";

// Para forzar seleccion de dia 3 en checkout-lp-landing
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
if (
    strpos($currentUrl, 'checkout-lp-landing') !== false
    && !$isPost
) {
    $selected = ($day === 3) ? 'true' : 'false';
}
?>

<button class="schedule__tab" role="tab" aria-selected="<?= $selected ?>" id="<?= $id ?>">
    <span class="dk"><?= $info['date'] ?><?= $finalized ?></span>
    <span class="mb"><?= $info['short'] ?><?= $finalized ?></span>
</button>
