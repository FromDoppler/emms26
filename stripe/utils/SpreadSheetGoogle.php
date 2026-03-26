<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SpreadSheetGoogle.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');

function saveSubscriptionSpreadSheet($user, $idSpread = ID_SPREADSHEET)
{
    return Logger::withDatabase(
        new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME),
        function($db) use ($idSpread, $user) {
            SpreadSheetGoogle::write($idSpread, $user, $db);
            return true;
        },
        [
            'email' => $user['email'],
            'table' => 'spreadsheet',
            'action' => 'spreadsheet_save',
            'spreadsheet_id' => $idSpread
        ],
        'SPREADSHEET'
    );
}
