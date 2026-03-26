<?php
define('COMPONENTS_PATH', dirname(__DIR__));


function render_speaker_card($speaker, $isRegistered, $isMobile, $digitalTrendsStates)
{
    include COMPONENTS_PATH . '/speaker-card.php';
}

function render_speaker_ribon($speaker, $isMobile)
{
    include COMPONENTS_PATH . '/speaker-ribon.php';
}
function render_speaker_image($speaker)
{
    include COMPONENTS_PATH . '/speaker-image.php';
}

function render_speaker_info($speaker, $isRegistered, $digitalTrendsStates)
{
    include COMPONENTS_PATH . '/speaker-info.php';
}

function render_speaker_button($speaker,  $isRegistered, $digitalTrendsStates)
{
    include COMPONENTS_PATH . '/speaker-button.php';
}

function render_speaker_hour($speaker)
{
    include COMPONENTS_PATH . '/speaker-hour.php';
}
