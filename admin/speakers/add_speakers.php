<?php
include_once '../config.php';
include_once '../../utils/GeoIp.php';
$ip = GeoIp::getIp();
isIPAllow($ip, $ALLOW_IPS);

if (isset($_POST['btn-save'])) {
    // variables for input data
    $name = $_POST['name'];
    $image =  $_FILES["image"]["name"];
    $file_name = $_FILES["image"]["name"];
    $file_tmp = $_FILES["image"]["tmp_name"];
    if ($file_name != '') {
        move_uploaded_file($file_tmp, "uploads/" . $file_name);
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
    $image_company =  $_FILES["image_company"]["name"];
    $file_name = $_FILES["image_company"]["name"];
    $file_tmp = $_FILES["image_company"]["tmp_name"];
    if ($file_name != '') {
        move_uploaded_file($file_tmp, "uploads/" . $file_name);
    }
    $alt_image_company = $_POST['alt_image_company'];
    $time = $_POST['time'];
    $link_time = $_POST['link_time'];
    $orden = $_POST['orden'];
    $day = $_POST['day'];
    $event = $_POST['event'];
    $exposes = $_POST['exposes'];
    $slug = $_POST['slug'];
    $youtube = $_POST['youtube'];
    $meta_title = $_POST['meta_title'];
    $meta_description = $_POST['meta_description'];
    $meta_twitter = $_POST['meta_twitter'];
    $meta_image =  $_FILES["meta_image"]["name"];
    $file_name = $_FILES["meta_image"]["name"];
    $file_tmp = $_FILES["meta_image"]["tmp_name"];
    if ($file_name != '') {
        move_uploaded_file($file_tmp, "uploads/" . $file_name);
    }

    // variables for input data

    // sql query for inserting data into database

    $sql_query = "INSERT INTO speakers (`name`,`image`,`alt_image`,`job`,`sm_twitter`,`sm_linkedin`,`sm_instagram`,`sm_facebook`,`title`,`description`,`bio`,`image_company`,`alt_image_company`,`time`,`link_time`,`orden`,`day`,`event`,`exposes`,`slug`,`youtube`,`meta_title`,`meta_description`,`meta_twitter`,`meta_image`) VALUES('" . $name . "','" . $image . "','" . $alt_image . "','" . $job . "','" . $sm_twitter . "','" . $sm_linkedin . "','" . $sm_instagram . "','" . $sm_facebook . "','" . $title . "','" . $description . "','" . $bio . "','" . $image_company . "','" . $alt_image_company . "','" . $time . "','" . $link_time . "','" . $orden . "','" . $day . "' ,'" . $event . "' ,'" . $exposes . "' ,'" . $slug . "','" . $youtube . "','" . $meta_title . "','" . $meta_description . "','" . $meta_twitter . "','" . $meta_image . "')";
    // sql query for inserting data into database

    // sql query execution function
    if (mysqli_query($con, $sql_query)) {
        @header("Location: /admin/speakers/index.php?token=" . $_GET['token']);
    } else {
?>
        <script type="text/javascript">
            alert('error occured while inserting your data');
        </script>
<?php
    }
    // sql query execution function
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
                <h3>Alta Speakers</h3>
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
                                    <option value="" disabled selected>Seleccione un tipo de evento</option>
                                    <option value="ecommerce">Ecommerce</option>
                                    <option value="digital-trends">Digital Trends</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="exposes" class="form-label">Tipo de Exposición:</label>
                            </td>
                            <td>
                                <select name="exposes" class="form-select" required>
                                    <option value="" disabled selected>Seleccione el tipo de speaker</option>
                                    <option value="conference">Conferencia</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="networking">Networking</option>
                                    <option value="debate">Mesa de debate</option>
                                    <option value="successStory">Caso de éxito</option>
                                    <option value="interview">Entrevista</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="name" class="form-label">Name:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Name">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="image" class="form-label">Image:</label>
                            </td>
                            <td>
                                <input type="file" class="form-control" id="image" name="image" placeholder="Image">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="alt_image" class="form-label">Alt_image:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="alt_image" name="alt_image" placeholder="Alt_image">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="job" class="form-label">Job:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="job" name="job" placeholder="Job">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_twitter" class="form-label">Sm_twitter: <br><small><em>ej: https://twitter.com/fromDoppler</em></small></label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="sm_twitter" name="sm_twitter" placeholder="Sm_twitter">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_linkedin" class="form-label">Sm_linkedin: <br><small><em>ej: https://www.linkedin.com/company/doppler/mycompany/</em></small></label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="sm_linkedin" name="sm_linkedin" placeholder="Sm_linkedin">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_instagram" class="form-label">Sm_instagram: <br><small><em>ej: https://www.instagram.com/fromdoppler/</em></small></label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="sm_instagram" name="sm_instagram" placeholder="Sm_instagram">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="sm_facebook" class="form-label">Sm_facebook: <br><small><em>ej: https://www.facebook.com/DopplerEmailMarketing</em></small></label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="sm_facebook" name="sm_facebook" placeholder="Sm_facebook">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="title" class="form-label">Title:</label>
                            </td>
                            <td>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Titulo" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="description" class="form-label">Description:</label>
                            </td>
                            <td>
                                <textarea rows="5" id="description" name="description"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="bio" class="form-label">Bio:</label>
                            </td>
                            <td>
                                <textarea rows="5" id="bio" name="bio"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="image_company" class="form-label">Image_company:</label>
                            </td>
                            <td>
                                <input type="file" class="form-control" id="image_company" name="image_company" placeholder="Image_company">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="alt_image_company" class="form-label">Alt_image_company:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="alt_image_company" name="alt_image_company" placeholder="Alt_image_company">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="time" class="form-label">Time:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="time" name="time" placeholder="Time">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="link_time" class="form-label">URL Time Zona Horaria:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="link_time" name="link_time" placeholder="URL Time Zona Horaria">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="day" class="form-label">Day:</label>
                            </td>
                            <td>
                                <select name="day" class="form-select">
                                    <option value="1" selected>Día 1</option>
                                    <option value="2">Día 2</option>
                                    <option value="3">Día 3</option>
                                    <option value="4">Día 4</option>
                                    <option value="5">Día 5</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="orden" class="form-label">Orden:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="orden" name="orden" placeholder="Orden">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="slug" class="form-label">Slug:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="slug" name="slug" placeholder="Slug">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="youtube" class="form-label">ZOOM (DURING) / Youtube(POST):</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="youtube" name="youtube" placeholder="Youtube">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_title" class="form-label">SEO Title:</label>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" placeholder="SEO Title">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_description" class="form-label">SEO Description:</label>
                            </td>
                            <td>
                                <textarea rows="5" id="meta_description" name="meta_description"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_twitter" class="form-label">SEO Twitter:</label>
                            </td>
                            <td>
                                <textarea rows="5" id="meta_twitter" name="meta_twitter"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="meta_image" class="form-label">Image Share:</label>
                            </td>
                            <td>
                                <input type="file" class="form-control" id="meta_image" name="meta_image" placeholder="Image Share">
                            </td>
                        </tr>

                        <tr>
                            <td><button type="submit" name="btn-save"><strong>SAVE</strong></button></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

    </center>
</body>

</html>
