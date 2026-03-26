<?php
include_once '../config.php';
include_once '../../utils/GeoIp.php';
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);
if (isset($_GET['edit_id'])) {
    $sql_query = "SELECT * FROM speakers WHERE id=" . $_GET['edit_id'];
    $result_set = mysqli_query($con, $sql_query);
    $fetched_row = mysqli_fetch_array($result_set, MYSQLI_ASSOC);
}
if (isset($_POST['btn-update'])) {
    // variables for input data
    $name = $_POST['name'];
    if ($_FILES["image"]["name"] == '') {
        $image =  $fetched_row['image'];
    } else {
        $image =  $_FILES["image"]["name"];
        $file_name = $_FILES["image"]["name"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        if ($file_name != '') {
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
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
    if ($_FILES["image_company"]["name"] == '') {
        $image_company =  $fetched_row['image_company'];
    } else {
        $image_company =  $_FILES["image_company"]["name"];
        $file_name = $_FILES["image_company"]["name"];
        $file_tmp = $_FILES["image_company"]["tmp_name"];
        if ($file_name != '') {
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
        }
    }

    if ($_FILES["meta_image"]["name"] == '') {
        $meta_image =  $fetched_row['meta_image'];
    } else {
        $meta_image =  $_FILES["meta_image"]["name"];
        $file_name = $_FILES["meta_image"]["name"];
        $file_tmp = $_FILES["meta_image"]["tmp_name"];
        if ($file_name != '') {
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
        }
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


    // variables for input data

    // sql query for update data into database
    $sql_query = "UPDATE speakers SET `name`='$name', `image`='$image', `alt_image`='$alt_image', `job`='$job', `sm_twitter`='$sm_twitter', `sm_linkedin`='$sm_linkedin', `sm_instagram`='$sm_instagram', `sm_facebook`='$sm_facebook', `title`='$title', `description`='$description', `bio`='$bio', `image_company`='$image_company', `alt_image_company`='$alt_image_company', `time`='$time', `link_time`='$link_time', `orden`='$orden', `day`='$day', `event`='$event', `exposes`='$exposes', `slug`='$slug', `youtube`='$youtube', `meta_title`='$meta_title', `meta_description`='$meta_description', `meta_twitter`='$meta_twitter', `meta_image`='$meta_image' WHERE id=" . $_GET['edit_id'];
    // sql query for update data into database
    // sql query execution function
    if (mysqli_query($con, $sql_query)) {
?>
        <script type="text/javascript">
            alert('speakers updated successfully');
            window.location.href = 'index.php?token=<?= $_GET['token'] ?>';
        </script>
    <?php
    } else {
    ?>
        <script type="text/javascript">
            alert('error occured while updating data');
        </script>
<?php
    }
    // sql query execution function
}
if (isset($_POST['btn-cancel'])) {
    header("Location: index.php?token=" . $_GET['token']);
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
    <link rel="stylesheet" href="style.css?v=1" type="text/css" />
</head>

<body>
    <center>
        <div id="container">
            <div id="table-responsive">
                <h3>ABM Speakers</h3>
            </div>
        </div>

        <div id="container">

            <div id="table-responsive">
                <form method="post" enctype="multipart/form-data">
                    <table class="table table-striped">
                        <tr>
                            <td align="center"><a href="index.php?token=<?= $_GET['token'] ?>">back to main page</a></td>
                        </tr>
                        <tr>
                            <td>
                                <label for="event" class="form-label">Evento:</label>
                            </td>
                            <td>
                                <select name="event" class="form-select" required>
                                    <option <?= ($fetched_row['event'] === 'ecommerce') ? 'selected' : '' ?> value="ecommerce">Ecommerce</option>
                                    <option <?= ($fetched_row['event'] === 'digital-trends') ? 'selected' : '' ?> value="digital-trends">Digital Trends</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="exposes" class="form-label">Tipo de Exposición:</label>
                            </td>
                            <td>
                                <select name="exposes" class="form-select" required>
                                    <option <?= ($fetched_row['exposes'] === 'conference') ? 'selected' : '' ?> value="conference">Conferencia</option>
                                    <option <?= ($fetched_row['exposes'] === 'workshop') ? 'selected' : '' ?> value="workshop">Workshop</option>
                                    <option <?= ($fetched_row['exposes'] === 'networking') ? 'selected' : '' ?> value="networking">Networking</option>
                                    <option <?= ($fetched_row['exposes'] === 'debate') ? 'selected' : '' ?> value="debate">Mesa de Debate</option>
                                    <option <?= ($fetched_row['exposes'] === 'successStory') ? 'selected' : '' ?> value="successStory">Caso de éxito</option>
                                    <option <?= ($fetched_row['exposes'] === 'interview') ? 'selected' : '' ?> value="interview">Entrevista</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="name" class="form-label">Name:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['name'] ?>" class="form-control" id="name" name="name" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="image" class="form-label">Image:</label>
                            </td>
                            <td>
                                <img src="uploads/<?= $fetched_row['image'] ?>" alt="<?= $fetched_row['alt_image'] ?>" width="150" height="150">
                                <input type="file" value="<?= $fetched_row['image'] ?>" class="form-control" id="image" name="image">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="alt_image" class="form-label">Alt_image:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['alt_image'] ?>" class="form-control" id="alt_image" name="alt_image">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="job" class="form-label">Job:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['job'] ?>" class="form-control" id="job" name="job">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_twitter" class="form-label">Sm_twitter:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['sm_twitter'] ?>" class="form-control" id="sm_twitter" name="sm_twitter">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_linkedin" class="form-label">Sm_linkedin:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['sm_linkedin'] ?>" class="form-control" id="sm_linkedin" name="sm_linkedin">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_instagram" class="form-label">Sm_instagram:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['sm_instagram'] ?>" class="form-control" id="sm_instagram" name="sm_instagram">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_facebook" class="form-label">Sm_facebook:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['sm_facebook'] ?>" class="form-control" id="sm_facebook" name="sm_facebook">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="title" class="form-label">title:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['title'] ?>" class="form-control" id="title" name="title" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="description" class="form-label">Description:</label>
                            </td>
                            <td>
                                <textarea rows="5" class="form-control" id="description" name="description"><?= $fetched_row['description'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="bio" class="form-label">Bio:</label>
                            </td>
                            <td>
                                <textarea rows="5" class="form-control" id="bio" name="bio"><?= $fetched_row['bio'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="image_company" class="form-label">Image_company:</label>
                            </td>
                            <td>
                                <img src="uploads/<?= $fetched_row['image_company'] ?>" alt="<?= $fetched_row['alt_image_company'] ?>" width="70" height="70">
                                <input type="file" value="<?= $fetched_row['image_company'] ?>" class="form-control" id="image_company" name="image_company">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="alt_image_company" class="form-label">Alt_image_company:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['alt_image_company'] ?>" class="form-control" id="alt_image_company" name="alt_image_company">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="time" class="form-label">Time:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['time'] ?>" class="form-control" id="time" name="time">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="link_time" class="form-label">URL Time Zona Horaria:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['link_time'] ?>" class="form-control" id="link_time" name="link_time">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="day" class="form-label">Day:</label>
                            </td>
                            <td>
                                <select name="day" class="form-select">
                                    <option <?= ($fetched_row['day'] === '1') ? 'selected ' : '' ?>value="1">Día 1</option>
                                    <option <?= ($fetched_row['day'] === '2') ? 'selected ' : '' ?>value="2">Día 2</option>
                                    <option <?= ($fetched_row['day'] === '3') ? 'selected ' : '' ?>value="3">Día 3</option>
                                    <option <?= ($fetched_row['day'] === '4') ? 'selected ' : '' ?>value="4">Día 4</option>
                                    <option <?= ($fetched_row['day'] === '5') ? 'selected ' : '' ?>value="5">Día 5</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="orden" class="form-label">Orden:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['orden'] ?>" class="form-control" id="orden" name="orden">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="slug" class="form-label">Slug:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['slug'] ?>" class="form-control" id="slug" name="slug">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="youtube" class="form-label">ZOOM (DURING) / Youtube(POST):</label>
                            </td>
                            <td>
                                <?php
                                if (!empty($fetched_row['youtube'])) {  ?>
                                    <iframe width="420" height="315" src="https://www.youtube.com/embed/<?= $fetched_row['youtube'] ?>">
                                    </iframe>
                                <?php
                                }
                                ?>
                                <input type="text" value="<?= $fetched_row['youtube'] ?>" class="form-control" id="youtube" name="youtube">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_title" class="form-label">Title SEO:</label>
                            </td>
                            <td>
                                <input type="text" value="<?= $fetched_row['meta_title'] ?>" class="form-control" id="meta_title" name="meta_title">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_description" class="form-label">Description SEO:</label>
                            </td>
                            <td>
                                <textarea rows="5" class="form-control" id="meta_description" name="meta_description"><?= $fetched_row['meta_description'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_twitter" class="form-label">Twitter SEO:</label>
                            </td>
                            <td>
                                <textarea rows="5" class="form-control" id="meta_twitter" name="meta_twitter"><?= $fetched_row['meta_twitter'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_image" class="form-label">Image SEO:</label>
                            </td>
                            <td>
                                <?php if ($fetched_row['meta_image']) : ?>
                                    <img src="uploads/<?= $fetched_row['meta_image'] ?>">
                                <?php endif; ?>
                                <input type="file" value="<?= $fetched_row['meta_image'] ?>" class="form-control" id="meta_image" name="meta_image">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <button type="submit" name="btn-update"><strong>UPDATE</strong></button>
                                <button type="submit" name="btn-cancel"><strong>Cancel</strong></button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </center>
</body>

</html>
