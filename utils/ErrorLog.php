<?php

class ErrorLog {
    private const debugger = 0;

    public static function log($from, $error, $data)
    {

        $text = "\n\tMetodo: " . $from . "\n\tDescripcion: " . $error . "\n\tData: " . json_encode($data) . "\n";
        error_log($text);
        if(self::debugger)
            print_r($text);
    }
}
