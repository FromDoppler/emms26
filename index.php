<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/app-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/pages/{$currentEvent['folder']}/{$currentPhase}/{$currentEvent['sharedPages']['home']['unregistered']['page']}");
?>
