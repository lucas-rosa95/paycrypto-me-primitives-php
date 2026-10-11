# M01 — Minimal public-address contracts

Work: PIP-0003 / M01. Status: contract draft for M02 investigation.

This document specifies semantic boundaries, not accepted PHP interfaces or
selected implementations. Requirement and qualification IDs are local to this
slice. A row does not require a separate class, package, interface or public API.
The [current plan](../EXECUTION-PLAN.md) and
[delegation inventory](../../../research/delegation-inventory.md) govern it.
Production contracts freeze at M05 after relevant qualification.

## 1. Requirements and responsibility map

| ID | Concrete requirement | OWN / COMPOSE | DELEGATE inventory | Qualification |
|---|---|---|---|---|
| R01 | Receive explicit public extended key, relative public path, network and address policy; return address or defined failure locally. | Operation admission and orchestration; result meaning. | Through the compositions below. | Q01 |
| R02 | Consume validated mainnet/testnet parameters without changing behavior by network identity. | Definition validation and immutable semantic views. | D11 where binary conversions are needed. | Q02 |
| R03 | Admit only valid supported serialized extended public keys. | Exact structure, metadata, version/material admission and semantic key value. | D04, D06–D07, D10–D11. | Q03 |
| R04 | Admit explicit bounded relative non-hardened paths. | Path/index/depth invariants; no inferred account layout. | D11 for index serialization. | Q04 |
| R05 | Derive public children with correct BIP32 metadata and observable path semantics. | CKDpub composition and derivation outcomes. | D03–D05, D11; D01–D02 for parent fingerprint. | Q05 |
| R06 | Validate and retain compressed secp256k1 public keys. | Public-key value invariant and normalized failures. | D04 parsing, curve validation and serialization. | Q06 |
| R07 | Apply a public scalar tweak without exposing curve machinery. | Scalar input invariant and semantic outcome mapping. | D05 point multiplication/addition and compressed serialization. | Q07 |
| R08 | Hash public material and BIP32 messages with exact binary outputs. | HASH160 composition and protocol input construction. | D01–D03 runtime algorithms. | Q08 |
| R09 | Produce P2PKH addresses. | Public-key hash plus validated P2PKH version. | D01–D02, D06–D07. | Q09 |
| R10 | Produce P2SH-P2WPKH addresses. | Witness redeem-script semantics and its HASH160 plus P2SH version. | D01–D02, D06–D07; qualify any needed serialization machinery. | Q10 |
| R11 | Produce P2WPKH addresses. | Witness v0 semantics, exact 20-byte key hash and network HRP. | D01–D02, D08–D09. | Q11 |
| R12 | Preserve meaningful failures and explicit provider replacement. | Failure categories, bounded work, wiring and containment. | All executed adapters/runtime facilities. | Q12 |

D06 and D08 are codec internals when the selected checked/witness codec covers
them. D10–D11 are conversion mechanisms, not a generic buffer framework. D04 and
D05 may share one ECC adapter. R09–R11 share R03–R08 until their terminal semantics
diverge. A complete external BIP32 package is not required.

## 2. Input, output and invariant contracts

### R06–R07: compressed key and public tweak

A compressed public-key value represents exactly 33 bytes with prefix 02 or 03
and a valid finite secp256k1 point. Length/prefix checks alone do not establish
curve validity. Parsing, point validation and serialization are delegated. The
value exposes semantic bytes only; provider points and GMP objects stay internal.

The tweak operation consumes an admitted compressed key and exactly 32 bytes
representing an unsigned big-endian public tweak. Its proposed admitted range is
0 through n−1, where n is the secp256k1 group order. It must not reduce an
out-of-range input modulo n. The result is a valid compressed public key for
P + tG, or a distinguished infinity outcome. Malformed/range-invalid inputs and
provider failure are separate outcomes. No private-key object is an input.

