<?php
declare(strict_types=1);

// Research-only test of the pinned extension's false, mutation and fault paths.
require_once __DIR__ . '/native-false-classifier.php';

const G = '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
const TWO_G = '02c6047f9441ed7d6d3045406e95c07cd85c778e4b8cef3ca7abac09b95c709ee5';
const NEGATIVE_G = '0379be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
const N = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
const N_MINUS_ONE = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140';
const N_MINUS_TWO = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd036413f';

function classified(callable $operation): string
{
    try {
        $result = $operation();
        return $result === null ? 'infinity' : bin2hex($result);
    } catch (InvalidArgumentException $error) {
        return $error->getMessage();
    } catch (Throwable $error) {
        return 'provider_failure';
    }
}

function rawTweak(string $pointHex, string $scalarHex): array
{
    $point = secp256k1_ec_pubkey_parse(hex2bin($pointHex));
    $alias = $point;
    $before = secp256k1_ec_pubkey_serialize($point);
    try {
        $ok = secp256k1_ec_pubkey_tweak_add($point, hex2bin($scalarHex));
        $kind = $ok ? 'true' : 'false';
    } catch (Throwable $error) {
        $kind = get_class($error);
    }
    $after = secp256k1_ec_pubkey_serialize($point);
    $afterAlias = secp256k1_ec_pubkey_serialize($alias);
    return ['raw' => $kind, 'before' => bin2hex($before), 'after' => bin2hex($after),
        'alias_same' => $afterAlias === $after, 'preserved' => $before === $after];
}

$cases = [
    'G_zero' => [G, str_repeat('00', 32), G, 'true'],
    'G_one' => [G, str_repeat('00', 31) . '01', TWO_G, 'true'],
    'G_n_minus_one' => [G, N_MINUS_ONE, 'infinity', 'false'],
    'two_G_n_minus_two' => [TWO_G, N_MINUS_TWO, 'infinity', 'false'],
    'negative_G_one' => [NEGATIVE_G, str_repeat('00', 31) . '01', 'infinity', 'false'],
    'G_n' => [G, N, 'invalid_scalar', 'false'],
    'G_n_plus_one' => [G, 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364142', 'invalid_scalar', 'false'],
    'G_short' => [G, str_repeat('00', 31), 'invalid_scalar', ValueError::class],
];
$results = [];
foreach ($cases as $label => [$point, $scalar, $expected, $rawExpected]) {
    $raw = rawTweak($point, $scalar);
    $meaning = classified(static fn () => qualifiedNativeTweak(hex2bin($point), hex2bin($scalar)));
    if ($raw['raw'] !== $rawExpected || !$raw['alias_same'] || $meaning !== $expected
        || (($rawExpected === 'false' || $rawExpected === ValueError::class) && !$raw['preserved'])) {
        throw new RuntimeException("Unexpected native result: $label");
    }
    $results[$label] = ['raw' => $raw['raw'], 'classification' => $meaning,
        'preserved' => $raw['preserved'], 'alias_same' => $raw['alias_same']];
}

$one = hex2bin(str_repeat('00', 31) . '01');
$zero = str_repeat("\0", 32);
$forced = [
    'false_on_finite' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): bool => false)),
    'false_on_zero' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $zero,
        static fn (): bool => false)),
    'false_on_infinity' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), hex2bin(N_MINUS_ONE),
        static fn (): bool => false)),
    'false_after_mutation' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static function ($point, string $tweak): bool {
            secp256k1_ec_pubkey_tweak_add($point, $tweak);
            return false;
        })),
    'nonboolean_return' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): int => 0)),
    'exception' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): never => throw new RuntimeException('injected'))),
    'vendor_invalid_argument_exception' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): never => throw new InvalidArgumentException('vendor detail'))),
    'confirmation_false_on_infinity' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), hex2bin(N_MINUS_ONE),
        static fn (): bool => false, static fn (): bool => false)),
    'confirmation_exception_on_infinity' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), hex2bin(N_MINUS_ONE),
        static fn (): bool => false, static fn (): never => throw new RuntimeException('injected confirmation fault'))),
    'confirmation_nonboolean_on_infinity' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), hex2bin(N_MINUS_ONE),
        static fn (): bool => false, static fn (): int => 1)),
    'comparator_false_positive_on_finite' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): bool => false,
        static fn (string $parent, string $scalar): bool => nativeInfinityConfirmed($parent, $scalar, static fn (): int => 0))),
    'comparator_false_negative_on_infinity' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), hex2bin(N_MINUS_ONE),
        static fn (): bool => false,
        static fn (string $parent, string $scalar): bool => nativeInfinityConfirmed($parent, $scalar, static fn (): int => 1))),
    // If both the tweak route and its same-library confirmation lie, this
    // helper has no independent mathematical witness for the exact index.
    'confirmation_false_positive_on_finite' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): bool => false, static fn (): bool => true)),
    // Likewise, a type-correct but wrong successful provider result cannot be
    // proved correct merely by serializing a valid-looking public point.
    'success_without_tweak' => classified(static fn () => qualifiedNativeTweak(hex2bin(G), $one,
        static fn (): bool => true)),
];
$expectedForced = [
    'false_on_finite' => 'provider_failure', 'false_on_zero' => 'provider_failure',
    'false_on_infinity' => 'infinity', 'false_after_mutation' => 'provider_failure',
    'nonboolean_return' => 'provider_failure', 'exception' => 'provider_failure',
    'vendor_invalid_argument_exception' => 'provider_failure',
    'confirmation_false_on_infinity' => 'provider_failure',
    'confirmation_exception_on_infinity' => 'provider_failure',
    'confirmation_nonboolean_on_infinity' => 'provider_failure',
    'comparator_false_positive_on_finite' => 'provider_failure',
    'comparator_false_negative_on_infinity' => 'provider_failure',
    'confirmation_false_positive_on_finite' => 'infinity',
    'success_without_tweak' => G,
];
if ($forced !== $expectedForced) {
    throw new RuntimeException('Injected native failure classification changed');
}

// A failed call cannot poison the same PHP object or its alias: the binding
// operates on a temporary and commits to the object only after success.
$reusable = secp256k1_ec_pubkey_parse(hex2bin(G));
$alias = $reusable;
if (secp256k1_ec_pubkey_tweak_add($reusable, hex2bin(N_MINUS_ONE)) !== false
    || secp256k1_ec_pubkey_tweak_add($reusable, $one) !== true
    || bin2hex(secp256k1_ec_pubkey_serialize($alias)) !== TWO_G) {
    throw new RuntimeException('Native object unusable after false');
}

$opaqueObjectAttempts = [
    'direct_new' => static fn () => new secp256k1_pubkey(),
    'reflection_without_constructor' => static fn () => (new ReflectionClass(secp256k1_pubkey::class))->newInstanceWithoutConstructor(),
    'clone' => static fn () => clone $alias,
    'serialize' => static fn () => serialize($alias),
    'unserialize' => static fn () => unserialize('O:16:"secp256k1_pubkey":0:{}'),
];
$opaqueOutcomes = [];
foreach ($opaqueObjectAttempts as $label => $operation) {
    try {
        $operation();
        throw new RuntimeException("Opaque object construction succeeded: $label");
    } catch (RuntimeException $error) {
        throw $error;
    } catch (Throwable $error) {
        $opaqueOutcomes[$label] = get_class($error);
    }
}

$report = ['php' => PHP_VERSION, 'binding' => phpversion('secp256k1'),
    'raw_and_classified' => $results, 'forced_faults' => $forced,
    'same_object_usable_after_false' => true, 'opaque_object_attempts' => $opaqueOutcomes];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
