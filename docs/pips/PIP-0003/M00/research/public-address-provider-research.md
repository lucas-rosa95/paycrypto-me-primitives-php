# Public Address Derivation — Provider Research

**Research checkpoint: 2026-10-02. Preliminary feasibility evidence; no production
selection. Historical work: [PIP-0002](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/83ccd5571843eed57726e140e493868f0f152b5f/docs/pips/PIP-0002/PIP-0002.md).**

This is the research worksheet linked from the [consolidated first-capability record](../../PIP-0003.md).
It updates the [2026-09-28 recommendations](public-address-candidate-worksheet.md)
with pinned package metadata, source inspection and bounded executable probes.
The earlier worksheet and BitWasp observations remain historical evidence.

The **User candidates** column remains empty for independent research. The
assistant's recommendations identify what to qualify next, not approved
production dependencies. Full contracts, two-provider replacement qualification
and independent verification remain open.

**Framing correction: 2026-10-03.** D01–D11 were derived from required operations,
but the initial candidate investigation relied substantially on familiar packages.
It is not an exhaustive comparison and does not establish the best providers.
The dated probe results below remain unchanged; this correction adds no new
external research or qualification evidence.

The research sequence is:

1. Identify the operation required by the admitted public-address flow.
2. Establish its owned semantics, delegated machinery, minimum contract,
   runtime constraints and verification criteria without a provider in mind.
3. Search for focused runtime/library implementations of that delegated need.
4. Compare candidates, including footprint and unwanted/transitive functionality,
   then qualify their actual integration before joint selection.

For this slice, **`bitwasp/bitcoin` is excluded from production options**. It is
retained only as comparison/reference evidence. Wrapping it in an adapter would
not remove its broad dependency footprint. A separate focused package such as
`bitwasp/bech32` is assessed independently; organization identity is not a reason
to accept or reject a package. References and transitive dependencies below are
not additional direct production recommendations.

## 1. Changes to the earlier recommendations

1. **Keep PHP runtime hashing and conversion first.** Known-answer hashing and
   unsigned fixed-width conversion probes passed on PHP 8.1.34 and 8.3.35.
2. **Evaluate Paragonie ECC 2.6.0 explicitly.** WooCommerce still installs 2.5.0;
   the latest stable release returned by Packagist is 2.6.0. Simplito 1.0.12 is
   a useful second API to exercise, not a GMP-free substitute. Bounded public
   point-operation probes ran against both, using the same inputs.
3. **Retain Tuupola, with concrete adapter requirements.** Its checked API has a
   one-byte version field. A provider-specific split of the serialized payload
   reproduced all 17 official public XPUB strings in the stored BIP32 snapshot.
   Passing a four-byte version as the option did not reproduce the XPUB. Empty
   checked input returned an empty string, so admission must reject it explicitly.
4. **Add StephenHill as a raw Base58 comparison candidate.** Four deterministic
   binary inputs agreed with Tuupola. It does not supply Base58Check and must not
   be counted as a second complete checked codec. Explicit GMP or BCMath service
   selection is required; its constructor otherwise selects by the environment.
5. **Keep BitWasp Bech32 as a focused candidate, with a disclosed maintenance gap.**
   The published release is from 2018; its README advertises older PHP versions.
   Current-runtime probes recorded acceptance/rejection of the selected BIP173
   examples and exact address/program agreement for three v0 cases. The generic
   success checks do not compare decoded HRP/data. This is stronger evidence
   than compatibility inferred from a dependency-free manifest, but not full
   codec conformance.
6. **Do not require a full Bitcoin/BIP32 package.** Prefer investigating the
   canonical CKDpub composition over qualified HMAC/ECC capabilities. For fixed
   extended-key fields, qualify runtime binary conversion plus owned field and
   admission semantics before importing a large Bitcoin package just for parsing.
   A focused external composition provider may be investigated if justified;
   the broad `bitwasp/bitcoin` package is not a production option. Owned CKDpub
   composition is an initial design option, not a fallback after package search.

## 2. Candidate worksheet

D01–D11 retain the [inventory](delegation-inventory.md) identities. A row does not
mandate a separate interface or package. Exact package revisions are in §3.

