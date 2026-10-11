# Historical Candidate Worksheet — Public Address Derivation

**Evidence checkpoint: 2026-09-28. Preserved during the 2026-10-02 plan rewrite.**

**Superseded for ongoing research:** use the [current provider worksheet](public-address-provider-research.md)
for updated recommendations, executed probes and new user candidate contributions.
The tables below preserve the earlier checkpoint.

This worksheet records earlier research leads, not current provider qualification
or dependency selections. Consult the [consolidated capability record](../../PIP-0003.md)
for scope and open decisions. No external source was
revalidated as part of the documentation rewrite.

This is the earlier worksheet for candidate discussion. D01–D11 match
the [delegation inventory](delegation-inventory.md); that document retains
the detailed requirements and verification criteria. C01–C07 below identify its
composite-provider and internal-entity considerations, not new public capabilities.

**Assistant recommendation** records the proposed first investigation target
among the options examined at that checkpoint, given PHP 8.1, permitted GMP and
our domain boundaries.
It is an engineering recommendation, not evidence of superiority, compatibility
or audit approval. Exact supported releases remain to be qualified. Where the
evidence does not support a production front-runner, the table says so explicitly.

**User candidates** cells are intentionally empty for the user's own research.
Add package/runtime names and links there; versions and notes are welcome. One
provider may cover multiple rows, and a runtime facility can be preferable to a
new Composer dependency. No row mandates a separate class, interface or package.

## Algorithms, public-key operations and codecs

| ID | Delegated item / required use | Assistant recommendation | Reason and qualification still needed | User candidates |
|---|---|---|---|---|
| D01 | SHA-256: HASH160 and checksum hashing | PHP Hash runtime: [`hash('sha256', ..., true)`][php-hash] | Direct runtime implementation with binary output; verify known-answer vectors and availability on both supported runtimes. | |
| D02 | RIPEMD-160: public-key and redeem-script hashing | PHP Hash runtime: [`hash('ripemd160', ..., true)`][php-hash] | Same runtime boundary as D01; confirm algorithm availability, 20-byte output and vectors. | |
| D03 | HMAC-SHA512: public derivation | PHP Hash runtime: [`hash_hmac('sha512', ..., ..., true)`][php-hmac] | Avoid a separate HMAC dependency; verify key/data order, long-key vectors and 64-byte output. | |
| D04 | secp256k1 public-key parsing, validation and compressed serialization | [`paragonie/ecc`][paragonie] first; [`simplito/elliptic-php`][elliptic] as comparison candidate | Fits the initial canonical candidate and permitted GMP deployment. Qualify compressed-key validation and contain all point types. Research [`libsecp256k1`][secp] as the independent ECC reference and possible future backend; no PHP binding is selected. | |
| D05 | secp256k1 public-key scalar tweak addition | [`paragonie/ecc`][paragonie] through a minimal adapter; compare with [`libsecp256k1`][secp] | Reuse D04's provider rather than duplicate ECC stacks. Explicitly test zero tweak, scalar bounds and infinity; a passing BitWasp run using Paragonie is not independent ECC evidence. | |
| D06 | Base58 encode/decode with the Bitcoin alphabet | [`tuupola/base58`][tuupola] first | Focused codec with documented PHP/GMP implementations. Explicitly select the Bitcoin alphabet and a backend; do not inherit its environment-dependent default selection. Verify leading zeros and malformed inputs. | |
| D07 | Base58Check for extended keys and address payloads | [`tuupola/base58`][tuupola] as a conditional first trial; compare the inspected [`BitWasp Base58`][bitwasp-base58] implementation | Tuupola documents checksum support, but full four-byte extended-key-version handling is not established here. BitWasp provides the broader payload reference, with a larger dependency graph. Reject a candidate that cannot cover the required payloads; do not fill gaps with a local codec. | |
| D08 | Bech32 encoding/decoding machinery | [`bitwasp/bech32`][bech32] first | Dedicated package rather than the full Bitcoin library. Qualify official valid/invalid vectors, case/length rules, checksum and padding behavior, release compatibility and maintenance. | |
| D09 | SegWit v0 address codec for P2WPKH | [`bitwasp/bech32`][bech32] `encodeSegwit` / `decodeSegwit` | Reuse D08's provider. Keep the admitted policy at witness v0 with a 20-byte program; verify HRP behavior and negative cases. Codec capabilities beyond this do not expand our API. | |
| D10 | Structural decoding of extended public keys | Investigate [`BitWasp RawExtendedKeySerializer`][bitwasp-raw] as a scoped parsing candidate; no qualified production choice yet | Existing structural machinery is preferable to writing a codec. Assess dependency cost and strict wrapper invariants: exact length, public-only admission, root metadata and version consistency. The inspected high-level parser is not acceptable unchanged. | |
| D11 | Fixed-width binary integer and byte conversion | PHP [`pack()`][php-pack] / [`unpack()`][php-unpack] first; `bitwasp/buffertools` only if a concrete gap remains | Runtime delegation avoids a generic buffer dependency. Verify endianness, unsigned ranges and integer-width assumptions. Domain field definitions remain ours; standardized conversion machinery does not. | |

## Composite operations and provider-internal entities

