<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Doppler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Validator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SubscriptionErrors.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');

date_default_timezone_set('America/Argentina/Buenos_Aires');

function isSubmitValid($ip)
{
    try {
        SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
        SecurityHelper::isSubmitValid(ALLOW_IPS);
    } catch (Exception $e) {
        processError("isSubmitValid", $e->getMessage(), ['ip' => $ip]);
        exit('submits');
    }
}

function getFieldValue($field, $default = null)
{
    $_POST = json_decode(file_get_contents('php://input'), true);
    return isset($_POST[$field]) ? $_POST[$field] : $default;
}


function setUserDataRequest($ip, $countryGeo)
{
    $firstname = getFieldValue('name');
    $email = getFieldValue('email');
    $company     =  getFieldValue('company');
    $phone     = getFieldValue('phone');
    $privacy = getFieldValue('acceptPolicies', false);
    $promotions = getFieldValue('acceptPromotions', false);
    $source_utm = getFieldValue('utm_source');
    $medium_utm = getFieldValue('utm_medium');
    $campaign_utm = getFieldValue('utm_campaign');
    $content_utm = getFieldValue('utm_content');
    $term_utm = getFieldValue('utm_term');
    $origin = getFieldValue('origin');
    $dataType = getFieldValue('dataType');
    $list = ($dataType === 'sponsor') ? LIST_SPONSORS : LIST_MEDIA_PARTNERS;
    $user = array(
        'register' => date("Y-m-d h:i:s A"),
        'firstname' => $firstname,
        'email' => $email,
        'company' =>  $company,
        'phone' =>  $phone,
        'privacy' => $privacy,
        'promotions' => $promotions,
        'ip' => $ip,
        'country_ip' => $countryGeo,
        'source_utm' => $source_utm,
        'medium_utm' => $medium_utm,
        'campaign_utm' => $campaign_utm,
        'content_utm' => $content_utm,
        'term_utm' => $term_utm,
        'origin' => $origin,
        'dataType' => $dataType,
        'list' => $list,
    );
    try {
        Validator::validateEmail($email);
        Validator::validateBool('privacy', $privacy);
        Validator::validateBool('promotions', $promotions);
        return $user;
    } catch (Exception $e) {
        processError("setDataRequest (Captura datos)", $e->getMessage(), ['user' => $user]);
        die();
    }
}

function saveSubscriptionDoppler($user)
{

    try {
        Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
        Doppler::subscriber($user);
        return 200;
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        if (stripos($errorMessage, "Unsubscribed") !== false) {
            sendDobleOptin($user);
        } else {
            $subscriptionErrors = new SubscriptionErrors();
            $subscriptionErrors->saveSubscriptionErrors($user['email'], $user['list'], $errorMessage);
            processError("saveSubscriptionDoppler (Almacena en Lista)", $errorMessage, ['user' => $user]);
        }
        return 400;
    }
}

function sendDobleOptin($user)
{
    try {
        Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
        Doppler::dobleOptin($user);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        processError("saveSubscriptionDoppler (Almacena en Lista)", $errorMessage, ['user' => $user]);
    }
}

function sendEmailSponsor($sponsor)
{
    try {
        EmailService::sendEmailSponsor($sponsor);
    } catch (Exception $e) {
        processError("sendEmail (Envia mail por Relay)", $e->getMessage(), ['sponsor' => $sponsor]);
    }
}

function sendEmailPartner($partner)
{
    try {
        EmailService::sendEmailpartner($partner);
    } catch (Exception $e) {
        processError("sendEmail (Envia mail por Relay)", $e->getMessage(), ['partner' => $partner]);
    }
}

function sendEmail($user)
{
    switch ($user['dataType']) {
        case 'sponsor':
            sendEmailSponsor($user);
            break;
        case 'mediaPartner':
            sendEmailPartner($user);
            break;
        default:
            error_log("Tipo de usuario desconocido: " . $user['dataType']);
    }
}


$ip = GeoIp::getIp();
$countryGeo =  GeoIp::getCountryName();
isSubmitValid($ip);
$user = setUserDataRequest($ip, $countryGeo);
$response = saveSubscriptionDoppler($user);
sendEmail($user);

echo json_encode($response);
