<?php
declare(strict_types=1);

// Test-only HMAC seam: normal calls delegate unchanged to PHP. Fault probes
// inject a public scalar and chain code without modifying vendor files.
namespace BitWasp\Bitcoin\Crypto {
    function hash_hmac($algorithm, $data, $key, $binary = false) {
        if (isset($GLOBALS['review_hmac'])) {
            return $binary ? $GLOBALS['review_hmac'] : bin2hex($GLOBALS['review_hmac']);
        }
        return \hash_hmac($algorithm, $data, $key, $binary);
    }
}
namespace {
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Base58;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\WitnessProgram;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Address\SegwitAddress;
use BitWasp\Buffertools\Buffer;

$root = '/reference';
$diagnostics = [];
set_error_handler(function ($severity, $message, $file, $line) use (&$diagnostics, $root) {
    $record = ['severity' => $severity, 'message' => $message, 'file' => str_replace($root . '/', '', $file), 'line' => $line];
    $diagnostics[hash('sha256', json_encode($record))] = $record;
    return true; // Record rather than discard diagnostics; keep JSON stdout clean.
});
require "$root/vendor/autoload.php";
function observe(callable $operation): array {
    try { return ['outcome' => 'returned', 'value' => $operation()]; }
    catch (\Throwable $error) { return ['outcome' => 'threw', 'class' => get_class($error), 'message' => $error->getMessage()]; }
}
function sourceManifest(string $root): array {
    $result = [];
    foreach (['bitwasp/bitcoin', 'bitwasp/buffertools', 'bitwasp/bech32', 'paragonie/ecc'] as $package) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/vendor/$package", FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) $result[substr($file->getPathname(), strlen($root) + 1)] = hash_file('sha256', $file->getPathname());
        }
    }
    ksort($result);
    return $result;
}
$manifest = sourceManifest($root);
$lock = json_decode(file_get_contents("$root/composer.lock"), true, 512, JSON_THROW_ON_ERROR);
$packages = [];
foreach ($lock['packages'] as $package) {
    $name = $package['name'];
    $packages[$name] = ['lock_version' => $package['version'], 'lock_reference' => $package['source']['reference'] ?? null,
        'installed_version' => \Composer\InstalledVersions::getPrettyVersion($name),
        'installed_reference' => \Composer\InstalledVersions::getReference($name)];
}
$factory = new HierarchicalKeyFactory();
$network = NetworkFactory::bitcoin();
$adapter = Bitcoin::getEcAdapter();
$report = ['status' => 'Reference implementation observations; not a Primitives audit or acceptance result',
    'runtime' => ['php' => PHP_VERSION, 'int_size' => PHP_INT_SIZE, 'gmp' => phpversion('gmp'), 'secp256k1' => phpversion('secp256k1'),
        'adapter' => get_class($adapter), 'math' => get_class($adapter->getMath()), 'math_parent' => get_parent_class($adapter->getMath())],
    'composer_lock_sha256' => hash_file('sha256', "$root/composer.lock"), 'packages' => $packages];
