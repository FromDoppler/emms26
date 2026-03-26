<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SecurityHelper.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Doppler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Validator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SpreadSheetGoogle.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Relay.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/SubscriptionErrors.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');

date_default_timezone_set('America/Argentina/Buenos_Aires');

function setDataRequest($ip, $countryGeo, $db)
{
  $postData = getPostData();
  $eventsData = processEvents(json_decode($postData['events'], true));

  $firstname = getFirstName($postData, $db);
  $privacy = getPrivacy($postData);
  $promotions = $postData['acceptPromotions'];
  $utmData = getUtmData($postData);
  $type = $postData['type'];
  $phase = getCurrentPhase($type, $db);
  $list = ($type === ECOMMERCE) ? LIST_LANDING_ECOMMERCE : LIST_LANDING_DIGITALT;
  $subject = getSubjectEmail($type, $phase);
  $formOrigin = $postData['formOrigin'];

  $user = buildUserArray($postData, $eventsData, $firstname, $privacy, $promotions, $utmData, $ip, $countryGeo, $type, $phase, $list, $subject, $formOrigin);

  try {
    validateRequest($postData, $privacy, $promotions);
    return $user;
  } catch (Exception $e) {
    processError("setDataRequest (Captura datos)", $e->getMessage(), ['user' => $user]);
  }
}

function getPostData()
{
  $postData = json_decode(file_get_contents('php://input'), true);

  return [
    'name' => isset($postData['name']) ? $postData['name'] : null,
    'email' => isset($postData['email']) ? $postData['email'] : null,
    'encodeEmail' => isset($postData['encodeEmail']) ? $postData['encodeEmail'] : null,
    'phone' => isset($postData['phone']) ? $postData['phone'] : null,
    'company' => isset($postData['company']) ? $postData['company'] : null,
    'emailPlatform' => isset($postData['emailPlatform']) ? $postData['emailPlatform'] : null,
    'jobPosition' => isset($postData['jobPosition']) ? $postData['jobPosition'] : null,
    'website' => isset($postData['website']) ? $postData['website'] : null,
    'acceptPolicies' => isset($postData['acceptPolicies']) ? $postData['acceptPolicies'] : false,
    'acceptPromotions' => isset($postData['acceptPromotions']) ? $postData['acceptPromotions'] : false,
    'utm_source' => isset($postData['utm_source']) ? $postData['utm_source'] : null,
    'utm_medium' => isset($postData['utm_medium']) ? $postData['utm_medium'] : null,
    'utm_campaign' => isset($postData['utm_campaign']) ? $postData['utm_campaign'] : null,
    'utm_content' => isset($postData['utm_content']) ? $postData['utm_content'] : null,
    'utm_term' => isset($postData['utm_term']) ? $postData['utm_term'] : null,
    'origin' => isset($postData['origin']) ? $postData['origin'] : null,
    'emms_ref' => isset($postData['emms_ref']) ? hex2bin($postData['emms_ref']) : null,
    'type' => isset($postData['type']) ? $postData['type'] : null,
    'events' => isset($postData['events']) ? $postData['events'] : '[]',
    'formOrigin' => isset($postData['formOrigin']) ? $postData['formOrigin'] : null,
  ];
}

function processEvents($events)
{
  return [
    'ecommerce' => in_array(ECOMMERCE, (array) $events) ? 1 : 0,
    'digital_trends' => in_array(DIGITALTRENDS, (array) $events) ? 1 : 0
  ];
}

function getFirstName($postData, $db)
{
  $firstname = $postData['name'];

  if ($firstname === null) {
    $results = $db->getUserNameByEmail($postData['email']);
    if (is_array($results) && isset($results[0]['firstname'])) {
      $firstname = $results[0]['firstname'];
    } else {
      $firstname = 'Asistente';
    }
  }

  return $firstname;
}

function getPrivacy($postData)
{
  return $postData['acceptPolicies'] ?? false;
}

function getUtmData($postData)
{
  return [
    'source' => $postData['utm_source'],
    'medium' => $postData['utm_medium'],
    'campaign' => $postData['utm_campaign'],
    'content' => $postData['utm_content'],
    'term' => $postData['utm_term'],
    'emms_ref' => $postData['emms_ref']
  ];
}

function buildUserArray($postData, $eventsData, $firstname, $privacy, $promotions, $utmData, $ip, $countryGeo, $type, $phase, $list, $subject, $formOrigin)
{
  return [
    'register' => date("Y-m-d h:i:s A"),
    'firstname' => $firstname,
    'email' => $postData['email'],
    'phone' => $postData['phone'],
    'company' => $postData['company'],
    'emailPlatform' => $postData['emailPlatform'],
    'jobPosition' => $postData['jobPosition'],
    'website' => $postData['website'],
    'ecommerce' => $eventsData['ecommerce'],
    'digital_trends' => $eventsData['digital_trends'],
    'encode_email' => $postData['encodeEmail'],
    'privacy' => $privacy,
    'promotions' => $promotions,
    'ip' => $ip,
    'country_ip' => $countryGeo,
    'source_utm' => $utmData['source'] ?? null,
    'medium_utm' => $utmData['medium'] ?? null,
    'campaign_utm' => $utmData['campaign'] ?? null,
    'content_utm' => $utmData['content'] ?? null,
    'term_utm' => $utmData['term'] ?? null,
    'emms_ref' => $utmData['emms_ref'] ?? null,
    'origin' => $postData['origin'],
    'type' => $type,
    'form_id' => $phase,
    'list' => $list,
    'subject' => $subject,
    'formOrigin' => $formOrigin
  ];
}

