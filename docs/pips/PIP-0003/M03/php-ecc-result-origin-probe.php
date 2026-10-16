<?php

declare(strict_types=1);

// Research-only observation of the two pinned PHP ECC providers' raw point
// addition results. Curve arithmetic and point encoding stay in the providers.
require '/research/vendor/autoload.php';

function check(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: $label");
    }
}

$fixtures = json_decode(file_get_contents(__DIR__ . '/ckdpub-public-fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
$vector = $fixtures['official_vectors']['vector_2_root_to_0'];
$parent = hex2bin($vector['parent']);
$expected = hex2bin($vector['expected']);
$parentPoint = substr($parent, 45, 33);
$expectedPoint = substr($expected, 45, 33);
$digest = hash_hmac('sha512', $parentPoint . pack('N', 0), substr($parent, 13, 32), true);
$tweak = substr($digest, 0, 32);
$orderMinusOne = hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140');
$generatorBytes = hex2bin('0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798');

$math = new Mdanter\Ecc\Math\GmpMath();
$generator = (new Mdanter\Ecc\Curves\SecgCurve($math))->generator256k1(null, false);
$serializer = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($math);
$parsed = $serializer->unserialize($generator->getCurve(), bin2hex($parentPoint));
$finite = $parsed->add($generator->mul(gmp_init(bin2hex($tweak), 16)));
check($finite instanceof Mdanter\Ecc\Primitives\PointInterface, 'Paragonie finite result type');
check(!$finite->isInfinity(), 'Paragonie official child is finite');
check(hex2bin($serializer->serialize($finite)) === $expectedPoint, 'Paragonie official child bytes');
$rawGenerator = $serializer->unserialize($generator->getCurve(), bin2hex($generatorBytes));
$infinity = $rawGenerator->add($generator->mul(gmp_init(bin2hex($orderMinusOne), 16)));
check($infinity instanceof Mdanter\Ecc\Primitives\PointInterface, 'Paragonie infinity result type');
check($infinity->isInfinity(), 'Paragonie genuine infinity');

$curve = new Elliptic\EC('secp256k1');
$parsed = $curve->keyFromPublic(bin2hex($parentPoint), 'hex')->getPublic();
$finiteSimplito = $parsed->add($curve->g->mul(new BN\BN(bin2hex($tweak), 16)));
check($finiteSimplito instanceof Elliptic\Curve\ShortCurve\Point, 'Simplito finite result type');
check(!$finiteSimplito->isInfinity(), 'Simplito official child is finite');
check(hex2bin($finiteSimplito->encode('hex', true)) === $expectedPoint, 'Simplito official child bytes');
$rawGeneratorSimplito = $curve->keyFromPublic(bin2hex($generatorBytes), 'hex')->getPublic();
$infinitySimplito = $rawGeneratorSimplito->add($curve->g->mul(new BN\BN(bin2hex($orderMinusOne), 16)));
check($infinitySimplito instanceof Elliptic\Curve\ShortCurve\Point, 'Simplito infinity result type');
check($infinitySimplito->isInfinity(), 'Simplito genuine infinity');

$report = [
    'php' => PHP_VERSION,
    'official_vector' => 'BIP32 vector 2 m -> 0',
    'published_child_point' => bin2hex($expectedPoint),
    'paragonie' => [
        'finite_raw_type' => get_class($finite),
        'finite_is_infinity' => $finite->isInfinity(),
        'finite_matches_published_point' => true,
        'infinity_raw_type' => get_class($infinity),
        'infinity_is_infinity' => $infinity->isInfinity(),
    ],
    'simplito' => [
        'finite_raw_type' => get_class($finiteSimplito),
        'finite_is_infinity' => $finiteSimplito->isInfinity(),
        'finite_matches_published_point' => true,
        'infinity_raw_type' => get_class($infinitySimplito),
        'infinity_is_infinity' => $infinitySimplito->isInfinity(),
    ],
];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
