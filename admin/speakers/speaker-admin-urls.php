<?php

function speakerSchedulePreviewUrl($token, $event = '')
{
    $params = ['token' => $token];

    if ($event !== '') {
        $params['event'] = $event;
    }

    return 'schedule-preview.php?' . http_build_query($params);
}
