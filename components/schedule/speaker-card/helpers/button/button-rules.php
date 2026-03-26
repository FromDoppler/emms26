<?php

/**
 * Determina la fase actual del evento.
 */
function resolveEventPhase(array $digitalTrendsStates): string
{
  if (!empty($digitalTrendsStates['isTransition'])) return 'transition';
  if (!empty($digitalTrendsStates['isDuring']) || !empty($digitalTrendsStates['isLive'])) return 'during';
  if (!empty($digitalTrendsStates['isPost'])) return 'post';
  return 'pre';
}

/**
 * Excepciones contextuales al texto/href del botón.
 */
function applySpeakerButtonExceptions(array $button, array $speaker, string $eventPhase, bool $isRegistered): array
{
  $type = $speaker['exposes'] ?? 'conference';
  $hasVideo = !empty($speaker['youtube']) && $speaker['youtube'] !== '#';

  // Solo aplicar excepciones en fase post
  if ($eventPhase !== 'post') {
    return $button;
  }

  // ───────────────────────────────────────────────
  // A) SPEAKERS NORMALES (conference / successStory)
  //     - Si está registrado y NO hay video → "VIDEO PRONTO DISPONIBLE"
  //     - Si NO está registrado → mantener CTA de registro
  // ───────────────────────────────────────────────
  if (in_array($type, ['conference', 'successStory'], true) && $isRegistered && !$hasVideo) {
    $button['text'] = 'VIDEO PRONTO DISPONIBLE';
    $button['href'] = '#';
    $existingClass = $button['class'] ?? '';
    $button['class'] = trim($existingClass . ' speaker-card__button--inactive');
    return $button;
  }

  // ───────────────────────────────────────────────
  // B) WORKSHOPS
  //     - No VIP (hidden--vip): mantiene "COMPRA TU ENTRADA VIP"
  //     - VIP (show--vip) y sin video → "VIDEO PRONTO DISPONIBLE" inactivo
  // ───────────────────────────────────────────────
  if ($type === 'workshop' && $isRegistered && !$hasVideo) {
    // Si es el botón visible para VIP (lo decide JS vía clase)
    if (!empty($button['class']) && strpos($button['class'], 'show--vip') !== false) {
      $button['text'] = 'VIDEO PRONTO DISPONIBLE';
      $button['href'] = '#';
      $existingClass = $button['class'] ?? '';
      $button['class'] = trim($existingClass . ' speaker-card__button--inactive');
      return $button;
    }

    // Si es hidden--vip (NO VIP) → dejar el botón de compra tal cual
    return $button;
  }

  return $button;
}

/**
 * Reglas que determinan si el botón debe mostrarse.
 */
function shouldShowSpeakerButton(array $speaker, string $eventPhase, bool $isRegistered): bool
{
  $type = $speaker['exposes'] ?? 'conference';

  // Durante el evento, si está registrado, solo workshops muestran botón
  if ($isRegistered && $eventPhase === 'during' && $type !== 'workshop') {
    return false;
  }

  return true;
}


/**
 * Reglas que definen si el botón debe ocultarse según la ruta.
 */
function shouldHideButton(array $speaker, string $currentPath): bool
{
  $isVipButton = ($speaker['exposes'] === 'workshop');
  return $currentPath === '/checkout-lp-success' ||
    ($currentPath === '/checkout-lp-landing' && !$isVipButton);
}



/**
 * Overrides por ruta para forzar o anular botones.
 * - /checkout-lp-landing: solo workshops con CTA directo a /checkout-lp
 * - /checkout-lp-success: ningún botón
 *
 */
function applyRouteButtonOverrides(array $buttons, array $speaker, string $currentPath): array
{
  // /checkout-lp-success -> nunca hay botones
  if ($currentPath === '/checkout-lp-success') {
    return [];
  }

  // /checkout-lp-landing -> solo workshop con CTA de compra
  if ($currentPath === '/checkout-lp-landing') {
    $isWorkshop = ($speaker['exposes'] ?? 'conference') === 'workshop';
    if (!$isWorkshop) {
      return [];
    }

    return [[
      'text'  => 'COMPRA TU ENTRADA VIP',
      'href'  => '/checkout-lp',
      'class' => 'speaker-card__button--primary'
    ]];
  }

  return $buttons;
}
