<?php
require_once __DIR__ . '/button/button-config.php';
require_once __DIR__ . '/button/button-rules.php';
require_once __DIR__ . '/button/button-utils.php';

/**
 * Retorna el boton final del speaker segun el contexto.
 */
function getSpeakerButtonData(array $speaker, string $eventPhase, bool $isRegistered, string $currentPath = ''): ?array
{
  if (!shouldShowSpeakerButton($speaker, $eventPhase, $isRegistered)) {
    return null;
  }

  $config = getButtonConfig();
  $type = $speaker['exposes'] ?? 'conference';
  $status = $isRegistered ? 'registered' : 'guest';
  $buttons = $config[$type][$eventPhase][$status] ?? null;
  if (!$buttons) return null;

  // Si solo hay un botón, lo normalizamos como array para iterar igual
  if (isset($buttons['text'])) $buttons = [$buttons];

  $final = [];

  foreach ($buttons as $button) {
    $button = replaceButtonPlaceholders($button, $speaker);
    $button = applySpeakerButtonExceptions($button, $speaker, $eventPhase, $isRegistered);
    $final[] = $button;
  }

  $final = applyRouteButtonOverrides($final, $speaker, $currentPath);
  return $final;
}