These rows do not reclassify domain-owned composition as an external entity.
They identify where delegated implementations are needed underneath it.

| ID | Item / responsibility | Assistant recommendation | Reason and qualification still needed | User candidates |
|---|---|---|---|---|
| C01 | BIP32 public derivation provider; protocol contract remains owned | No production front-runner established. Keep [`bitwasp/bitcoin`][bitwasp] as a reference and compare additional providers before choosing the integration. | The inspected version's zero-tweak/infinity behavior prevents recommending it unchanged. Primitives may own CKDpub semantic composition over delegated HMAC and ECC, as established by canonical §§10A and 12. Compare composite providers against that boundary; do not port reference algorithm internals. | |
| C02 | HASH160 operation | Compose the delegated PHP implementations recommended for D01 and D02 | This is owned operation composition, not a new hash implementation or a reason for another package. Verify composition order and expected outputs. | |
| C03 | Double-SHA256/checksum support | Prefer the selected D07 provider for the complete Base58Check operation; reuse D01 where an admitted operation needs hashing | Keep checksum and encoding algorithms delegated. Do not infer permission to implement a local Base58Check codec from the availability of `hash()`. | |
| C04 | Big integers, field elements and modular arithmetic | Use the chosen ECC provider's internal machinery; GMP is permitted | Do not select a domain-wide big-integer API separately. Assess transitive dependencies and prohibit external math objects in capability signatures. | |
| C05 | Points, generators, curves and infinity representations | Keep these inside the D04/D05 provider and adapter | They are backend mechanisms, not domain entities. Map invalid/infinity outcomes to our semantic contract. | |
| C06 | Buffers, parsers and serializers | Prefer runtime byte operations and the selected codec's internal types; assess `bitwasp/buffertools` only if justified | Avoid importing a generic buffer abstraction into public contracts. Qualify bounds checking and runtime diagnostics. | |
| C07 | Script serialization helpers for the three address constructions | No separate script engine recommended. Evaluate [`BitWasp script helpers`][bitwasp-script] only if delegated serialization is needed beyond the chosen provider surface. | Bitcoin address/script semantics remain owned. Reject an unrelated interpreter, transaction or signing dependency unless its footprint is explicitly justified; do not reproduce a standardized serializer locally. | |

## Recommendation basis and limitations

Primary documentation and available source were checked for this worksheet on
2026-09-28. PHP documents the hashing and binary-conversion facilities above.
The Paragonie development manifest inspected then declared PHP 8 support and GMP;
this does not qualify a particular release. The inspected Simplito manifest also
required GMP, so it was not proposed as a GMP-free alternative. Tuupola documented
multiple backends and automatic selection; explicit selection is a qualification
gate. The inspected Bech32 package exposed generic and SegWit codec functions.
The BitWasp concerns come from our preserved, version-specific investigation.

Library documentation, manifests and a focused inspection are preliminary
evidence. This checkpoint adds no new executable qualification results and makes
no claim that every candidate's maintenance, security history or PHP matrix has
already been assessed. Source links to moving branches must be replaced by exact
release/commit references in the eventual selection records.

## Joint research and selection workflow

1. Collect independently researched candidates, including user contributions in
   the empty column when provided. Empty cells do not block local research.
2. Combine both lists by item, retaining why each candidate was proposed. Expand
   the search where neither list provides a credible implementation.
3. Evaluate the same contract, runtime constraints, license, maintenance evidence,
   dependency footprint, vectors, failure cases and reference independence for
   every candidate. Record rejected options and reasons.
4. Discuss the evidence together and explicitly record the selected provider and
   exact version for each item. A recommendation or an empty user cell is not a
   selection or approval; shared providers can cover multiple items.
5. Reconcile the resulting contracts and provider selections with the current
   plan. Follow its implementation and Counter-Proof gates; this historical
   worksheet does not define a separate execution sequence.

[php-hash]: https://www.php.net/manual/en/function.hash.php
[php-hmac]: https://www.php.net/manual/en/function.hash-hmac.php
[php-pack]: https://www.php.net/manual/en/function.pack.php
[php-unpack]: https://www.php.net/manual/en/function.unpack.php
[paragonie]: https://github.com/paragonie/phpecc
[elliptic]: https://github.com/simplito/elliptic-php
[secp]: https://github.com/bitcoin-core/secp256k1
[tuupola]: https://github.com/tuupola/base58
[bech32]: https://github.com/Bit-Wasp/bech32
[bitwasp]: https://github.com/Bit-Wasp/bitcoin-php
[bitwasp-base58]: https://github.com/Bit-Wasp/bitcoin-php/blob/527b1ee7d2cd958b5ce011c7918801ffbfb53f97/src/Base58.php
[bitwasp-raw]: https://github.com/Bit-Wasp/bitcoin-php/blob/527b1ee7d2cd958b5ce011c7918801ffbfb53f97/src/Serializer/Key/HierarchicalKey/RawExtendedKeySerializer.php
[bitwasp-script]: https://github.com/Bit-Wasp/bitcoin-php/blob/527b1ee7d2cd958b5ce011c7918801ffbfb53f97/src/Script/Factory/OutputScriptFactory.php
