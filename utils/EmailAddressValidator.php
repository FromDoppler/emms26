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
    private const KNOWN_TLD_TYPOS = [
        'con' => 'com',
    ];

    private const KNOWN_DOMAIN_TYPOS = [
        'gamil.com' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gmial.com' => 'gmail.com',
        'gnail.com' => 'gmail.com',
        'gmil.com' => 'gmail.com',
        'gmsil.com' => 'gmail.com',
        'gmal.com' => 'gmail.com',
        'gmaill.com' => 'gmail.com',
        'gmail.comm' => 'gmail.com',
        'hormail.com' => 'hotmail.com',
        'homail.com' => 'hotmail.com',
        'hotmsil.com' => 'hotmail.com',
        'htomail.com' => 'hotmail.com',
        'hotmai.com' => 'hotmail.com',
        'hotmail.cim' => 'hotmail.com',
        'outook.com' => 'outlook.com',
    ];

    public static function isValid($email): bool
    {
        try {
            self::assertValid($email);
            return true;
        } catch (EmailValidationException $e) {
            return false;
        }
    }

    public static function assertValid($email): string
    {
        if (!is_string($email) || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailValidationException('invalid_syntax');
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));
        $suggestedDomain = self::findSuggestedDomain($domain);

        if ($suggestedDomain !== null) {
            $localPart = substr($email, 0, strrpos($email, '@'));
            throw new EmailValidationException(
                'known_domain_typo',
                $localPart . '@' . $suggestedDomain
            );
        }

        return $email;
    }

    private static function findSuggestedDomain(string $domain): ?string
    {
        $candidateDomain = self::correctKnownTldTypo($domain) ?? $domain;

        if (isset(self::KNOWN_DOMAIN_TYPOS[$candidateDomain])) {
            return self::KNOWN_DOMAIN_TYPOS[$candidateDomain];
        }

        return $candidateDomain !== $domain ? $candidateDomain : null;
    }

    private static function correctKnownTldTypo(string $domain): ?string
    {
        $lastDotPosition = strrpos($domain, '.');
        if ($lastDotPosition === false) {
            return null;
        }

        $tld = substr($domain, $lastDotPosition + 1);
        if (!isset(self::KNOWN_TLD_TYPOS[$tld])) {
            return null;
        }

        return substr($domain, 0, $lastDotPosition + 1) . self::KNOWN_TLD_TYPOS[$tld];
    }
}
