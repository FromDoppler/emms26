<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
$ip = GeoIp::getIp();
if (!in_array($ip, ALLOW_IPS)) {
    echo "$ip No tienes permisos";
    exit();
}
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$errors = $db->getLogErrors();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">


    <title>Log errors</title>
    <link href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="row">
        <h2>Last 100 errors</h2><br /></br>
    </div>
    <div class="container-xxl d-flex align-items-md-center">

        <?php if ($errors) : ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Date</th>
                        <th scope="col">Function</th>
                        <th scope="col">Description</th>
                        <th scope="col">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    foreach ($errors as $error) : ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td style="width: 200px;"><?= $error['date'] ?></td>
                            <td style="width: 300px;"><?= $error['function_name'] ?></td>
                            <td><?= $error['description'] ?></td>
                            <td><?php print_r(json_decode($error['data'])) ?></td>

                        </tr>
                    <?php endforeach ?>

                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>
