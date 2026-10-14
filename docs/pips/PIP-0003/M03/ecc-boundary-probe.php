<?php
declare(strict_types=1);

// Research only. Run with M03-ECC-COMPOSER.lock installed in /research.
// Curve arithmetic remains inside the two candidate providers. This bounded
// probe does not establish provider-fault classification or CKDpub correctness.
require '/research/vendor/autoload.php';

const ORDER_HEX = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
const FIELD_PRIME_HEX = 'fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f';
const GENERATOR_HEX = '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';

interface EccTrial
{
    public function name(): string;
    public function admit(string $compressed): string;
    public function tweak(string $compressed, string $scalar): ?string;
}

function checkPointShape(string $compressed): void
{
    if (strlen($compressed) !== 33 || !in_array(ord($compressed[0]), [2, 3], true)) {
        throw new InvalidArgumentException('invalid_point_shape');
    }
    if (strcmp(substr($compressed, 1), hex2bin(FIELD_PRIME_HEX)) >= 0) {
        throw new InvalidArgumentException('invalid_point_coordinate');
    }
}

function checkScalar(string $scalar): void
{
    if (strlen($scalar) !== 32 || strcmp($scalar, hex2bin(ORDER_HEX)) >= 0) {
        throw new InvalidArgumentException('invalid_scalar');
    }
}

final class ParagonieTrial implements EccTrial
{
    private Mdanter\Ecc\Math\GmpMath $math;
    private Mdanter\Ecc\Primitives\GeneratorPoint $generator;
    private Mdanter\Ecc\Serializer\Point\CompressedPointSerializer $serializer;

    public function __construct()
    {
        $this->math = new Mdanter\Ecc\Math\GmpMath();
        $this->generator = (new Mdanter\Ecc\Curves\SecgCurve($this->math))->generator256k1(null, false);
        $this->serializer = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($this->math);
    }

    public function name(): string { return 'paragonie/ecc'; }

    public function admit(string $compressed): string
    {
        checkPointShape($compressed);
        try {
            $point = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($compressed));
            $canonical = hex2bin($this->serializer->serialize($point));
        } catch (Throwable $error) {
            throw new InvalidArgumentException('invalid_point', 0, $error);
        }
        if ($canonical !== $compressed) {
            throw new InvalidArgumentException('noncanonical_point');
        }
        return $canonical;
    }

    public function tweak(string $compressed, string $scalar): ?string
    {
        $this->admit($compressed);
        checkScalar($scalar);
        $point = $this->serializer->unserialize($this->generator->getCurve(), bin2hex($compressed));
        $sum = $point->add($this->generator->mul(gmp_init(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($this->serializer->serialize($sum));
    }
}

final class SimplitoTrial implements EccTrial
{
    private Elliptic\EC $curve;

    public function __construct() { $this->curve = new Elliptic\EC('secp256k1'); }
    public function name(): string { return 'simplito/elliptic-php'; }

    public function admit(string $compressed): string
    {
        checkPointShape($compressed);
        try {
            $key = $this->curve->keyFromPublic(bin2hex($compressed), 'hex');
            if ($key->validate()['result'] !== true) {
                throw new InvalidArgumentException('invalid_point');
            }
            $canonical = hex2bin($key->getPublic(true, 'hex'));
        } catch (Throwable $error) {
            throw new InvalidArgumentException('invalid_point', 0, $error);
        }
        if ($canonical !== $compressed) {
            throw new InvalidArgumentException('noncanonical_point');
        }
        return $canonical;
    }

    public function tweak(string $compressed, string $scalar): ?string
    {
        $this->admit($compressed);
        checkScalar($scalar);
        $point = $this->curve->keyFromPublic(bin2hex($compressed), 'hex')->getPublic();
        $sum = $point->add($this->curve->g->mul(new BN\BN(bin2hex($scalar), 16)));
        return $sum->isInfinity() ? null : hex2bin($sum->encode('hex', true));
    }
}

function outcome(callable $operation): array
{
    try {
        $value = $operation();
        return ['kind' => $value === null ? 'infinity' : 'point', 'value' => $value === null ? null : bin2hex($value)];
    } catch (InvalidArgumentException $error) {
        return ['kind' => 'invalid', 'reason' => $error->getMessage()];
    } catch (Throwable $error) {
        return ['kind' => 'provider_error', 'class' => get_class($error)];
    }
}

$generator = hex2bin(GENERATOR_HEX);
$order = hex2bin(ORDER_HEX);
$orderMinusOne = hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140');
$orderPlusOne = hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364142');
$points = [
    'generator' => $generator,
    'short' => substr($generator, 0, 32),
    'long' => $generator . "\0",
    'bad_prefix' => "\x04" . substr($generator, 1),
    'x_zero' => "\x02" . str_repeat("\0", 32),
    'x_over_field' => "\x02" . str_repeat("\xff", 32),
    'x_at_field_prime' => "\x02" . hex2bin(FIELD_PRIME_HEX),
];
$scalars = [
    'zero' => str_repeat("\0", 32),
    'one' => str_repeat("\0", 31) . "\x01",
    'order_minus_one' => $orderMinusOne,
    'order' => $order,
    'order_plus_one' => $orderPlusOne,
    'short' => str_repeat("\0", 31),
];

$results = [];
foreach ([new ParagonieTrial(), new SimplitoTrial()] as $trial) {
    foreach ($points as $label => $point) {
        $results[$trial->name()]['points'][$label] = outcome(fn () => $trial->admit($point));
    }
    foreach ($scalars as $label => $scalar) {
        $results[$trial->name()]['tweaks'][$label] = outcome(fn () => $trial->tweak($generator, $scalar));
    }
}
$expectedPoints = [
    'generator' => 'point',
    'short' => 'invalid',
    'long' => 'invalid',
    'bad_prefix' => 'invalid',
    'x_zero' => 'invalid',
    'x_over_field' => 'invalid',
    'x_at_field_prime' => 'invalid',
];
$expectedTweaks = [
    'zero' => 'point',
    'one' => 'point',
    'order_minus_one' => 'infinity',
    'order' => 'invalid',
    'order_plus_one' => 'invalid',
    'short' => 'invalid',
];
foreach ($results as $provider => $cases) {
    foreach ($expectedPoints as $label => $kind) {
        if ($cases['points'][$label]['kind'] !== $kind) {
            throw new RuntimeException("Unexpected $provider point outcome: $label");
        }
    }
    foreach ($expectedTweaks as $label => $kind) {
        if ($cases['tweaks'][$label]['kind'] !== $kind) {
            throw new RuntimeException("Unexpected $provider tweak outcome: $label");
        }
    }
    if ($cases['tweaks']['zero']['value'] !== GENERATOR_HEX) {
        throw new RuntimeException("Zero tweak changed $provider generator");
    }
}
if ($results['paragonie/ecc'] !== $results['simplito/elliptic-php']) {
    throw new RuntimeException('Candidate outcomes differ');
}
// Preserve the provider-level observation that led to the coordinate guard.
// This intentionally bypasses checkPointShape and is not an admitted result.
$math = new Mdanter\Ecc\Math\GmpMath();
$generatorPoint = (new Mdanter\Ecc\Curves\SecgCurve($math))->generator256k1(null, false);
$serializer = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($math);
$rawOverField = outcome(static fn () => hex2bin($serializer->serialize(
    $serializer->unserialize($generatorPoint->getCurve(), '02' . str_repeat('ff', 32))
)));
$report = ['php' => PHP_VERSION, 'gmp' => phpversion('gmp'), 'raw_paragonie_x_over_field' => $rawOverField, 'results' => $results];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
