<?php
declare(strict_types=1);

// Research-only combined admission/CKDpub/address probe using the M02 trial
// lock. ECC, hashes and standardized address codecs remain delegated.
putenv('M04_LIBRARY_ONLY=1');
require __DIR__ . '/codec-composition-probe.php';

interface TrialPointProvider
{
    public function name(): string;
    /** Return the identical compressed point; use TrialMalformedPoint only for validated invalid input. */
    public function admit(string $point): string;
    public function tweak(string $point, string $scalar): ?string;
}

// Only a validated input or a source-pinned invalid-point signal may use this
// outcome. Other adapter exceptions, even DomainException, are provider faults.
final class TrialMalformedPoint extends DomainException
{
    public function __construct()
    {
        parent::__construct('malformed_key');
    }
}

function trialPointShape(string $point): void
{
    $prime = hex2bin('fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f');
    if (strlen($point) !== 33 || !in_array(ord($point[0]), [2, 3], true)
        || strcmp(substr($point, 1), $prime) >= 0) {
        throw new TrialMalformedPoint();
    }
}

final class ParagonieM04 implements TrialPointProvider
{
    private Mdanter\Ecc\Primitives\GeneratorPoint $generator;
    private Mdanter\Ecc\Serializer\Point\CompressedPointSerializer $serializer;

    public function __construct()
    {
        $math = new Mdanter\Ecc\Math\GmpMath();
        $this->generator = (new Mdanter\Ecc\Curves\SecgCurve($math))->generator256k1(null, false);
        $this->serializer = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($math);
    }

    public function name(): string { return 'paragonie/ecc'; }

    public function admit(string $point): string
    {
        trialPointShape($point);
        try {
            $parsed = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($point));
        } catch (Mdanter\Ecc\Exception\PointRecoveryException) {
            throw new TrialMalformedPoint();
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        try {
            $encoded = $this->serializer->serialize($parsed);
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_string($encoded) || strlen($encoded) !== 66 || !ctype_xdigit($encoded)
            || hex2bin($encoded) !== $point) {
            throw new RuntimeException('provider_failure');
        }
        return $point;
    }

