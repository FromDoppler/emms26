<?php

class EmailValidationException extends InvalidArgumentException
{
    private $reason;
    private $suggestion;

    public function __construct(string $reason, ?string $suggestion = null)
    {
        parent::__construct('Email validation failed: ' . $reason);
        $this->reason = $reason;
        $this->suggestion = $suggestion;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }
}

final class EmailAddressValidator
{
    private static $knownDomainTypos = null;
    private static $domainCache = [];

    public static function validate($email): array
    {
        if (!is_string($email)) {
            return self::invalid('invalid_syntax');
        }

        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return self::invalid('invalid_syntax');
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));
        $knownTypos = self::knownDomainTypos();
        if (isset($knownTypos[$domain])) {
            $suggestedDomain = $knownTypos[$domain];
            $localPart = substr($email, 0, strrpos($email, '@'));
            return self::invalid('known_domain_typo', $localPart . '@' . $suggestedDomain);
        }

        $domainStatus = self::domainCanReceiveEmail($domain);
        if ($domainStatus === false) {
            return self::invalid('invalid_domain');
        }

        return [
            'valid' => true,
            'reason' => $domainStatus === null ? 'dns_unavailable' : null,
            'suggestion' => null,
        ];
    }

    public static function isValid($email): bool
    {
        return self::validate($email)['valid'] === true;
    }

    public static function assertValid($email): string
    {
        $result = self::validate($email);
        if (!$result['valid']) {
            throw new EmailValidationException($result['reason'], $result['suggestion']);
        }

        return trim((string) $email);
    }

    private static function invalid(string $reason, ?string $suggestion = null): array
    {
        return [
            'valid' => false,
            'reason' => $reason,
            'suggestion' => $suggestion,
        ];
    }

    private static function knownDomainTypos(): array
    {
        if (self::$knownDomainTypos !== null) {
            return self::$knownDomainTypos;
        }

        $configPath = dirname(__DIR__) . '/config/email-validation.php';
        $config = is_file($configPath) ? require $configPath : [];
        $knownTypos = isset($config['known_domain_typos']) && is_array($config['known_domain_typos'])
            ? $config['known_domain_typos']
            : [];

        self::$knownDomainTypos = [];
        foreach ($knownTypos as $invalidDomain => $suggestedDomain) {
            $invalidDomain = strtolower(trim((string) $invalidDomain));
            $suggestedDomain = strtolower(trim((string) $suggestedDomain));
            if ($invalidDomain !== '' && $suggestedDomain !== '') {
                self::$knownDomainTypos[$invalidDomain] = $suggestedDomain;
            }
        }

        return self::$knownDomainTypos;
    }

    private static function domainCanReceiveEmail(string $domain): ?bool
    {
        if (array_key_exists($domain, self::$domainCache)) {
            return self::$domainCache[$domain];
        }

        $mxRecords = self::dnsRecords($domain, DNS_MX);
        if ($mxRecords === null) {
            return self::$domainCache[$domain] = null;
        }

        if (self::hasNullMx($mxRecords)) {
            return self::$domainCache[$domain] = false;
        }

        if (!empty($mxRecords)) {
            return self::$domainCache[$domain] = true;
        }

        $aRecords = self::dnsRecords($domain, DNS_A);
        if ($aRecords === null) {
            return self::$domainCache[$domain] = null;
        }
        if (!empty($aRecords)) {
            return self::$domainCache[$domain] = true;
        }

        $aaaaRecords = self::dnsRecords($domain, DNS_AAAA);
        if ($aaaaRecords === null) {
            return self::$domainCache[$domain] = null;
        }

        return self::$domainCache[$domain] = !empty($aaaaRecords);
    }

    private static function hasNullMx(array $records): bool
    {
        foreach ($records as $record) {
            $target = isset($record['target']) ? trim((string) $record['target']) : '';
            if ($target === '.') {
                return true;
            }
        }

        return false;
    }

    private static function dnsRecords(string $domain, int $type): ?array
    {
        try {
            $records = @dns_get_record($domain, $type);
        } catch (Throwable $e) {
            return null;
        }

        return is_array($records) ? $records : null;
    }
}
