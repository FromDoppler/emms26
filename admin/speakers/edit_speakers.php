<?php
include_once '../config.php';
include_once '../../utils/GeoIp.php';
require_once __DIR__ . '/speaker-admin-urls.php';
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

$token = $_GET['token'] ?? '';
$returnFilter = $_GET['return_filter'] ?? '';
$returnDay = $_GET['return_day'] ?? '';
$backParams = ['token' => $token];
if ($returnFilter !== '') {
    $backParams['filter'] = $returnFilter;
}
if ($returnDay !== '') {
    $backParams['day'] = $returnDay;
}
$backUrl = 'index.php?' . http_build_query($backParams);

if (isset($_GET['edit_id'])) {
    $sql_query = "SELECT * FROM speakers WHERE id=" . $_GET['edit_id'];
    $result_set = mysqli_query($con, $sql_query);
    $fetched_row = mysqli_fetch_array($result_set, MYSQLI_ASSOC);
}

if (isset($_POST['btn-update'])) {
    $name = $_POST['name'];

    if ($_FILES['image']['name'] == '') {
        $image = $fetched_row['image'];
    } else {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    if (isset($_POST['use_card_image'])) {
        $image_modal = null;
    } elseif ($_FILES['image_modal']['name'] == '') {
        $image_modal = $fetched_row['image_modal'];
    } else {
        $image_modal = $_FILES['image_modal']['name'];
        move_uploaded_file($_FILES['image_modal']['tmp_name'], 'uploads/' . $image_modal);
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

    if ($_FILES['image_company']['name'] == '') {
        $image_company = $fetched_row['image_company'];
    } else {
        $image_company = $_FILES['image_company']['name'];
        move_uploaded_file($_FILES['image_company']['tmp_name'], 'uploads/' . $image_company);
    }

    if ($_FILES['meta_image']['name'] == '') {
        $meta_image = $fetched_row['meta_image'];
    } else {
        $meta_image = $_FILES['meta_image']['name'];
        move_uploaded_file($_FILES['meta_image']['tmp_name'], 'uploads/' . $meta_image);
    }

    $alt_image_company = $_POST['alt_image_company'];
    $time = $_POST['time'];
    $link_time = $_POST['link_time'];
    $orden = $_POST['orden'];
    $day = $_POST['day'];
    $event = $_POST['event'];
    $exposes = $_POST['exposes'];
    $slug = strtolower($_POST['slug']);
    $youtube = $_POST['youtube'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_twitter = $_POST['meta_twitter'];

    $imageModalValue = $image_modal === null
        ? 'NULL'
        : "'" . mysqli_real_escape_string($con, $image_modal) . "'";

    $sql_query = "UPDATE speakers SET `name`='$name', `image`='$image', `image_modal`=$imageModalValue, `alt_image`='$alt_image', `job`='$job', `sm_twitter`='$sm_twitter', `sm_linkedin`='$sm_linkedin', `sm_instagram`='$sm_instagram', `sm_facebook`='$sm_facebook', `title`='$title', `description`='$description', `bio`='$bio', `image_company`='$image_company', `alt_image_company`='$alt_image_company', `time`='$time', `link_time`='$link_time', `orden`='$orden', `day`='$day', `event`='$event', `exposes`='$exposes', `slug`='$slug', `youtube`='$youtube', `meta_title`='$meta_title', `meta_description`='$meta_description', `meta_twitter`='$meta_twitter', `meta_image`='$meta_image' WHERE id=" . $_GET['edit_id'];

    if (mysqli_query($con, $sql_query)) {
        $redirectParams = $backParams;
        $redirectParams['updated'] = '1';
        header('Location: index.php?' . http_build_query($redirectParams));
        exit;
    }

    $updateError = 'Ocurrió un error al actualizar el speaker';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Editar speaker</title>
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
                <h1>Editar speaker</h1>
                <p><?= htmlspecialchars($fetched_row['name'] ?? '') ?></p>
            </div>
            <div class="speaker-form-header__actions">
                <a class="btn btn-default" href="<?= htmlspecialchars(speakerSchedulePreviewUrl($token, $fetched_row['event'] ?? '')) ?>" target="_blank" rel="noopener">Ver agenda</a>
            </div>
        </header>

        <?php if (!empty($updateError)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($updateError) ?></div>
        <?php endif; ?>

        <form class="speaker-form" method="post" enctype="multipart/form-data">
            <section class="speaker-form-section">
                <h2>Información</h2>
                <p class="speaker-form-section__description">Datos principales del speaker y el tipo de participación.</p>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="event">Evento</label>
                        <select name="event" id="event" class="form-control" required>
                            <option <?= ($fetched_row['event'] === 'ecommerce') ? 'selected' : '' ?> value="ecommerce">Ecommerce</option>
                            <option <?= ($fetched_row['event'] === 'digital-trends') ? 'selected' : '' ?> value="digital-trends">Digital Trends</option>
                        </select>
                    </div>
                    <div class="speaker-form-field">
                        <label for="exposes">Tipo de exposición</label>
                        <select name="exposes" id="exposes" class="form-control" required>
                            <option <?= ($fetched_row['exposes'] === 'conference') ? 'selected' : '' ?> value="conference">Conferencia</option>
                            <option <?= ($fetched_row['exposes'] === 'workshop') ? 'selected' : '' ?> value="workshop">Workshop</option>
                            <option <?= ($fetched_row['exposes'] === 'networking') ? 'selected' : '' ?> value="networking">Networking</option>
                            <option <?= ($fetched_row['exposes'] === 'debate') ? 'selected' : '' ?> value="debate">Mesa de Debate</option>
                            <option <?= ($fetched_row['exposes'] === 'successStory') ? 'selected' : '' ?> value="successStory">Caso de éxito</option>
                            <option <?= ($fetched_row['exposes'] === 'interview') ? 'selected' : '' ?> value="interview">Entrevista</option>
                        </select>
                    </div>
                    <div class="speaker-form-field">
                        <label for="name">Nombre</label>
                        <input type="text" value="<?= htmlspecialchars($fetched_row['name'] ?? '') ?>" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="speaker-form-field">
                        <label for="job">Cargo</label>
                        <input type="text" value="<?= htmlspecialchars($fetched_row['job'] ?? '') ?>" class="form-control" id="job" name="job">
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Imágenes</h2>
                <p class="speaker-form-section__description">La imagen del modal es opcional. Si no existe, el modal utiliza la imagen de la card.</p>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="image">Imagen de la card</label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image">
                                <?php if (!empty($fetched_row['image'])) : ?>
                                    <img src="uploads/<?= htmlspecialchars($fetched_row['image']) ?>" alt="<?= htmlspecialchars($fetched_row['alt_image'] ?? '') ?>">
                                <?php else : ?><span>Sin imagen</span><?php endif; ?>
                            </div>
                            <div>
                                <div class="image-file-name" data-file-name-for="image"><?= htmlspecialchars($fetched_row['image'] ?? '') ?></div>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" data-image-input>
                                <span class="speaker-form-help">Elegí un archivo solo si querés reemplazar la imagen actual.</span>
                                <label for="alt_image">Texto alternativo</label>
                                <input type="text" value="<?= htmlspecialchars($fetched_row['alt_image'] ?? '') ?>" class="form-control" id="alt_image" name="alt_image">
                            </div>
                        </div>
                    </div>
                    <div class="speaker-form-field">
                        <label for="image_modal">Imagen del modal <span class="speaker-form-help">Opcional</span></label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image_modal">
                                <?php if (!empty($fetched_row['image_modal'])) : ?>
                                    <img src="uploads/<?= htmlspecialchars($fetched_row['image_modal']) ?>" alt="Preview modal">
                                <?php elseif (!empty($fetched_row['image'])) : ?>
                                    <img src="uploads/<?= htmlspecialchars($fetched_row['image']) ?>" alt="Fallback de card">
                                <?php else : ?><span>Usará la imagen de la card</span><?php endif; ?>
                            </div>
                            <div>
                                <div class="image-file-name" data-file-name-for="image_modal"><?= !empty($fetched_row['image_modal']) ? htmlspecialchars($fetched_row['image_modal']) : 'Usando imagen de la card' ?></div>
                                <input type="file" class="form-control" id="image_modal" name="image_modal" accept="image/*" data-image-input>
                                <span class="speaker-form-help">Si no cargás una imagen específica, se mantiene el fallback a la card.</span>
                                <?php if (!empty($fetched_row['image_modal'])) : ?>
                                    <label class="image-fallback-option" for="use_card_image">
                                        <input type="checkbox" id="use_card_image" name="use_card_image" value="1">
                                        Usar imagen de la card
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="image_company">Logo de empresa</label>
                        <div class="image-field">
                            <div class="image-preview" data-preview-for="image_company">
                                <?php if (!empty($fetched_row['image_company'])) : ?>
                                    <img src="uploads/<?= htmlspecialchars($fetched_row['image_company']) ?>" alt="<?= htmlspecialchars($fetched_row['alt_image_company'] ?? '') ?>">
                                <?php else : ?><span>Sin logo</span><?php endif; ?>
                            </div>
                            <div>
                                <div class="image-file-name" data-file-name-for="image_company"><?= htmlspecialchars($fetched_row['image_company'] ?? '') ?></div>
                                <input type="file" class="form-control" id="image_company" name="image_company" accept="image/*" data-image-input>
                                <label for="alt_image_company">Texto alternativo del logo</label>
                                <input type="text" value="<?= htmlspecialchars($fetched_row['alt_image_company'] ?? '') ?>" class="form-control" id="alt_image_company" name="alt_image_company">
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
                        <input type="text" value="<?= htmlspecialchars($fetched_row['title'] ?? '') ?>" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($fetched_row['description'] ?? '') ?></textarea>
                    </div>
                    <div class="speaker-form-field speaker-form-field--full">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio"><?= htmlspecialchars($fetched_row['bio'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Agenda</h2>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field">
                        <label for="day">Día</label>
                        <select name="day" id="day" class="form-control">
                            <?php for ($day = 1; $day <= 5; $day++) : ?>
                                <option value="<?= $day ?>" <?= ($fetched_row['day'] === (string) $day) ? 'selected' : '' ?>>Día <?= $day ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="speaker-form-field"><label for="time">Hora</label><input type="text" value="<?= htmlspecialchars($fetched_row['time'] ?? '') ?>" class="form-control" id="time" name="time"></div>
                    <div class="speaker-form-field"><label for="orden">Orden</label><input type="text" value="<?= htmlspecialchars($fetched_row['orden'] ?? '') ?>" class="form-control" id="orden" name="orden"></div>
                    <div class="speaker-form-field"><label for="link_time">URL zona horaria</label><input type="text" value="<?= htmlspecialchars($fetched_row['link_time'] ?? '') ?>" class="form-control" id="link_time" name="link_time"></div>
                    <div class="speaker-form-field speaker-form-field--full"><label for="slug">Slug</label><input type="text" value="<?= htmlspecialchars($fetched_row['slug'] ?? '') ?>" class="form-control" id="slug" name="slug"></div>
                </div>
            </section>

            <section class="speaker-form-section">
                <h2>Redes</h2>
                <div class="speaker-form-grid">
                    <div class="speaker-form-field"><label for="sm_linkedin">LinkedIn</label><input type="text" value="<?= htmlspecialchars($fetched_row['sm_linkedin'] ?? '') ?>" class="form-control" id="sm_linkedin" name="sm_linkedin"></div>
                    <div class="speaker-form-field"><label for="sm_instagram">Instagram</label><input type="text" value="<?= htmlspecialchars($fetched_row['sm_instagram'] ?? '') ?>" class="form-control" id="sm_instagram" name="sm_instagram"></div>
                    <div class="speaker-form-field"><label for="sm_facebook">Facebook</label><input type="text" value="<?= htmlspecialchars($fetched_row['sm_facebook'] ?? '') ?>" class="form-control" id="sm_facebook" name="sm_facebook"></div>
                    <div class="speaker-form-field"><label for="sm_twitter">X / Twitter</label><input type="text" value="<?= htmlspecialchars($fetched_row['sm_twitter'] ?? '') ?>" class="form-control" id="sm_twitter" name="sm_twitter"></div>
                </div>
            </section>

            <section class="speaker-form-section">
                <details class="speaker-form-details">
                    <summary>SEO / Video</summary>
                    <div class="speaker-form-grid">
                        <div class="speaker-form-field speaker-form-field--full">
                            <label for="youtube">Zoom (durante) / YouTube (post)</label>
                            <?php if (!empty($fetched_row['youtube'])) : ?>
                                <iframe class="video-preview" src="https://www.youtube.com/embed/<?= htmlspecialchars($fetched_row['youtube']) ?>"></iframe>
                            <?php endif; ?>
                            <input type="text" value="<?= htmlspecialchars($fetched_row['youtube'] ?? '') ?>" class="form-control" id="youtube" name="youtube">
                        </div>
                        <div class="speaker-form-field"><label for="meta_title">Título SEO</label><input type="text" value="<?= htmlspecialchars($fetched_row['meta_title'] ?? '') ?>" class="form-control" id="meta_title" name="meta_title"></div>
                        <div class="speaker-form-field">
                            <label for="meta_image">Imagen SEO / Share</label>
                            <?php if (!empty($fetched_row['meta_image'])) : ?><div class="image-file-name"><?= htmlspecialchars($fetched_row['meta_image']) ?></div><?php endif; ?>
                            <input type="file" class="form-control" id="meta_image" name="meta_image" accept="image/*">
                        </div>
                        <div class="speaker-form-field speaker-form-field--full"><label for="meta_description">Descripción SEO</label><textarea id="meta_description" name="meta_description"><?= htmlspecialchars($fetched_row['meta_description'] ?? '') ?></textarea></div>
                        <div class="speaker-form-field speaker-form-field--full"><label for="meta_twitter">Twitter SEO</label><textarea id="meta_twitter" name="meta_twitter"><?= htmlspecialchars($fetched_row['meta_twitter'] ?? '') ?></textarea></div>
                    </div>
                </details>
            </section>

            <div class="speaker-form-actions">
                <a href="<?= htmlspecialchars($backUrl) ?>">← Volver a speakers</a>
                <div class="speaker-form-actions__buttons">
                    <a class="btn btn-default" href="<?= htmlspecialchars($backUrl) ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit" name="btn-update">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        (function () {
            var useCardImage = document.getElementById('use_card_image');
            var modalImageInput = document.getElementById('image_modal');

            if (useCardImage && modalImageInput) {
                useCardImage.addEventListener('change', function () {
                    modalImageInput.disabled = useCardImage.checked;
                });
            }

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