Zero-tweak identity is a required qualification case, distinct from private-key
or master-key scalar admission. M02 must confirm its protocol rationale before
freezing this draft; M03 must establish the actual adapter behavior. Infinity
never becomes a serialized public-key value or a successful address result.

### R05: public child and relative-path derivation

Inputs are an admitted extended public key and an admitted non-hardened index,
or a bounded sequence of such indices. Outputs retain a valid child key, exact
32-byte chain code and correct metadata. The composition owns message layout,
HMAC split, scalar admission, child outcome and metadata transitions. HMAC/ECC
algorithms and fixed-width conversions are delegated.

Each successful edge increments depth without overflow, records the index
actually used and computes the parent fingerprint from the parent's public key.
Neither fingerprint nor imported metadata is proof of ancestry. Publicly
imported keys may have a hardened historical child number; requesting a new
hardened child remains prohibited. R03 must not reject such ancestry merely
because R04 permits only public requests.

Invalid tweak range and infinity are derivation outcomes, not environment
failures. A low-level attempted edge must not silently advance its index.
Whether the selected operation exposes exact-index attempts, advancing BIP32
orchestration or both is O01; this statement sketches separation rather than
freezing a PHP API. If advancement is admitted, requested and effective paths
remain distinguishable at every level. No retry may cross into hardened space,
wrap an integer, return partial success or substitute another provider.

### R03: extended public-key admission

Inputs enter through delegated Base58Check decoding or a deliberately chosen
prevalidated-key boundary (O03). The checksum-free serialized structure is
exactly 78 bytes: version 4, depth 1, parent fingerprint 4, child number 4,
chain code 32 and compressed public key 33. Conversions use qualified runtime
or library facilities. Reject truncated or trailing data and invalid checksums.

Root depth requires zero parent fingerprint and zero child number. Admit only
supported public version/material combinations and validated points. Private
versions/material, unknown versions and incompatible intent are rejected, never
converted to public material. Full version bytes, network meaning and any
SLIP-132 policy meaning remain separate from generic BIP32 structure (O02).
Leading zero bytes in fields are preserved. Chain codes are fixed-size bytes;
they are not private scalars and do not inherit private-scalar validity tests.

### R04: relative public path

The semantic path is a finite ordered sequence of public indices 0 through
2^31−1 relative to the imported node. Absolute wallet origins, account-depth
assumptions and a default 0/index path are not inferred. Malformed, negative,
fractional, hardened and out-of-range requests fail before derivation. The
remaining depth capacity is checked against the path length. Syntax, empty-path
admission and explicit resource limits remain O04; no permissive string grammar
or unbounded retry behavior is accepted by default.

### R02: definitions and compatibility

Loading yields immutable semantic views for public-version admission, P2PKH and
P2SH version bytes, and SegWit HRP/profile compatibility. Views contain data only:
no vendor classes, callbacks, execution graphs or fallback lists. Validate required
fields, widths, values and cross-field compatibility before use. Unknown-field
handling, physical format and version/profile allowlists are O02/O05. Preserve
parameter provenance. Mainnet and testnet consume the same compositions with
different admitted data. A network name alone is not validation evidence.

### R08: hashing and conversion

SHA-256 and RIPEMD-160 consume exact byte sequences and return respectively
32 and 20 binary bytes. HMAC-SHA512 consumes explicitly distinguished key/message
byte sequences and returns 64 binary bytes. HASH160 applies SHA-256 followed by
RIPEMD-160 to the intended input bytes. No implicit hex-text hashing or text
normalization is allowed. Conversions preserve widths, unsigned values, byte
order and leading zeros without float coercion. Runtime availability and host
integer width are qualification obligations, not inferred from Composer's PHP
version constraint. Missing algorithms/extensions are environment failures.

### R09–R11: address semantics and codecs

