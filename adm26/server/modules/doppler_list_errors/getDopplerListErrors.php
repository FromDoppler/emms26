<?php
include_once '../../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$config = new Config();
$config->VPNMiddleware();
$con = $config->getCon();

$sql_query = "SELECT
    `id`,
    `email`,
    `list`,
    `reason`,
    `error_code`,
    DATE_FORMAT(MAX(`created_at`), '%d-%m-%Y %H:%i') AS created_at
FROM
    `subscription_doppler_list_errors`
GROUP BY
    `email`;
";
//$sql_query = "SELECT * FROM subscription_doppler_list_errors ORDER BY created_at DESC";
$result_set = mysqli_query($con, $sql_query);
while ($row = mysqli_fetch_array($result_set, MYSQLI_ASSOC)) {
    $fetched_row[] = $row;
}
echo json_encode($fetched_row);
