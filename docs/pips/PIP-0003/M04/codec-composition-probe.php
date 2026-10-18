<?php
declare(strict_types=1);

// Research-only M04 probe. No production dependency or public API is selected here.
require '/trial/vendor/autoload.php';

use Bech32\Bech32;
use Muvon\KISS\Base58Codec;
use Tuupola\Base58;
use Tuupola\Base58\PhpEncoder;

function check(bool $condition, string $name): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: {$name}");
    }
}

function category(callable $operation): string
{
    try {
        $operation();
        return 'accepted';
    } catch (DomainException $error) {
        return $error->getMessage();
    } catch (Throwable $error) {
        return 'provider_failure';
    }
}

function hash160(string $bytes): string
{
    $sha = hash('sha256', $bytes, true);
    check(is_string($sha) && strlen($sha) === 32, 'SHA-256 facility');
    $ripe = hash('ripemd160', $sha, true);
    check(is_string($ripe) && strlen($ripe) === 20, 'RIPEMD-160 facility');
    return $ripe;
}

function decodeChecked(string $adapter, string $text, ?Closure $checkedOverride = null): string
{
    if (strlen($text) > 160 || $text === '') {
        throw new DomainException('malformed_key');
    }
    if ($adapter === 'tuupola') {
        // The checked API accepts one version byte. The same library's raw
        // decoder supplies it; the checked API owns checksum validation.
        try {
            $raw = (new PhpEncoder(['characters' => Base58::BITCOIN]))->decode($text);
        } catch (InvalidArgumentException) {
            throw new DomainException('malformed_key');
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_string($raw) || strlen($raw) < 5) {
            throw new DomainException('malformed_key');
        }
        $first = ord($raw[0]);
        $checked = new PhpEncoder(['characters' => Base58::BITCOIN, 'check' => true, 'version' => $first]);
        $payload = substr($raw, 0, -4);
        try {
            $body = $checkedOverride === null ? $checked->decode($text) : $checkedOverride();
        } catch (RuntimeException) {
            // Re-encoding through the candidate distinguishes a bad checked
            // input from a decoder fault on self-consistent text. This does
            // not establish independence from a common-mode provider defect.
            try {
                $reencoded = $checked->encode(substr($payload, 1));
            } catch (Throwable) {
                throw new RuntimeException('provider_failure');
            }
            throw $reencoded === $text
                ? new RuntimeException('provider_failure')
                : new DomainException('malformed_key');
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_string($body) || chr($first) . $body !== $payload) {
            throw new RuntimeException('provider_failure');
        }
        return $payload;
    }
    if ($adapter === 'muvon') {
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException('provider_failure');
        }
        try {
            $hex = $checkedOverride === null ? Base58Codec::checkDecode($text) : $checkedOverride();
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if ($hex === '' || $hex === false) {
            try {
                $raw = Base58Codec::decode($text);
                if (!is_string($raw) || strlen($raw) < 5) {
                    throw new DomainException('malformed_key');
                }
                $reencoded = Base58Codec::checkEncode(bin2hex(substr($raw, 0, -4)));
            } catch (DomainException $error) {
                throw $error;
            } catch (Throwable) {
                throw new RuntimeException('provider_failure');
            }
            throw $reencoded === $text
                ? new RuntimeException('provider_failure')
                : new DomainException('malformed_key');
        }
        if (!is_string($hex) || strlen($hex) % 2 !== 0 || !ctype_xdigit($hex)) {
            throw new RuntimeException('provider_failure');
        }
        try {
            if (Base58Codec::checkEncode($hex) !== $text) {
                throw new RuntimeException('provider_failure');
            }
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        $bytes = hex2bin($hex);
        if ($bytes === false) {
            throw new RuntimeException('provider_failure');
        }
        return $bytes;
    }
    throw new LogicException('Unknown research adapter');
}

function encodeChecked(string $adapter, string $payload, ?Closure $encodeOverride = null): string
{
    if ($payload === '') {
        throw new DomainException('malformed_key');
    }
    try {
        if ($encodeOverride !== null) {
            $text = $encodeOverride();
        } elseif ($adapter === 'tuupola') {
            $text = (new PhpEncoder([
                'characters' => Base58::BITCOIN,
                'check' => true,
                'version' => ord($payload[0]),
            ]))->encode(substr($payload, 1));
        } elseif ($adapter === 'muvon') {
            if (!extension_loaded('bcmath')) {
                throw new RuntimeException('BCMath missing');
            }
            $text = Base58Codec::checkEncode(bin2hex($payload));
        } else {
            throw new LogicException('Unknown research adapter');
        }
        if (!is_string($text) || $text === '' || decodeChecked($adapter, $text) !== $payload) {
            throw new RuntimeException('Malformed codec output');
        }
        return $text;
    } catch (Throwable) {
        throw new RuntimeException('provider_failure');
    }
}

if (getenv('M04_CHECK_MISSING_BCMATH') === '1') {
    $sample = 'xpub661MyMwAqRbcFtXgS5sYJABqqG9YLmC4Q1Rdap9gSE8NqtwybGhePY2gZ29ESFjqJoCu1Rupje8YtGqsefD265TMg7usUDFdp6W1EGMcet8';
    $result = category(fn () => decodeChecked('muvon', $sample));
    check(!extension_loaded('bcmath') && $result === 'provider_failure', 'missing BCMath is provider failure');
    echo json_encode(['php' => PHP_VERSION, 'bcmath' => false, 'muvon_checked_decode' => $result], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
    exit;
}

const VERSIONS = [
    '0488b21e' => ['mainnet', 'p2pkh'],
    '049d7cb2' => ['mainnet', 'p2sh-p2wpkh'],
    '04b24746' => ['mainnet', 'p2wpkh'],
    '043587cf' => ['testnet', 'p2pkh'],
    '044a5262' => ['testnet', 'p2sh-p2wpkh'],
    '045f1cf6' => ['testnet', 'p2wpkh'],
];

// A bounded, data-only semantic view for O05 research. The public type and
// source format are deliberately left to M05.
final readonly class TrialDefinition
{
    public function __construct(
        public string $id,
        public string $p2pkh,
        public string $p2sh,
        public string $hrp,
        public array $versions,
    ) {}
}

function definition(array $source, bool $testSignet = false): TrialDefinition
{
    $fields = ['id', 'p2pkh', 'p2sh', 'hrp', 'versions'];
    $keys = array_keys($source);
    sort($keys);
    sort($fields);
    if ($keys !== $fields || !is_string($source['id']) || !is_string($source['p2pkh'])
        || !is_string($source['p2sh']) || !is_string($source['hrp']) || !is_array($source['versions'])) {
        throw new DomainException('invalid_definition');
    }
    $id = $source['id'];
    $expected = match ($id) {
        'mainnet' => ['00', '05', 'bc'],
        'testnet' => ['6f', 'c4', 'tb'],
        'signet' => $testSignet ? ['6f', 'c4', 'tb'] : null,
        default => null,
    };
    if ($expected === null || [$source['p2pkh'], $source['p2sh'], $source['hrp']] !== $expected) {
        throw new DomainException('invalid_definition');
    }
    $network = $id === 'signet' ? 'testnet' : $id;
    $versions = [];
    foreach (VERSIONS as $hex => [$allowedNetwork, $policy]) {
        if ($allowedNetwork === $network) {
            $versions[$hex] = $policy;
        }
    }
    // Version mappings are semantic maps; source insertion order is irrelevant.
    // Sort copies before strict comparison so values and keys still match exactly.
    $providedVersions = $source['versions'];
    ksort($providedVersions, SORT_STRING);
    $expectedVersions = $versions;
    ksort($expectedVersions, SORT_STRING);
    if ($providedVersions !== $expectedVersions) {
        throw new DomainException('invalid_definition');
    }
    return new TrialDefinition($id, hex2bin($source['p2pkh']), hex2bin($source['p2sh']), $source['hrp'], $versions);
}

function definitionSource(string $id): array
{
    $network = $id === 'signet' ? 'testnet' : $id;
    [$p2pkh, $p2sh, $hrp] = $network === 'mainnet' ? ['00', '05', 'bc'] : ['6f', 'c4', 'tb'];
    $versions = [];
    foreach (VERSIONS as $hex => [$allowedNetwork, $policy]) {
        if ($allowedNetwork === $network) {
            $versions[$hex] = $policy;
        }
    }
    return compact('id', 'p2pkh', 'p2sh', 'hrp', 'versions');
}

function definitionRows(array $rows, bool $testSignet = false): array
{
    $result = [];
    foreach ($rows as $row) {
        $view = definition($row, $testSignet);
        if (isset($result[$view->id])) {
            throw new DomainException('invalid_definition');
        }
        $result[$view->id] = $view;
    }
    return $result;
}

function admitStructure(string $adapter, string $text): array
{
    // This focused probe checks structure; the combined M04 probe adds ECC.
    $bytes = decodeChecked($adapter, $text);
    if (strlen($bytes) !== 78) {
        throw new DomainException('malformed_key');
    }
    $depth = ord($bytes[4]);
    if ($depth === 0 && (substr($bytes, 5, 4) !== "\0\0\0\0" || substr($bytes, 9, 4) !== "\0\0\0\0")) {
        throw new DomainException('malformed_key');
    }
    $version = bin2hex(substr($bytes, 0, 4));
    if (!isset(VERSIONS[$version])) {
        // Reject private/unknown versions before interpreting key material.
        throw new DomainException('unsupported_version');
    }
    $point = substr($bytes, 45, 33);
    if (strlen($point) !== 33 || !in_array(ord($point[0]), [2, 3], true)) {
        throw new DomainException('malformed_key');
    }
    return [
        'version' => $version,
        'depth' => $depth,
        'point' => bin2hex($point),
        'chain_code' => substr($bytes, 13, 32),
    ];
}

function admitPolicy(string $policy): void
{
    if (!in_array($policy, ['p2pkh', 'p2sh-p2wpkh', 'p2wpkh'], true)) {
        throw new DomainException('unsupported_policy');
    }
}

function enforceIntent(array $admitted, string $network, string $policy): void
{
    admitPolicy($policy);
    if (!isset(VERSIONS[$admitted['version']])) {
        throw new DomainException('unsupported_version');
    }
    [$expectedNetwork, $expectedPolicy] = VERSIONS[$admitted['version']];
    if ($network !== $expectedNetwork || $policy !== $expectedPolicy) {
        throw new DomainException('incompatible_intent');
    }
}

function admit(string $adapter, string $text, string $network, string $policy): array
{
    admitPolicy($policy);
    $admitted = admitStructure($adapter, $text);
    enforceIntent($admitted, $network, $policy);
    return $admitted;
}

function segwitEncode(string $adapter, string $hrp, string $program, ?Closure $encodeOverride = null): string
{
    if (!in_array($hrp, ['bc', 'tb'], true) || strlen($program) !== 20) {
        throw new DomainException('invalid_witness');
    }
    try {
        if ($encodeOverride !== null) {
            $address = $encodeOverride();
        } elseif ($adapter === 'bitwasp') {
            $address = \BitWasp\Bech32\encodeSegwit($hrp, 0, $program);
        } elseif ($adapter === 'cjpgdk') {
            $address = null;
            if (!Bech32::encodeSegWit($program, $hrp, 0, $address)) {
                throw new RuntimeException('Codec returned false');
            }
        } else {
            throw new LogicException('Unknown research adapter');
        }
        if (!is_string($address) || $address === '' || $address !== strtolower($address)
            || segwitDecode($adapter, $hrp, $address) !== $program) {
            throw new RuntimeException('Malformed codec output');
        }
        return $address;
    } catch (Throwable $error) {
        throw new RuntimeException('SegWit provider failure', 0, $error);
    }
}

function segwitDecode(string $adapter, string $hrp, string $address, ?Closure $decodeOverride = null,
    ?Closure $bitwaspDecodeOverride = null): string
{
    if (!in_array($hrp, ['bc', 'tb'], true) || strlen($address) > 90 || $address === '') {
        throw new DomainException('invalid_witness');
    }
    try {
        if ($decodeOverride !== null) {
            try {
                $returned = $decodeOverride();
            } catch (Throwable) {
                // The injected call is known to be the provider boundary.
                throw new RuntimeException('provider_failure');
            }
            if (!is_array($returned) || count($returned) !== 3) {
                throw new RuntimeException('provider_failure');
            }
            [$version, $program, $actualHrp] = $returned;
        } elseif ($adapter === 'bitwasp') {
            if ($bitwaspDecodeOverride === null) {
                $returned = \BitWasp\Bech32\decodeSegwit($hrp, $address);
            } else {
                try {
                    $returned = $bitwaspDecodeOverride();
                } catch (Throwable) {
                    throw new RuntimeException('provider_failure');
                }
            }
            if (!is_array($returned) || array_keys($returned) !== [0, 1]
                || !is_int($returned[0]) || !is_string($returned[1])) {
                throw new RuntimeException('provider_failure');
            }
            [$version, $program] = $returned;
            $actualHrp = $hrp;
        } elseif ($adapter === 'cjpgdk') {
            $program = null;
            $version = -1;
            $actualHrp = '';
            $encoding = null;
            if (!Bech32::decodeSegWit($address, $program, $version, $actualHrp, $encoding)
                || strtolower($actualHrp) !== $hrp
                || $encoding !== \Bech32\Bech32Encoding::BECH32) {
                throw new DomainException('invalid_witness');
            }
        } else {
            throw new LogicException('Unknown research adapter');
        }
        if ($version !== 0 || !is_string($program) || strlen($program) !== 20 || $actualHrp !== $hrp) {
            throw $decodeOverride === null && $bitwaspDecodeOverride === null
                ? new DomainException('invalid_witness')
                : new RuntimeException('provider_failure');
        }
        try {
            if ($adapter === 'bitwasp') {
                $reencoded = \BitWasp\Bech32\encodeSegwit($hrp, 0, $program);
            } else {
                $reencoded = null;
                if (!Bech32::encodeSegWit($program, $hrp, 0, $reencoded)) {
                    throw new RuntimeException('provider_failure');
                }
            }
        } catch (Throwable) {
            throw new RuntimeException('provider_failure');
        }
        if (!is_string($reencoded) || $reencoded !== strtolower($address)) {
            throw $decodeOverride === null && $bitwaspDecodeOverride === null
                ? new DomainException('invalid_witness')
                : new RuntimeException('provider_failure');
        }
        return $program;
    } catch (DomainException $error) {
        throw $error;
    } catch (\BitWasp\Bech32\Exception\Bech32Exception $error) {
        // Without injection provenance, this type does not distinguish bad
        // input from a provider malfunction; see the M04 handoff.
        throw new DomainException('invalid_witness');
    } catch (Throwable) {
        throw new RuntimeException('provider_failure');
    }
}

if (getenv('M04_LIBRARY_ONLY') === '1') {
    return;
}

$bip49Point = hex2bin('03a1af804ac108a8a51782198c2d034b28bf90c8803f5a53f76276fa69a4eae77f');
$bip49KeyHash = hex2bin('38971f73930f6c141d977ac4fd4a727c854935b3');
$bip49ScriptHash = hex2bin('336caa13e08b96080a32b5d818d59b4ab3b36742');
check(hash160($bip49Point) === $bip49KeyHash, 'BIP49 full key HASH160');
$redeem = "\x00\x14" . $bip49KeyHash;
check(bin2hex($redeem) === '001438971f73930f6c141d977ac4fd4a727c854935b3', 'BIP49 redeem preimage');
check(hash160($redeem) === $bip49ScriptHash, 'BIP49 full redeem HASH160');
check(hash('sha256', hash('ripemd160', $bip49Point, true), true) !== $bip49KeyHash, 'HASH160 order negative control');

$mainSource = definitionSource('mainnet');
$testSource = definitionSource('testnet');
$publicDefinitions = definitionRows([$mainSource, $testSource]);
$copy = json_decode(json_encode($testSource, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
check(definition($copy) == $publicDefinitions['testnet'], 'equivalent independent definition');
$reordered = $testSource;
$reordered['versions'] = array_reverse($testSource['versions'], true);
check($reordered['versions'] !== $testSource['versions'], 'reordered definition fixture');
check(definition($reordered) == $publicDefinitions['testnet'], 'equivalent reordered definition');
$copy['hrp'] = 'other';
check($publicDefinitions['testnet']->hrp === 'tb', 'source mutation cannot affect view');
$signetSource = definitionSource('signet');
$definitionNegatives = [
    'missing' => category(fn () => definition(array_diff_key($testSource, ['hrp' => true]))),
    'extra' => category(fn () => definition($testSource + ['provider' => 'forbidden'])),
    'wrong_byte' => category(fn () => definition(array_replace($testSource, ['p2sh' => '00']))),
    'wrong_hrp' => category(fn () => definition(array_replace($testSource, ['hrp' => 'bc']))),
    'wrong_version' => category(fn () => definition(array_replace($testSource, ['versions' => ['043587cf' => 'p2wpkh']]))),
    'duplicate_profile' => category(fn () => definitionRows([$testSource, $testSource])),
    'public_signet' => category(fn () => definition($signetSource)),
    'wrong_signet' => category(fn () => definition(array_replace($signetSource, ['hrp' => 'bc']), true)),
];
foreach ($definitionNegatives as $name => $actual) {
    check($actual === 'invalid_definition', "definition {$name}: {$actual}");
}
$testDefinitions = definitionRows([$testSource, $signetSource], true);
check($testDefinitions['testnet']->id !== $testDefinitions['signet']->id, 'Signet context identifier distinct');
check($testDefinitions['testnet']->p2sh === $testDefinitions['signet']->p2sh
    && $testDefinitions['testnet']->hrp === $testDefinitions['signet']->hrp, 'Signet shared address parameters');

$bip32 = file_get_contents(__DIR__ . '/../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki');
check(is_string($bip32) && preg_match('/^\*\* ext pub: (xpub\S+)/m', $bip32, $matches) === 1, 'pinned BIP32 xpub');
$xpub = $matches[1];
$invalidBip32 = json_decode(file_get_contents(__DIR__ . '/../M03/ecc-official-invalid-points.json'), true, 512, JSON_THROW_ON_ERROR);
$publishedExtendedKeys = [
    // BIP49 account 0, testnet; BIP84 account 0, mainnet.
    ['upub5EFU65HtV5TeiSHmZZm7FUffBGy8UKeqp7vw43jYbvZPpoVsgU93oac7Wk3u6moKegAEWtGNF8DehrnHtv21XXEMYRUocHqguyjknFHYfgY', 'testnet', 'p2sh-p2wpkh', '044a5262'],
    ['zpub6rFR7y4Q2AijBEqTUquhVz398htDFrtymD9xYYfG1m4wAcvPhXNfE3EfH1r1ADqtfSdVCToUG868RvUUkgDKf31mGDtKsAYz2oz2AGutZYs', 'mainnet', 'p2wpkh', '04b24746'],
];
$base58 = [];
foreach (['tuupola', 'muvon'] as $adapter) {
    $base = decodeChecked($adapter, $xpub);
    check(strlen($base) === 78 && bin2hex(substr($base, 0, 4)) === '0488b21e', "{$adapter} BIP32 payload");
    $cases = [];
    foreach (VERSIONS as $version => [$network, $policy]) {
        $payload = hex2bin($version) . substr($base, 4);
        $text = encodeChecked($adapter, $payload);
        check(decodeChecked($adapter, $text) === $payload, "{$adapter} {$version} round trip");
        check(admit($adapter, $text, $network, $policy)['version'] === $version, "{$adapter} {$version} admission");
        $cases[$version] = $text;
    }
    foreach ($publishedExtendedKeys as [$text, $network, $policy, $version]) {
        check(admit($adapter, $text, $network, $policy)['version'] === $version, "{$adapter} published {$version} account key");
    }
    $corrupt = substr($xpub, 0, -1) . ($xpub[-1] === '1' ? '2' : '1');
    $negative = [
        'checksum' => category(fn () => admit($adapter, $corrupt, 'mainnet', 'p2pkh')),
        'checksum_and_wrong_network' => category(fn () => admit($adapter, $corrupt, 'testnet', 'p2pkh')),
        'alphabet' => category(fn () => admit($adapter, '0OIl', 'mainnet', 'p2pkh')),
        'empty' => category(fn () => admit($adapter, '', 'mainnet', 'p2pkh')),
        'short_payload' => category(fn () => admit($adapter, encodeChecked($adapter, substr($base, 0, -1)), 'mainnet', 'p2pkh')),
        'trailing_payload' => category(fn () => admit($adapter, encodeChecked($adapter, $base . "\0"), 'mainnet', 'p2pkh')),
        'unknown_version' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('04ffffff') . substr($base, 4)), 'mainnet', 'p2pkh')),
        'unknown_version_and_wrong_policy' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('04ffffff') . substr($base, 4)), 'mainnet', 'p2wpkh')),
        'unknown_version_and_bad_point' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('04ffffff') . substr_replace(substr($base, 4), "\x00", 41, 1)), 'mainnet', 'p2pkh')),
        'private_version' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('0488ade4') . substr($base, 4)), 'mainnet', 'p2pkh')),
        'private_version_and_bad_point' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('0488ade4') . substr_replace(substr($base, 4), "\x00", 41, 1)), 'mainnet', 'p2pkh')),
        'multisig_version' => category(fn () => admit($adapter, encodeChecked($adapter, hex2bin('0295b43f') . substr($base, 4)), 'mainnet', 'p2pkh')),
        'opposite_network' => category(fn () => admit($adapter, $cases['0488b21e'], 'testnet', 'p2pkh')),
        'wrong_policy' => category(fn () => admit($adapter, $cases['0488b21e'], 'mainnet', 'p2wpkh')),
        'bad_root_metadata' => category(fn () => admit($adapter, encodeChecked($adapter, substr_replace($base, "\x01", 5, 1)), 'mainnet', 'p2pkh')),
        'bad_root_and_wrong_network' => category(fn () => admit($adapter, encodeChecked($adapter, substr_replace($base, "\x01", 5, 1)), 'testnet', 'p2pkh')),
        'bad_point_prefix' => category(fn () => admit($adapter, encodeChecked($adapter, substr_replace($base, "\x04", 45, 1)), 'mainnet', 'p2pkh')),
        'bad_point_and_wrong_policy' => category(fn () => admit($adapter, encodeChecked($adapter, substr_replace($base, "\x04", 45, 1)), 'mainnet', 'p2wpkh')),
        'unsupported_policy' => category(fn () => admit($adapter, $xpub, 'mainnet', 'p2tr')),
        'unsupported_policy_and_bad_key' => category(fn () => admit($adapter, 'bad', 'mainnet', 'p2tr')),
    ];
    foreach ($negative as $name => $actual) {
        $expected = in_array($name, ['unsupported_policy', 'unsupported_policy_and_bad_key'], true) ? 'unsupported_policy'
            : (in_array($name, ['unknown_version', 'unknown_version_and_wrong_policy', 'unknown_version_and_bad_point', 'private_version', 'private_version_and_bad_point', 'multisig_version'], true)
                ? 'unsupported_version'
                : (in_array($name, ['opposite_network', 'wrong_policy'], true) ? 'incompatible_intent' : 'malformed_key'));
        check($actual === $expected, "{$adapter} {$name}: {$actual}");
    }
    check(encodeChecked($adapter, "\xc4" . $bip49ScriptHash) === '2Mww8dCYPUpKHofjgcXcBCEGmniw9CoaiD2', "{$adapter} BIP49 address");
    check(encodeChecked($adapter, hex2bin('00010966776006953d5567439e5e39f86a0d273bee'))
        === '16UwLL9Risc3QfPqBUvKofHmBQ7wMtjvM', "{$adapter} published P2PKH address");
    check(encodeChecked($adapter, $testDefinitions['signet']->p2sh . $bip49ScriptHash)
        === encodeChecked($adapter, $testDefinitions['testnet']->p2sh . $bip49ScriptHash), "{$adapter} Signet reuse");
    $invalidPoints = [];
    foreach ($invalidBip32 as $name => $fixture) {
        $decoded = decodeChecked($adapter, $fixture['xpub']);
        check(strlen($decoded) === 78 && bin2hex(substr($decoded, 45, 33)) === $fixture['point'], "{$adapter} {$name} sourced point bytes");
        $invalidPoints[$name] = $fixture['point'];
    }
    $historicalHardened = substr_replace($base, "\x01", 4, 1);
    $historicalHardened = substr_replace($historicalHardened, "\x80\0\0\0", 9, 4);
    check(admit($adapter, encodeChecked($adapter, $historicalHardened), 'mainnet', 'p2pkh')['depth'] === 1,
        "{$adapter} imported hardened ancestry metadata");
    $base58[$adapter] = ['six_versions' => $cases, 'published_account_keys' => count($publishedExtendedKeys), 'negative' => $negative, 'bip49_address' => true, 'p2pkh_address' => true, 'bip32_invalid_point_bytes' => $invalidPoints, 'historical_hardened_ancestry' => true];
}
check($base58['tuupola']['six_versions'] === $base58['muvon']['six_versions'], 'Base58 adapter parity');
$codecFaults = [];
foreach (['tuupola', 'muvon'] as $adapter) {
    foreach ([
        'empty_success' => static fn (): string => '',
        'false_success' => static fn (): bool => false,
        'malformed_success' => static fn (): string => 'gg',
        'wrong_success' => static fn (): string => '00',
        'vendor_exception' => static fn (): string => throw new RuntimeException('vendor diagnostic with key material'),
    ] as $mode => $override) {
        $actual = category(fn () => decodeChecked($adapter, $xpub, $override));
        check($actual === 'provider_failure', "{$adapter} {$mode}: {$actual}");
        $codecFaults[$adapter][$mode] = $actual;
    }
    foreach ([
        'encode_empty' => static fn (): string => '',
        'encode_false' => static fn (): bool => false,
        'encode_wrong' => static fn (): string => '16UwLL9Risc3QfPqBUvKofHmBQ7wMtjvM',
        'encode_throw' => static fn (): string => throw new RuntimeException('vendor diagnostic with key material'),
    ] as $mode => $override) {
        $actual = category(fn () => encodeChecked($adapter, decodeChecked($adapter, $xpub), $override));
        check($actual === 'provider_failure', "{$adapter} {$mode}: {$actual}");
        $codecFaults[$adapter][$mode] = $actual;
    }
}