$spec = file_get_contents('/evidence/bip-0032.mediawiki');
$report['bip32_snapshot_sha256'] = hash('sha256', $spec);
$report['official_public_edges'] = [];
$report['official_public_roundtrips'] = [];
foreach (preg_split('/===Test vector [0-9]+===/', $spec) as $sectionNumber => $section) {
    preg_match_all('/\* Chain ([^\r\n]+)\R\*\* ext pub: (\S+)/', $section, $matches, PREG_SET_ORDER);
    $parents = [];
    foreach ($matches as $match) {
        [$all, $path, $xpub] = $match;
        $parents[$path] = $xpub;
        $report['official_public_roundtrips'][] = ['vector' => $sectionNumber, 'path' => $path,
            'matches' => $factory->fromExtended($xpub, $network)->toExtendedPublicKey($network) === $xpub];
        $slash = strrpos($path, '/');
        if ($slash === false) continue;
        $suffix = substr($path, $slash + 1);
        $parentPath = substr($path, 0, $slash);
        if (!ctype_digit($suffix) || !isset($parents[$parentPath])) continue;
        $actual = $factory->fromExtended($parents[$parentPath], $network)->deriveChild((int) $suffix)->toExtendedPublicKey($network);
        $report['official_public_edges'][] = ['vector' => $sectionNumber, 'path' => $path, 'expected' => $xpub, 'actual' => $actual, 'matches' => $actual === $xpub];
    }
}
$report['official_invalid_public_keys'] = [];
preg_match_all('/^\* (xpub\S+) \(([^\r\n]+)\)$/m', $spec, $invalid, PREG_SET_ORDER);
foreach ($invalid as $case) {
    $report['official_invalid_public_keys'][] = ['reason' => $case[2], 'observation' => observe(fn() => $factory->fromExtended($case[1], $network)->toExtendedPublicKey($network))];
}
// Exercise all three address construction branches using fixture public keys.
// These are regression observations, not independent proof of correctness.
$fixtures = json_decode(file_get_contents("$root/tests/vectors/bitcoin_addresses.json"), true, 512, JSON_THROW_ON_ERROR);
$report['fixture_sha256'] = hash_file('sha256', "$root/tests/vectors/bitcoin_addresses.json");
$report['address_regressions'] = [];
$report['excluded_cross_network_fixture_entries'] = 0;
foreach ($fixtures as $entry) {
    $isTest = in_array($entry['prefix'], ['tpub','upub','vpub'], true);
    if ($isTest !== ($entry['network'] === 'testnet')) { ++$report['excluded_cross_network_fixture_entries']; continue; }
    $net = $isTest ? NetworkFactory::bitcoinTestnet() : $network;
    // Reference-only normalization: preserve payload, use network BIP32 public version.
    // This is not a proposed Primitives import policy.
    $raw = Base58::decodeCheck($entry['xpub']);
    $normalized = Base58::encodeCheck(Buffer::hex($net->getHDPubByte() . substr($raw->getHex(), 8)));
    foreach ($entry['addresses'] as $expected) {
        $path = '0/' . $expected['index'];
        $key = $factory->fromExtended($normalized, $net)->derivePath($path);
        $hash = $key->getPublicKey()->getPubKeyHash();
        $scripts = ScriptFactory::scriptPubKey();
        $creator = new AddressCreator();
        switch ($expected['type']) {
            case 'p2pkh': $address = $creator->fromOutputScript($scripts->payToPubKeyHash($hash)); break;
            case 'p2sh-p2wpkh': $address = $creator->fromOutputScript($scripts->payToScriptHash($scripts->witnessKeyHash($hash)->getScriptHash())); break;
            case 'p2wpkh': $address = new SegwitAddress(WitnessProgram::v0($hash)); break;
            default: throw new \RuntimeException('Unexpected fixture policy');
        }
        $actual = $address->getAddress($net);
        $report['address_regressions'][] = ['network' => $entry['network'], 'prefix' => $entry['prefix'], 'path' => $path, 'policy' => $expected['type'],
            'public_key' => $key->getPublicKey()->getBuffer()->getHex(), 'expected' => $expected['address'], 'actual' => $actual, 'matches' => $actual === $expected['address']];
    }
}
// get_included_files is a loaded-file inventory, NOT branch or function coverage.
$report['pre_fault_loaded_vendor_files'] = [];
foreach (get_included_files() as $file) if (str_starts_with($file, "$root/vendor/")) $report['pre_fault_loaded_vendor_files'][substr($file, strlen($root) + 1)] = hash_file('sha256', $file);
$parentXpub = 'xpub661MyMwAqRbcFtXgS5sYJABqqG9YLmC4Q1Rdap9gSE8NqtwybGhePY2gZ29ESFjqJoCu1Rupje8YtGqsefD265TMg7usUDFdp6W1EGMcet8';
$parent = $factory->fromExtended($parentXpub, $network);
$report['boundary_observations'] = [];
foreach ([0, 2147483647, 2147483648, -1] as $index) $report['boundary_observations'][(string)$index] = observe(fn() => $parent->deriveChild($index)->toExtendedPublicKey($network));
$payload = Base58::decodeCheck($parentXpub)->getBinary();
$extra = Base58::encodeCheck(new Buffer($payload . "\x00"));
$report['trailing_payload_byte'] = observe(fn() => $factory->fromExtended($extra, $network)->toExtendedPublicKey($network));
$report['fault_injection'] = [];
foreach (['IL_zero' => str_repeat("\x00", 32), 'IL_order' => hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141')] as $label => $scalar) {
    $GLOBALS['review_hmac'] = $scalar . str_repeat("\x42", 32);
    $report['fault_injection'][$label] = observe(fn() => $parent->deriveChild(0)->toExtendedPublicKey($network));
    $report['fault_injection'][$label . '_path'] = observe(fn() => $parent->derivePath('0/1')->toExtendedPublicKey($network));
    unset($GLOBALS['review_hmac']);
}
// Public generator point plus public tweak n-1 forces infinity; no private-key API.
$generatorHex = '0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798';
$generatorPayload = substr($payload, 0, 45) . hex2bin($generatorHex);
$generatorParent = $factory->fromExtended(Base58::encodeCheck(new Buffer($generatorPayload)), $network);
$GLOBALS['review_hmac'] = hex2bin('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364140') . str_repeat("\x42", 32);
$report['fault_injection']['point_at_infinity'] = observe(function () use ($generatorParent, $network) {
    $child = $generatorParent->deriveChild(0);
    return ['is_infinity' => $child->getPublicKey()->getPoint()->isInfinity(), 'serialized' => observe(fn() => $child->toExtendedPublicKey($network))];
});
unset($GLOBALS['review_hmac']);
$report['vendor_manifest_sha256'] = $manifest;
$report['vendor_unchanged_during_run'] = $manifest === sourceManifest($root);
$report['diagnostics'] = array_values($diagnostics);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
}
