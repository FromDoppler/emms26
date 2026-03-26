<?php

/**
 * Reemplaza placeholders {slug} y {youtube} en los botones.
 */
function replaceButtonPlaceholders(array $button, array $speaker): array
{
  foreach ($button as $key => $value) {
    if (is_string($value)) {
      $button[$key] = strtr($value, [
        '{slug}' => $speaker['slug'] ?? '',
        '{youtube}' => $speaker['youtube'] ?? '#'
      ]);
    }
  }
  return $button;
}
