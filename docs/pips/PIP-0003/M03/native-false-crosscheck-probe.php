<?php
declare(strict_types=1);

// Research-only independent-provider challenge to the native false classifier.
// Every curve operation is delegated to the pinned PHP ECC libraries.
require '/research/vendor/autoload.php';

$cases = [
    'G_n_minus_one' => [
        '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
        'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140', true,
    ],
    'two_G_n_minus_two' => [
        '02c6047f9441ed7d6d3045406e95c07cd85c778e4b8cef3ca7abac09b95c709ee5',
        'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd036413f', true,
    ],
    'negative_G_one' => [
        '0379be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
        str_repeat('00', 31) . '01', true,
    ],
    'G_zero' => [
        '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
        str_repeat('00', 32), false,
    ],
    'G_one' => [
        '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798',
        str_repeat('00', 31) . '01', false,
    ],
];

$math = new Mdanter\Ecc\Math\GmpMath();
$paragonieGenerator = (new Mdanter\Ecc\Curves\SecgCurve($math))->generator256k1(null, false);
$serializer = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($math);
$simplito = new Elliptic\EC('secp256k1');
$results = [];
foreach ($cases as $label => [$point, $scalar, $expectedInfinity]) {
    $parsed = $serializer->unserialize($paragonieGenerator->getCurve(), $point);
    $paragonieSum = $parsed->add($paragonieGenerator->mul(gmp_init($scalar, 16)));
    $simplitoPoint = $simplito->keyFromPublic($point, 'hex')->getPublic();
    $simplitoSum = $simplitoPoint->add($simplito->g->mul(new BN\BN($scalar, 16)));
    $paragonieInfinity = $paragonieSum->isInfinity();
    $simplitoInfinity = $simplitoSum->isInfinity();
    if ($paragonieInfinity !== $expectedInfinity || $simplitoInfinity !== $expectedInfinity) {
        throw new RuntimeException("Independent provider mismatch: $label");
    }
    $paragonieValue = $paragonieInfinity ? null : $serializer->serialize($paragonieSum);
    $simplitoValue = $simplitoInfinity ? null : $simplitoSum->encode('hex', true);
    if ($paragonieValue !== $simplitoValue) {
        throw new RuntimeException("Independent provider point mismatch: $label");
    }
    $results[$label] = ['infinity' => $expectedInfinity, 'point' => $paragonieValue];
}

echo json_encode(['php' => PHP_VERSION, 'results' => $results], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
