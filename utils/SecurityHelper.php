<?php
class SecurityHelper {

    const CAPTCHA_TIME =  86400; // 60*60*24 seconds (24 hours)
    const SUBMISSIONS_TIME = 86400; // 60*60*24 seconds (24 hours)
    const ALLOWED_SUBMISSIONS = 200;

    private static $ip;
    private static $memcached;
    private static $submissionsKey;
    private static $bannedKey;
    private static $enabled;


    public static function init($ipAddress, $enabled) {
        if($ipAddress != '::1' && $ipAddress != '127.0.0.1') {
            self::$memcached = new Memcached();
            self::$memcached->addServer('localhost', 11211);
            self::$ip = $ipAddress;
            self::$enabled = $enabled;
            self::$submissionsKey = 'submissions'.self::$ip;
            self::$bannedKey = 'banned'.self::$ip;
        }
    }

    public static function incrementSubmissions() {
        if(self::$enabled) {

            $submissions = self::$memcached->increment(self::$submissionsKey);
            if (!$submissions) {
                $submissions = 1;
                self::$memcached->set(self::$submissionsKey, $submissions, self::SUBMISSIONS_TIME);
            }
            if ($submissions > self::ALLOWED_SUBMISSIONS) {
                self::$memcached->set(self::$bannedKey, true, self::CAPTCHA_TIME);
            }
        }
    }

    public static function maximumSubmissionsCount() {
        return (self::$enabled) ? self::$memcached->get(self::$bannedKey) : 0;
    }

    public static function isSubmitValid($allowIps) {
        if (in_array(self::$ip, $allowIps) || !self::maximumSubmissionsCount()) {
            self::incrementSubmissions();
            }
            else {
                throw new Exception('SecurityHelper: error submision');
            }
    }
}

?>
