# BitWasp public derivation reference investigation

**Status:** reproducible reference evidence; not canonical architecture, an independent audit, or acceptance of a Primitives implementation.

**Purpose:** inspect the installed `bitwasp/bitcoin` public extended-key-to-address path and identify evidence and limitations relevant to Primitives verification. Requirement provenance does not introduce knowledge of higher layers into Primitives. No production implementation, dependency selection, or public API is established by this investigation.

The source path map and fault probes describe the external reference only. They
do not authorize porting its algorithms or codecs into Primitives. Library and
runtime feasibility research must precede implementation selection; Primitives
owns the contracts, semantics, invariants and composition at its boundary.

## Provenance and integrity

The reference installation was read from `src/trunk/vendor` in the local `paycrypto-me-for-woocommerce` checkout at commit `2c36c4d7197c4464b011ea63a2f768bb42b6c02d`. The checkout was clean when inspected. No plugin PHP code was loaded by the harness. Existing public address fixtures were used only as regression observations, not as architectural or normative authority.

| Package | Installed version | Source reference recorded by Composer |
|---|---|---|
| `bitwasp/bitcoin` | `v1.1.0` | `527b1ee7d2cd958b5ce011c7918801ffbfb53f97` |
| `bitwasp/buffertools` | `v0.5.7` | `133746d0b514e0016d8479b54aa97475405a9f1f` |
| `bitwasp/bech32` | `v0.0.1` | `e1ea58c848a4ec59d81b697b3dfe9cc99968d0e7` |
| `paragonie/ecc` | `v2.5.0` | `d25bd2aab9b1205db1cf3aa3e83531d4549377bf` |

Lockfile and installed package metadata agree for these packages. Metadata agreement alone is not proof of unmodified source. As a separate check, the complete installed `bitwasp/bitcoin/src` tree was compared with the upstream archive at the exact locked commit: no differences were found. `upstream-comparison.json` records this check. Other dependencies were fingerprinted locally, but were not independently compared with upstream archives in this investigation.

The harness records SHA-256 hashes for 463 files across the four packages, plus the lockfile, fixture input and loaded-file inventory. It verifies that the four-package manifest is unchanged across execution. Containers mount the reference installation read-only, run without network access and use a read-only root filesystem. The full local image ID is recorded in `image-id.txt`; this is a local image identity, not a published registry digest or a reproducible image-build attestation.

