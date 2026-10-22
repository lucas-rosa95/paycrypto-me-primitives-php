<?php
declare(strict_types=1);

// Research only: run in an isolated candidate graph mounted at /trial and
// /research. Reuse the corrected M03 consumer and M04 codec adapters unchanged.
// No production API, provider selection or public exception contract is defined.
$m05Ecc = $argv[1] ?? '';
$m05Segwit = $argv[2] ?? '';
if (!in_array($m05Ecc, ['paragonie', 'simplito'], true)
    || !in_array($m05Segwit, ['bitwasp', 'cjpgdk', 'cardano'], true)) {
    throw new InvalidArgumentException('Pass an ECC and SegWit research candidate');
}

putenv('M04_LIBRARY_ONLY=1');
require __DIR__ . '/../M04/codec-composition-probe.php';
$argv = [$argv[0], $m05Ecc];
ob_start();
require __DIR__ . '/../M03/ckdpub-composition-probe.php';
$m05Ckd = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
$m05ExpectedCkd = json_decode(file_get_contents(__DIR__ . '/../M03/ckdpub-comparison.json'), true, 512, JSON_THROW_ON_ERROR);
unset($m05Ckd['php'], $m05Ckd['provider']);
check($m05Ckd === $m05ExpectedCkd['common_result'], 'unchanged complete-node and forced CKDpub results');

/**
 * Bounded research adapter for the newly examined generic Cardano codec.
 * Return a lowercase v0 address, or throw invalid_witness for invalid intent
 * and provider_failure for a failed/malformed delegated operation.
 * A plausible but incorrect encoder result remains a qualification trust limit.
 */
function m05CardanoEncode(string $hrp, string $program, ?Closure $convert = null, ?Closure $encode = null): string
{
    if (!in_array($hrp, ['bc', 'tb'], true) || strlen($program) !== 20) {
        throw new DomainException('invalid_witness');
    }
    try {
        // The provider performs 8-to-5-bit conversion and all checksum/text
        // encoding. Primitives contributes only the witness-v0 data element.
        $words = $convert === null
            ? \CardanoPhp\Bech32\Bech32::hexToByteArray(bin2hex($program))
            : $convert($program);
        if (!is_array($words) || !array_is_list($words) || count($words) !== 32) {
            throw new RuntimeException('Malformed conversion result');
        }
        foreach ($words as $word) {
            if (!is_int($word) || $word < 0 || $word > 31) {
                throw new RuntimeException('Malformed conversion word');
            }
        }
        $data = array_merge([0], $words);
        $text = $encode === null
            ? \CardanoPhp\Bech32\Bech32::encode($hrp, $data)
            : $encode($hrp, $data);
        if (!is_string($text) || $text === '' || strlen($text) > 90
            || $text !== strtolower($text) || !str_starts_with($text, $hrp . '1')) {
            throw new RuntimeException('Malformed encoder result');
        }
        return $text;
    } catch (Throwable) {
        throw new RuntimeException('provider_failure');
    }
}

$m05Encode = $m05Segwit === 'cardano'
    ? static fn (string $hrp, string $program, ?Closure $override = null): string => m05CardanoEncode($hrp, $program, null, $override)
    : static fn (string $hrp, string $program, ?Closure $override = null): string => segwitEncode($m05Segwit, $hrp, $program, $override);

// Published outputs, independently anchored in the pinned BIP173 snapshot
// and the BIP84 source used by M04. Generated stress inputs below are only
// differential evidence; they do not acquire normative-vector status.
$m05Vectors = [
    'bip173_mainnet' => ['bc', '751e76e8199196d454941c45d1b3a323f1433bd6', 'bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kv8f3t4'],
    'bip173_testnet' => ['tb', '751e76e8199196d454941c45d1b3a323f1433bd6', 'tb1qw508d6qejxtdg4y5r3zarvary0c5xw7kxpjzsx'],
    'bip84_mainnet' => ['bc', 'c0cebcd6c3d3ca8c75dc5ec62ebe55330ef910e2', 'bc1qcr8te4kr609gcawutmrza0j4xv80jy8z306fyu'],
];
$m05Addresses = [];
foreach ($m05Vectors as $name => [$hrp, $hex, $expected]) {
    $text = $m05Encode($hrp, hex2bin($hex));
    check($text === $expected, $name);
    $m05Addresses[$name] = $text;
}
foreach (['bc', 'tb'] as $hrp) {
    foreach (['zeros' => str_repeat("\0", 20), 'ones' => str_repeat("\xff", 20),
        'ascending' => implode('', array_map('chr', range(0, 19)))] as $name => $program) {
        $m05Addresses[$hrp . '_' . $name] = $m05Encode($hrp, $program);
    }
}

