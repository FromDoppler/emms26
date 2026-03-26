<?php

function toHex($string) {
    $hex = '';
    foreach (str_split($string) as $char) {
        $hex .= sprintf('%02x', ord($char));
    }
    return $hex;
}
