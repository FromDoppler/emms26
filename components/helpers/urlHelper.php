<?php

function getNormalizeUrl()
{
    $url = $_SERVER['REQUEST_URI'];
    $normalizedUrl = rtrim(parse_url($url, PHP_URL_PATH), '/');
    return $normalizedUrl;
}