    public function tweak(string $point, string $scalar): ?string
    {
        $parsed = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($point));
        $sum = $parsed->add($this->generator->mul(gmp_init(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($this->serializer->serialize($sum));
    }
}

final class SimplitoM04 implements TrialPointProvider
{
    private Elliptic\EC $curve;

    public function __construct() { $this->curve = new Elliptic\EC('secp256k1'); }
    public function name(): string { return 'simplito/elliptic-php'; }

    public function admit(string $point): string
    {
        trialPointShape($point);
        try {
            $key = $this->curve->keyFromPublic(bin2hex($point), 'hex');
        } catch (Exception $error) {
            if ($error::class === Exception::class && $error->getMessage() === 'Invalid point') {
                throw new TrialMalformedPoint();
            }
            throw new RuntimeException('provider_failure');
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        try {
            $valid = $key->validate();
            $encoded = $key->getPublic(true, 'hex');
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_array($valid) || !isset($valid['result']) || !is_bool($valid['result'])) {
            throw new RuntimeException('provider_failure');
        }
        if ($valid['result'] === false) {
            throw new TrialMalformedPoint();
        }
        if (!is_string($encoded) || strlen($encoded) !== 66 || !ctype_xdigit($encoded)
            || hex2bin($encoded) !== $point) {
            throw new RuntimeException('provider_failure');
        }
        return $point;
    }

    public function tweak(string $point, string $scalar): ?string
    {
        $parsed = $this->curve->keyFromPublic(bin2hex($point), 'hex')->getPublic();
        $sum = $parsed->add($this->curve->g->mul(new BN\BN(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($sum->encode('hex', true));
    }
}

final class FaultPointM04 implements TrialPointProvider
{
    public function __construct(private TrialPointProvider $delegate, private string $mode) {}
    public function name(): string { return 'fault:' . $this->mode; }
    public function admit(string $point): string
    {
        if ($this->mode === 'admit_domain') {
            throw new DomainException('malformed_key');
        }
        if ($this->mode === 'admit_throw') {
            throw new RuntimeException('vendor diagnostic with key material');
        }
        if ($this->mode === 'admit_changed') {
            return str_repeat("\x02", 33);
        }
        return $this->delegate->admit($point);
    }
    public function tweak(string $point, string $scalar): ?string
    {
        if ($this->mode === 'tweak_throw') {
            throw new RuntimeException('vendor diagnostic with key material');
        }
        if ($this->mode === 'tweak_short') {
            return "\x02";
        }
        if ($this->mode === 'tweak_infinity') {
            return null;
        }
        return $this->delegate->tweak($point, $scalar);
    }
}

function composeAddress(
    string $base58Adapter,
    string $segwitAdapter,
    TrialPointProvider $pointProvider,
    TrialDefinition $definition,
    string $key,
    string $policy,
    array $path,
    ?Closure $hmacOverride = null,
): array {
    if ($path === [] || !array_is_list($path)) {
        throw new DomainException('path_bounds');
    }
    foreach ($path as $index) {
        if (!is_int($index) || $index < 0 || $index > 2147483647) {
            throw new DomainException('path_bounds');
        }
    }
    admitPolicy($policy);
    $admitted = admitStructure($base58Adapter, $key);
    $point = hex2bin($admitted['point']);
    try {
        if ($pointProvider->admit($point) !== $point) {
            throw new RuntimeException('provider_failure');
        }
    } catch (TrialMalformedPoint $error) {
        throw $error;
    } catch (Throwable) {
        throw new RuntimeException('provider_failure');
    }
    if (!isset($definition->versions[$admitted['version']])
        || $definition->versions[$admitted['version']] !== $policy) {
        throw new DomainException('incompatible_intent');
    }
    $depth = $admitted['depth'];
    if (count($path) > 255 - $depth) {
        throw new DomainException('path_bounds');
    }
    $chain = $admitted['chain_code'];
    $hmac = $hmacOverride ?? static fn (string $key, string $message): string => hash_hmac('sha512', $message, $key, true);
    foreach ($path as $index) {
        try {
            $digest = $hmac($chain, $point . pack('N', $index));
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_string($digest) || strlen($digest) !== 64) {
            throw new RuntimeException('provider_failure');
        }
        $left = substr($digest, 0, 32);
        if (strcmp($left, hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141')) >= 0) {
            throw new DomainException('invalid_child');
        }
        try {
            $next = $left === str_repeat("\0", 32) ? $point : $pointProvider->tweak($point, $left);
            if ($next !== null && (!is_string($next) || $pointProvider->admit($next) !== $next)) {
                throw new RuntimeException('provider_failure');
            }
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if ($next === null) {
            throw new DomainException('invalid_child');
        }
        $point = $next;
        $chain = substr($digest, 32, 32);
        $depth++;
    }
    $keyHash = hash160($point);
    $redeem = null;
    $scriptHash = null;
    if ($policy === 'p2pkh') {
        $address = encodeChecked($base58Adapter, $definition->p2pkh . $keyHash);
    } elseif ($policy === 'p2sh-p2wpkh') {
        $redeem = "\x00\x14" . $keyHash;
        $scriptHash = hash160($redeem);
        $address = encodeChecked($base58Adapter, $definition->p2sh . $scriptHash);
    } elseif ($policy === 'p2wpkh') {
        $address = segwitEncode($segwitAdapter, $definition->hrp, $keyHash);
    } else {
        throw new DomainException('unsupported_policy');
    }
    return [
        'context' => $definition->id,
        'policy' => $policy,
        'path' => $path,
        'point' => bin2hex($point),
        'key_hash' => bin2hex($keyHash),
        'redeem_script' => $redeem === null ? null : bin2hex($redeem),
        'script_hash' => $scriptHash === null ? null : bin2hex($scriptHash),
        'address' => $address,
    ];
}

$bip49 = 'upub5EFU65HtV5TeiSHmZZm7FUffBGy8UKeqp7vw43jYbvZPpoVsgU93oac7Wk3u6moKegAEWtGNF8DehrnHtv21XXEMYRUocHqguyjknFHYfgY';
$bip84 = 'zpub6rFR7y4Q2AijBEqTUquhVz398htDFrtymD9xYYfG1m4wAcvPhXNfE3EfH1r1ADqtfSdVCToUG868RvUUkgDKf31mGDtKsAYz2oz2AGutZYs';
$definitions = definitionRows([definitionSource('mainnet'), definitionSource('testnet'), definitionSource('signet')], true);
$published = [
    'bip49' => [$definitions['testnet'], $bip49, 'p2sh-p2wpkh', '03a1af804ac108a8a51782198c2d034b28bf90c8803f5a53f76276fa69a4eae77f', '38971f73930f6c141d977ac4fd4a727c854935b3', '2Mww8dCYPUpKHofjgcXcBCEGmniw9CoaiD2'],
    'bip84' => [$definitions['mainnet'], $bip84, 'p2wpkh', '0330d54fd0dd420a6e5f8d3624f5f3482cae350f79d5f0753bf5beef9c2d91af3c', null, 'bc1qcr8te4kr609gcawutmrza0j4xv80jy8z306fyu'],
    'slip132_p2pkh' => [$definitions['mainnet'], 'xpub6BosfCnifzxcFwrSzQiqu2DBVTshkCXacvNsWGYJVVhhawA7d4R5WSWGFNbi8Aw6ZRc1brxMyWMzG3DSSSSoekkudhUd9yLb6qx39T9nMdj', 'p2pkh', null, null, '1LqBGSKuX5yYUonjxT5qGfpUsXKYYWeabA'],
    'slip132_nested' => [$definitions['mainnet'], 'ypub6Ww3ibxVfGzLrAH1PNcjyAWenMTbbAosGNB6VvmSEgytSER9azLDWCxoJwW7Ke7icmizBMXrzBx9979FfaHxHcrArf3zbeJJJUZPf663zsP', 'p2sh-p2wpkh', null, null, '37VucYSaXLCAsxYyAPfbSi9eh4iEcbShgf'],
];
$results = [];
foreach (['tuupola', 'muvon'] as $base58Adapter) {
    foreach (['bitwasp', 'cjpgdk'] as $segwitAdapter) {
        foreach ([new ParagonieM04(), new SimplitoM04()] as $provider) {
            $record = [];
            foreach ($published as $name => [$definition, $key, $policy, $expectedPoint, $expectedHash, $expectedAddress]) {
                $value = composeAddress($base58Adapter, $segwitAdapter, $provider, $definition, $key, $policy, [0, 0]);
                check(($expectedPoint === null || $value['point'] === $expectedPoint) && $value['address'] === $expectedAddress,
                    "{$name} {$base58Adapter} {$segwitAdapter} {$provider->name()}");
                if ($expectedHash !== null) {
                    check($value['key_hash'] === $expectedHash, "{$name} full key hash");
                }
                $record[$name] = $value;
            }
            $signet = composeAddress($base58Adapter, $segwitAdapter, $provider, $definitions['signet'], $bip49, 'p2sh-p2wpkh', [0, 0]);
            check($signet['address'] === $record['bip49']['address'] && $signet['context'] === 'signet'
                && $record['bip49']['context'] === 'testnet', 'Signet same path/address, distinct context');
            $record['signet_test_only'] = ['address' => $signet['address'], 'context' => $signet['context']];
            $results[$base58Adapter][$segwitAdapter][$provider->name()] = $record;
        }
    }
}

// The published BIP32 invalid xpubs exercise the full checked-decoding and
// point-admission sequence on both codec and ECC adapters before any HMAC.
$invalid = json_decode(file_get_contents(__DIR__ . '/../M03/ecc-official-invalid-points.json'), true, 512, JSON_THROW_ON_ERROR);
$invalidResults = [];
foreach (['tuupola', 'muvon'] as $base58Adapter) {
    foreach ([new ParagonieM04(), new SimplitoM04()] as $provider) {
        foreach ($invalid as $name => $fixture) {
            $actual = category(fn () => composeAddress($base58Adapter, 'bitwasp', $provider,
                $definitions['mainnet'], $fixture['xpub'], 'p2pkh', [0, 0]));
            check($actual === 'malformed_key', "{$name} {$base58Adapter} {$provider->name()}: {$actual}");
            $invalidResults[$base58Adapter][$provider->name()][$name] = $actual;
        }
    }
}

$faults = [];
foreach (['admit_domain', 'admit_throw', 'admit_changed', 'tweak_throw', 'tweak_short', 'tweak_infinity'] as $mode) {
    $actual = category(fn () => composeAddress('tuupola', 'bitwasp',
        new FaultPointM04(new ParagonieM04(), $mode), $definitions['testnet'], $bip49, 'p2sh-p2wpkh', [0, 0]));
    $expected = $mode === 'tweak_infinity' ? 'invalid_child' : 'provider_failure';
    check($actual === $expected, "fault {$mode}: {$actual}");
    $faults[$mode] = $actual;
}
foreach ([
    'hmac_throw' => static fn (): string => throw new RuntimeException('vendor diagnostic with key material'),
    'hmac_short' => static fn (): string => 'short',
    'hmac_order' => static fn (): string => hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141') . str_repeat("\0", 32),
] as $mode => $hmac) {
    $actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
        $definitions['testnet'], $bip49, 'p2sh-p2wpkh', [0, 0], $hmac));
    $expected = $mode === 'hmac_order' ? 'invalid_child' : 'provider_failure';
    check($actual === $expected, "fault {$mode}: {$actual}");
    $faults[$mode] = $actual;
}
$calls = 0;
$countingHmac = static function (string $key, string $message) use (&$calls): string {
    $calls++;
    return hash_hmac('sha512', $message, $key, true);
};
$offCurve = $invalid['bip32_vector_5_off_curve_02']['xpub'];
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['mainnet'], $offCurve, 'p2pkh', [0, 0], $countingHmac));
check($actual === 'malformed_key' && $calls === 0, 'invalid point rejected before HMAC');
$faults['invalid_point_before_hmac'] = ['category' => $actual, 'hmac_calls' => $calls];
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['testnet'], $offCurve, 'p2pkh', [0, 0], $countingHmac));
check($actual === 'malformed_key' && $calls === 0, 'invalid point precedes incompatible intent');
$faults['invalid_point_before_intent'] = $actual;
$actual = category(fn () => composeAddress('tuupola', 'bitwasp',
    new FaultPointM04(new ParagonieM04(), 'admit_throw'),
    $definitions['mainnet'], $offCurve, 'p2pkh', [2147483648], $countingHmac));
check($actual === 'path_bounds' && $calls === 0, 'request bounds precede key/provider');
$faults['request_before_key_provider'] = $actual;
$calls = 0;
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['mainnet'], 'bad', 'p2tr', [0], $countingHmac));
check($actual === 'unsupported_policy' && $calls === 0, 'unknown policy precedes malformed key');
$faults['unknown_policy_before_key'] = ['category' => $actual, 'hmac_calls' => $calls];

