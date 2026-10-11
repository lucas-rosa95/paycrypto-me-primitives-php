<?php
declare(strict_types=1);

// Research only: invoke pinned providers; never implement their algorithms.
$root = '/candidates';
spl_autoload_register(static function (string $class) use ($root): void {
    $map = ['Tuupola\\' => 'tuupola/src/', 'StephenHill\\' => 'stephenhill/src/',
        'Mdanter\\Ecc\\' => 'paragonie/src/', 'Elliptic\\' => 'elliptic/lib/',
        'BN\\' => 'bn/lib/', 'BI\\' => 'bigint/lib/',
        'BitWasp\\Bech32\\' => '/reference/vendor/bitwasp/bech32/src/'];
    foreach ($map as $prefix => $dir) {
        if (!str_starts_with($class, $prefix)) continue;
        $path = ($dir[0] === '/' ? $dir : "$root/$dir") . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) require $path;
        return;
    }
});
require '/reference/vendor/bitwasp/bech32/src/bech32.php';
$diagnostics = [];
set_error_handler(static function ($severity, $message, $file, $line) use (&$diagnostics): bool {
    $diagnostics[] = compact('severity', 'message', 'file', 'line');
    return true;
});
function observe(callable $operation): array {
    try { return ['outcome' => 'returned', 'value' => $operation()]; }
    catch (Throwable $e) { return ['outcome' => 'threw', 'class' => get_class($e), 'message' => $e->getMessage()]; }
}
$report = ['runtime' => ['php' => PHP_VERSION, 'int_size' => PHP_INT_SIZE, 'gmp' => phpversion('gmp')], 'checks' => []];
$c = &$report['checks'];
$c['sha256_abc'] = hash('sha256', 'abc') === 'ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad';
$c['ripemd160_empty'] = hash('ripemd160', '') === '9c1185a5c5e9fc54612808977ee8f548b2258d31';
$c['hmac_sha512_rfc4231_case1'] = hash_hmac('sha512', 'Hi There', str_repeat("\x0b", 20)) ===
    '87aa7cdea5ef619d4ff0b4241a1d6cb02379f4e2ce4ec2787ad0b30545e17cde' .
    'daa833b7d6b8a702038b274eaea3f4e4be9d914eeb61f1702e696c203a126854';
$c['uint32_roundtrip'] = [];
foreach ([0, 2147483647, 4294967295] as $i) $c['uint32_roundtrip'][] = unpack('N', pack('N', $i))[1] === $i;
$spec = file_get_contents('/old-evidence/bip-0032.mediawiki');
preg_match_all('/\*\* ext pub: (\S+)/', $spec, $keys);
$raw = new Tuupola\Base58\PhpEncoder(['characters' => Tuupola\Base58::BITCOIN]);
$checked = new Tuupola\Base58\PhpEncoder(['characters' => Tuupola\Base58::BITCOIN, 'check' => true, 'version' => 4]);
// A provider-specific byte split; the entire four-byte version still belongs to
// domain admission. All checksum/base conversion remains inside the provider.
$c['tuupola_xpub_byte_split_roundtrips'] = [];
foreach ($keys[1] as $key) {
    $decoded = $raw->decode($key);
    $body = $checked->decode($key);
    $c['tuupola_xpub_byte_split_roundtrips'][] = strlen($body) === 77 &&
        $body === substr($decoded, 1, -4) && $checked->encode($body) === $key;
}
$first = $keys[1][0];
$payload = substr($raw->decode($first), 0, -4);
$c['tuupola_four_byte_version_option_matches'] = (new Tuupola\Base58\PhpEncoder([
    'characters' => Tuupola\Base58::BITCOIN, 'check' => true, 'version' => 0x0488b21e
]))->encode(substr($payload, 4)) === $first;
$c['tuupola_empty_checked_decode'] = observe(static fn () => bin2hex($checked->decode('')));
$c['tuupola_corrupt_checksum'] = observe(static fn () => $checked->decode(substr($first, 0, -1) . ($first[-1] === '1' ? '2' : '1')));
$c['tuupola_leading_zeros'] = $raw->encode("\0\0\0") === '111' && $raw->decode('111') === "\0\0\0";
$c['tuupola_invalid_alphabet'] = observe(static fn () => $raw->decode('0OIl'));
$c['tuupola_p2pkh_vector'] = (new Tuupola\Base58\PhpEncoder([
    'characters' => Tuupola\Base58::BITCOIN, 'check' => true, 'version' => 0
]))->encode(hex2bin('010966776006953d5567439e5e39f86a0d273bee')) === '16UwLL9Risc3QfPqBUvKofHmBQ7wMtjvM';
if (extension_loaded('gmp')) {
    $other = new StephenHill\Base58(Tuupola\Base58::BITCOIN, new StephenHill\GMPService(Tuupola\Base58::BITCOIN));
    $c['stephenhill_raw_corpus'] = [];
    foreach (["\0\0\0", 'hello', $payload, "\xff\0\x01"] as $bytes) {
        $c['stephenhill_raw_corpus'][] = $other->encode($bytes) === $raw->encode($bytes) && $other->decode($raw->encode($bytes)) === $bytes;
    }
    $c['stephenhill_invalid_alphabet'] = observe(static fn () => $other->decode('0OIl'));
} else $c['stephenhill_raw_corpus'] = 'SKIPPED: explicit GMP provider unavailable';

