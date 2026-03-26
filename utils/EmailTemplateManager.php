<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

class EmailTemplateManager
{
  private const TEMPLATE_DIR = __DIR__ . '/relay-templates/';

  public static function getTemplate($templateType, $templateName, $encodeEmail, $userData = [])
  {
    $isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || substr($_SERVER['HTTP_HOST'], 0, 9) === '127.0.0.1');
    $domain = $_SERVER['HTTP_HOST'];
    $protocol = $isLocalhost ? 'http://' : 'https://';
    $url = $protocol . $domain;

    $templatePath = self::TEMPLATE_DIR . $templateType . '/' . $templateName;

    if (!file_exists($templatePath)) {
      Logger::error("Template file not found", [
        'template_path' => $templatePath,
        'template_type' => $templateType,
        'template_name' => $templateName,
        'template_dir' => self::TEMPLATE_DIR,
        'file_exists' => file_exists($templatePath),
        'dir_exists' => is_dir(self::TEMPLATE_DIR . $templateType),
        'available_files' => is_dir(self::TEMPLATE_DIR . $templateType) ? scandir(self::TEMPLATE_DIR . $templateType) : 'Directory not found'
      ], 'EMAIL_TEMPLATE');
      throw new Exception("Template file not found: {$templatePath}. Type: {$templateType}, Name: {$templateName}");
    }

    $html = file_get_contents($templatePath);
    $html = str_replace(['http://goemms.com', 'https://goemms.com'], $url, $html);

    foreach (array_merge(['$encodeEmail' => $encodeEmail], $userData) as $key => $value) {
      $html = str_replace('{' . $key . '}', $value, $html);
    }

