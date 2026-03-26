<?php


$DB_HOST = 'db';
$DB_USER = 'root';
$DB_PASSWORD = '';
$DB_NAME = 'EMMS26';
$ALLOW_IPS = array('::1', '200.5.229.58', '200.5.253.210', '127.0.0.1', '172.19.0.1');

$con=mysqli_connect($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_NAME);

function isIPAllow($ip, $ALLOW_IPS) {
    if($_GET['token']!=='E1111522N37r0') {
        echo "No deberias estar aqui! no tienes el Token";
        exit;
    }
/*     if(!in_array($ip, $ALLOW_IPS)){
        echo "No tienes IP valida";
        exit;
    }  */


}

?>
