<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/ErrorLog.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');



function processError($functionName, $descriptionError, $data)
{
    ErrorLog::log($functionName, $descriptionError, $data);
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $date = date("Y-m-d h:i:s A");
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->insertLogErrors($date, $functionName, addslashes($descriptionError), addslashes(json_encode($data)));
    $db->close();
}

function processPhaseToShow($event)
{
    $mem_var = new Memcached();
    $mem_var->addServer(MEMCACHED_SERVER, 11211);
    $settings_phase = $mem_var->get("settings_phase_".$event);

    if (!$settings_phase)
    {
        $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $settings_phase = $db->getCurrentPhase($event)[0];
        $db->close();
        $mem_var->set("settings_phase_".$event, $settings_phase, CACHE_TIME);
    }
    $phaseToShow =  array_search(1, $settings_phase);
    return array('phaseToShow' => $phaseToShow);
}

function getTransition($event) {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $phase = $db->getCurrentPhase($event)[0];
    return $phase['transition'];
}
