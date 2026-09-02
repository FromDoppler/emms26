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
    private const KNOWN_DOMAIN_TYPOS = [
        'gamil.com' => 'gmail.com',
        'gmail.con' => 'gmail.com',
        'homail.com' => 'hotmail.com',
    ];

    public static function validate($email): array
    {
        if (!is_string($email) || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return self::invalid('invalid_syntax');
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));
        if (isset(self::KNOWN_DOMAIN_TYPOS[$domain])) {
            $localPart = substr($email, 0, strrpos($email, '@'));
            return self::invalid(
                'known_domain_typo',
                $localPart . '@' . self::KNOWN_DOMAIN_TYPOS[$domain]
            );
        }

        return [
            'valid' => true,
            'reason' => null,
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
}
