<?php
declare(strict_types=1);

// Research-only CKDpub composition. Invoke once per provider with the same
// fixture set; no ECC arithmetic or standardized encoding is implemented here.
require '/research/vendor/autoload.php';
require_once __DIR__ . '/native-false-classifier.php';

const CURVE_ORDER = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
const FIELD_PRIME = 'fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f';

interface PublicPointTrial
{
    public function name(): string;
    public function admit(string $point): string;
    /** Return a compressed point, or null only for provider-confirmed infinity; throw on any other failure. */
    public function tweak(string $point, string $scalar): ?string;
}

final class TrialFailure extends RuntimeException
{
    public function __construct(public readonly string $category, ?Throwable $previous = null)
    {
        parent::__construct($category, 0, $previous);
    }
}

function pointShape(string $point): void
{
    if (strlen($point) !== 33 || !in_array(ord($point[0]), [2, 3], true)
        || strcmp(substr($point, 1), hex2bin(FIELD_PRIME)) >= 0) {
        throw new TrialFailure('malformed_point');
    }
}

final class ParagoniePointTrial implements PublicPointTrial
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
        pointShape($point);
        // The raw parser may accept x >= p; pointShape rejects that first.
        try {
            $parsed = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($point));
            $canonical = hex2bin($this->serializer->serialize($parsed));
        } catch (Throwable $error) {
            throw new TrialFailure('malformed_point', $error);
        }
        if ($canonical !== $point) {
            throw new TrialFailure('malformed_point');
        }
        return $canonical;
    }
    public function tweak(string $point, string $scalar): ?string
    {
        $parsed = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($point));
        $sum = $parsed->add($this->generator->mul(gmp_init(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($this->serializer->serialize($sum));
    }
}

final class SimplitoPointTrial implements PublicPointTrial
{
    private Elliptic\EC $curve;

    public function __construct() { $this->curve = new Elliptic\EC('secp256k1'); }
    public function name(): string { return 'simplito/elliptic-php'; }
    public function admit(string $point): string
    {
        pointShape($point);
        try {
            $key = $this->curve->keyFromPublic(bin2hex($point), 'hex');
            if ($key->validate()['result'] !== true) {
                throw new TrialFailure('malformed_point');
            }
            $canonical = hex2bin($key->getPublic(true, 'hex'));
        } catch (Throwable $error) {
            throw new TrialFailure('malformed_point', $error);
        }
        if ($canonical !== $point) {
            throw new TrialFailure('malformed_point');
        }
        return $canonical;
    }
    public function tweak(string $point, string $scalar): ?string
    {
        $parsed = $this->curve->keyFromPublic(bin2hex($point), 'hex')->getPublic();
        $sum = $parsed->add($this->curve->g->mul(new BN\BN(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($sum->encode('hex', true));
    }
}

final class NativePointTrial implements PublicPointTrial
{
    public function name(): string { return 'uma/secp256k1-php+libsecp256k1'; }
    public function admit(string $point): string
    {
        pointShape($point);
        $parsed = secp256k1_ec_pubkey_parse($point);
        if ($parsed === false) {
            throw new TrialFailure('malformed_point');
        }
        $canonical = secp256k1_ec_pubkey_serialize($parsed);
        if ($canonical !== $point) {
            throw new TrialFailure('malformed_point');
        }
        return $canonical;
    }
    public function tweak(string $point, string $scalar): ?string
    {
        // The helper reparses for every call and confirms a native false as
        // infinity before exposing the invalid-child outcome to CKDpub.
        return qualifiedNativeTweak($point, $scalar);
    }
}

function parsePublicNode(string $payload, PublicPointTrial $provider): array
{
    if (strlen($payload) !== 78 || substr($payload, 0, 4) !== hex2bin('0488b21e')) {
        throw new TrialFailure('malformed_parent');
    }
    $encodedPoint = substr($payload, 45, 33);
    try {
        $point = $provider->admit($encodedPoint);
    } catch (TrialFailure $error) {
        throw $error;
    } catch (Throwable $error) {
        throw new TrialFailure('provider_failure', $error);
    }
    if ($point !== $encodedPoint) {
        throw new TrialFailure('provider_failure');
    }
    return [
        'version' => substr($payload, 0, 4),
        'depth' => ord($payload[4]),
        'parent_fingerprint' => substr($payload, 5, 4),
        'child_number' => unpack('N', substr($payload, 9, 4))[1],
        'chain_code' => substr($payload, 13, 32),
        'point' => $point,
    ];
}

function publicPayload(array $node): string
{
    return $node['version'] . chr($node['depth']) . $node['parent_fingerprint']
        . pack('N', $node['child_number']) . $node['chain_code'] . $node['point'];
}

function deriveExact(array $parent, array $path, PublicPointTrial $provider, ?Closure $hmac = null): array
{
    if ($path === [] || !array_is_list($path)) {
        throw new TrialFailure('path_bounds');
    }
    foreach ($path as $index) {
        if (!is_int($index) || $index < 0 || $index > 2147483647) {
            throw new TrialFailure('path_bounds');
        }
    }
    if (count($path) > 255 - $parent['depth']) {
        throw new TrialFailure('path_bounds');
    }
    $hmac ??= static fn (string $key, string $message): string => hash_hmac('sha512', $message, $key, true);
    $node = $parent;
    foreach ($path as $index) {
        try {
            $digest = $hmac($node['chain_code'], $node['point'] . pack('N', $index));
        } catch (Throwable $error) {
            throw new TrialFailure('provider_failure', $error);
        }
        if (!is_string($digest) || strlen($digest) !== 64) {
            throw new TrialFailure('provider_failure');
        }
        $left = substr($digest, 0, 32);
        if (strcmp($left, hex2bin(CURVE_ORDER)) >= 0) {
            throw new TrialFailure('invalid_child');
        }
        try {
            $point = $provider->tweak($node['point'], $left);
        } catch (Throwable $error) {
            throw new TrialFailure('provider_failure', $error);
        }
        if ($point === null) {
            throw new TrialFailure('invalid_child');
        }
        if (!is_string($point) || strlen($point) !== 33) {
            throw new TrialFailure('provider_failure');
        }
        try {
            // Output admission catches malformed/cross-curve provider results.
            $canonical = $provider->admit($point);
        } catch (Throwable $error) {
            throw new TrialFailure('provider_failure', $error);
        }
        if ($canonical !== $point) {
            throw new TrialFailure('provider_failure');
        }
        $node = [
            'version' => $node['version'],
            'depth' => $node['depth'] + 1,
            'parent_fingerprint' => substr(hash('ripemd160', hash('sha256', $node['point'], true), true), 0, 4),
            'child_number' => $index,
            'chain_code' => substr($digest, 32, 32),
            'point' => $point,
        ];
    }
    return $node;
}

function requireFailure(string $label, string $category, Closure $operation): void
{
    try {
        $operation();
    } catch (TrialFailure $error) {
        if ($error->category !== $category) {
            throw new RuntimeException("$label: expected $category, got $error->category", 0, $error);
        }
        return;
    }
    throw new RuntimeException("$label: expected $category");
}

$selected = $argv[1] ?? '';
$provider = match ($selected) {
    'paragonie' => new ParagoniePointTrial(),
    'simplito' => new SimplitoPointTrial(),
    'native' => new NativePointTrial(),
    default => throw new InvalidArgumentException('Pass paragonie, simplito or native'),
};
if ($selected === 'native' && !extension_loaded('secp256k1')) {
    throw new RuntimeException('Native extension unavailable');
}
$fixtures = json_decode(file_get_contents(__DIR__ . '/ckdpub-public-fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
$report = ['php' => PHP_VERSION, 'provider' => $provider->name(), 'official_vectors' => [], 'forced_cases' => []];
foreach ($fixtures['official_vectors'] as $label => $fixture) {
    $parent = parsePublicNode(hex2bin($fixture['parent']), $provider);
    $actual = bin2hex(publicPayload(deriveExact($parent, $fixture['path'], $provider)));
    if ($actual !== $fixture['expected']) {
        throw new RuntimeException("$label: published BIP32 public node mismatch");
    }
    $report['official_vectors'][$label] = ['path' => $fixture['path'], 'node' => $actual];
}

$base = parsePublicNode(hex2bin($fixtures['official_vectors']['vector_2_root_to_0']['parent']), $provider);
$realHmac = static fn (string $key, string $message): string => hash_hmac('sha512', $message, $key, true);
$observedInput = false;
deriveExact($base, [0], $provider, static function (string $key, string $message) use ($base, $realHmac, &$observedInput): string {
    $observedInput = $key === $base['chain_code'] && $message === $base['point'] . "\0\0\0\0";
    return $realHmac($key, $message);
});
if (!$observedInput) {
    throw new RuntimeException('HMAC key, message or index serialization changed');
}
$report['forced_cases']['hmac_arguments'] = 'exact_parent_chain_code_point_and_index';
$payload = hex2bin($fixtures['official_vectors']['vector_2_root_to_0']['parent']);
foreach ([
    'short_payload' => substr($payload, 0, -1),
    'long_payload' => $payload . "\0",
    'bad_prefix' => substr_replace($payload, "\x04", 45, 1),
    'x_zero' => substr_replace($payload, "\x02" . str_repeat("\0", 32), 45, 33),
    'x_at_field_prime' => substr_replace($payload, "\x02" . hex2bin(FIELD_PRIME), 45, 33),
    'x_over_field' => substr_replace($payload, "\x02" . str_repeat("\xff", 32), 45, 33),
] as $label => $candidate) {
    requireFailure($label, $label === 'short_payload' || $label === 'long_payload' ? 'malformed_parent' : 'malformed_point',
        fn () => parsePublicNode($candidate, $provider));
    $report['forced_cases']["admission_$label"] = 'rejected';
}
$right = str_repeat("\x55", 32);
$forced = static function (int $badAt, string $left, int &$attempt) use ($realHmac, $right): Closure {
    $attempt = 0;
    return static function (string $key, string $message) use (&$attempt, $badAt, $left, $right, $realHmac): string {
        ++$attempt;
        if ($attempt > $badAt) {
            throw new RuntimeException('advanced after invalid child');
        }
        return $attempt === $badAt ? $left . $right : $realHmac($key, $message);
    };
};
foreach (['first' => 1, 'intermediate' => 2, 'last' => 3] as $label => $at) {
    $attempt = 0;
    $hmac = $forced($at, hex2bin(CURVE_ORDER), $attempt);
    requireFailure($label, 'invalid_child', fn () => deriveExact($base, [0, 1, 2], $provider, $hmac));
    if ($attempt !== $at) {
        throw new RuntimeException("$label: unexpected HMAC call count");
    }
    $report['forced_cases']["invalid_$label"] = ['category' => 'invalid_child', 'attempts' => $attempt];
}
$attempt = 0;
$zero = deriveExact($base, [0], $provider, $forced(1, str_repeat("\0", 32), $attempt));
if ($zero['point'] !== $base['point'] || $zero['chain_code'] !== $right
    || $zero['depth'] !== 1 || $zero['child_number'] !== 0
    || $zero['parent_fingerprint'] !== substr(hash('ripemd160', hash('sha256', $base['point'], true), true), 0, 4)) {
    throw new RuntimeException('Zero scalar metadata mismatch');
}
$report['forced_cases']['zero_tweak'] = bin2hex(publicPayload($zero));

// G + (n - 1)G is infinity; the original parent remains unchanged.
$generatorParent = $base;
$generatorParent['point'] = hex2bin('0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798');
$attempt = 0;
$hmac = $forced(1, hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140'), $attempt);
requireFailure('infinity', 'invalid_child', fn () => deriveExact($generatorParent, [0], $provider, $hmac));
$report['forced_cases']['infinity'] = 'invalid_child';

$attempt = 0;
$lastPublic = deriveExact($base, [2147483647], $provider, $forced(1, str_repeat("\0", 32), $attempt));
if ($lastPublic['child_number'] !== 2147483647 || $attempt !== 1) {
    throw new RuntimeException('Last public index changed');
}
$report['forced_cases']['last_public_index'] = 2147483647;
$lastIndexCalls = 0;
$lastIndexHmac = static function (string $key, string $message) use ($base, &$lastIndexCalls, $right): string {
    ++$lastIndexCalls;
    if ($key !== $base['chain_code'] || $message !== $base['point'] . "\x7f\xff\xff\xff") {
        throw new RuntimeException('Last public index was not encoded exactly');
    }
    return hex2bin(CURVE_ORDER) . $right;
};
requireFailure('last_public_invalid', 'invalid_child', fn () => deriveExact($base, [2147483647], $provider, $lastIndexHmac));
if ($lastIndexCalls !== 1) {
    throw new RuntimeException('Last public index was retried');
}
$report['forced_cases']['last_public_invalid'] = ['category' => 'invalid_child', 'attempts' => $lastIndexCalls];
$attempt = 0;
$hmac = $forced(1, hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364142'), $attempt);
requireFailure('order_plus_one', 'invalid_child', fn () => deriveExact($base, [0], $provider, $hmac));
$report['forced_cases']['order_plus_one'] = 'invalid_child';

foreach ([
    'empty' => [],
    'hardened' => [2147483648],
    'negative' => [-1],
    'string' => ['0'],
    'fractional' => [0.5],
    'boolean' => [true],
    'null' => [null],
    'integer_overflow' => [PHP_INT_MAX],
    'associative' => ['index' => 0],
    'nested' => [[0]],
    'root_over_capacity' => array_fill(0, 256, 0),
] as $label => $path) {
    $calls = 0;
    requireFailure($label, 'path_bounds', fn () => deriveExact($base, $path, $provider,
        static function () use (&$calls): never {
            ++$calls;
            throw new RuntimeException('HMAC called for rejected path');
        }));
    if ($calls !== 0) {
        throw new RuntimeException("$label: derivation began before path admission");
    }
    $report['forced_cases']["path_$label"] = 'path_bounds';
}

// These depths are synthetic capacity probes, not canonical imported nodes.
// A zero tweak keeps their point finite while exercising metadata increments.
$zeroHmac = static fn (): string => str_repeat("\0", 32) . $right;
$fullPath = array_fill(0, 255, 0);
$fullPath[254] = 2147483647;
$fullCalls = 0;
$fullDepth = deriveExact($base, $fullPath, $provider,
    static function () use (&$fullCalls, $zeroHmac): string {
        ++$fullCalls;
        return $zeroHmac();
    });
if ($fullDepth['depth'] !== 255 || $fullDepth['child_number'] !== 2147483647 || $fullCalls !== 255) {
    throw new RuntimeException('Full 255-level capacity mismatch');
}
$report['forced_cases']['root_full_capacity'] = ['depth' => 255, 'attempts' => $fullCalls, 'last_index' => 2147483647];
$at253 = $base;
$at253['depth'] = 253;
$twoLevels = deriveExact($at253, [0, 2147483647], $provider, $zeroHmac);
if ($twoLevels['depth'] !== 255 || $twoLevels['child_number'] !== 2147483647) {
    throw new RuntimeException('Depth 253 two-level capacity mismatch');
}
$at254 = $base;
$at254['depth'] = 254;
$oneLevel = deriveExact($at254, [0], $provider, $zeroHmac);
if ($oneLevel['depth'] !== 255 || $oneLevel['child_number'] !== 0) {
    throw new RuntimeException('Depth 254 one-level capacity mismatch');
}
$atMaximum = $base;
$atMaximum['depth'] = 255;
foreach ([
    'depth_254_two_levels' => [$at254, [0, 0]],
    'depth_255_one_level' => [$atMaximum, [0]],
] as $label => [$parent, $path]) {
    $calls = 0;
    requireFailure($label, 'path_bounds', fn () => deriveExact($parent, $path, $provider,
        static function () use (&$calls): never {
            ++$calls;
            throw new RuntimeException('HMAC called after depth exhaustion');
        }));
    if ($calls !== 0) {
        throw new RuntimeException("$label: derivation began before depth admission");
    }
    $report['forced_cases'][$label] = 'path_bounds';
}
$report['forced_cases']['depth_capacity'] = ['depth_253_two_levels' => 255, 'depth_254_one_level' => 255];

// The approved receiving suffix is [0, index]. Its generic path must equal
// two explicit CKDpub calls without admitting a caller-supplied branch.
$receivingPath = deriveExact($base, [0, 0], $provider);
$externalNode = deriveExact($base, [0], $provider);
$receivingSequential = deriveExact($externalNode, [0], $provider);
if (publicPayload($receivingPath) !== publicPayload($receivingSequential)) {
    throw new RuntimeException('Receiving suffix and sequential CKDpub disagree');
}
$report['forced_cases']['receiving_suffix_0_0'] = bin2hex(publicPayload($receivingPath));

requireFailure('short_hmac', 'provider_failure', fn () => deriveExact($base, [0], $provider,
    static fn (): string => str_repeat("\0", 63)));
requireFailure('hmac_exception', 'provider_failure', fn () => deriveExact($base, [0], $provider,
    static fn (): never => throw new RuntimeException('injected')));
$brokenProvider = new class($provider) implements PublicPointTrial {
    public function __construct(private PublicPointTrial $real) {}
    public function name(): string { return 'fault injection'; }
    public function admit(string $point): string { return $this->real->admit($point); }
    public function tweak(string $point, string $scalar): ?string { throw new RuntimeException('injected'); }
};
requireFailure('ecc_exception', 'provider_failure', fn () => deriveExact($base, [0], $brokenProvider));
$malformedProvider = new class($provider) implements PublicPointTrial {
    public function __construct(private PublicPointTrial $real) {}
    public function name(): string { return 'malformed output injection'; }
    public function admit(string $point): string { return $this->real->admit($point); }
    public function tweak(string $point, string $scalar): ?string { return str_repeat("\0", 33); }
};
requireFailure('ecc_malformed_output', 'provider_failure', fn () => deriveExact($base, [0], $malformedProvider));
$badAdmission = new class implements PublicPointTrial {
    public function name(): string { return 'admission fault injection'; }
    public function admit(string $point): string { throw new RuntimeException('injected'); }
    public function tweak(string $point, string $scalar): ?string { throw new LogicException('not reached'); }
};
requireFailure('ecc_admission_exception', 'provider_failure', fn () => parsePublicNode($payload, $badAdmission));
$mutatingAdmission = new class implements PublicPointTrial {
    public function name(): string { return 'mutating admission injection'; }
    public function admit(string $point): string
    {
        return hex2bin('0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798');
    }
    public function tweak(string $point, string $scalar): ?string { throw new LogicException('not reached'); }
};
requireFailure('ecc_mutating_admission', 'provider_failure', fn () => parsePublicNode($payload, $mutatingAdmission));
$report['forced_cases']['provider_faults'] = ['short_hmac', 'hmac_exception', 'ecc_exception', 'ecc_malformed_output', 'ecc_admission_exception', 'ecc_mutating_admission'];

// Q12 adapter-contract challenge: the official vector proves this sum is
// finite. This synthetic adapter deliberately violates the documented meaning
// of null, showing the consumer trusts its selected adapter's classification.
$unconfirmedInfinity = new class($provider) implements PublicPointTrial {
    public function __construct(private PublicPointTrial $real) {}
    public function name(): string { return 'fabricated infinity injection'; }
    public function admit(string $point): string { return $this->real->admit($point); }
    public function tweak(string $point, string $scalar): ?string { return null; }
};
requireFailure('unconfirmed_infinity', 'invalid_child', fn () => deriveExact($base, [0], $unconfirmedInfinity));
$report['forced_cases']['adapter_contract_violation'] = 'fabricated_null_on_official_finite_child_is_accepted_as_claimed_infinity';

echo json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
