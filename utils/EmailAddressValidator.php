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
    private const COMMON_EMAIL_DOMAINS = [
        'gmail.com',
        'hotmail.com',
        'outlook.com',
        'yahoo.com',
        'icloud.com',
        'live.com',
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
        $suggestedDomain = self::findLikelyDomainTypo($domain);

        if ($suggestedDomain !== null) {
            $localPart = substr($email, 0, strrpos($email, '@'));
            throw new EmailValidationException(
                'known_domain_typo',
                $localPart . '@' . $suggestedDomain
            );
        }

        return $email;
    }

    private static function findLikelyDomainTypo(string $domain): ?string
    {
        if (in_array($domain, self::COMMON_EMAIL_DOMAINS, true)) {
            return null;
        }

        $suggestions = [];
        foreach (self::COMMON_EMAIL_DOMAINS as $knownDomain) {
            // Keep the heuristic deliberately conservative. Different first
            // characters are common across legitimate domains (for example
            // mail.com vs gmail.com) and should not be treated as typos.
            if ($domain[0] !== $knownDomain[0]) {
                continue;
            }

            if (self::isSingleEditOrAdjacentTransposition($domain, $knownDomain)) {
                $suggestions[] = $knownDomain;
            }
        }

        return count($suggestions) === 1 ? $suggestions[0] : null;
    }

    private static function isSingleEditOrAdjacentTransposition(string $candidate, string $expected): bool
    {
        $candidateLength = strlen($candidate);
        $expectedLength = strlen($expected);
        $lengthDifference = $candidateLength - $expectedLength;

        if (abs($lengthDifference) > 1) {
            return false;
        }

        if ($lengthDifference === 0) {
            $mismatches = [];
            for ($i = 0; $i < $candidateLength; $i++) {
                if ($candidate[$i] === $expected[$i]) {
                    continue;
                }

                $mismatches[] = $i;
                if (count($mismatches) > 2) {
                    return false;
                }
            }

            if (count($mismatches) === 1) {
                return true;
            }

            if (count($mismatches) !== 2) {
                return false;
            }

            [$first, $second] = $mismatches;
            return $second === $first + 1
                && $candidate[$first] === $expected[$second]
                && $candidate[$second] === $expected[$first];
        }

        $shorter = $lengthDifference < 0 ? $candidate : $expected;
        $longer = $lengthDifference < 0 ? $expected : $candidate;
        $shorterIndex = 0;
        $longerIndex = 0;
        $skippedCharacter = false;

        while ($shorterIndex < strlen($shorter) && $longerIndex < strlen($longer)) {
            if ($shorter[$shorterIndex] === $longer[$longerIndex]) {
                $shorterIndex++;
                $longerIndex++;
                continue;
            }

            if ($skippedCharacter) {
                return false;
            }

            $skippedCharacter = true;
            $longerIndex++;
        }

        return true;
    }
}
