<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/app-config.php');

// Si no estamos en la etapa post, redireccionar al index
if ($currentPhase !== 'post') {
    header('Location: /');
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/pages/digital-trends/post/speaker-interna.php");
?>