function validateRequest($postData, $privacy, $promotions)
{
  Validator::validateEmail($postData['email']);
  Validator::validateBool('privacy', $privacy);
  Validator::validateBool('promotions', $promotions);
}


function getSubjectEmail($type, $phase)
{
  $subjects = [
    ECOMMERCE => [
      'pre' => SUBJECT_FREE_PRE_ECOMMERCE,
      'during' => SUBJECT_FREE_DURING_ECOMMERCE,
      'post' => SUBJECT_FREE_POST_ECOMMERCE,
    ],
    DIGITALTRENDS => [
      'pre' => SUBJECT_FREE_PRE_DIGITALT,
      'during' => SUBJECT_FREE_DURING_DIGITALT,
      'post' => '¡Te damos la bienvenida al EMMS!',
    ]
  ];

  return $subjects[$type][$phase] ?? '';
}

function getCurrentPhase($type, $db)
{
  try {
    $mem_var = new Memcached();
    $mem_var->addServer(MEMCACHED_SERVER, 11211);
    $settings_phase = $mem_var->get("settings_phase_" . $type);
    if (!$settings_phase) {
      $settings_phase = $db->getCurrentPhase($type)[0];
      $mem_var->set("settings_phase_" . $type, $settings_phase, CACHE_TIME);
    }
    $phaseToShow = array_search(1, $settings_phase);
    return $phaseToShow;
  } catch (Exception $e) {
    processError("getCurrentPhase", $e->getMessage(), ['type' => $type]);
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

function saveSubscriptionDoppler($user)
{
  try {
    Doppler::init(ACCOUNT_DOPPLER, API_KEY_DOPPLER);
    Doppler::subscriber($user);
  } catch (Exception $e) {
    $errorMessage = $e->getMessage();
    if (stripos($errorMessage, "Unsubscribed") !== false) {
      sendDobleOptin($user);
    } else {
      $subscriptionErrors = new SubscriptionErrors();
      $subscriptionErrors->saveSubscriptionErrors($user['email'], $user['list'], $errorMessage);
      processError("saveSubscriptionDoppler (Almacena en Lista)", $errorMessage, ['user' => $user]);
    }
  }
}

function saveSubscriptionDopplerTable($user, $db)
{
  try {
    $db->insertSubscriptionDoppler($user);
    $db->saveRegistered($user);
  } catch (Exception $e) {
    processError("saveSubscriptionDopplerTable (Guarda en la BD subscriptions_doppler and registered)", $e->getMessage(), ['user' => $user]);
  }
}

function saveSubscriptionSpreadSheet($user, $db)
{
  try {
    SpreadSheetGoogle::write(ID_SPREADSHEET, $user, $db);
  } catch (Exception $e) {
    processError("saveSubscriptionSpreadSheet (Guarda en SpreadSheet)", $e->getMessage(), ['user' => $user]);
  }
}

function sendEmail($user, $subject)
{
  try {
    EmailService::sendEmailRegister($user, $subject);
  } catch (Exception $e) {
    processError("sendEmail (Envia mail por Relay)", $e->getMessage(), ['user' => $user, 'subject' => $subject]);
  }
}

function getIp()
{
  try {
    $ip = GeoIp::getIp();
    return $ip;
  } catch (Exception $e) {
    processError("getIp (Obtiene la IP)", $e->getMessage(), []);
  }
}

function getCountryName()
{
  try {
    $countryGeo = GeoIp::getCountryName();
    return $countryGeo;
  } catch (Exception $e) {
    processError("getCountryName (Obtiene el nombre del pais por geoIp de Cloudflare)", $e->getMessage(), []);
  }
}

function isSubmitValid($ip)
{
  try {
    SecurityHelper::init($ip, SECURITYHELPER_ENABLE);
    SecurityHelper::isSubmitValid(ALLOW_IPS);
  } catch (Exception $e) {
    processError("isSubmitValid", $e->getMessage(), ['ip' => $ip]);
  }
}

function processUser($user, $db)
{
  if ($user['formOrigin'] === 'extraDataModal') {
    saveSubscriptionDoppler($user);
    saveSubscriptionDopplerTable($user, $db);
  } else {
    saveSubscriptionDoppler($user);
    saveSubscriptionDopplerTable($user, $db);
    saveSubscriptionSpreadSheet($user, $db);
    sendEmail($user, $user['subject']);
  }
}

try {
  $ip = getIp();
  $countryGeo = getCountryName();
  isSubmitValid($ip);
  $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $user = setDataRequest($ip, $countryGeo, $db);

  $is_new = false;
  try {
    $existing = $db->getUserNameByEmail($user['email']);
    $is_new = !(is_array($existing) && count($existing) > 0);
  } catch (Exception $e) {
    processError("getUserNameByEmail failed: ", $e->getMessage(), []);
  }

  processUser($user, $db);

  $db->close();
  echo json_encode([
    'status' => 'success',
    'message' => 'User registered successfully.',
    'is_new' => $is_new,
    'user' => print_r($user, true)
  ]);
} catch (Exception $e) {
  $errorMessage = "Error in Main Execution: " . $e->getMessage();
  $errorContext = [
    'ip' => $ip,
    'countryGeo' => $countryGeo,
    'user' => isset($user) ? $user : null
  ];
  error_log($errorMessage . ' | Context: ' . json_encode($errorContext));

  echo json_encode(['status' => 'error', 'message' => 'User registered error.']);
}
