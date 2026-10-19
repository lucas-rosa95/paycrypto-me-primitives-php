<?php
declare(strict_types=1);

// Raw observations for one pinned codec, not a Primitives admission adapter.
require '/candidate/src/Bech32Encoding.php';
require '/candidate/src/Bech32.php';

use Bech32\Bech32;
use Bech32\Bech32Encoding;

$spec = file_get_contents('/evidence/bip-0173.mediawiki');
if ($spec === false) {
    throw new RuntimeException('BIP173 corpus unavailable');
}

if (preg_match('/The following strings are valid Bech32:(.*?)The following string are not valid Bech32/s', $spec, $section) !== 1
    || preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors) !== 7) {
    throw new RuntimeException('Expected seven valid BIP173 Bech32 vectors');
}
$checks['generic_valid'] = [];
foreach ($vectors[1] as $address) {
    $encoding = null;
    $hrp = '';
    $decoded = Bech32::decode($address, $hrp, $encoding);
    $checks['generic_valid'][] = is_array($decoded) && $encoding === Bech32Encoding::BECH32;
}

if (preg_match('/The following string are not valid Bech32.*?:\n(.*?)The following list gives valid segwit/s', $spec, $section) !== 1
    || preg_match_all('/^\* (?:0x([0-9A-F]+) \+ )?<tt>([^<]+)<\/tt>(?: \+ 0x([0-9A-F]+))?/m', $section[1], $vectors, PREG_SET_ORDER) !== 12) {
    throw new RuntimeException('Expected twelve invalid BIP173 Bech32 vectors');
}
$checks['generic_invalid'] = [];
foreach ($vectors as $vector) {
    $prefix = empty($vector[1]) ? '' : chr(hexdec($vector[1]));
    $suffix = empty($vector[3]) ? '' : chr(hexdec($vector[3]));
    $encoding = null;
    $hrp = '';
    Bech32::decode($prefix . $vector[2] . $suffix, $hrp, $encoding);
    $checks['generic_invalid'][] = $encoding !== Bech32Encoding::BECH32;
}

if (preg_match('/The following list gives valid segwit(.*?)The following list gives invalid segwit/s', $spec, $section) !== 1
    || preg_match_all('/<tt>([^<]+)<\/tt>: <tt>00(?:14|20)([a-f0-9]+)<\/tt>/', $section[1], $vectors, PREG_SET_ORDER) !== 3) {
    throw new RuntimeException('Expected three valid BIP173 SegWit-v0 vectors');
}
$checks['segwit_v0_valid'] = [];
foreach ($vectors as $vector) {
    $address = strtolower($vector[1]);
    $program = hex2bin($vector[2]);
    if ($program === false) {
        throw new RuntimeException('Malformed BIP173 witness-program fixture');
    }
    $expectedHrp = substr($address, 0, 2);
    $encoded = null;
    $decoded = null;
    $version = -1;
    $hrp = '';
    $encodedOk = Bech32::encodeSegWit($program, $expectedHrp, 0, $encoded);
    $decodedOk = Bech32::decodeSegWit($vector[1], $decoded, $version, $hrp);
    $checks['segwit_v0_valid'][] = $encodedOk && $encoded === $address
        && $decodedOk && $decoded === $program && $version === 0
        && strtolower($hrp) === $expectedHrp;
}

if (preg_match('/The following list gives invalid segwit(.*?)===Checksum design===/s', $spec, $section) !== 1
    || preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors) !== 10) {
    throw new RuntimeException('Expected ten invalid BIP173 SegWit vectors');
}
$checks['segwit_invalid'] = [];
foreach ($vectors[1] as $address) {
    $decoded = null;
    $checks['segwit_invalid'][] = !Bech32::decodeSegWit($address, $decoded);
}

$encoded = null;
$checks['encode_21_byte_v0_rejected'] = false;
try {
    $checks['encode_21_byte_v0_rejected'] = !Bech32::encodeSegWit(str_repeat("\0", 21), 'bc', 0, $encoded);
} catch (InvalidArgumentException) {
    $checks['encode_21_byte_v0_rejected'] = true;
}

$decoded = null;
$version = -1;
$hrp = '';
$words = Bech32::convertBitsFromString(str_repeat("\0", 21), 5, 8, true);
$checksummed = Bech32::encode(array_merge([0], $words), 'bc', Bech32Encoding::BECH32);
$checks['decode_21_byte_v0_rejected'] = !Bech32::decodeSegWit($checksummed, $decoded, $version, $hrp);

echo json_encode(['runtime' => PHP_VERSION, 'checks' => $checks],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