$bip173 = file_get_contents('/evidence/bip-0173.mediawiki');
preg_match('/The following strings are valid Bech32:(.*?)The following string are not valid Bech32/s', $bip173, $section);
preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors);
$c['bech32_valid_generic'] = [];
foreach ($vectors[1] as $v) $c['bech32_valid_generic'][] = observe(static fn () => is_array(BitWasp\Bech32\decode($v)));
preg_match('/The following string are not valid Bech32.*?:\n(.*?)The following list gives valid segwit/s', $bip173, $section);
preg_match_all('/^\* (?:0x([0-9A-F]+) \+ )?<tt>([^<]+)<\/tt>(?: \+ 0x([0-9A-F]+))?/m', $section[1], $vectors, PREG_SET_ORDER);
$c['bech32_invalid_generic'] = [];
foreach ($vectors as $v) {
    $input = (empty($v[1]) ? '' : chr(hexdec($v[1]))) . $v[2] . (empty($v[3]) ? '' : chr(hexdec($v[3])));
    $c['bech32_invalid_generic'][] = observe(static fn () => BitWasp\Bech32\decode($input));
}
preg_match('/The following list gives valid segwit(.*?)The following list gives invalid segwit/s', $bip173, $section);
preg_match_all('/<tt>([^<]+)<\/tt>: <tt>00(?:14|20)([a-f0-9]+)<\/tt>/', $section[1], $vectors, PREG_SET_ORDER);
$c['segwit_v0_vectors'] = [];
foreach ($vectors as $v) {
    $address = strtolower($v[1]); $hrp = substr($address, 0, 2); $program = hex2bin($v[2]);
    $c['segwit_v0_vectors'][] = BitWasp\Bech32\encodeSegwit($hrp, 0, $program) === $address && BitWasp\Bech32\decodeSegwit($hrp, $v[1]) === [0, $program];
}
preg_match('/The following list gives invalid segwit(.*?)===Checksum design===/s', $bip173, $section);
preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $section[1], $vectors);
$c['segwit_invalid_vectors'] = [];
foreach ($vectors[1] as $v) $c['segwit_invalid_vectors'][] = observe(static fn () => BitWasp\Bech32\decodeSegwit(str_starts_with(strtolower($v), 'tb') ? 'tb' : 'bc', $v));
$c['segwit_v0_bad_program'] = observe(static fn () => BitWasp\Bech32\encodeSegwit('bc', 0, str_repeat("\0", 21)));

if (extension_loaded('gmp')) {
    define('S_MATH_BIGINTEGER_MODE', 'gmp');
    $ghex = '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
    $nhex = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';
    $nm1 = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140';
    $math = new Mdanter\Ecc\Math\GmpMath();
    $g = (new Mdanter\Ecc\Curves\SecgCurve($math))->generator256k1(null, false);
    $ser = new Mdanter\Ecc\Serializer\Point\CompressedPointSerializer($math);
    $p = $ser->unserialize($g->getCurve(), $ghex);
    $ec = new Elliptic\EC('secp256k1');
    $ep = $ec->keyFromPublic($ghex, 'hex')->getPublic();
    foreach (['zero' => '0', 'one' => '1', 'order' => $nhex, 'infinity' => $nm1] as $label => $scalar) {
        $c['ecc_paragonie_' . $label] = observe(static function () use ($p, $g, $ser, $scalar) {
            $q = $p->add($g->mul(gmp_init($scalar, 16)));
            return $q->isInfinity() ? 'INFINITY' : $ser->serialize($q);
        });
        $c['ecc_simplito_' . $label] = observe(static function () use ($ep, $ec, $scalar) {
            $q = $ep->add($ec->g->mul(new BN\BN($scalar, 16)));
            return $q->isInfinity() ? 'INFINITY' : $q->encode('hex', true);
        });
    }
    $c['ecc_paragonie_wrong_length'] = observe(static fn () => $ser->serialize($ser->unserialize($g->getCurve(), '0200' . substr($ghex, 2))));
    $c['ecc_simplito_wrong_length'] = observe(static fn () => $ec->keyFromPublic('0200' . substr($ghex, 2), 'hex')->getPublic(true, 'hex'));
    $report['ecc_classes'] = [get_class($g), get_class($g->getCurve()), get_class($ep)];
} else $c['ecc'] = 'SKIPPED: GMP unavailable';
$report['diagnostics'] = $diagnostics;
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
