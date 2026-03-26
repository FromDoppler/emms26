<?php
include_once '../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$config = new Config();
$config->VPNMiddleware();
$con = $config->getCon();

foreach ($_FILES as $file) {
    print_r($file);
    move_uploaded_file($file["tmp_name"], "uploads/" . $file["name"]);
};

$body = json_decode($_POST["str"], true);
$mapped = array_map(
    function ($k, $v) {
        return "`" . $k . "` = '" . $v . "'";
    },
    array_keys($body),
    array_values($body)
);

if (isset($body["sponsor_id"])) {
    $sql_query = "UPDATE sponsors SET " . implode(", ", $mapped) . " WHERE sponsor_id=" . $body["sponsor_id"];
} else {
    $sql_query = "INSERT INTO `sponsors` SET " . implode(", ", $mapped);
}

echo $sql_query;

if (mysqli_query($con, $sql_query))
    echo json_encode("success");
