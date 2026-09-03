<?php
include_once '../config.php';
include_once '../../utils/GeoIp.php';
require_once __DIR__ . '/speaker-admin-urls.php';
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$token = $_GET['token'] ?? '';
$selectedEvent = $_POST['event'] ?? ($_GET['event'] ?? '');
if (!in_array($selectedEvent, ['ecommerce', 'digital-trends'], true)) {
    $selectedEvent = '';
}
$backUrl = 'index.php?token=' . urlencode($token);
if ($selectedEvent !== '') {
    $backUrl .= '&filter=' . urlencode($selectedEvent);
}

if (isset($_POST['btn-save'])) {
    $orden = trim($_POST['orden'] ?? '');
    $event = $_POST['event'] ?? '';

    if (!in_array($event, ['ecommerce', 'digital-trends'], true)) {
        $saveError = 'Seleccioná un evento válido.';
    } elseif ($orden !== '' && !ctype_digit($orden)) {
        $saveError = 'El orden debe ser un número entero mayor o igual a 0.';
    }
}

if (isset($_POST['btn-save']) && empty($saveError)) {
    $name = $_POST['name'];

    $image = $_FILES['image']['name'];
    if ($image != '') {
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    $image_modal = '';
    if (speakerSupportsModalImage($event)) {
        $image_modal = $_FILES['image_modal']['name'];
        if ($image_modal != '') {
            move_uploaded_file($_FILES['image_modal']['tmp_name'], 'uploads/' . $image_modal);
        }
    }

    $alt_image = $_POST['alt_image'];
    $job = $_POST['job'];
    $sm_twitter = $_POST['sm_twitter'];
    $sm_linkedin = $_POST['sm_linkedin'];
    $sm_instagram = $_POST['sm_instagram'];
    $sm_facebook = $_POST['sm_facebook'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $bio = $_POST['bio'];

    $image_company = $_FILES['image_company']['name'];
    if ($image_company != '') {
        move_uploaded_file($_FILES['image_company']['tmp_name'], 'uploads/' . $image_company);
    }

    $alt_image_company = $_POST['alt_image_company'];
    $time = $_POST['time'];
    $link_time = $_POST['link_time'];
    $day = $_POST['day'];
    $exposes = $_POST['exposes'];
    $slug = $_POST['slug'];
    $youtube = $_POST['youtube'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_twitter = $_POST['meta_twitter'];

    $meta_image = $_FILES['meta_image']['name'];
    if ($meta_image != '') {
        move_uploaded_file($_FILES['meta_image']['tmp_name'], 'uploads/' . $meta_image);
    }

    $imageModalValue = $image_modal === ''
        ? 'NULL'
        : "'" . mysqli_real_escape_string($con, $image_modal) . "'";

    $sql_query = "INSERT INTO speakers (`name`,`image`,`image_modal`,`alt_image`,`job`,`sm_twitter`,`sm_linkedin`,`sm_instagram`,`sm_facebook`,`title`,`description`,`bio`,`image_company`,`alt_image_company`,`time`,`link_time`,`orden`,`day`,`event`,`exposes`,`slug`,`youtube`,`meta_title`,`meta_description`,`meta_twitter`,`meta_image`) VALUES('" . $name . "','" . $image . "'," . $imageModalValue . ",'" . $alt_image . "','" . $job . "','" . $sm_twitter . "','" . $sm_linkedin . "','" . $sm_instagram . "','" . $sm_facebook . "','" . $title . "','" . $description . "','" . $bio . "','" . $image_company . "','" . $alt_image_company . "','" . $time . "','" . $link_time . "','" . $orden . "','" . $day . "','" . $event . "','" . $exposes . "','" . $slug . "','" . $youtube . "','" . $meta_title . "','" . $meta_description . "','" . $meta_twitter . "','" . $meta_image . "')";

    if (mysqli_query($con, $sql_query)) {
        header('Location: /admin/speakers/index.php?token=' . urlencode($token) . '&filter=' . urlencode($event) . '&created=1');
        exit;
    }

    $saveError = 'Ocurrió un error al guardar el speaker';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Agregar speaker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css?v=3" type="text/css" />
</head>
<body>
    <div class="speaker-form-page">
        <header class="speaker-form-header">
            <div>
                <a class="speaker-form-header__back" href="<?= htmlspecialchars($backUrl) ?>">← Volver a speakers</a>
                <h1>Agregar speaker</h1>
                <p>Cargá la información que se mostrará en la agenda.</p>
            </div>
            <div class="speaker-form-header__actions">
                <a id="schedule-preview-link" class="btn btn-default" href="<?= htmlspecialchars(speakerSchedulePreviewUrl($token)) ?>" target="_blank" rel="noopener" <?= speakerSupportsSchedulePreview($selectedEvent) ? '' : 'hidden' ?>>Ver agenda</a>
            </div>
        </header>

        <?php if (!empty($saveError)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($saveError) ?></div>
        <?php endif; ?>

        <form class="speaker-form" method="post" enctype="multipart/form-data">
            <section class="speaker-form-section">
                <h2>Información</h2>
                <p class="speaker-form-section__description">Datos principales del speaker y el tipo de participación.</p>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="event">Evento</label>
                        <select name="event" id="event" class="form-control" required>
                            <option value="" disabled <?= $selectedEvent === '' ? 'selected' : '' ?>>Seleccioná un evento</option>
                            <option value="ecommerce" <?= $selectedEvent === 'ecommerce' ? 'selected' : '' ?>>Ecommerce</option>
                            <option value="digital-trends" <?= $selectedEvent === 'digital-trends' ? 'selected' : '' ?>>Digital Trends</option>
                        </select>
                    </div>
                    <div class="speaker-form-field">
                        <label for="exposes">Tipo de exposición</label>
                        <select name="exposes" id="exposes" class="form-control" required>
                            <option value="" disabled selected>Seleccioná un tipo</option>
                            <option value="conference">Conferencia</option>
                            <option value="workshop">Workshop</option>
                            <option value="networking">Networking</option>
                            <option value="debate">Mesa de Debate</option>
                            <option value="successStory">Caso de éxito</option>
                            <option value="interview">Entrevista</option>
                        </select>
                    </div>
                    <div class="speaker-form-field">
                        <label for="name">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="speaker-form-field">
                        <label for="job">Cargo</label>
                        <input type="text" class="form-control" id="job" name="job">
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Imágenes</h2>
                <p class="speaker-form-section__description">Digital Trends permite cargar una imagen específica para el modal. Si no se carga, utiliza la imagen de la card.</p>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="image">Imagen de la card</label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image"><span>Sin imagen seleccionada</span></div>
                            <div>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" data-image-input>
                                <div class="image-file-name" data-file-name-for="image"></div>
                                <label for="alt_image">Texto alternativo</label>
                                <input type="text" class="form-control" id="alt_image" name="alt_image">
                            </div>
                        </div>
                    </div>
                    <div class="speaker-form-field" id="modal-image-field" <?= speakerSupportsModalImage($selectedEvent) ? '' : 'hidden' ?>>
                        <label for="image_modal">Imagen del modal <span class="speaker-form-help">Opcional</span></label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image_modal"><span>Usará la imagen de la card</span></div>
                            <div>
                                <input type="file" class="form-control" id="image_modal" name="image_modal" accept="image/*" data-image-input <?= speakerSupportsModalImage($selectedEvent) ? '' : 'disabled' ?>>
                                <div class="image-file-name" data-file-name-for="image_modal"></div>
                                <span class="speaker-form-help">Si queda vacío, se mantiene el fallback a la imagen de la card.</span>
                            </div>
                        </div>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="image_company">Logo de empresa</label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image_company"><span>Sin logo seleccionado</span></div>
                            <div>
                                <input type="file" class="form-control" id="image_company" name="image_company" accept="image/*" data-image-input>
                                <div class="image-file-name" data-file-name-for="image_company"></div>
                                <label for="alt_image_company">Texto alternativo del logo</label>
                                <input type="text" class="form-control" id="alt_image_company" name="alt_image_company">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Conferencia</h2>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="title">Título de la charla</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio"></textarea>
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Agenda</h2>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="day">Día</label>
                        <select name="day" id="day" class="form-control">
                            <option value="1" selected>Día 1</option>
                            <option value="2">Día 2</option>
                            <option value="3">Día 3</option>
                            <option value="4">Día 4</option>
                            <option value="5">Día 5</option>
                        </select>
                    </div>
                    <div class="speaker-form-field">
                        <label for="time">Hora</label>
                        <input type="text" class="form-control" id="time" name="time" placeholder="Ej. 14:45">
                    </div>
                    <div class="speaker-form-field">
                        <label for="orden">Orden</label>
                        <input type="number" class="form-control" id="orden" name="orden" min="0" step="1" value="<?= htmlspecialchars($_POST['orden'] ?? '') ?>">
                    </div>
                    <div class="speaker-form-field">
                        <label for="link_time">URL zona horaria</label>
                        <input type="text" class="form-control" id="link_time" name="link_time">
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug">
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Redes</h2>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field"><label for="sm_linkedin">LinkedIn</label><input type="text" class="form-control" id="sm_linkedin" name="sm_linkedin"></div>
                    <div class="speaker-form-field"><label for="sm_instagram">Instagram</label><input type="text" class="form-control" id="sm_instagram" name="sm_instagram"></div>
                    <div class="speaker-form-field"><label for="sm_facebook">Facebook</label><input type="text" class="form-control" id="sm_facebook" name="sm_facebook"></div>
                    <div class="speaker-form-field"><label for="sm_twitter">X / Twitter</label><input type="text" class="form-control" id="sm_twitter" name="sm_twitter"></div>
                </div>
            </section>

            <section class="speaker-form-section">
                <details class="speaker-form-details">
                    <summary>SEO / Video</summary>
                    <div class="speaker-form-grid">
                        <div class="speaker-form-field speaker-form-field--full"><label for="youtube">Zoom (durante) / YouTube (post)</label><input type="text" class="form-control" id="youtube" name="youtube"></div>
                        <div class="speaker-form-field"><label for="meta_title">Título SEO</label><input type="text" class="form-control" id="meta_title" name="meta_title"></div>
                        <div class="speaker-form-field"><label for="meta_image">Imagen SEO / Share</label><input type="file" class="form-control" id="meta_image" name="meta_image" accept="image/*"></div>
                        <div class="speaker-form-field speaker-form-field--full"><label for="meta_description">Descripción SEO</label><textarea id="meta_description" name="meta_description"></textarea></div>
                        <div class="speaker-form-field speaker-form-field--full"><label for="meta_twitter">Twitter SEO</label><textarea id="meta_twitter" name="meta_twitter"></textarea></div>
                    </div>
                </details>
            </section>

            <div class="speaker-form-actions">
                <a href="<?= htmlspecialchars($backUrl) ?>">← Volver a speakers</a>
                <div class="speaker-form-actions__buttons">
                    <a class="btn btn-default" href="<?= htmlspecialchars($backUrl) ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit" name="btn-save">Guardar speaker</button>
                </div>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        (function () {
            var eventSelect = document.getElementById('event');
            var previewLink = document.getElementById('schedule-preview-link');
            var modalImageField = document.getElementById('modal-image-field');
            var modalImageInput = document.getElementById('image_modal');

            function syncEventCapabilities() {
                var supportsDigitalTrendsAgenda = eventSelect.value === 'digital-trends';
                previewLink.hidden = !supportsDigitalTrendsAgenda;
                modalImageField.hidden = !supportsDigitalTrendsAgenda;
                modalImageInput.disabled = !supportsDigitalTrendsAgenda;
            }

            eventSelect.addEventListener('change', syncEventCapabilities);
            syncEventCapabilities();

            Array.prototype.forEach.call(document.querySelectorAll('[data-image-input]'), function (input) {
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    var preview = document.querySelector('[data-preview-for="' + input.id + '"]');
                    var name = document.querySelector('[data-file-name-for="' + input.id + '"]');
                    if (!file || !preview) return;
                    if (name) name.textContent = file.name;
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview">';
                    };
                    reader.readAsDataURL(file);
                });
            });
        })();
    </script>
</body>
</html>
