<?php

const SPEAKER_EVENT_DIGITAL_TRENDS = 'digital-trends';

function speakerSupportsSchedulePreview($event)
{
    return $event === SPEAKER_EVENT_DIGITAL_TRENDS;
}

function speakerSupportsModalImage($event)
{
    return $event === SPEAKER_EVENT_DIGITAL_TRENDS;
}

function speakerSchedulePreviewUrl($token)
{
    return 'schedule-preview.php?' . http_build_query(['token' => $token]);
}
