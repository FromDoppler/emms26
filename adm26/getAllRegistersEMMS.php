<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');

$ip = GeoIp::getIp();
if (!in_array($ip, ALLOW_IPS)) {
    echo "No tienes permisos: $ip";
    exit();
}
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$registers = $db->getAllRegistersEMMS();
$today = getdate();
$timestamp_current = $today[0];
$today = date('Y-m-d--h-i-s-A', $timestamp_current);
header("Content-Disposition: attachment; filename=\"emms-$today.xls\"");
header("Content-Type: application/vnd.ms-excel;");
header("Pragma: no-cache");
header("Expires: 0");

$headerTable = ['register', 'phase', 'email', 'firstname', 'ecommerce', 'digital-trends', 'source_utm', 'medium_utm', 'campaign_utm', 'content_utm', 'term_utm'];
$out = fopen("php://output", 'w');
fputcsv($out, $headerTable, "\t");
foreach ($registers as $data) {
    unset($data['id']);
    fputcsv($out, $data, "\t");
}
fclose($out);
exit;
