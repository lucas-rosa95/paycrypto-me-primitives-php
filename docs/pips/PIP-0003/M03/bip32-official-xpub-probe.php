<?php
declare(strict_types=1);

// Research-only fixture provenance check. The normative BIP32 xpub text,
// separately captured BitWasp public payload bytes, and two installed Base58
// decoders must agree. This is not a production codec or key admission API.
require '/research/vendor/autoload.php';

$bip = file_get_contents(__DIR__ . '/../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki');
if (hash('sha256', $bip) !== 'e5e00a8289db2f681052cf24a745320afc225e66b25d1e489a7c884d2fc7f11f') {
    throw new RuntimeException('Pinned BIP32 snapshot changed');
}
$fixtures = json_decode(file_get_contents(__DIR__ . '/ckdpub-public-fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
$published = [];
foreach ([1, 2] as $number) {
    $start = '===Test vector ' . $number . '===';
    $next = '===Test vector ' . ($number + 1) . '===';
    $section = explode($next, explode($start, $bip, 2)[1], 2)[0];
    preg_match_all('/^\*\* ext pub: (xpub\S+)/m', $section, $matches);
    if (count($matches[1]) !== 6) {
        throw new RuntimeException("BIP32 vector $number public-node count changed");
    }
    $published[$number] = $matches[1];
}

// Positions refer only to the six published public nodes in each BIP vector.
$positions = [
    'vector_1_hardened_parent_to_1' => [1, 1, 2],
    'vector_1_hardened_parent_to_2_to_1000000000' => [1, 3, 5],
    'vector_2_root_to_0' => [2, 0, 1],
    'vector_1_hardened_parent_to_2' => [1, 3, 4],
    'vector_2_hardened_parent_to_1' => [2, 2, 3],
    'vector_2_hardened_parent_to_2' => [2, 4, 5],
];
if (array_keys($fixtures['official_vectors']) !== array_keys($positions)) {
    throw new RuntimeException('Fixture set differs from the BIP32 provenance map');
}

$decoders = [
    'tuupola/base58' => new Tuupola\Base58(['characters' => Tuupola\Base58::BITCOIN]),
    'stephenhill/base58' => new StephenHill\Base58(),
];
$uniqueNodes = [];
foreach ($positions as $label => [$vector, $parentPosition, $childPosition]) {
    $fixture = $fixtures['official_vectors'][$label];
    foreach (['parent' => $parentPosition, 'expected' => $childPosition] as $role => $position) {
        $xpub = $fixture[$role . '_xpub'];
        if ($xpub !== $published[$vector][$position]) {
            throw new RuntimeException("$label $role: xpub differs from pinned BIP32 text");
        }
        $expectedPayload = hex2bin($fixture[$role]);
        if ($expectedPayload === false || strlen($expectedPayload) !== 78) {
            throw new RuntimeException("$label $role: expected payload is not 78 bytes");
        }
        foreach ($decoders as $name => $decoder) {
            $decoded = $decoder->decode($xpub);
            if (strlen($decoded) !== 82) {
                throw new RuntimeException("$label $role: $name decoded wrong length");
            }
            $payload = substr($decoded, 0, 78);
            $checksum = substr($decoded, 78, 4);
            $computed = substr(hash('sha256', hash('sha256', $payload, true), true), 0, 4);
            if (!hash_equals($computed, $checksum) || $payload !== $expectedPayload) {
                throw new RuntimeException("$label $role: $name disagrees with BIP32/BitWasp public payload");
            }
        }
        $uniqueNodes[$xpub] = true;
    }
}
if (count($uniqueNodes) !== 11) {
    throw new RuntimeException('Unexpected number of participating public nodes');
}

echo json_encode([
    'php' => PHP_VERSION,
    'source' => 'pinned BIP32 vectors 1 and 2',
    'public_derivation_cases' => count($positions),
    'distinct_public_nodes' => count($uniqueNodes),
    'decoders' => array_keys($decoders),
    'checksum' => 'double SHA-256, first 4 bytes',
    'all_match' => true,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