    return $html;
  }

  public static function getTemplateForUser($user)
  {
    $type = $user['type'];
    $phase = processPhaseToShow($type)["phaseToShow"] ?? 'unknown';
    $ticketType = $user['ticketType'] ?? 'undefined';

    $templateMappings = [
      ECOMMERCE => [
        'pre' => 'getEcommerceEmailTemplate',
        'during' => 'getEcommerceEmailTemplateDuring',
        'post' => 'getEcommerceEmailTemplatePost',
      ],
      DIGITALTRENDS => [
        'pre' => 'getDigitalTEmailTemplatePRE',
        'during' => 'getDigitalTEmailTemplateDuring',
        'post' => 'getDigitalTEmailTemplatePost',
      ],
      'ecommerceVipPre' => 'getEcommerceVipTemplatePre',
      'ecommerceVipDuring' => 'getEcommerceVipEmailTemplateDuring',
      'ecommerceVipPost' => 'getEcommerceVipEmailTemplatePost',
      'digitalTrendsVipPre' => 'getDTVipEmailTemplatePre',
      'digitalTrendsVipDuring' => 'getDigitalTrendsVipEmailTemplateDuring',
      'digitalTrendsVipPost' => 'getDigitalTrendsVipEmailTemplatePost',
    ];

    if (isset($templateMappings[$ticketType])) {
      $templateFunction = $templateMappings[$ticketType];
      $html = self::$templateFunction($user['encode_email'], $user);
    } elseif (isset($templateMappings[$type][$phase])) {
      $templateFunction = $templateMappings[$type][$phase];
      $html = self::$templateFunction($user['encode_email']);
    } else {
      Logger::error("Template mapping not found", [
        'user_type' => $type,
        'user_phase' => $phase,
        'ticketType' => $ticketType
      ], 'EMAIL_TEMPLATE');
      throw new Exception("Template mapping not found for type: $type, phase: $phase, ticketType: $ticketType");
    }

    return $html;
  }

  public static function getEcommerceEmailTemplate($encodeEmail)
  {
    $templateName = 'ecommerce-pre-template.html';
    return self::getTemplate('ecommerce', $templateName, $encodeEmail);
  }

  public static function getEcommerceEmailTemplateDuring($encodeEmail)
  {
    $templateName = 'ecommerce-during-template.html';
    return self::getTemplate('ecommerce', $templateName, $encodeEmail);
  }

  public static function getEcommerceEmailTemplatePost($encodeEmail)
  {
    $templateName = 'ecommerce-post-template.html';
    return self::getTemplate('ecommerce', $templateName, $encodeEmail);
  }

  public static function getDigitalTEmailTemplatePREEarlyBirds($encodeEmail)
  {
    $templateName = 'dt-earybirds-template.html';
    return self::getTemplate('dt', $templateName, $encodeEmail);
  }

  public static function getDigitalTEmailTemplatePRE($encodeEmail)
  {
    $userData = self::decodeHexToUserData($encodeEmail);
    $hexEmail = self::encodeToHex($userData['userEmail']);
    $templateName = 'dt-pre-template.html';
    return self::getTemplate('dt', $templateName, $encodeEmail, [
      'userEmail' => $hexEmail
    ]);
  }

  public static function getDigitalTEmailTemplateDuring($encodeEmail)
  {
    $templateName = 'dt-during-template.html';
    return self::getTemplate('dt', $templateName, $encodeEmail);
  }

  public static function getDigitalTEmailTemplatePost($encodeEmail)
  {
    $templateName = 'dt-post-template.html';
    return self::getTemplate('dt', $templateName, $encodeEmail);
  }

  public static function getDigitalTEmailTemplate($encodeEmail)
  {
    $templateName = 'dt-oldtemplate.html';
    return self::getTemplate('dt', $templateName, $encodeEmail);
  }

  public static function getEcommerceVipTemplatePre($encodeEmail, $user)
  {
    $templateName = 'ecommerce-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'type' => $user['ticketType'],
      'paymentMethod' =>  $user['payment_status'],
      'date' => $user['register'],
      'amount' => $user['final_price'],
      'name' => $user['firstname'],
    ];
    return self::getTemplate('ecommerce', $templateName, $encodeEmail, $userData);
  }

  public static function getEcommerceVipEmailTemplateDuring($encodeEmail, $user)
  {
    $templateName = 'ecommerce-during-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'type' => $user['ticketType'],
      'paymentMethod' =>  $user['payment_status'],
      'date' => $user['register'],
      'amount' => $user['final_price'],
    ];
    return self::getTemplate('ecommerce', $templateName, $encodeEmail, $userData);
  }

  public static function getEcommerceVipEmailTemplatePost($encodeEmail, $user)
  {
    $templateName = 'ecommerce-post-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'type' => $user['ticketType'],
      'paymentMethod' =>  $user['payment_status'],
      'date' => $user['register'],
      'amount' => $user['final_price'],
    ];
    return self::getTemplate('ecommerce', $templateName, $encodeEmail, $userData);
  }

  public static function getDTVipEmailTemplatePre($encodeEmail, $user)
  {
    $templateName = 'dt-pre-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'name' => $user['firstname'],
      'type' => $user['ticketType'],
      'paymentMethod' => 'Tarjeta de Crédito',
      'date' => $user['register'],
      'amount' => $user['final_price'],
    ];
    return self::getTemplate('dt', $templateName, $encodeEmail, $userData);
  }

  public static function getDigitalTrendsVipEmailTemplateDuring($encodeEmail, $user)
  {
    $templateName = 'dt-during-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'type' => $user['ticketType'],
      'paymentMethod' =>  $user['payment_status'],
      'date' => $user['register'],
      'amount' => $user['final_price'],
    ];
    return self::getTemplate('dt', $templateName, $encodeEmail, $userData);
  }

  public static function getDigitalTrendsVipEmailTemplatePost($encodeEmail, $user)
  {
    $templateName = 'dt-post-vip-template.html';
    $userData = [
      '$encodeEmail' => $encodeEmail,
      'name' => $user['firstname'],
      'type' => $user['ticketType'],
      'paymentMethod' =>  $user['payment_status'],
      'date' => $user['register'],
      'amount' => $user['final_price'],
    ];
    return self::getTemplate('dt', $templateName, $encodeEmail, $userData);
  }


  public static function getTemplateForSponsor($sponsor)
  {
    $dataKeys = [
      'register' => 'Fecha de Registro',
      'firstname' => 'Nombre',
      'email' => 'Correo Electrónico',
      'company' => 'Empresa',
      'phone' => 'Teléfono',
      'ip' => 'Dirección IP',
      'country_ip' => 'País de la IP',
      'source_utm' => 'UTM Source',
      'medium_utm' => 'UTM Medium',
      'campaign_utm' => 'UTM Campaign',
      'content_utm' => 'UTM Content',
      'term_utm' => 'UTM Term',
      'origin' => 'Origen',
    ];

    $html = "<h3>Información del Sponsor</h3><ul>";

    foreach ($dataKeys as $key => $label) {
      if (isset($sponsor[$key])) {
        $html .= "<li><strong>$label:</strong> " . htmlspecialchars($sponsor[$key]) . "</li>";
      }
    }

    $html .= "</ul>";

    return $html;
  }

  public static function getTemplateForPartner($partner)
  {
    $dataKeys = [
      'register' => 'Fecha de Registro',
      'firstname' => 'Nombre',
      'email' => 'Correo Electrónico',
      'company' => 'Empresa',
      'phone' => 'Teléfono',
      'ip' => 'Dirección IP',
      'country_ip' => 'País de la IP',
      'source_utm' => 'UTM Source',
      'medium_utm' => 'UTM Medium',
      'campaign_utm' => 'UTM Campaign',
      'content_utm' => 'UTM Content',
      'term_utm' => 'UTM Term',
      'origin' => 'Origen',
    ];

    $html = "<h3>Información del Partner</h3><ul>";

    foreach ($dataKeys as $key => $label) {
      if (isset($partner[$key])) {
        $html .= "<li><strong>$label:</strong> " . htmlspecialchars($partner[$key]) . "</li>";
      }
    }

    $html .= "</ul>";

    return $html;
  }



  public static function decodeHexToUserData(string $hexString): array
  {
    $jsonString = hex2bin($hexString);
    if ($jsonString === false) {
      throw new \InvalidArgumentException("Invalid hexadecimal input.");
    }

    $data = json_decode($jsonString, true);
    if (!is_array($data) || !isset($data['userEmail'])) {
      throw new \UnexpectedValueException("Invalid JSON or missing 'userEmail' field.");
    }

    return $data;
  }

  public static function encodeToHex(string $value): string
  {
    return bin2hex($value);
  }
}
