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

    private $dnsLookup;

    public function __construct(?callable $dnsLookup = null)
    {
        $this->dnsLookup = $dnsLookup ?: static function (string $domain, int $type): ?array {
            return self::dnsRecords($domain, $type);
        };
    }

    public function validate($email): array
    {
        if (!is_string($email)) {
            return self::invalid('invalid_syntax');
        }

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

        $domainStatus = $this->domainCanReceiveEmail($domain);
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
        return (new self())->validate($email)['valid'] === true;
    }

    public static function assertValid($email): string
    {
        $result = (new self())->validate($email);
        if (!$result['valid']) {
            throw new EmailValidationException($result['reason'], $result['suggestion']);
        }

        return (string) $email;
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

    private function domainCanReceiveEmail(string $domain): ?bool
    {
        $mxRecords = ($this->dnsLookup)($domain, DNS_MX);
        if ($mxRecords === null) {
            return null;
        }

        if (self::hasNullMx($mxRecords)) {
            return false;
        }

        if (!empty($mxRecords)) {
            return true;
        }

        $aRecords = ($this->dnsLookup)($domain, DNS_A);
        if ($aRecords === null) {
            return null;
        }
        if (!empty($aRecords)) {
            return true;
        }

        $aaaaRecords = ($this->dnsLookup)($domain, DNS_AAAA);
        if ($aaaaRecords === null) {
            return null;
        }

        return !empty($aaaaRecords);
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
        $dnsError = false;
        set_error_handler(function () use (&$dnsError) {
            $dnsError = true;
            return true;
        });

        try {
            $records = dns_get_record($domain, $type);
        } catch (Throwable $e) {
            return null;
        } finally {
            restore_error_handler();
        }

        if ($records === false) {
            return $dnsError ? null : [];
        }

        return is_array($records) ? $records : null;
    }
}
