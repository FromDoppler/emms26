<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/speaker-modal/speaker-modal-helper.php');

$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$eventLabels = [
    'ecommerce' => 'Ecommerce',
    'digital-trends' => 'Digital Trends',
];
$previewEvent = $_GET['event'] ?? '';

if (!isset($eventLabels[$previewEvent])) {
    http_response_code(400);
    exit('Seleccioná un evento válido para previsualizar la agenda.');
}

$eventStates = $previewEvent === 'ecommerce' ? $ecommerceStates : $digitalTrendsStates;
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$speakers = $db
    ->query(
        'SELECT * FROM speakers WHERE event = ? ORDER BY CAST(day AS UNSIGNED), CAST(orden AS UNSIGNED)',
        [$previewEvent]
    )
    ->fetchAll();
$db->close();

$speakersByDay = [];
foreach ($speakers as $speaker) {
    $day = $speaker['day'] !== '' ? $speaker['day'] : '-';
    if (!isset($speakersByDay[$day])) {
        $speakersByDay[$day] = [];
    }
    $speakersByDay[$day][] = $speaker;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/components/head.php'); ?>
</head>

<body class="<?= htmlspecialchars($previewEvent) ?>">
    <main>
        <section class="emms__calendar" id="agenda">
            <div class="emms__container--lg">
                <div class="emms__calendar__title">
                    <h2>AGENDA <?= htmlspecialchars(strtoupper($eventLabels[$previewEvent])) ?></h2>
                    <p>Preview interno de los speakers cargados para este evento.</p>
                </div>

                <?php if (!$speakersByDay) : ?>
                    <p>No hay speakers cargados para este evento.</p>
                <?php endif; ?>

                <?php foreach ($speakersByDay as $day => $daySpeakers) : ?>
                    <section aria-labelledby="preview-day-<?= htmlspecialchars($day) ?>">
                        <h3 id="preview-day-<?= htmlspecialchars($day) ?>">Día <?= htmlspecialchars($day) ?></h3>
                        <div class="speaker-grid">
                            <?php foreach ($daySpeakers as $speaker) : ?>
                                <div class="speaker-grid__item">
                                    <?php render_speaker_card($speaker, true, false, $eventStates); ?>
                                </div>
                                <?php render_speaker_modal($speaker, false); ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function closeModal(modal) {
                modal.classList.remove('modal-overlay--show');
                modal.classList.add('modal-overlay--hide');
                setTimeout(function () {
                    modal.style.display = 'none';
                }, 300);
            }

            document.querySelectorAll('.speaker-card__more-info').forEach(function (card) {
                card.addEventListener('click', function () {
                    var speakerCard = card.closest('.speaker-card');
                    var targetId = speakerCard && speakerCard.getAttribute('data-target-speaker');
                    var modal = targetId ? document.getElementById(targetId) : null;
                    if (!modal) return;
                    modal.classList.remove('modal-overlay--hide');
                    modal.classList.add('modal-overlay--show');
                    modal.style.display = 'flex';
                });
            });

            document.querySelectorAll('.modal .modal__close-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var modal = button.closest('.modal-overlay');
                    if (modal) closeModal(modal);
                });
            });

            window.addEventListener('click', function (event) {
                if (event.target.classList.contains('modal-overlay')) {
                    closeModal(event.target);
                }
            });
        });
    </script>
</body>

</html>