The BIP32 snapshot is included as `bip-0032.mediawiki`, preserving its BSD-2-Clause notice. It was retrieved from [the official BIP32 source](https://raw.githubusercontent.com/bitcoin/bips/master/bip-0032.mediawiki); the exact bytes are pinned by SHA-256 `e5e00a8289db2f681052cf24a745320afc225e66b25d1e489a7c884d2fc7f11f`. The download used a mutable upstream URL; this record pins the retrieved content, not an upstream Git commit.

`SHA256SUMS` fingerprints the review artifacts. It detects changes relative to the recorded manifest; it is not a signature or independent attestation.

## Runtime and reference independence

Observed runtime: PHP 8.3.35, 64-bit integers, GMP enabled, no native `secp256k1` extension. The selected adapter is `BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Adapter\EcAdapter`. Its exposed math object is `BitWasp\Bitcoin\Math\Math`, extending `Mdanter\Ecc\Math\GmpMath`.

This does not imply that every internal arithmetic operation uses only `GmpMath`: source inspection shows `ConstantTimeMath` inside Paragonie's point operations. Neither a class name nor this inspection establishes constant-time behavior of the full path.

**BitWasp with this adapter is not an independent ECC oracle for a Primitives adapter using the same Paragonie implementation.** It can provide additional composition and serialization evidence. ECC differential verification still needs a separately implemented reference, such as libsecp256k1, with its own version and provenance recorded.

## Source path map

Paths below are relative to `vendor/bitwasp/bitcoin/src`, except where stated otherwise. These are reference implementation paths, not proposed Primitives namespaces or abstractions.

| Stage | Files and principal calls |
|---|---|
| Adapter initialization | `Bitcoin.php`: `getEcAdapter()`, `getGenerator()`; `Crypto/EcAdapter/EcAdapterFactory.php`: `getAdapter()`; `Math/Math.php` |
| Extended key entry | `Key/Factory/HierarchicalKeyFactory.php`: `fromExtended()` |
| Base58 envelope | `Serializer/Key/HierarchicalKey/Base58ExtendedKeySerializer.php`: `parse()`; `Base58.php`: `decodeCheck()`, `decode()`, `checksum()` |
| BIP32 structure and version dispatch | `Serializer/Key/HierarchicalKey/ExtendedKeySerializer.php`: `parse()`, `fromParser()`; `RawExtendedKeySerializer.php`: `fromParser()`; `RawKeyParams.php`; `Serializer/Types.php` |
| Public-key decoding | `Crypto/EcAdapter/EcSerializer.php`; `Impl/PhpEcc/Serializer/Key/PublicKeySerializer.php`: `parse()`; `Impl/PhpEcc/Adapter/EcAdapter.php`: `publicKeyFromBuffer()` (the latter two under `Crypto/EcAdapter/`) |
| Relative paths | `Key/Deterministic/HierarchicalKeySequence.php`: `decodeRelative()`, `decodeDerivation()` |
| CKDpub | `Key/Deterministic/HierarchicalKey.php`: `derivePath()`, `deriveChild()`, `getHmacSeed()`, `getChildFingerprint()` |
| Hashing | `Crypto/Hash.php`: `hmac()`, `sha256ripe160()`, `sha256d()`; delegated PHP `hash_hmac()` and `hash()` |
| Scalar check and point tweak | `Crypto/EcAdapter/Impl/PhpEcc/Adapter/EcAdapter.php`: `validatePrivateKey()`; `Crypto/EcAdapter/Impl/PhpEcc/Key/PublicKey.php`: `tweakAdd()` |
| ECC machinery | `vendor/paragonie/ecc/src/Curves/SecgCurve.php`, `Primitives/CurveFp.php`, `GeneratorPoint.php`, `Point.php`, and their math helpers: generator construction, point recovery, multiplication, addition and infinity handling |
| Compressed serialization and HASH160 | `Crypto/EcAdapter/Impl/PhpEcc/Serializer/Key/PublicKeySerializer.php`: `serialize()`; `Crypto/EcAdapter/Key/Key.php`: `getPubKeyHash()` |
| Script construction | `Script/ScriptFactory.php`; `Script/Factory/OutputScriptFactory.php`; `ScriptCreator.php`; `Script/Script.php`: `getScriptHash()`; opcode and parser helpers |
| Script-to-address dispatch | `Address/AddressCreator.php`: `fromOutputScript()`; `Script/Classifier/OutputClassifier.php` and `OutputData.php` |
| P2PKH | `payToPubKeyHash()` → `PayToPubKeyHashAddress.php` → `Base58Address.php` → `Base58::encodeCheck()` |
| P2SH-P2WPKH | `witnessKeyHash()` → redeem-script `getScriptHash()` → `payToScriptHash()` → `ScriptHashAddress.php` → `Base58Address.php` → `Base58::encodeCheck()` |
| P2WPKH | `Script/WitnessProgram.php`: `v0()` → `Address/SegwitAddress.php`: `getAddress()` → `vendor/bitwasp/bech32/src/bech32.php`: `encodeSegwit()`, `validateWitnessProgram()`, `convertBits()`, `encode()`, checksum helpers |
| Network constants | `Network/NetworkFactory.php`, `Network.php`, `Networks/Bitcoin.php`, `Networks/BitcoinTestnet.php` |
| Binary support | `vendor/bitwasp/buffertools/src/Buffertools/`: `Buffer.php`, `Parser.php`, type factories, byte-order and integer/byte-string types |

`observations.json` contains the complete inventory of 134 vendor files loaded before fault injection, including autoloaded support and development files. **Loaded is not equivalent to executed, and this is not function or branch coverage.** In particular, private-key support classes loaded by the general-purpose factory do not mean that private derivation was exercised. The harness invokes public-key operations only.

## Executed evidence

| Check | Observation |
|---|---|
| Official BIP32 public-key serialization | 17 public extended keys round-trip exactly |
| Official public derivation edges | All 6 adjacent non-hardened derivation edges available in the snapshot match the expected extended public keys |
| Address regressions | 30 outputs match: 3 constructions × 2 networks × 5 indices |
| Fixture exclusions | 6 cross-network fixture entries excluded; the old implementation's network rewriting is not a correctness requirement |
| Public child index boundaries | 0 and 2147483647 return results; -1 and 2147483648 throw |
| Official invalid public-key cases | 4 of 6 selected `xpub` cases rejected; 2 accepted despite being listed as invalid |
| Diagnostics | 10 unique PHP deprecations recorded, not discarded |
| Vendor consistency | Four-package file manifest unchanged during execution |

The fixture harness normalizes extended public version bytes solely to exercise the reference BIP32/address path. This is explicitly not a Primitives import policy. No fixture results establish independent ECC correctness. The harness does not execute the full BIP32 private-key vectors or every malformed-key category.

## Findings that must not be inherited

1. **Root metadata validation:** official invalid public keys with depth zero and a nonzero parent fingerprint or child index are accepted. The fingerprint case is normalized on reserialization. Source anchors: `ExtendedKeySerializer::fromParser()`, `HierarchicalKey::__construct()`, `getFingerprint()`.
2. **Trailing payload data:** a valid BIP32 payload with one appended byte and a recalculated Base58 checksum is accepted; reserialization drops the byte. Source anchor: `ExtendedKeySerializer::parse()` returns after parsing fields without enforcing complete buffer consumption.
3. **Zero public tweak:** controlled HMAC output with `IL = 0` is rejected by `deriveChild()` because it calls a private-scalar validity check requiring a strictly positive scalar. BIP32 CKDpub invalidity is defined by `IL >= n` or an infinity result; a zero tweak with a valid parent leaves the public point unchanged and is not invalid for that reason.
4. **Invalid-child progression:** `IL = n` throws `InvalidDerivationException`; `derivePath()` also throws rather than advancing. This is a reference API behavior, not evidence that a Primitives path/result contract should copy it. The division between an exact-index operation, retry orchestration and effective-path reporting must be explicit.
5. **Infinity result:** using the public generator point and a controlled tweak of `n-1`, `deriveChild()` returns a child whose public point is infinity; extended public serialization also returns a string. BIP32 requires treating that child as invalid. Source anchors: `PublicKey::tweakAdd()` and `HierarchicalKey::deriveChild()` lack the required result check on this path.

The rare-case probes use a test-only namespaced `hash_hmac()` seam. Normal calls delegate unchanged to PHP; selected probes supply deterministic 64-byte HMAC outputs. Vendor files are not patched. The injection mechanism, public generator point and scalar values are visible in `inspect.php`. These are controlled protocol-boundary experiments, not claims that a natural HMAC collision was found, or that a deployed payment was affected.

Normative comparison: [BIP32](https://github.com/bitcoin/bips/blob/master/bip-0032.mediawiki). Address composition references for the implementation phase: [BIP141](https://github.com/bitcoin/bips/blob/master/bip-0141.mediawiki) and [BIP173](https://github.com/bitcoin/bips/blob/master/bip-0173.mediawiki). This run does not claim to have executed their complete official address suites.

## Reproduction

The issued `SHA256SUMS` identifies the original pack at Git revision `61c2e48`.
This README's path example was updated when the pack moved; its original hash
therefore applies to that revision. Verify unchanged data and scripts here with
`sed '/  README.md$/d' SHA256SUMS | sha256sum -c`. Set the two absolute host
paths, then run with the recorded local image if available:

```bash
reference_root=/absolute/path/to/paycrypto-me-for-woocommerce/src/trunk
review_root=/absolute/path/to/paycrypto-me-primitives-php/docs/pips/PIP-0003/M00/reviews/bitwasp-public-derivation
review_image=$(cat "$review_root/image-id.txt")
docker run --rm --network none --read-only \
  --mount "type=bind,src=$reference_root,dst=/reference,readonly" \
  --mount "type=bind,src=$review_root,dst=/evidence,readonly" \
  --entrypoint php "$review_image" /evidence/inspect.php \
  > /tmp/bitwasp-review-reproduced.json
cmp "$review_root/observations.json" /tmp/bitwasp-review-reproduced.json
```

On a different image or vendor installation, compare runtime and source identities before comparing outcomes; do not overwrite the original evidence. `inspect.php` is an observational harness and does not use its exit code as a conformance verdict. Exceptions under investigation are deliberately captured in JSON. The reference installation includes development autoload files; a production-only installation will have a different loaded-file inventory.

## Required implementation-review gates

- Map every admitted Primitives behavior to its normative source, contract tests and evidence; preserve expected outputs independently of the implementation under test.
- Include strict malformed-key tests, zero tweak, `IL >= n`, infinity, depth overflow, hardened rejection, index exhaustion, and explicit effective-path/error semantics.
- Separate official vectors, regression observations and independent differential evidence. Record shared dependencies so two wrappers around one backend do not count as two independent implementations.
- Run the full admitted address and encoding vector suites, including leading-zero bytes, checksum failures and invalid witness programs.
- Record dependency revisions, runtime requirements, source hashes, commands, outputs and unresolved discrepancies. Never resolve disagreements by majority vote.
- Validate the actual Primitives capability on PHP 8.1 and 8.3. This reference investigation ran only on PHP 8.3.35 and cannot replace those checks.
- Present the reviewer with a frozen revision and evidence manifest. External independent review and upstream reporting have not been performed here.
