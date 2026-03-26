<?php

// Info
function translateExposes($exposes)
{
    $mapa = [
        'conference'    => 'Conferencia',
        'workshop'      => 'workshop',
        'networking'    => 'NETWORKING',
        'successStory'  => 'CASO DE EXITO'
    ];

    return $mapa[$exposes] ?? $exposes;
}
// End Info


//Ribbon
function isSpeakerWithRibbon($speaker)
{
    $speakersTypeWithRibbon = ['workshop', 'networking'];
    return in_array($speaker['exposes'], $speakersTypeWithRibbon);
}
// End Ribbon
