<?php
include_once '../../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$config = new Config();
$config->VPNMiddleware();
$con = $config->getCon();

$sql_query = "SELECT * FROM sponsors  WHERE status = '1' AND sponsor_type = '" . $_GET["sponsorType"] . "' ORDER BY priority_home";
$result_set = mysqli_query($con, $sql_query);
while ($row = mysqli_fetch_array($result_set, MYSQLI_ASSOC)) {
    $fetched_row[] = $row;
}
echo json_encode($fetched_row);
