<?php

declare(strict_types=1);

// Research-only Q08/Q12 probe. Expected answers come from the linked sources
// in README.md; this file does not implement any hash or standard codec.
function check(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: $label");
    }
}

function expectDigest(string $label, string $algorithm, string $input, string $expected): array
{
    $binary = hash($algorithm, $input, true);
    check(strlen($binary) === strlen($expected) / 2, "$label binary length");
    check(bin2hex($binary) === $expected, "$label published answer");
    check(hash($algorithm, $input) === $expected, "$label hex answer");
    return ['bytes' => strlen($binary), 'hex' => $expected];
}

function expectHmac(string $label, string $key, string $data, string $expected): array
{
    $binary = hash_hmac('sha512', $data, $key, true);
    check(strlen($binary) === 64, "$label binary length");
    check(bin2hex($binary) === $expected, "$label RFC answer");
    check(hash_hmac('sha512', $data, $key) === $expected, "$label hex answer");
    check(hash_hmac('sha512', $key, $data, true) !== $binary, "$label argument order");
    return ['key_bytes' => strlen($key), 'data_bytes' => strlen($data), 'hex' => $expected];
}

function ownedRuntimeCall(Closure $operation, int $bytes): string
{
    try {
        $result = $operation();
    } catch (Throwable) {
        // A public exception must not expose unsanitized vendor details through
        // getPrevious(), even when its own message is safe.
        throw new RuntimeException('provider_environment_failure');
    }
    if (!is_string($result) || strlen($result) !== $bytes) {
        throw new RuntimeException('provider_environment_failure');
    }
    return $result;
}

function expectRuntimeFailure(string $label, Closure $operation): string
{
    try {
        ownedRuntimeCall($operation, 32);
    } catch (RuntimeException $error) {
        check($error->getMessage() === 'provider_environment_failure', "$label category");
        check($error->getPrevious() === null, "$label previous exception containment");
        check(!str_contains($error->getMessage(), 'secret'), "$label safe public diagnostic");
        return $error->getMessage();
    }
    throw new RuntimeException("Failed: $label should fail");
}

function observedRawFailure(string $label, Closure $operation): string
{
    try {
        $operation();
    } catch (Throwable $error) {
        return get_class($error);
    }
    throw new RuntimeException("Failed: $label should fail at the raw runtime boundary");
}

function requireFacilities(array $extensions, array $algorithms): void
{
    if (!function_exists('hash') || !function_exists('hash_hmac')) {
        throw new RuntimeException('provider_environment_failure');
    }
    foreach ($extensions as $extension) {
        if (!extension_loaded($extension)) {
            throw new RuntimeException('provider_environment_failure');
        }
    }
    foreach ($algorithms as $algorithm) {
        if (!in_array($algorithm, hash_algos(), true)) {
            throw new RuntimeException('provider_environment_failure');
        }
    }
}

function trialUint32Bytes(mixed $value): string
{
    if (!is_int($value) || $value < 0 || $value > 4294967295) {
        throw new InvalidArgumentException('invalid_uint32');
    }
    return pack('N', $value);
}

function trialUint32Value(string $bytes): int
{
    if (strlen($bytes) !== 4) {
        throw new InvalidArgumentException('invalid_uint32');
    }
    $result = unpack('N', $bytes);
    if (!is_array($result) || !is_int($result[1])) {
        throw new RuntimeException('provider_environment_failure');
    }
    return $result[1];
}

