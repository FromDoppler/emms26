<?php
function renderButton($buttonData, $eventState)
{
    $defaults = array(
        'buttonText' => '',
        'buttonLink' => '',
        'buttonType' => 'primary' // emms__cta, emms__cta--secondary, emms__cta--inactive
    );

    $buttonData = array_merge($defaults, $buttonData);
    extract($buttonData);

    $class = 'emms__cta'; // default

    switch ($buttonType) {
        case 'secondary':
            $class .= ' emms__cta--secondary';
            break;
        case 'disabled':
            $class .= ' emms__cta--inactive';
            break;
    }

    // Si el botón es 'disabled', no debería tener link
    $html = $buttonType === 'disabled'
        ? "<span class=\"$class\">$buttonText</span>"
        : "<a href=\"$buttonLink\" class=\"$class\">$buttonText</a>";

    return $html;
}


function renderEventCard($eventData, $eventState)
{
    $defaults = array(
        'imageSrc' => '',
        'imageAlt' => '',
        'title' => '',
        'description' => '',
        'buttonText' => '',
        'buttonLink' => '',
        'ribbonText' => '',
        'isShortRibbon' => '',
        'isRegistered' => '',
        'spanText' => '',
        'spanExtraClass' => '', //ribbon--coming-soon
        'buttonType' => 'primary',
    );

    $eventData = array_merge($defaults, $eventData);

    extract($eventData);

    $postEventHtml = $eventState['isPost'] ? '<p class="top hide">EVENTO FINALIZADO</p>' : '';
    $liveEventHtml = $eventState['isLive'] ? '<span>EN VIVO</span>' : '';
    $isShortRibbonClass = $isShortRibbon ? "ribbon--short" : '';
    $ribbonHtml = $ribbonText ? "<div class=\"ribbon__end $isShortRibbonClass $spanExtraClass\"> $ribbonText</div>" : '';

    $spanRegistered = $isRegistered ? "<small class=\"success-register\">🗹 YA TE HAS REGISTRADO</small>" : '';

    $buttonHtml = renderButton(array(
        'buttonText' => $buttonText,
        'buttonLink' => $buttonLink,
        'buttonType' => $buttonType
    ), $eventState);

    return <<<HTML
    <li class="emms__eventCards__list__item">
        <div class="emms__eventCards__list__item__picture">
            <img src="$imageSrc" alt="$imageAlt">
            $postEventHtml
            $ribbonHtml
        </div>
        <div class="emms__eventCards__list__item__text">
            <h3>$title
            </h3>
            <p>$description</p>
            $spanRegistered
            <span>$spanText</span>
            <div class="emms__eventCards__list__item__text--bottom">
                $buttonHtml
            </div>
        </div>
    </li>
HTML;
}
