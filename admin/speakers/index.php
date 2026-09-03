<?php
include_once '../config.php';
include_once '../../utils/GeoIp.php';
require_once __DIR__ . '/speaker-admin-urls.php';
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$token = $_GET['token'] ?? '';
$selectedEvent = $_GET['filter'] ?? '';
$selectedDay = $_GET['day'] ?? '';

if (isset($_GET['delete_id'])) {
    $sql_query = "DELETE FROM speakers WHERE id=" . $_GET['delete_id'];
    mysqli_query($con, $sql_query);
    $redirect = "index.php?token=" . urlencode($token);
    if ($selectedEvent !== '') {
        $redirect .= "&filter=" . urlencode($selectedEvent);
    }
    if ($selectedDay !== '') {
        $redirect .= "&day=" . urlencode($selectedDay);
    }
    @header("Location: " . $redirect);
    exit;
}
if (isset($_GET['changestatus_id'])) {
    $sql_query = "UPDATE speakers SET `status`='" . $_GET['status'] . "' WHERE id=" . $_GET['changestatus_id'];
    mysqli_query($con, $sql_query);
    header("Location: $_SERVER[PHP_SELF]");
    exit;
}

$eventLabels = [
    'ecommerce' => 'Ecommerce',
    'digital-trends' => 'Digital Trends',
];

$typeLabels = [
    'conference' => 'Conferencia',
    'workshop' => 'Workshop',
    'networking' => 'Networking',
    'debate' => 'Mesa de Debate',
    'successStory' => 'Caso de éxito',
    'interview' => 'Entrevista',
];

$dayCounts = [];
if ($selectedEvent !== '') {
    $escapedEvent = mysqli_real_escape_string($con, $selectedEvent);
    $dayResult = mysqli_query(
        $con,
        "SELECT day, COUNT(*) AS total FROM speakers WHERE event = '$escapedEvent' AND day IS NOT NULL AND day <> '' GROUP BY day ORDER BY CAST(day AS UNSIGNED)"
    );
    while ($dayRow = mysqli_fetch_assoc($dayResult)) {
        $dayCounts[$dayRow['day']] = (int) $dayRow['total'];
    }
}

$listConditions = [];
if ($selectedEvent !== '') {
    $escapedEvent = mysqli_real_escape_string($con, $selectedEvent);
    $listConditions[] = "event = '$escapedEvent'";
}
if ($selectedEvent !== '' && $selectedDay !== '') {
    $escapedDay = mysqli_real_escape_string($con, $selectedDay);
    $listConditions[] = "day = '$escapedDay'";
}

$sql_query = "SELECT * FROM speakers";
if ($listConditions) {
    $sql_query .= " WHERE " . implode(' AND ', $listConditions);
}
$sql_query .= " ORDER BY event, CAST(day AS UNSIGNED), CAST(orden AS UNSIGNED)";
$result_set = mysqli_query($con, $sql_query);
$hasSpeakers = mysqli_num_rows($result_set) > 0;

function speakersAdminUrl($token, $event = '', $day = '')
{
    $params = ['token' => $token];
    if ($event !== '') {
        $params['filter'] = $event;
    }
    if ($day !== '') {
        $params['day'] = $day;
    }
    return 'index.php?' . http_build_query($params);
}