if (($argv[1] ?? '') === '--expect-missing-gmp') {
    check(!extension_loaded('gmp'), 'GMP intentionally absent in php -n run');
    $result = expectRuntimeFailure('missing GMP', static function (): string {
        requireFacilities(['gmp'], ['sha256', 'ripemd160', 'sha512']);
        return str_repeat("\0", 32);
    });
    echo json_encode(['php' => PHP_VERSION, 'gmp_loaded' => false, 'missing_gmp' => $result], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
    exit;
}
if (in_array($argv[1] ?? '', ['--expect-disabled-hash', '--expect-disabled-hmac'], true)) {
    $disabled = $argv[1] === '--expect-disabled-hash' ? 'hash' : 'hash_hmac';
    check(!function_exists($disabled), "$disabled intentionally disabled");
    $result = expectRuntimeFailure("disabled $disabled", static function (): string {
        requireFacilities(['hash', 'gmp'], ['sha256', 'ripemd160', 'sha512']);
        return str_repeat("\0", 32);
    });
    echo json_encode(['php' => PHP_VERSION, 'disabled_function' => $disabled,
        'category' => $result], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
    exit;
}

check(PHP_INT_SIZE === 8, 'unsigned 32-bit integers require qualified integer width');
requireFacilities(['hash', 'gmp'], ['sha256', 'ripemd160', 'sha512']);

$report = ['php' => PHP_VERSION, 'int_size' => PHP_INT_SIZE, 'sha256' => [], 'ripemd160' => [], 'hmac_sha512' => [], 'hash160' => [], 'uint32' => [], 'failure' => []];
$report['sha256']['nist_abc'] = expectDigest('NIST SHA-256 abc', 'sha256', 'abc',
    'ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad');
$report['sha256']['nist_million_a'] = expectDigest('NIST SHA-256 million a', 'sha256', str_repeat('a', 1000000),
    'cdc76e5c9914fb9281a1c7e284d73e67f1809a48a497200e046d39ccc7112cd0');
$report['sha256']['empty_binary'] = expectDigest('SHA-256 empty', 'sha256', '',
    'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855');
$report['ripemd160']['author_empty'] = expectDigest('RIPEMD-160 empty', 'ripemd160', '',
    '9c1185a5c5e9fc54612808977ee8f548b2258d31');
$report['ripemd160']['author_abc'] = expectDigest('RIPEMD-160 abc', 'ripemd160', 'abc',
    '8eb208f7e05d987a9b044a8e98c6b087f15a0bfc');

$report['hmac_sha512']['rfc4231_case1'] = expectHmac('RFC 4231 case 1', str_repeat("\x0b", 20), 'Hi There',
    '87aa7cdea5ef619d4ff0b4241a1d6cb02379f4e2ce4ec2787ad0b30545e17cde'
    . 'daa833b7d6b8a702038b274eaea3f4e4be9d914eeb61f1702e696c203a126854');
$report['hmac_sha512']['rfc4231_case4'] = expectHmac('RFC 4231 case 4', hex2bin('0102030405060708090a0b0c0d0e0f10111213141516171819'), str_repeat("\xcd", 50),
    'b0ba465637458c6990e5a8c5f61d4af7e576d97ff94b872de76f8050361ee3db'
    . 'a91ca5c11aa25eb4d679275cc5788063a5f19741120c4f2de2adebeb10a298dd');
$report['hmac_sha512']['rfc4231_case6'] = expectHmac('RFC 4231 case 6', str_repeat("\xaa", 131), 'Test Using Larger Than Block-Size Key - Hash Key First',
    '80b24263c7c1a3ebb71493c1dd7be8b49b46d1f41b4aeec1121b013783f8f352'
    . '6b56d037e05f2598bd0fd2215d6a1e5295e64f73f63f0aec8b915a985d786598');
$emptyHmac = hash_hmac('sha512', '', '', true);
check(strlen($emptyHmac) === 64, 'empty key/message returns 64 binary bytes');
$report['hmac_sha512']['empty_key_and_message'] = ['bytes' => strlen($emptyHmac), 'hex' => bin2hex($emptyHmac), 'oracle' => 'runtime observation; no cited published answer'];

$fixtures = json_decode(file_get_contents(__DIR__ . '/ckdpub-public-fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
$root = hex2bin($fixtures['official_vectors']['vector_2_root_to_0']['parent']);
$child = hex2bin($fixtures['official_vectors']['vector_2_root_to_0']['expected']);
$parentKey = substr($root, 45, 33);
$sha = hash('sha256', $parentKey, true);
$hash160 = hash('ripemd160', $sha, true);
check(strlen($parentKey) === 33 && strlen($sha) === 32 && strlen($hash160) === 20, 'HASH160 binary widths');
$publishedFingerprint = substr($child, 5, 4);
check(substr($hash160, 0, 4) === $publishedFingerprint, 'BIP32 vector 2 parent fingerprint');
$reversed = hash('sha256', hash('ripemd160', $parentKey, true), true);
check(substr($reversed, 0, 4) !== $publishedFingerprint,
    'BIP32 fingerprint rejects reversed HASH160 composition');
check(hash('ripemd160', hash('sha256', bin2hex($parentKey), true), true) !== $hash160, 'HASH160 hashes point bytes');
$report['hash160']['bip32_vector2_parent'] = [
    'parent_key_bytes' => 33,
    'digest_bytes' => 20,
    'published_child_fingerprint' => bin2hex($publishedFingerprint),
    'actual_fingerprint' => bin2hex(substr($hash160, 0, 4)),
    'reversed_composition_fingerprint' => bin2hex(substr($reversed, 0, 4)),
    'reversed_rejected_by_published_fingerprint' => true,
];

foreach ([
    'zero' => [0, '00000000'],
    'one' => [1, '00000001'],
    'signed_boundary' => [2147483647, '7fffffff'],
    'high_bit' => [2147483648, '80000000'],
    'unsigned_max' => [4294967295, 'ffffffff'],
] as $label => [$integer, $hex]) {
    $packed = trialUint32Bytes($integer);
    check(strlen($packed) === 4 && bin2hex($packed) === $hex, "$label fixed width and big endian");
    check(trialUint32Value($packed) === $integer, "$label unsigned round trip");
    $report['uint32'][$label] = ['integer' => $integer, 'hex' => $hex];
}
check(bin2hex(pack('N', 1000000000)) === '3b9aca00', 'BIP32 vector 1 child number bytes');
check(bin2hex(substr(hex2bin($fixtures['official_vectors']['vector_1_hardened_parent_to_2_to_1000000000']['expected']), 9, 4)) === '3b9aca00', 'BIP32 published child number');
foreach ([-1, 4294967296, 0.0, '0', true, null] as $invalid) {
    try {
        trialUint32Bytes($invalid);
        throw new RuntimeException('Invalid unsigned input passed');
    } catch (InvalidArgumentException $error) {
        check($error->getMessage() === 'invalid_uint32', 'unsigned input rejection category');
    }
}
foreach (["\0\0\0", "\0\0\0\0\0", ''] as $invalid) {
    try {
        trialUint32Value($invalid);
        throw new RuntimeException('Invalid unsigned byte width passed');
    } catch (InvalidArgumentException $error) {
        check($error->getMessage() === 'invalid_uint32', 'unsigned byte width rejection category');
    }
}
$report['uint32']['rejected_inputs'] = ['negative', 'above_uint32', 'float', 'numeric_string', 'boolean', 'null', 'short_bytes', 'trailing_bytes', 'empty_bytes'];

$badHash = static fn () => hash('primitives_missing_sha256', 'secret', true);
$badHmac = static fn () => hash_hmac('primitives_missing_sha512', 'secret', 'key', true);
$report['failure']['unknown_hash'] = ['raw' => observedRawFailure('unknown hash', $badHash), 'public' => expectRuntimeFailure('unknown hash', $badHash)];
$report['failure']['unknown_hmac'] = ['raw' => observedRawFailure('unknown HMAC', $badHmac), 'public' => expectRuntimeFailure('unknown HMAC', $badHmac)];
check($report['failure']['unknown_hash']['raw'] === 'ValueError' && $report['failure']['unknown_hmac']['raw'] === 'ValueError', 'unknown algorithms raise ValueError');
$report['failure']['short_result'] = expectRuntimeFailure('short result', static fn () => str_repeat("\0", 31));
$report['failure']['non_string_result'] = expectRuntimeFailure('non-string result', static fn () => false);
$report['failure']['vendor_exception'] = expectRuntimeFailure('vendor exception', static fn () => throw new RuntimeException('secret vendor detail'));
$report['failure']['missing_native_extension'] = expectRuntimeFailure('missing native extension', static function (): string {
    requireFacilities(['secp256k1'], ['sha256', 'ripemd160', 'sha512']);
    return str_repeat("\0", 32);
});
$report['failure']['missing_algorithm_preflight'] = expectRuntimeFailure('missing algorithm', static function (): string {
    requireFacilities(['hash'], ['primitives_missing_sha256']);
    return str_repeat("\0", 32);
});

echo json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
