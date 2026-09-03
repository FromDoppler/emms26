<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/cacheSettings.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/helpers/index.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/components/schedule/speaker-card/speaker-modal/speaker-modal-helper.php');

$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$previewEvent = 'digital-trends';
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$dayRows = $db
    ->query(
        "SELECT DISTINCT day FROM speakers WHERE event = ? AND day IS NOT NULL AND day <> '' ORDER BY CAST(day AS UNSIGNED)",
        [$previewEvent]
    )
    ->fetchAll();

$speakersByDay = [];
foreach ($dayRows as $dayRow) {
    $day = $dayRow['day'];
    $speakersByDay[$day] = $db->getSpeakersByDay($day, $previewEvent);
}
$db->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview agenda — Digital Trends</title>
    <link rel="stylesheet" href="/src/<?= VERSION ?>/css/styles.css">
</head>

<body class="digital-trends">
    <main>
        <section class="emms__calendar" id="agenda">
            <div class="emms__container--lg">
                <div class="emms__calendar__title">
                    <h2>AGENDA DIGITAL TRENDS</h2>
                    <p>Preview interno de los speakers cargados para Digital Trends.</p>
                </div>

                <?php if (!$speakersByDay) : ?>
                    <p>No hay speakers cargados para Digital Trends.</p>
                <?php endif; ?>

                <?php foreach ($speakersByDay as $day => $daySpeakers) : ?>
                    <section aria-labelledby="preview-day-<?= htmlspecialchars($day) ?>">
                        <h3 id="preview-day-<?= htmlspecialchars($day) ?>">Día <?= htmlspecialchars($day) ?></h3>
                        <div class="speaker-grid">
                            <?php foreach ($daySpeakers as $speaker) : ?>
                                <div class="speaker-grid__item">
                                    <?php render_speaker_card($speaker, true, false, $digitalTrendsStates); ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            function closeModal(modal) {
                modal.classList.remove('modal-overlay--show');
                modal.classList.add('modal-overlay--hide');
                setTimeout(function() {
                    modal.style.display = 'none';
                }, 300);
            }

            document.querySelectorAll('.speaker-card__more-info').forEach(function(card) {
                card.addEventListener('click', function() {
                    var speakerCard = card.closest('.speaker-card');
                    var targetId = speakerCard && speakerCard.getAttribute('data-target-speaker');
                    var modal = targetId ? document.getElementById(targetId) : null;
                    if (!modal) return;
                    modal.classList.remove('modal-overlay--hide');
                    modal.classList.add('modal-overlay--show');
                    modal.style.display = 'flex';
                });
            });

            document.querySelectorAll('.modal .modal__close-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var modal = button.closest('.modal-overlay');
                    if (modal) closeModal(modal);
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal-overlay')) {
                    closeModal(event.target);
                }
            });
        });
    </script>
</body>

</html>
