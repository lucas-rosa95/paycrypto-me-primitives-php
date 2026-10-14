<?php
declare(strict_types=1);

// Research-only qualification helper for the pinned native binding. The tweak
// is public BIP32 HMAC material, not a wallet secret. All point operations stay
// in libsecp256k1; no curve arithmetic is implemented in Primitives.
function qualifiedNativeTweak(string $compressed, string $scalar, ?Closure $call = null): ?string
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
    if ($point === false || $canonical !== $compressed) {
        throw new InvalidArgumentException('invalid_point');
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

        // A second native API route confirms the sole remaining mathematical
        // failure: scalar*G equals the negation of the admitted parent point.
        // ec_pubkey_create treats its input as a secret key, but this input is
        // the publicly derived BIP32 tweak; no wallet private material is used.
        $tweakPoint = secp256k1_ec_pubkey_create($scalar);
        $negativeParent = secp256k1_ec_pubkey_parse($compressed);
        if ($tweakPoint === false || $negativeParent === false) {
            throw new RuntimeException('provider_failure');
        }
        secp256k1_ec_pubkey_negate($negativeParent);
        if (secp256k1_ec_pubkey_cmp($tweakPoint, $negativeParent) === 0) {
            return null;
        }
        throw new RuntimeException('provider_failure');
    } catch (Throwable $error) {
        throw new RuntimeException('provider_failure', 0, $error);
    }
}
