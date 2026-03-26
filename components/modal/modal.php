<?php
function render_modal(string $id, string $type, string $variant, bool $captor = false): void
{
  $types = [
    'extradata'            => __DIR__ . '/partials/modal-extradata.php',
    'vipmodal' => __DIR__ . '/partials/modal-vip.php',
    'form'                   => __DIR__ . '/partials/modal-form.php',
  ];
  if (!isset($types[$type])) return;

  $variantClass = $variant ? ' popup-modal__dialog--' . preg_replace('/[^a-z0-9_-]/i', '', $variant) : '';
  $titleId = $id . '-title';
?>
  <div id="<?= $id ?>" class="popup-modal" aria-hidden="true" <?= $captor ? 'data-captor="1"' : '' ?>>
    <div class="popup-modal__dialog<?= $variantClass ?>" role="dialog" aria-modal="true" aria-labelledby="<?= $titleId ?>">
      <button class="popup-modal__close" data-modal-close aria-label="Cerrar">×</button>
      <div class="popup-modal__body">
        <?php require $types[$type]; ?>
      </div>
    </div>
  </div>

  <?php if (!defined('EMMS_MODAL_CSS_INCLUDED')) {
    define('EMMS_MODAL_CSS_INCLUDED', true); ?>
    <link rel="stylesheet" href="/components/modal/modal.css?v=<?= VERSION ?>">
  <?php } ?>

  <?php if ($captor && !defined('EMMS_CAPTOR_JS_INCLUDED')) {
    define('EMMS_CAPTOR_JS_INCLUDED', true); ?>
    <script type="module" src="/components/modal/captor.js?v=<?= VERSION ?>"></script>
  <?php } ?>
<?php
}
