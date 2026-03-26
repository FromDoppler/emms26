<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT']  . '/utils/DB.php');

$ip = GeoIp::getIp();
if (!in_array($ip, ALLOW_IPS)) {
    echo $ip . "<br>";
    print_r(ALLOW_IPS);
    echo "<br>No tienes permisos";
    exit();
}
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$subscriptions = $db->getSubscriptionsDoppler();
$db->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">


    <title>Subscriptions Doppler</title>
    <link href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="row">
        <h2>Last 100 subscriptions</h2><br /></br>
    </div>
    <div class="container-xxl d-flex align-items-md-center">

        <?php if ($subscriptions) : ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Date</th>
                        <th scope="col">email</th>
                        <th scope="col">form_id</th>
                        <th scope="col">list</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    foreach ($subscriptions as $subs) : ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td style="width: 200px;"><?= $subs['register'] ?></td>
                            <td style="width: 300px;"><?= $subs['email'] ?></td>
                            <td><?= $subs['form_id'] ?></td>
                            <td><?= $subs['list'] ?></td>

                        </tr>
                    <?php endforeach ?>

                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>