function speakerUpdatedLabel($value)
{
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ABM Speakers</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css?v=3" type="text/css" />
</head>

<body>
    <div class="speakers-admin">
        <header class="speakers-admin__header">
            <div>
                <a class="speakers-admin__back" href="/<?= ADMIN_BASE_PATH ?>?token=<?= htmlspecialchars($token) ?>">← Menu principal</a>
                <h1>Speakers<?= $selectedEvent !== '' ? ' — ' . htmlspecialchars($eventLabels[$selectedEvent] ?? $selectedEvent) : '' ?></h1>
                <p>Administrá speakers, charlas y su ubicación dentro de la agenda.</p>
            </div>
            <div class="speakers-admin__header-actions">
                <?php if (speakerSupportsSchedulePreview($selectedEvent)) : ?>
                    <a class="btn btn-default" href="<?= htmlspecialchars(speakerSchedulePreviewUrl($token)) ?>" target="_blank" rel="noopener">Ver agenda</a>
                <?php endif; ?>
                <a class="btn btn-primary" href="add_speakers.php?token=<?= urlencode($token) ?><?= $selectedEvent !== '' ? '&event=' . urlencode($selectedEvent) : '' ?>">+ Agregar speaker</a>
            </div>
        </header>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="alert alert-success">Speaker actualizado correctamente.</div>
        <?php elseif (isset($_GET['created'])) : ?>
            <div class="alert alert-success">Speaker guardado correctamente.</div>
        <?php endif; ?>

        <section class="speakers-admin__toolbar">
            <div class="speakers-admin__search">
                <label for="speaker-search">Buscar</label>
                <input id="speaker-search" type="search" class="form-control" placeholder="Speaker o charla…" autocomplete="off">
            </div>
            <div class="speakers-admin__event-filter">
                <label for="event-filter">Evento</label>
                <select id="event-filter" class="form-control" onchange="if (this.value) window.location.href = this.value;">
                    <option value="<?= htmlspecialchars(speakersAdminUrl($token)) ?>" <?= $selectedEvent === '' ? 'selected' : '' ?>>Todos los eventos</option>
                    <?php foreach ($eventLabels as $eventKey => $eventLabel) : ?>
                        <option value="<?= htmlspecialchars(speakersAdminUrl($token, $eventKey)) ?>" <?= $selectedEvent === $eventKey ? 'selected' : '' ?>><?= htmlspecialchars($eventLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>

        <?php if ($selectedEvent !== '' && count($dayCounts) > 1) : ?>
            <nav class="speakers-admin__days" aria-label="Filtrar speakers por día">
                <a class="day-filter <?= $selectedDay === '' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(speakersAdminUrl($token, $selectedEvent)) ?>">
                    Todos <span><?= array_sum($dayCounts) ?></span>
                </a>
                <?php foreach ($dayCounts as $day => $count) : ?>
                    <a class="day-filter <?= $selectedDay === (string) $day ? 'is-active' : '' ?>" href="<?= htmlspecialchars(speakersAdminUrl($token, $selectedEvent, $day)) ?>">
                        Día <?= htmlspecialchars($day) ?> <span><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <section class="speakers-list-card">
            <div class="table-responsive">
                <table class="table speakers-list">
                    <thead>
                        <tr>
                            <th>Speaker</th>
                            <th>Charla</th>
                            <th>Tipo</th>
                            <th>Día / Hora</th>
                            <th>Orden</th>
                            <th>Empresa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="speakers-list-body">
                        <?php if (!$hasSpeakers) : ?>
                            <tr class="speaker-search-empty">
                                <td colspan="7">No hay speakers para el evento o día seleccionado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php while ($row = mysqli_fetch_assoc($result_set)) :
                            $searchText = trim(($row['name'] ?? '') . ' ' . ($row['job'] ?? '') . ' ' . ($row['title'] ?? ''));
                            $editParams = [
                                'edit_id' => $row['id'],
                                'token' => $token,
                            ];
                            if ($selectedEvent !== '') {
                                $editParams['return_filter'] = $selectedEvent;
                            }
                            if ($selectedDay !== '') {
                                $editParams['return_day'] = $selectedDay;
                            }
                            $deleteUrl = speakersAdminUrl($token, $selectedEvent, $selectedDay) . '&delete_id=' . urlencode($row['id']);
                            $hasCardImage = !empty($row['image']);
                            $hasModalImage = speakerSupportsModalImage($row['event'] ?? '') && !empty($row['image_modal']);
                            $updatedLabel = speakerUpdatedLabel($row['updated_at'] ?? '');
                        ?>
                            <tr class="speaker-row" data-search="<?= htmlspecialchars($searchText) ?>">
                                <td>
                                    <div class="speaker-summary">
                                        <?php if ($hasCardImage) : ?>
                                            <a class="media-thumb media-thumb--speaker media-preview-trigger" href="uploads/<?= htmlspecialchars($row['image']) ?>" data-preview-src="uploads/<?= htmlspecialchars($row['image']) ?>" data-preview-alt="<?= htmlspecialchars($row['alt_image'] ?? $row['name'] ?? '') ?>" title="Ver imagen">
                                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['alt_image'] ?? '') ?>">
                                            </a>
                                        <?php else : ?>
                                            <div class="media-thumb media-thumb--speaker"><span>Sin imagen</span></div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($row['name'] ?? '') ?></strong>
                                            <?php if (!empty($row['job'])) : ?>
                                                <small title="<?= htmlspecialchars($row['job']) ?>"><?= htmlspecialchars($row['job']) ?></small>
                                            <?php endif; ?>
                                            <?php if ($hasModalImage) : ?>
                                                <span class="speaker-modal-badge">Modal propia</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="speaker-title" title="<?= htmlspecialchars($row['title'] ?? '') ?>"><?= htmlspecialchars($row['title'] ?? '') ?></span></td>
                                <td><span class="speaker-type speaker-type--<?= htmlspecialchars($row['exposes'] ?? '') ?>"><?= htmlspecialchars($typeLabels[$row['exposes']] ?? ($row['exposes'] ?? '')) ?></span></td>
                                <td>
                                    <strong>Día <?= !empty($row['day']) ? htmlspecialchars($row['day']) : '-' ?></strong>
                                    <small><?= htmlspecialchars($row['time'] ?? '') ?></small>
                                </td>
                                <td><span class="speaker-order"><?= !empty($row['orden']) ? htmlspecialchars($row['orden']) : '-' ?></span></td>
                                <td>
                                    <?php if (!empty($row['image_company'])) : ?>
                                        <a class="media-thumb media-thumb--company media-preview-trigger" href="uploads/<?= htmlspecialchars($row['image_company']) ?>" data-preview-src="uploads/<?= htmlspecialchars($row['image_company']) ?>" data-preview-alt="<?= htmlspecialchars($row['alt_image_company'] ?? '') ?>" title="Ver logo">
                                            <img src="uploads/<?= htmlspecialchars($row['image_company']) ?>" alt="<?= htmlspecialchars($row['alt_image_company'] ?? '') ?>">
                                        </a>
                                    <?php else : ?>
                                        <div class="media-thumb media-thumb--company"><span>Sin logo</span></div>
                                    <?php endif; ?>
                                </td>
                                <td class="speaker-actions">
                                    <div class="speaker-actions__links">
                                        <a class="btn btn-sm btn-primary" href="edit_speakers.php?<?= htmlspecialchars(http_build_query($editParams)) ?>">Editar</a>
                                        <a class="speaker-delete" href="<?= htmlspecialchars($deleteUrl) ?>" onclick="return confirm('¿Seguro que querés eliminar este speaker?');">Eliminar</a>
                                    </div>
                                    <?php if ($updatedLabel !== '') : ?>
                                        <small class="speaker-updated">Actualizado <?= htmlspecialchars($updatedLabel) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <tr id="speaker-search-empty" class="speaker-search-empty" hidden>
                            <td colspan="7">No encontramos speakers para esa búsqueda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="media-preview" class="media-preview" hidden aria-hidden="true">
        <button type="button" class="media-preview__close" aria-label="Cerrar preview">×</button>
        <img class="media-preview__image" src="" alt="">
    </div>

    <script type="text/javascript">
        (function () {
            var input = document.getElementById('speaker-search');
            var rows = Array.prototype.slice.call(document.querySelectorAll('.speaker-row'));
            var empty = document.getElementById('speaker-search-empty');
            var preview = document.getElementById('media-preview');
            var previewImage = preview.querySelector('.media-preview__image');
            var previewClose = preview.querySelector('.media-preview__close');

            input.addEventListener('input', function () {
                var query = input.value.trim().toLowerCase();
                var visible = 0;

                rows.forEach(function (row) {
                    var searchText = row.getAttribute('data-search').toLowerCase();
                    var match = searchText.indexOf(query) !== -1;
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                empty.hidden = visible !== 0;
            });

            function closePreview() {
                preview.hidden = true;
                preview.setAttribute('aria-hidden', 'true');
                previewImage.setAttribute('src', '');
                previewImage.setAttribute('alt', '');
                previewImage.classList.remove('media-preview__image--company');
            }

            Array.prototype.forEach.call(document.querySelectorAll('.media-preview-trigger'), function (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    previewImage.setAttribute('src', trigger.getAttribute('data-preview-src'));
                    previewImage.setAttribute('alt', trigger.getAttribute('data-preview-alt') || 'Preview');
                    previewImage.classList.toggle('media-preview__image--company', trigger.classList.contains('media-thumb--company'));
                    preview.hidden = false;
                    preview.setAttribute('aria-hidden', 'false');
                });
            });

            previewClose.addEventListener('click', closePreview);
            preview.addEventListener('click', function (event) {
                if (event.target === preview) closePreview();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !preview.hidden) closePreview();
            });
        })();
    </script>
</body>

</html>
