<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$eventPhase = resolveEventPhase($digitalTrendsStates);

$buttons = shouldHideButton($speaker, $currentPath)
  ? null
  : getSpeakerButtonData($speaker, $eventPhase, $isRegistered, $currentPath);

if ($buttons):
  if (isset($buttons['text'])) $buttons = [$buttons];
?>
  <div class="speaker-card__cta">
    <?php foreach ($buttons as $button): ?>
      <?php
        $href = $button['href'] ?? '#';
        $text = $button['text'] ?? '';
        $class = 'speaker-card__button';
        if (!empty($button['class'])) $class .= ' ' . htmlspecialchars($button['class']);
      ?>
      <?php if (!empty($text)): ?>
        <a class="<?= $class ?>" href="<?= htmlspecialchars($href) ?>">
          <?= htmlspecialchars($text) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
