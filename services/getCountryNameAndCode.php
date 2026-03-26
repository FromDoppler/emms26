<?php
require_once('../utils/GeoIp.php');

$countryGeoNameAndCode = GeoIp::getGeoLocalitationCountryNameAndCode();

echo json_encode($countryGeoNameAndCode);
