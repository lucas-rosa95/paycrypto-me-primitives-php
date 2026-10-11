<?php
declare(strict_types=1);
require '/candidate/src/Bech32Encoding.php';
require '/candidate/src/Bech32.php';
$spec = file_get_contents('/evidence/bip-0173.mediawiki');
$checks = [];
preg_match('/The following strings are valid Bech32:(.*?)The following string are not valid Bech32/s', $spec, $section);
preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors);
$checks['generic_valid'] = [];
foreach ($vectors[1] as $v) { $enc = null; $hrp = ''; $decoded = \Bech32\Bech32::decode($v, $hrp, $enc); $checks['generic_valid'][] = is_array($decoded) && $enc === \Bech32\Bech32Encoding::BECH32; }
preg_match('/The following string are not valid Bech32.*?:\n(.*?)The following list gives valid segwit/s', $spec, $section);
preg_match_all('/^\* (?:0x([0-9A-F]+) \+ )?<tt>([^<]+)<\/tt>(?: \+ 0x([0-9A-F]+))?/m', $section[1], $vectors, PREG_SET_ORDER);
$checks['generic_invalid'] = [];
foreach ($vectors as $v) {
    $input = (empty($v[1]) ? '' : chr(hexdec($v[1]))) . $v[2] . (empty($v[3]) ? '' : chr(hexdec($v[3])));
    $enc = null; $hrp = ''; \Bech32\Bech32::decode($input, $hrp, $enc); $checks['generic_invalid'][] = $enc !== \Bech32\Bech32Encoding::BECH32;
}
preg_match('/The following list gives valid segwit(.*?)The following list gives invalid segwit/s', $spec, $section);
preg_match_all('/<tt>([^<]+)<\/tt>: <tt>00(?:14|20)([a-f0-9]+)<\/tt>/', $section[1], $vectors, PREG_SET_ORDER);
$checks['segwit_v0_valid'] = [];
foreach ($vectors as $v) {
    $address = strtolower($v[1]); $program = hex2bin($v[2]); $encoded = null; $decoded = null; $version = -1; $hrp = '';
    $ok = \Bech32\Bech32::encodeSegWit($program, substr($address, 0, 2), 0, $encoded);
    $decodeOk = \Bech32\Bech32::decodeSegWit($v[1], $decoded, $version, $hrp);
    $checks['segwit_v0_valid'][] = $ok && $encoded === $address && $decodeOk && $decoded === $program && $version === 0 && strtolower($hrp) === substr($address, 0, 2);
}
preg_match('/The following list gives invalid segwit(.*?)===Checksum design===/s', $spec, $section);
preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors);
$checks['segwit_invalid'] = [];
foreach ($vectors[1] as $v) { $out = null; $checks['segwit_invalid'][] = !\Bech32\Bech32::decodeSegWit($v, $out); }
$out = null; $checks['encode_21_byte_v0_rejected'] = false;
try { $checks['encode_21_byte_v0_rejected'] = !\Bech32\Bech32::encodeSegWit(str_repeat("\0",21),'bc',0,$out); } catch (InvalidArgumentException $e) { $checks['encode_21_byte_v0_rejected'] = true; }
$out = null; $version = -1; $hrp = '';
$word = \Bech32\Bech32::convertBitsFromString(str_repeat("\0", 21), 5, 8, true);
$validGeneric = \Bech32\Bech32::encode(array_merge([0], $word), 'bc', \Bech32\Bech32Encoding::BECH32);
$checks['decode_21_byte_v0_rejected'] = !\Bech32\Bech32::decodeSegWit($validGeneric, $out, $version, $hrp);
echo json_encode(['runtime'=>PHP_VERSION,'checks'=>$checks], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";