| IDs | Required operation | Assistant recommendation | Evidence and remaining qualification | User candidates |
|---|---|---|---|---|
| D01 | SHA-256 | PHP `hash`, binary output | Known-answer probe passed on both runtimes; complete binary/length corpus still needed. | |
| D02 | RIPEMD-160 | PHP `hash`, binary output | Known-answer probe passed on both runtimes; verify composition order and all supported environments. | |
| D03 | HMAC-SHA512 | PHP `hash_hmac`, binary output | RFC 4231 case 1 passed on both runtimes; long-key cases, argument order and BIP32 composition qualification remain. | |
| D04 | Compressed public-key parsing, validity and serialization | Paragonie 2.6.0 first; Simplito 1.0.12 comparison | Raw APIs differ in rejection behavior. Enforce exact key size before delegation and verify curve validity/postconditions. Full invalid-point corpus and PHP 8.1+GMP remain open. | |
| D05 | Public scalar tweak addition | Same ECC candidates; libsecp256k1 for independent reference research | Zero/one/order/infinity probes exercised both APIs. Scalar admission and infinity-to-failure mapping belong to the contract/adapter. No complete replacement or independent ECC gate is passed. | |
| D06 | Bitcoin-alphabet Base58 | Explicit Tuupola `PhpEncoder`; compare explicit StephenHill service | Tuupola ran without GMP on PHP 8.1. Four raw cases agreed with StephenHill/GMP on 8.3. Require wider corpus and explain shared lineage before claiming independence. | |
| D07 | Base58Check | Tuupola checked encoder as conditional first candidate | 17 XPUB byte-split round trips and one P2PKH example passed. The one-byte option is not the four-byte BIP32 version. Enforce empty/length/full-version admission and test every admitted public version. A second qualified checked-codec provider remains open; BitWasp is comparison evidence only. | |
| D08 | Bech32 | `bitwasp/bech32` 0.0.1, conditional | Seven valid generic examples returned arrays and twelve invalid examples threw on both runtimes; decoded generic HRP/data was not compared with independent expected values. Maintenance/support disposition and a second distinct provider remain open. | |
| D09 | SegWit v0 address codec | Same focused Bech32 package | Three valid v0 examples matched the expected address and witness program; ten invalid SegWit examples threw. Only P2WPKH is admitted; codec support for other witness versions must not expand the public API. | |
| D10 | Extended public-key structural decoding | Qualify runtime `unpack` for fixed fields, plus owned admission | This revises the previous first lead. Field widths/order and interpretation remain owned; conversions are delegated to runtime. BitWasp's serializer is comparison evidence only. No bespoke generic decoder or copied serializer. Full structural qualification has not run. | |
| D11 | Fixed-width conversion | PHP `pack` / `unpack` | Unsigned 32-bit round trips at 0, 2^31−1 and 2^32−1 passed on 64-bit PHP 8.1/8.3. Document host-width requirements; no 32-bit result is established. | |

For the inventory's C01–C07 considerations: C01 is owned CKDpub composition over
D03/D05 unless a qualified integration proves better; C02 remains owned HASH160
composition over D01/D02; C03 stays inside the selected checked codec. C04/C05
are provider math/point internals. C06 should start with runtime conversions,
without a generic buffer API. C07 needs a separately justified serializer only
where runtime facilities and owned script-field semantics cannot satisfy the
admitted construction without implementing standardized machinery.

## 3. Package roles and versioned evidence

These are the latest stable entries returned by the public Packagist metadata
endpoints at this checkpoint, not permanently current release claims. The
[metadata snapshot](provider-refresh-2026-10-02/package-metadata.json) records full
source commits, declared requirements, licenses and release timestamps.

### Direct production candidates — unselected