$bip49Payload = decodeChecked('tuupola', $bip49);
$depth254 = encodeChecked('tuupola', substr_replace($bip49Payload, "\xfe", 4, 1));
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['testnet'], $depth254, 'p2sh-p2wpkh', [0, 0], $countingHmac));
check($actual === 'path_bounds' && $calls === 0, 'insufficient depth rejected before HMAC');
$faults['depth_before_hmac'] = ['category' => $actual, 'hmac_calls' => $calls];
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['mainnet'], $depth254, 'p2sh-p2wpkh', [0, 0], $countingHmac));
check($actual === 'incompatible_intent' && $calls === 0, 'intent precedes depth capacity');
$faults['intent_before_depth'] = $actual;

$calls = 0;
$failSecond = static function (string $key, string $message) use (&$calls): string {
    $calls++;
    return $calls === 2
        ? hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141') . str_repeat("\0", 32)
        : hash_hmac('sha512', $message, $key, true);
};
$actual = category(fn () => composeAddress('tuupola', 'bitwasp', new ParagonieM04(),
    $definitions['testnet'], $bip49, 'p2sh-p2wpkh', [0, 0], $failSecond));
check($actual === 'invalid_child' && $calls === 2, 'second-level invalid child has no retry');
$faults['invalid_second_child'] = ['category' => $actual, 'hmac_calls' => $calls];

echo json_encode(['php' => PHP_VERSION, 'combinations' => $results, 'invalid_bip32_points' => $invalidResults, 'faults' => $faults],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
