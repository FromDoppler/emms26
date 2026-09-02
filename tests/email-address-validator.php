<?php

require_once __DIR__ . '/../utils/EmailAddressValidator.php';

function failTest(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        failTest(
            $message
            . ' | expected=' . var_export($expected, true)
            . ' actual=' . var_export($actual, true)
        );
    }
}

function fakeDnsResolver(array $responses): callable
{
    return static function (string $domain, int $type) use ($responses): ?array {
        $key = $domain . ':' . $type;
        return array_key_exists($key, $responses) ? $responses[$key] : [];
    };
}

function validateWithDns(string $email, array $responses): array
{
    $validator = new EmailAddressValidator(fakeDnsResolver($responses));
    return $validator->validate($email);
}

$result = validateWithDns('invalid-email', []);
assertSameValue(false, $result['valid'], 'Invalid syntax should be rejected');
assertSameValue('invalid_syntax', $result['reason'], 'Invalid syntax should expose its reason');

$result = validateWithDns(' user@gmail.com ', []);
assertSameValue(false, $result['valid'], 'Whitespace must not be normalized implicitly');
assertSameValue('invalid_syntax', $result['reason'], 'Whitespace must preserve legacy validation semantics');

$result = validateWithDns('user@gamil.com', []);
assertSameValue(false, $result['valid'], 'Known typo should be rejected');
assertSameValue('known_domain_typo', $result['reason'], 'Known typo should expose its reason');
assertSameValue('user@gmail.com', $result['suggestion'], 'Known typo should expose the suggested email');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => [['target' => 'mx.example.test']],
]);
assertSameValue(true, $result['valid'], 'MX domain should be accepted');
assertSameValue(null, $result['reason'], 'MX domain should not expose a warning reason');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => [],
    'example.test:' . DNS_A => [['ip' => '203.0.113.10']],
]);
assertSameValue(true, $result['valid'], 'A record should be accepted when MX is absent');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => [],
    'example.test:' . DNS_A => [],
    'example.test:' . DNS_AAAA => [['ipv6' => '2001:db8::1']],
]);
assertSameValue(true, $result['valid'], 'AAAA record should be accepted when MX and A are absent');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => [['target' => '.']],
]);
assertSameValue(false, $result['valid'], 'Null MX domain should be rejected');
assertSameValue('invalid_domain', $result['reason'], 'Null MX should be an invalid domain');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => [],
    'example.test:' . DNS_A => [],
    'example.test:' . DNS_AAAA => [],
]);
assertSameValue(false, $result['valid'], 'Domain without MX, A or AAAA should be rejected');
assertSameValue('invalid_domain', $result['reason'], 'Domain without records should be invalid');

$result = validateWithDns('user@example.test', [
    'example.test:' . DNS_MX => null,
]);
assertSameValue(true, $result['valid'], 'Resolver failure should fail open');
assertSameValue('dns_unavailable', $result['reason'], 'Resolver failure should be observable');

fwrite(STDOUT, 'EmailAddressValidator tests passed' . PHP_EOL);
