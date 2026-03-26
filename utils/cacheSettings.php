<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');

$mem_var = new Memcached();
$mem_var->addServer(MEMCACHED_SERVER, 11211);

$settings_phase = $mem_var->get("settings_phase_".ECOMMERCE);
$settings_phase_DT = $mem_var->get("settings_phase_".DIGITALTRENDS);

if (!$settings_phase) {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $settings_phase = $db->getCurrentPhase(ECOMMERCE)[0];
    $db->close();
    $mem_var->set("settings_phase_".ECOMMERCE, $settings_phase, CACHE_TIME);
}

function determineState($event, $settings_phase)
{
    return [
        'isPre' => ($settings_phase['event'] === $event && $settings_phase['pre'] === 1),
        'isLive' => ($settings_phase['event'] === $event && $settings_phase['during'] === 1 && $settings_phase['transition'] === "live-on"),
        'isTransition' => ($settings_phase['event'] === $event && $settings_phase['during'] === 1 && $settings_phase['transition'] === "live-off"),
        'isDuring' => ($settings_phase['event'] === $event && $settings_phase['during'] === 1),
        'isPost' => ($settings_phase['event'] === $event && $settings_phase['post'] === 1),
    ];
}

$ecommerceStates = determineState(ECOMMERCE, $settings_phase);


if (!$settings_phase_DT) {
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $settings_phase_DT = $db->getCurrentPhase(DIGITALTRENDS)[0];
    $db->close();
    $mem_var->set("settings_phase_".DIGITALTRENDS, $settings_phase_DT, CACHE_TIME);
}

$digitalTrendsStates = determineState(DIGITALTRENDS, $settings_phase_DT);