P2PKH consumes the HASH160 of the derived compressed public key and its admitted
P2PKH version. P2SH-P2WPKH consumes the HASH160 of the witness-v0/20-byte-key-hash
redeem script and its admitted P2SH version. These are distinct 20-byte semantic
payloads. The domain owns the redeem-script meaning; M02/O07 must identify and
qualify suitable runtime/library construction mechanisms without introducing a
generic script engine or a locally implemented standardized serializer.

P2WPKH consumes exactly the public-key HASH160 as a witness v0 program and the
admitted network HRP. Its codec uses Bech32; Bech32m or other witness versions do
not become admitted behavior merely because a provider supports them. Checked
and witness codecs own checksum/encoding machinery, bit conversion and padding.
Each construction returns an address associated with the explicit requested
policy/network or a defined failure. No policy inference or silent substitution.

### R01/R12: operation, result and failure boundary

The operation consumes explicit key/path/network/policy intent and reuses the
same lower compositions used directly. It returns an address with enough
semantic context to identify its policy/network and the path actually used.
Exact result fields and serialized versus prevalidated key input remain O03.
No wallet allocation, persistence, remote lookup or higher-domain object is needed.

Draft failure meanings are malformed input, unsupported intent, incompatible
intent, invalid derivation candidate, exhausted derivation range/depth/resource
budget, invalid definition, and provider/environment failure. Exact exception
versus result representation is O06. A valid-but-unadmitted version differs from
a corrupt checksum; a provider exception differs from a valid infinity outcome.
Failures contain no partial address and do not automatically print input keys,
chain codes or wallet-identifying paths. Vendor failures are contained and
normalized; explicit construction wiring chooses one provider, with no fallback.

## 3. Qualification criteria

These IDs specify suites to build, not tests already executed. Every fixture
needs provenance, independently justified expected results and requirement links.

| ID | Required positive, negative and controlled evidence | Closing milestone |
|---|---|---|
| Q01 | All six policy/network combinations; operation/direct-composition parity; explicit-input rejection; local standalone use. | M06–M07 |
| Q02 | Equivalent behavior with two data definitions; required/unknown fields; invalid widths/HRP/profiles; reuse without mutation; no executable configuration. | M04 |
| Q03 | BIP32 public valid/invalid fixtures; private/mismatched/unknown versions; bad root metadata; exact 78-byte boundary; trailing data; corrupt checksum; point validity. Include historical hardened ancestry. | M04 |
| Q04 | Index 0 and 2^31−1; negative/fractional/hardened/overflow requests; syntax and resource boundaries; depth 255 versus remaining capacity; empty-path disposition. | M03–M04 |
| Q05 | Official public non-hardened edges and metadata; composed paths; forced invalid first/intermediate/last candidate; zero tweak; range exhaustion; requested/effective paths. | M03 |
| Q06 | Exact 33-byte positive keys; bad length/prefix, field bounds and non-curve points; delegated validation and byte round trips. | M03 |
| Q07 | Tweak 0, 1, n−1, n and larger; wrong width; valid additions and infinity; normalized failures; unchanged composition through two different ECC adapters. | M03 |
| Q08 | Independently sourced known answers, empty/binary hash inputs and long HMAC keys; argument order/output lengths; HASH160 input/order; fixed-width unsigned extremes/endianness. | M03–M04 |
| Q09 | Normative/justified P2PKH vectors on both networks; key-hash distinction; leading zeros, alphabet/checksum/length rejection in codec conformance. | M04/M06–M07 |
| Q10 | Independently justified nested-SegWit addresses; exact redeem-script preimage and hash; distinguish P2PKH key hash from redeem-script hash. | M04/M06–M07 |
| Q11 | BIP173 valid/invalid corpus, case/checksum/HRP/padding/length; exact 20-byte v0 admission; reject unsupported policy combinations. | M04/M06–M07 |
| Q12 | Missing extensions/algorithms, malformed provider outputs and normalized vendor exceptions; explicit wiring and no fallback; bounded failure work and safe diagnostics. | M03–M08 |