$m05Bip32 = file_get_contents(__DIR__ . '/../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki');
check(preg_match('/^\*\* ext pub: (xpub\S+)/m', $m05Bip32, $m05Match) === 1, 'pinned BIP32 xpub');
$m05Xpub = $m05Match[1];
$m05Payload = decodeChecked('tuupola', $m05Xpub);
check(strlen($m05Payload) === 78 && encodeChecked('tuupola', $m05Payload) === $m05Xpub, 'published xpub text');
$m05Versions = [];
foreach (VERSIONS as $version => [$network, $policy]) {
    $payload = hex2bin($version) . substr($m05Payload, 4);
    $text = encodeChecked('tuupola', $payload);
    check(decodeChecked('tuupola', $text) === $payload, 'full public version retained');
    check(admit('tuupola', $text, $network, $policy)['version'] === $version, 'version/policy admission');
    $m05Versions[$version] = $text;
}
$m05Nested = encodeChecked('tuupola', hex2bin('c4336caa13e08b96080a32b5d818d59b4ab3b36742'));
check($m05Nested === '2Mww8dCYPUpKHofjgcXcBCEGmniw9CoaiD2', 'published BIP49 address');

$m05Failures = [];
foreach ([0, 19, 21, 32] as $length) {
    $actual = category(fn () => $m05Encode('bc', str_repeat("\0", $length)));
    check($actual === 'invalid_witness', 'wrong v0 key-hash width');
    $m05Failures['program_' . $length] = $actual;
}
foreach (['', 'BC', 'tc'] as $hrp) {
    check(category(fn () => $m05Encode($hrp, str_repeat("\0", 20))) === 'invalid_witness', 'unadmitted HRP');
}
$m05Corrupt = substr($m05Xpub, 0, -1) . ($m05Xpub[-1] === '1' ? '2' : '1');
check(category(fn () => decodeChecked('tuupola', $m05Corrupt)) === 'malformed_key', 'corrupt checked input');
check(category(fn () => decodeChecked('tuupola', $m05Xpub, static fn () => throw new RuntimeException('injected')))
    === 'provider_failure', 'decoder fault on valid text');

foreach ([
    'empty' => static fn () => '',
    'wrong_type' => static fn () => false,
    'uppercase' => static fn () => strtoupper($m05Vectors['bip84_mainnet'][2]),
    'exception' => static fn () => throw new DomainException('raw provider diagnostic'),
    'wrong_valid_address' => static fn () => $m05Vectors['bip173_mainnet'][2],
] as $name => $override) {
    $actual = category(fn () => $m05Encode('bc', hex2bin($m05Vectors['bip84_mainnet'][1]), $override));
    check($actual === ($name === 'wrong_valid_address' ? 'accepted' : 'provider_failure'), 'encoder fault ' . $name);
    $m05Failures['encode_' . $name] = $actual;
}
// Explicitly prove the independent published output rejects the plausible
// wrong address which the coarse runtime boundary cannot distinguish.
check($m05Encode('bc', hex2bin($m05Vectors['bip84_mainnet'][1]), static fn () => $m05Vectors['bip173_mainnet'][2])
    !== $m05Vectors['bip84_mainnet'][2], 'published oracle catches wrong valid address');

$m05ConversionFaults = [];
if ($m05Segwit === 'cardano') {
    foreach ([
        'exception' => static fn () => throw new DomainException('raw conversion diagnostic'),
        'wrong_type' => static fn () => false,
        'short' => static fn () => array_fill(0, 31, 0),
        'non_list' => static fn () => array_fill(1, 32, 0),
        'float' => static fn () => array_fill(0, 32, 0.0),
        'out_of_range' => static fn () => array_fill(0, 32, 32),
    ] as $name => $convert) {
        $actual = category(fn () => m05CardanoEncode('bc', str_repeat("\0", 20), $convert));
        check($actual === 'provider_failure', 'conversion fault ' . $name);
        $m05ConversionFaults[$name] = $actual;
    }
}

$m05Loaded = [];
foreach (get_included_files() as $file) {
    foreach (['/trial/vendor/', '/research/vendor/'] as $prefix) {
        if (str_starts_with($file, $prefix) && !str_starts_with(substr($file, strlen($prefix)), 'composer/')) {
            $m05Loaded[substr($file, strlen($prefix))] = hash_file('sha256', $file);
        }
    }
}
ksort($m05Loaded);
$m05Installed = [];
foreach (\Composer\InstalledVersions::getInstalledPackages() as $package) {
    if ($package === \Composer\InstalledVersions::getRootPackage()['name']) {
        continue;
    }
    $m05Installed[$package] = \Composer\InstalledVersions::getReference($package);
}
ksort($m05Installed);
echo json_encode([
    'php' => PHP_VERSION,
    'integer_bytes' => PHP_INT_SIZE,
    'ecc' => $m05Ecc,
    'segwit' => $m05Segwit,
    'ckdpub_matches_saved_common_result' => true,
    'published_and_differential_addresses' => $m05Addresses,
    'six_public_versions' => $m05Versions,
    'bip49_address' => $m05Nested,
    'faults' => $m05Failures,
    'cardano_conversion_faults' => $m05ConversionFaults,
    'installed_package_references' => $m05Installed,
    'loaded_vendor_files_sha256' => $m05Loaded,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