| Package | Version / release date | Source | Declared production requirements / license |
|---|---|---|---|
| paragonie/ecc | 2.6.0 / 2026-08-20 | [1a493804](https://github.com/paragonie/phpecc/tree/1a49380410b8ce826bc7fd3de6324053e049205c) | PHP ^7.1 or ^8.0, GMP, genkgo/php-asn1 ^2, sodium_compat ^1 or ^2; MIT. |
| simplito/elliptic-php | 1.0.12 / 2024-01-09 | [be321666](https://github.com/simplito/elliptic-php/tree/be321666781be2be2c89c79c43ffcac834bc8868) | GMP, bn-php ~1.1.0; MIT. No explicit PHP constraint in this manifest is not proof of compatibility. |
| tuupola/base58 | 2.2.0 / 2025-12-29 | [a2fac671](https://github.com/tuupola/base58/tree/a2fac671f14890c8fa62ac081eeeb90f42839637) | PHP ^7.2 or ^8.0; MIT. Direct `PhpEncoder` avoids automatic GMP selection. |
| stephenhill/base58 | 2.1.0 / 2025-11-19 | [3030c00c](https://github.com/stephen-hill/base58php/tree/3030c00c0a1e1b78520f3ace6fbf813dacddfab5) | PHP >=8.1; MIT. Runtime service additionally needs GMP or BCMath. |
| bitwasp/bech32 | 0.0.1 / 2018-02-05 | [e1ea58c8](https://github.com/Bit-Wasp/bech32/tree/e1ea58c848a4ec59d81b697b3dfe9cc99968d0e7) | No production require block; Unlicense. Old release/support documentation needs explicit disposition. |

### Transitive dependencies of an ECC candidate

These support Simplito internally; they are not separately required domain capabilities.

| Package | Version / release date | Source | Declared production requirements / license |
|---|---|---|---|
| simplito/bn-php | 1.1.4 / 2024-01-10 | [83446756](https://github.com/simplito/bn-php/tree/83446756a81720eacc2ffb87ff97958431451fd6) | bigint-wrapper-php ~1.0.0; MIT. |
| simplito/bigint-wrapper-php | 1.0.0 / see metadata | [cf21ec76](https://github.com/simplito/bigint-wrapper-php/tree/cf21ec76d33f103add487b3eadbd9f5033a25930) | No declared require block; implementation selects GMP/BCMath unless explicitly configured; MIT. |

### Reference stack only — not direct production candidates

BitWasp Bitcoin is excluded from production for this slice. Buffertools is recorded
here as part of that reference stack, not recommended as a direct dependency.

| Package | Version / release date | Source | Declared production requirements / license |
|---|---|---|---|
| bitwasp/bitcoin | 1.1.0 / 2026-02-25 | [527b1ee7](https://github.com/Bit-Wasp/bitcoin-php/tree/527b1ee7d2cd958b5ce011c7918801ffbfb53f97) | 64-bit PHP >=7.0, ECC, Bech32, Buffertools, Composer Semver, Merkle tree and MurmurHash packages; Unlicense. |
| bitwasp/buffertools | 0.5.7 / 2020-01-17 | [133746d0](https://github.com/Bit-Wasp/buffertools-php/tree/133746d0b514e0016d8479b54aa97475405a9f1f) | 64-bit PHP >=7.0; MIT. Runtime diagnostics remain a qualification concern. |

The Paragonie probe directly loads its 2.6.0 math/point source; it is not a
Composer-resolved production installation of its complete dependency graph.
Simplito's two math dependencies were pinned and explicitly configured for GMP.
Both share the GMP extension, even though their PHP APIs and curve implementations
differ. Mathematical independence must be assessed separately from API diversity.

The Paragonie README describes OpenSSL and alternative implementation paths.
Do not infer that any method automatically executes OpenSSL. The probe explicitly
uses `SecgCurve::generator256k1(null, false)` and records the resulting class names;
it does not qualify the optimized or OpenSSL paths.

### Additional leads considered

- **Native libsecp256k1:** retain as the independent ECC reference target. The
  [BitWasp PHP binding](https://github.com/Bit-Wasp/secp256k1-php) is a separate
  qualification problem: the API snapshot reported default branch `v0.2` and
  last push 2022-05-16. No binding build, PHP 8.1/8.3 compatibility, ABI or native
  public-tweak test was performed. Do not recommend native production deployment
  solely from libsecp256k1's capabilities.
- **cardano-php/bech32 1.1.0:** its
  [pinned manifest](https://github.com/cardano-php/bech32/blob/4d46b51f78a1968d7d0a0ddccf9595ac47294060/composer.json)
  requires PHP ^8.2 and sodium. Exclude that release from the current PHP 8.1
  candidate set. Its Cardano orientation also does not establish Bitcoin SegWit
  semantics. No executable qualification was attempted.

### Security and maintenance evidence

The [advisory snapshot](provider-refresh-2026-10-02/advisories.json) records the
Packagist public advisory API response for the six primary library candidates.
It reports historical timing advisories for Paragonie `<2.0.1` within the 2.x
range and Simplito `<1.0.6`; the investigated 2.6.0 and 1.0.12 releases are outside
those stated ranges. It returned no entries for BitWasp Bitcoin/Bech32, Tuupola
and StephenHill. This is a limited registry query, not a security audit, exhaustive
advisory search or complete transitive-dependency scan. Public-only use does not
justify ignoring maintenance or input-validation findings.

Release dates and manifest constraints are evidence to consider, not maintenance
scores. Before selection, assess maintainer response, unresolved relevant issues,
release/support policy, licenses for the resolved graph and a full dependency
advisory check. No candidate receives a security certification here.

## 4. Executed observations and their limits

[Reproduction and artifacts](provider-refresh-2026-10-02/README.md) identify the
probe source, exact inputs, package commits, image IDs, commands and JSON outputs.
No plugin code, private key, seed or signing operation was loaded or invoked.

**2026-10-18 review clarification:** [Issue #28's focused evidence check](../reviews/issue-28-deep-review.md)
narrows the original generic Bech32 wording to the acceptance/rejection
observations actually recorded. Its [JSON verifier](../reviews/issue-28-bech32-evidence-check.py)
asserts the selected vector inventory and observed outcome shape without editing
the issued probe or outputs. A generic decoder that returned the wrong HRP/data
while still returning an array would pass M00's success check. M04's later
qualification remains separate evidence.

| Investigation | Result | Interpretation |
|---|---|---|
| Existing BitWasp harness, PHP 8.3.35 | New JSON is byte-for-byte identical to the preserved observations. | The known parser/zero-tweak/infinity findings remain relevant to the installed stack. No new independent oracle is established. |
| PHP hashing / integer conversion | Three known-answer hashes and three unsigned conversion cases passed on both runtimes. | Useful runtime feasibility evidence, not a complete algorithm conformance corpus. |
| Tuupola 2.2.0, explicit PHP backend | 17 BIP32 public XPUB round trips, one P2PKH example, leading-zero handling and invalid-alphabet/checksum cases behaved as recorded on both runtimes. | Four-byte version support requires representation adaptation and full admission; all admitted SLIP-132/network versions still need tests. |
| Tuupola edge behavior | Empty checked decode returned empty; four-byte version option failed to reproduce the XPUB. | Never equate a returned value with validated extended-key input. Do not pass the whole BIP32 version into this one-byte option. |
| StephenHill 2.1.0, explicit GMP | Four binary raw-Base58 cases matched Tuupola and invalid alphabet was rejected on PHP 8.3. | Raw-codec comparison only; no second Base58Check provider or independent-lineage claim. |
| BitWasp Bech32 | 7 generic valid examples returned arrays and 12 generic invalid examples threw; 3 valid v0 SegWit address/program comparisons passed, 10 invalid SegWit examples threw, and a 21-byte v0 program was rejected on both runtimes. | Generic decoded HRP/data was not compared; the corpus establishes only these bounded outcomes. Two 32-byte v0 programs check codec behavior without adding P2WSH delivery. No v1+ valid address support is claimed. |
| Paragonie 2.6.0 and Simplito 1.0.12 | Zero returned the parent; one returned matching serialized points; order-sized tweak returned the parent; `n−1` with generator parent produced infinity. | These are raw math observations. The semantic adapter must reject an out-of-range tweak and map infinity; mathematical modulo behavior is not itself a library defect. |
| Compressed key length | Paragonie parsed and normalized the 34-byte leading-zero variant; Simplito rejected it. | Exact 33-byte admission must precede delegated point parsing. Successful parsing alone does not establish the domain invariant. |
| Diagnostics / PHP matrix | New probes recorded no diagnostics. ECC and StephenHill/GMP were skipped on PHP 8.1 because the available image lacks GMP. | Full PHP 8.1 ECC compatibility is still untested. The old BitWasp harness still records its existing diagnostics. |

These probes call vendor APIs directly. They do **not** implement production
adapters or run an unchanged BIP32 composition with two backends. Consequently,
the plan's replacement gate remains open, even where two libraries were exercised.
The harness captures exceptions as observations; process exit alone is not a
conformance verdict.

## 5. Next qualification work and joint selection

1. Refine D01–D11 contracts independently of vendor APIs and broaden discovery
   from those requirements. Add independently researched user candidates without
   treating the assistant's familiar starting points as preferred or exhaustive.
   Compare all candidates against the same requirements and footprint constraints.
2. Turn ECC observations into a semantic adapter spike with explicit size/range/
   infinity checks, the full invalid-point corpus and PHP 8.1+GMP execution.
   Add libsecp256k1 or another justified independent reference.
3. Qualify complete key admission and all admitted public versions. Exercise the
   Tuupola representation adaptation without allowing it to define the domain's
   version model. Evaluate the second checked-codec boundary explicitly.
4. Close the Bech32 second-provider and maintenance gaps. A thin wrapper around
   the same BitWasp implementation does not satisfy distinct-library evidence.
5. Qualify owned CKDpub composition, invalid-child/effective-path behavior,
   metadata, errors and all three address branches through the selected minimal
   boundaries. Run an actual Composer-resolved runtime/dependency matrix before
   final selection.

Apply the [current canonical's](../../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md)
technical Counter-Proofs and the [current reporting protocol](../../../../protocols/primitives/COUNTER-PROOF-REPORT-v1.5.md)
to the actual implementation artifact. The consolidated record identifies the
historical plan's two-library exercise as a verification method to reassess,
not an approved current gate.
This research improves candidate evidence; it neither replaces the user's
independent research nor records joint approval of a provider.

A missing suitable algorithm or codec provider does not authorize implementing
it inside Primitives. Further research or explicit scope reconsideration is
required. Creating a separate specialized library would be separately admitted
work with its own maintenance and verification obligations, not an automatic
escape from the domain's delegation boundary.