$bip84Program = hash160(hex2bin('0330d54fd0dd420a6e5f8d3624f5f3482cae350f79d5f0753bf5beef9c2d91af3c'));
$bip84Address = 'bc1qcr8te4kr609gcawutmrza0j4xv80jy8z306fyu';
$bech32 = [];
foreach (['bitwasp', 'cjpgdk'] as $adapter) {
    check(segwitEncode($adapter, 'bc', $bip84Program) === $bip84Address, "{$adapter} BIP84 address");
    check(segwitDecode($adapter, 'bc', $bip84Address) === $bip84Program, "{$adapter} BIP84 decode");
    $negative = [
        'wrong_hrp' => category(fn () => segwitDecode($adapter, 'tb', $bip84Address)),
        'bad_checksum' => category(fn () => segwitDecode($adapter, 'bc', substr($bip84Address, 0, -1) . 'q')),
        'mixed_case' => category(fn () => segwitDecode($adapter, 'bc', 'BC' . substr($bip84Address, 2))),
        'short_program' => category(fn () => segwitEncode($adapter, 'bc', str_repeat("\0", 19))),
        'long_program' => category(fn () => segwitEncode($adapter, 'bc', str_repeat("\0", 21))),
    ];
    foreach ($negative as $name => $actual) {
        check($actual === 'invalid_witness', "{$adapter} {$name}: {$actual}");
    }
    $encodeFaults = [];
    foreach ([
        'empty_success' => static fn (): string => '',
        'malformed_success' => static fn (): string => 'bc1notvalid',
        'wrong_program' => static fn (): string => 'bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kv8f3t4',
        'uppercase_success' => static fn (): string => 'BC1QCR8TE4KR609GCAWUTMRZA0J4XV80JY8Z306FYU',
        'vendor_exception' => static fn (): string => throw new RuntimeException('vendor diagnostic with key material'),
    ] as $mode => $override) {
        $actual = category(fn () => segwitEncode($adapter, 'bc', $bip84Program, $override));
        check($actual === 'provider_failure', "{$adapter} encode {$mode}: {$actual}");
        $encodeFaults[$mode] = $actual;
    }
    $decodeFaults = [];
    foreach ([
        'false_success' => static fn (): bool => false,
        'short_result' => static fn (): array => [0, str_repeat("\0", 19), 'bc'],
        'wrong_version' => static fn (): array => [1, $bip84Program, 'bc'],
        'wrong_hrp' => static fn (): array => [0, $bip84Program, 'tb'],
        'wrong_program' => static fn (): array => [0, str_repeat("\0", 20), 'bc'],
        'vendor_exception' => static fn (): array => throw new RuntimeException('vendor diagnostic with key material'),
        'domain_exception' => static fn (): array => throw new DomainException('invalid_witness'),
    ] as $mode => $override) {
        $actual = category(fn () => segwitDecode($adapter, 'bc', $bip84Address, $override));
        check($actual === 'provider_failure', "{$adapter} decode {$mode}: {$actual}");
        $decodeFaults[$mode] = $actual;
    }
    if ($adapter === 'bitwasp') {
        foreach ([
            'native_false_success' => static fn (): bool => false,
            'native_short_result' => static fn (): array => [0],
            'native_wrong_program' => static fn (): array => [0, str_repeat("\0", 20)],
            'native_vendor_exception' => static fn (): array => throw new \BitWasp\Bech32\Exception\Bech32Exception('vendor diagnostic with key material'),
        ] as $mode => $override) {
            $actual = category(fn () => segwitDecode($adapter, 'bc', $bip84Address, null, $override));
            check($actual === 'provider_failure', "{$adapter} decode {$mode}: {$actual}");
            $decodeFaults[$mode] = $actual;
        }
    }
    $bip173 = file_get_contents(__DIR__ . '/../M00/research/provider-refresh-2026-10-02/bip-0173.mediawiki');
    check(is_string($bip173), 'pinned BIP173 source');
    check(preg_match('/The following strings are valid Bech32:(.*?)The following string are not valid Bech32/s', $bip173, $genericValidSection) === 1
        && preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $genericValidSection[1], $genericValid) === 7,
        'seven BIP173 generic valid examples');
    foreach ($genericValid[1] as $text) {
        if ($adapter === 'bitwasp') {
            check(category(fn () => \BitWasp\Bech32\decode($text)) === 'accepted', "{$adapter} generic valid {$text}");
        } else {
            $hrp = '';
            $encoding = null;
            check(is_array(Bech32::decode($text, $hrp, $encoding)) && $encoding === \Bech32\Bech32Encoding::BECH32,
                "{$adapter} generic valid {$text}");
        }
    }
    check(preg_match('/The following string are not valid Bech32.*?:\n(.*?)The following list gives valid segwit/s', $bip173, $genericInvalidSection) === 1
        && preg_match_all('/^\* (?:0x([0-9A-F]+) \+ )?<tt>([^<]+)<\/tt>(?: \+ 0x([0-9A-F]+))?/m', $genericInvalidSection[1], $genericInvalid, PREG_SET_ORDER) === 12,
        'twelve BIP173 generic invalid examples');
    foreach ($genericInvalid as $case) {
        $text = ($case[1] === '' ? '' : chr(hexdec($case[1]))) . $case[2] . (($case[3] ?? '') === '' ? '' : chr(hexdec($case[3])));
        if ($adapter === 'bitwasp') {
            check(category(fn () => \BitWasp\Bech32\decode($text)) !== 'accepted', "{$adapter} generic invalid {$text}");
        } else {
            $hrp = '';
            $encoding = null;
            Bech32::decode($text, $hrp, $encoding);
            check($encoding !== \Bech32\Bech32Encoding::BECH32, "{$adapter} generic invalid {$text}");
        }
    }
    check(preg_match('/The following list gives valid segwit.*?\n(.*?)The following list gives invalid segwit/s', $bip173, $validSection) === 1
        && preg_match_all('/^\* <tt>([^<]+)<\/tt>: <tt>([0-9a-f]+)<\/tt>/m', $validSection[1], $validCases, PREG_SET_ORDER) === 6,
        'six BIP173 valid SegWit examples');
    $valid = [];
    foreach ($validCases as $case) {
        $address = $case[1];
        $script = $case[2];
        $hrp = str_starts_with(strtolower($address), 'tb') ? 'tb' : 'bc';
        $admitted = str_starts_with($script, '0014') && strlen($script) === 44;
        $actual = category(fn () => segwitDecode($adapter, $hrp, $address));
        check($actual === ($admitted ? 'accepted' : 'invalid_witness'), "{$adapter} BIP173 valid {$address}: {$actual}");
        if ($admitted) {
            check(segwitDecode($adapter, $hrp, $address) === hex2bin(substr($script, 4)),
                "{$adapter} BIP173 witness program {$address}");
        }
        $valid[] = ['policy' => $admitted ? 'admitted_v0_20' : 'outside_initial_policy', 'category' => $actual];
    }
    check(preg_match('/The following list gives invalid segwit.*?\n(.*?)===Checksum design===/s', $bip173, $invalidSection) === 1
        && preg_match_all('/^\* <tt>([^<]+)<\/tt>/m', $invalidSection[1], $invalidCases) === 10,
        'ten BIP173 invalid SegWit examples');
    foreach ($invalidCases[1] as $address) {
        $hrp = str_starts_with(strtolower($address), 'tb') ? 'tb' : 'bc';
        check(category(fn () => segwitDecode($adapter, $hrp, $address)) === 'invalid_witness', "{$adapter} BIP173 invalid {$address}");
    }
    check(segwitEncode($adapter, $testDefinitions['signet']->hrp, $bip84Program)
        === segwitEncode($adapter, $testDefinitions['testnet']->hrp, $bip84Program), "{$adapter} Signet SegWit reuse");
    $bech32[$adapter] = ['bip84_address' => true, 'negative' => $negative, 'encode_faults' => $encodeFaults, 'decode_faults' => $decodeFaults, 'bip173_generic_valid_count' => count($genericValid[1]), 'bip173_generic_invalid_count' => count($genericInvalid), 'bip173_valid' => $valid, 'bip173_invalid_count' => count($invalidCases[1])];
}

echo json_encode([
    'php' => PHP_VERSION,
    'extensions' => ['gmp' => extension_loaded('gmp'), 'bcmath' => extension_loaded('bcmath')],
    'bip49' => ['key_hash' => bin2hex($bip49KeyHash), 'redeem_script' => bin2hex($redeem), 'script_hash' => bin2hex($bip49ScriptHash)],
    'definitions' => ['public' => array_keys($publicDefinitions), 'expanded_test' => array_keys($testDefinitions), 'reordered_equivalent' => true, 'negative' => $definitionNegatives],
    'base58check' => $base58,
    'base58check_faults' => $codecFaults,
    'segwit' => $bech32,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
