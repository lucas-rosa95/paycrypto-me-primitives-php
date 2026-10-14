<?php
declare(strict_types=1);

// Research only. Run with the pinned 1ma binding and its libsecp256k1 submodule.
// These are raw native API outcomes, not Primitives failure classifications.
const GENERATOR = '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
const DOUBLE_GENERATOR = '02c6047f9441ed7d6d3045406e95c07cd85c778e4b8cef3ca7abac09b95c709ee5';
const ORDER = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
const ORDER_MINUS_ONE = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140';
const ORDER_MINUS_TWO = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd036413f';
const ORDER_PLUS_ONE = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364142';

$points = [
    'generator' => GENERATOR,
    'negative_generator' => '03' . substr(GENERATOR, 2),
    'double_generator' => DOUBLE_GENERATOR,
    'short' => substr(GENERATOR, 0, -2),
    'long' => GENERATOR . '00',
    'bad_prefix' => '04' . substr(GENERATOR, 2),
    'x_zero' => '02' . str_repeat('00', 32),
    'x_over_field' => '02' . str_repeat('ff', 32),
    'x_at_field_prime' => '02fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f',
    'x_field_prime_minus_one' => '02fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2e',
];
$officialInvalid = json_decode(file_get_contents(__DIR__ . '/ecc-official-invalid-points.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($officialInvalid as $label => $fixture) {
    $points[$label] = $fixture['point'];
}
$tweaks = [
    'zero' => str_repeat('00', 32),
    'one' => str_repeat('00', 31) . '01',
    'order_minus_one' => ORDER_MINUS_ONE,
    'order_minus_two' => ORDER_MINUS_TWO,
    'order' => ORDER,
    'order_plus_one' => ORDER_PLUS_ONE,
    'max_256_bit' => str_repeat('ff', 32),
    'short' => str_repeat('00', 31),
    'long' => str_repeat('00', 32) . '01',
];
$out = ['php' => PHP_VERSION, 'binding' => phpversion('secp256k1'), 'points' => [], 'tweaks' => []];
foreach ($points as $label => $hex) {
    try {
        $point = secp256k1_ec_pubkey_parse(hex2bin($hex));
        $out['points'][$label] = $point === false ? ['kind' => 'invalid'] : ['kind' => 'point', 'value' => bin2hex(secp256k1_ec_pubkey_serialize($point))];
    } catch (Throwable $error) {
        $out['points'][$label] = ['kind' => 'exception', 'type' => get_class($error), 'message' => $error->getMessage()];
    }
}
foreach ($tweaks as $label => $hex) {
    try {
        $point = secp256k1_ec_pubkey_parse(hex2bin(GENERATOR));
        $before = bin2hex(secp256k1_ec_pubkey_serialize($point));
        $ok = secp256k1_ec_pubkey_tweak_add($point, hex2bin($hex));
        $out['tweaks'][$label] = $ok ? ['kind' => 'point', 'value' => bin2hex(secp256k1_ec_pubkey_serialize($point))] : ['kind' => 'false', 'before' => $before];
    } catch (Throwable $error) {
        $out['tweaks'][$label] = ['kind' => 'exception', 'type' => get_class($error), 'message' => $error->getMessage()];
    }
}
foreach (['generator' => GENERATOR, 'negative_generator' => $points['negative_generator'], 'double_generator' => DOUBLE_GENERATOR] as $label => $expected) {
    if ($out['points'][$label] !== ['kind' => 'point', 'value' => $expected]) {
        throw new RuntimeException("Native valid point changed: $label");
    }
}
foreach (['short', 'long', 'bad_prefix', 'x_zero', 'x_over_field', 'x_at_field_prime', 'x_field_prime_minus_one', ...array_keys($officialInvalid)] as $label) {
    if ($out['points'][$label] !== ['kind' => 'invalid']) {
        throw new RuntimeException("Native point admission changed: $label");
    }
}
if ($out['tweaks']['zero'] !== ['kind' => 'point', 'value' => GENERATOR]
    || $out['tweaks']['one'] !== ['kind' => 'point', 'value' => DOUBLE_GENERATOR]
    || $out['tweaks']['order_minus_two'] !== ['kind' => 'point', 'value' => $points['negative_generator']]) {
    throw new RuntimeException('Native public tweak result changed');
}
foreach (['order_minus_one', 'order', 'order_plus_one', 'max_256_bit'] as $label) {
    if ($out['tweaks'][$label] !== ['kind' => 'false', 'before' => GENERATOR]) {
        throw new RuntimeException("Native tweak failure changed: $label");
    }
}
foreach (['short', 'long'] as $label) {
    if ($out['tweaks'][$label]['kind'] !== 'exception' || $out['tweaks'][$label]['type'] !== ValueError::class) {
        throw new RuntimeException("Native wrong-width tweak failure changed: $label");
    }
}
$crossTweaks = [
    'double_generator_plus_order_minus_two' => [DOUBLE_GENERATOR, ORDER_MINUS_TWO],
    'negative_generator_plus_one' => [$points['negative_generator'], $tweaks['one']],
];
foreach ($crossTweaks as $label => [$pointHex, $scalarHex]) {
    $point = secp256k1_ec_pubkey_parse(hex2bin($pointHex));
    $before = bin2hex(secp256k1_ec_pubkey_serialize($point));
    $ok = secp256k1_ec_pubkey_tweak_add($point, hex2bin($scalarHex));
    $after = bin2hex(secp256k1_ec_pubkey_serialize($point));
    if ($ok !== false || $after !== $before) {
        throw new RuntimeException("Native cross-point infinity or atomicity changed: $label");
    }
    $out['cross_tweaks'][$label] = ['kind' => 'false', 'before' => $before];
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
