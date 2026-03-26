<?php

foreach ($days as $day => $info) {
    $isSelected = false;
    $isFinalized = false;

    switch ($state) {
        case 'pre':
            $isSelected = $day === 1;
            break;
        case 'live':
            $isSelected = $day === $currentDay;
            $isFinalized = $day < $currentDay;
            break;
        case 'post':
            $isSelected = $day === 1;
            $isFinalized = true;
            break;
    }

    include __DIR__ . '/schedule-button.php';
}
