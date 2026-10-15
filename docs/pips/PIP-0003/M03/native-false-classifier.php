<?php
declare(strict_types=1);

// Research-only qualification helper for the pinned native binding. The tweak
// is public BIP32 HMAC material, not a wallet secret. All point operations stay
// in libsecp256k1; no curve arithmetic is implemented in Primitives.
function nativeInfinityConfirmed(string $compressed, string $scalar, ?Closure $compare = null): bool
{
    // This is a second route through the same native library, not an
    // independent implementation. The caller treats false as provider failure.
    $tweakPoint = secp256k1_ec_pubkey_create($scalar);
    $negativeParent = secp256k1_ec_pubkey_parse($compressed);
    if ($tweakPoint === false || $negativeParent === false) {
        return false;
    }
    secp256k1_ec_pubkey_negate($negativeParent);
    $negativeBytes = secp256k1_ec_pubkey_serialize($negativeParent);
    if ($negativeBytes === $compressed) {
        // An admitted secp256k1 point cannot equal its own negation.
        return false;
    }
    $compare ??= static fn ($left, $right): int => secp256k1_ec_pubkey_cmp($left, $right);
    // The comparison and canonical serialization must agree. Both routes
    // still depend on this one native library, so this is bounded defense.
    return $compare($tweakPoint, $negativeParent) === 0
        && secp256k1_ec_pubkey_serialize($tweakPoint) === $negativeBytes;
}

function qualifiedNativeTweak(string $compressed, string $scalar, ?Closure $call = null, ?Closure $confirm = null): ?string
{
    if (!extension_loaded('secp256k1')) {
        throw new RuntimeException('provider_unavailable');
    }
    if (strlen($scalar) !== 32 || strcmp($scalar, hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141')) >= 0) {
        throw new InvalidArgumentException('invalid_scalar');
    }
    if (strlen($compressed) !== 33) {
        throw new InvalidArgumentException('invalid_point');
    }
    try {
        $point = secp256k1_ec_pubkey_parse($compressed);
        $canonical = $point === false ? null : secp256k1_ec_pubkey_serialize($point);
    } catch (Throwable $error) {
        throw new RuntimeException('provider_failure', 0, $error);
    }
    if ($point === false) {
        throw new InvalidArgumentException('invalid_point');
    }
    if ($canonical !== $compressed) {
        throw new RuntimeException('provider_failure');
    }
    $call ??= static fn ($pubkey, string $tweak): bool => secp256k1_ec_pubkey_tweak_add($pubkey, $tweak);
    try {
        $ok = $call($point, $scalar);
        if ($ok === true) {
            $result = secp256k1_ec_pubkey_serialize($point);
            if (strlen($result) !== 33) {
                throw new RuntimeException('provider_failure');
            }
            return $result;
        }
        if ($ok !== false || secp256k1_ec_pubkey_serialize($point) !== $compressed) {
            throw new RuntimeException('provider_failure');
        }
        if ($scalar === str_repeat("\0", 32)) {
            // Adding zero to a finite admitted point cannot produce infinity.
            throw new RuntimeException('provider_failure');
        }

        // ec_pubkey_create treats its input as a secret key, but this input is
        // the publicly derived BIP32 tweak; no wallet private material is used.
        $confirm ??= static fn (string $parent, string $tweak): bool => nativeInfinityConfirmed($parent, $tweak);
        if ($confirm($compressed, $scalar) !== true) {
            throw new RuntimeException('provider_failure');
        }
        return null;
    } catch (Throwable $error) {
        throw new RuntimeException('provider_failure', 0, $error);
    }
}
