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
        if (isset(self::KNOWN_DOMAIN_TYPOS[$domain])) {
            $localPart = substr($email, 0, strrpos($email, '@'));
            throw new EmailValidationException(
                'known_domain_typo',
                $localPart . '@' . self::KNOWN_DOMAIN_TYPOS[$domain]
            );
        }

        return $email;
    }
}
