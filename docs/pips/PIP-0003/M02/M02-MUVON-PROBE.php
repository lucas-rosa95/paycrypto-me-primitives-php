<?php
declare(strict_types=1);

require '/candidate/Base58Codec.php';

use Muvon\KISS\Base58Codec;

$spec = file_get_contents('/evidence/bip-0032.mediawiki');
if ($spec === false || preg_match_all('/^\*\* ext pub: (xpub\S+)/m', $spec, $matches) !== 17) {
    throw new RuntimeException('Expected seventeen BIP32 public vectors');
}

$roundTrips = [];
foreach ($matches[1] as $key) {
    $payloadHex = Base58Codec::checkDecode($key);
    $roundTrips[] = strlen($payloadHex) === 156
        && substr($payloadHex, 0, 8) === '0488b21e'
        && Base58Codec::checkEncode($payloadHex) === $key;
}

$addressPayload = '00' . '010966776006953d5567439e5e39f86a0d273bee';
$firstXpub = $matches[1][0];
$corruptChecksum = substr($firstXpub, 0, -1) . ($firstXpub[-1] === '1' ? '2' : '1');
$observeCheckedDecode = static function (string $input): array {
    try {
        $decoded = Base58Codec::checkDecode($input);
        return ['outcome' => 'returned', 'hex_length' => strlen($decoded)];
    } catch (Throwable $error) {
        return ['outcome' => get_class($error)];
    }
};

echo json_encode([
    'runtime' => PHP_VERSION,
    'bcmath' => extension_loaded('bcmath'),
    'source_vectors' => count($roundTrips),
    'xpub_round_trips' => $roundTrips,
    'p2pkh_vector' => Base58Codec::checkEncode($addressPayload) === '16UwLL9Risc3QfPqBUvKofHmBQ7wMtjvM',
    'leading_zero_bytes' => Base58Codec::decode('111') === "\0\0\0",
    'corrupt_checksum' => $observeCheckedDecode($corruptChecksum),
    'invalid_alphabet' => $observeCheckedDecode('0OIl'),
    'empty_input' => $observeCheckedDecode(''),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