Run relevant suites on PHP 8.1 and 8.3 with actual extension/package/source
identities. Two suitable distinct libraries must satisfy each library-backed
boundary through real adapters and unchanged consuming compositions; runtime
facilities need conformance without artificial duplicate wrappers. Keep shared
mathematical/codec lineage analysis separate from adapter replaceability. M03
needs an independent ECC reference; an adapter backed by the same ECC library
does not provide it. Missing alternative codecs keep M04's gate open. M05 is
joint selection; candidates and isolated successes never substitute for it.

## 4. Open decisions and evidence tasks

Owner for each task: the M02 executor; provider selection belongs jointly to
the user and M05 executor. Each M02 disposition must link source revisions,
alternatives, reasons, limitations and affected Q IDs.

| ID | Decision still open | Required M02 evidence/task | Affected requirements |
|---|---|---|---|
| O01 | Exact-index versus advancing operation; every-level effective-path reporting and exhaustion/budget behavior. | Read versioned BIP32 CKDpub; establish zero/range/infinity distinctions and intermediate retry semantics; compare explicit API alternatives. | R04–R07, R12 |
| O02 | Supported BIP32/SLIP-132 versions and key/network/policy combinations. | Pin SLIP-132 and relevant Bitcoin parameter sources; build explicit allowlist/compatibility matrix and rejection fixtures without inferring policy defaults. | R01–R03, R09–R11 |
| O03 | Serialized versus prevalidated operation input; semantic byte representation and result fields. | Compare direct composition and typed-request invocation; decide validation ownership and minimal context/effective-path result without exposing vendor types. | R01, R03–R08 |
| O04 | Path grammar, empty path, input/retry limits and host integer-width requirement. | Map BIP32 field limits to bounded API alternatives and public fixtures; separate protocol exhaustion from an operational work budget. | R03–R05, R08, R12 |
| O05 | Definition representation, schema and unknown-field handling. | Define only parameters actually consumed, prove immutable semantic views and reject executable/provider configuration; pin parameter provenance. | R02–R03, R09–R11 |
| O06 | Failure representation and adapter obligations. | Map semantic failure categories to candidate-independent outcomes, deterministic validation precedence and controlled provider faults; settle safe diagnostic policy. | R01–R12 |
| O07 | Focused providers/runtime facilities and serialization support. | Search from D01–D11 requirements; record exact versions/licenses/dependencies/security/runtime evidence, actual API fit, replacement candidates and independent references. Specifically close codec alternatives and PHP 8.1 ECC/GMP feasibility. | R03, R05–R12 |

## 5. Evidence baseline and limits

The existing [provider research](../../../research/public-address-provider-research.md)
and [BitWasp investigation](../../../reviews/bitwasp-public-derivation/README.md)
are inputs only. Known admission and zero-tweak discrepancies become negative
and controlled qualification cases; they are not behavior to reproduce.

The draft's BIP32 widths/public-index conventions are checked against
[BIP32 at revision 3a10b5b](https://github.com/bitcoin/bips/blob/3a10b5b5f0a7586df8928d580a3009744ebb2079/bip-0032.mediawiki),
using its public derivation and serialization sections; retry contract decisions
remain O01. The existing [BIP32 source snapshot](../../../reviews/bitwasp-public-derivation/bip-0032.mediawiki)
is recoverable locally with its investigation provenance.
The [BIP173 source at the same revision](https://github.com/bitcoin/bips/blob/3a10b5b5f0a7586df8928d580a3009744ebb2079/bip-0173.mediawiki)
and [stored snapshot](../../../research/provider-refresh-2026-10-02/bip-0173.mediawiki)
anchor codec qualification, not a completed adapter.

M01 introduces no production provider, concrete PHP API or algorithm/codec
implementation. Executable guarantees, full protocol decisions, runtime support,
replacement and independent correctness remain to be demonstrated downstream.
