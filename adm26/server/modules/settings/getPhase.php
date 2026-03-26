<?php
include_once '../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $config = new Config();
    $config->VPNMiddleware();
    $con = $config->getCon();

    $sql_query = "SELECT * from settings_phase WHERE event='".$_GET['event']."' AND 1=1";
    $result_set = mysqli_query($con, $sql_query);
    while ($row = mysqli_fetch_array($result_set, MYSQLI_ASSOC)) {
        $fetched_row[] = $row;
    }
    $result['current_phase'] = array_search(1, $fetched_row[0]);
    $result['transition'] = $fetched_row[0]['transition'];
    $result['transmission'] = $fetched_row[0]['transmission'];
    echo json_encode($result);
} catch (Exception $e) {
    processError("getPhase", $e->getMessage(), []);
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit();
}
