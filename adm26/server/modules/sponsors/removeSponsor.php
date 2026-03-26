<?php
include_once '../../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$config = new Config();
$config->VPNMiddleware();
$con = $config->getCon();

if (isset($_POST['sponsorId'])) {
    $sql_query = "DELETE FROM sponsors WHERE id=" . $_POST['sponsorId'];
    $sql_query = "UPDATE sponsors SET `status`='0' WHERE sponsor_id=" . $_POST['sponsorId'];
    mysqli_query($con, $sql_query);
    echo json_encode($sql_query);
} else {
    echo "error";
}
