# Public Address Derivation — Delegation Research Inventory

**Status:** discussion and feasibility-research input. Not canonical architecture,
a dependency selection, a PHP API specification, or an implementation approval.

The [current provider worksheet](public-address-provider-research.md) contains
updated candidates, bounded probe evidence and the user's independent-research
column. The [historical worksheet](public-address-candidate-worksheet.md) preserves
the 2026-09-28 leads. The [consolidated capability record](../../PIP-0003.md)
identifies the scope and evidence. This inventory supplies per-item research requirements,
not architectural authority or evidence of current provider qualification.

## Scope and responsibility

**Research rule clarified 2026-10-03:** this inventory describes required
operations independently of providers. Define each operation's contract and
qualification criteria first, then discover and compare implementations. The
existing named leads are preliminary, not a comprehensive or unbiased search.
At this research checkpoint, `bitwasp/bitcoin` was excluded from production
options for this slice on footprint grounds and retained as reference evidence.
This is a dated candidate disposition, not an architectural invariant or final
provider selection. A separately packaged focused codec such as
`bitwasp/bech32` is evaluated on its own footprint and contract fit.

The admitted requirement is public extended-key derivation to Bitcoin P2PKH,
P2SH-P2WPKH and P2WPKH addresses, using validated network definitions. This
inventory contains no knowledge of higher architectural layers.

Primitives owns contracts, protocol semantics, invariants, semantic values,
definitions, pertinent operations and composition. Cryptographic algorithms,
elliptic-curve mathematics and standardized encoders/decoders must be provided
by evaluated libraries or runtime facilities, regardless of implementation
simplicity. A provider's API must not determine the domain model.

The [current canonical](../../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md)
sections 10A–14 establish the semantic/delegation boundaries. Section 13.3 still
contains open wording about codec implementation ownership; canonical promotion
must reconcile that wording with the explicit delegation instruction recorded
in [AGENTS.md](../../../../../AGENTS.md). This inventory does not silently revise it.

The items below are research requirements, not a mandate for one package,
interface or class per row. One provider can satisfy several items. Runtime
delegation can avoid adding a Composer dependency altogether.

## Delegated cryptographic and public-key machinery

| ID | Item | Why this capability needs it | Required evidence |
|---|---|---|---|
| D01 | SHA-256 | HASH160 composition and Base58Check hashing | Known-answer vectors; empty and binary inputs; exact 32-byte output; supported-runtime availability |
| D02 | RIPEMD-160 | Public-key and redeem-script HASH160 | Known-answer vectors; exact 20-byte binary output; composition order and runtime availability |
| D03 | HMAC-SHA512 | BIP32 public child derivation | HMAC vectors, including long keys; key/data argument order; exact 64-byte output; BIP32 vectors |
| D04 | secp256k1 public-key parsing, point validation and compressed serialization | Validate imported public material and obtain the compressed key representation required by the protocol | SEC1 compressed-key vectors; invalid prefixes and lengths; field-coordinate bounds; points not on the curve; round-trip consistency |
| D05 | secp256k1 public-key scalar tweak addition | Public child-key operation `Q = P + tG` | Zero tweak; valid scalar range; scalar at/above order; infinity result; exact compressed output; comparison with an independently implemented backend |

D04 and D05 should normally be researched together as one ECC provider surface.
Their implementation may require point recovery, multiplication, addition and
modular arithmetic. Those are provider internals, not additional public
Primitives capabilities. A public tweak scalar must not be conflated with a
private-key scalar: a zero tweak has different validity semantics.

PHP's documented [`hash()`](https://www.php.net/manual/en/function.hash.php)
and [`hash_hmac()`](https://www.php.net/manual/en/function.hash-hmac.php) are the
first runtime facilities to assess for D01–D03. Algorithm availability and binary
output must be verified on supported runtimes; no hashing algorithm is to be
implemented locally.

The public derivation and compressed-key requirements are specified in
[BIP32](https://github.com/bitcoin/bips/blob/master/bip-0032.mediawiki).

## Delegated encoders, decoders and binary machinery

| ID | Item | Required use | Required evidence |
|---|---|---|---|
| D06 | Base58 with the Bitcoin alphabet | Underlying representation for extended keys and legacy/nested addresses | Leading-zero preservation; alphabet rejection; binary round trips; bounded-input behavior; no implicit alphabet changes |
| D07 | Base58Check | Decode checked extended-key input; encode P2PKH and P2SH address payloads | Correct checksum; corrupt/truncated input rejection; preservation of leading zeros; support for both extended-key and address payloads without assuming one-byte versions |
| D08 | Bech32 | Encoding required by native SegWit v0 | Official valid/invalid vectors; checksum and case rules; HRP handling; length limits; correct bit conversion and padding |
| D09 | SegWit v0 address codec | Turn a validated P2WPKH witness program and network HRP into an address | Witness v0 with a 20-byte program for this policy; HRP and checksum correctness; distinguish generic v0 codec support from the admitted P2WPKH construction |
| D10 | BIP32 extended public-key structural decoding | Extract the standardized extended-key fields from decoded input | Exact payload length; field widths and byte order; no ignored trailing bytes; version/material consistency; public-key validation; invalid root metadata rejection |
| D11 | Fixed-width binary integer and byte conversion | Index serialization and structured key fields where composition/adapters need them | Endianness, unsigned ranges and leading zeros; no float conversion; explicit host integer-width assumptions |

D06/D07 and D08/D09 may each be provided by a single package. Bech32 checksum
polymod, HRP expansion, alphabet mapping and 8-to-5-bit conversion belong inside
the delegated codec; they do not justify custom local implementations or a
separate package per sub-operation.

D07 must be assessed against both four-byte extended-key versions and address
versions. A library offering a convenient single-byte-version API is not yet
evidence that it can handle the complete required payload surface.

D10 covers mechanical parsing; Primitives remains responsible for the admission
rules and for enforcing its invariants, including rejecting private material.
Public extended-key export is not automatically an admitted public feature:
reserialization can be useful to a verification harness without expanding the
production API.

D11 can use runtime facilities such as
[`pack()`](https://www.php.net/manual/en/function.pack.php) and corresponding
decoding functions, or an evaluated binary codec. The domain-owned field layout
does not require a new generic buffer framework.

Address decoding can be useful for verification of generated outputs. This
inventory does not add a general address-validation product capability merely
because a candidate codec also supports decoding.

Sources: [BIP32 serialization](https://github.com/bitcoin/bips/blob/master/bip-0032.mediawiki),
[BIP141 witness programs](https://github.com/bitcoin/bips/blob/master/bip-0141.mediawiki),
and [BIP173 Bech32](https://github.com/bitcoin/bips/blob/master/bip-0173.mediawiki).
Only witness v0 is admitted here; Bech32m and later witness versions are outside
this research requirement.

## Composite-provider research and internal entities

| Item | Research treatment | Boundary constraint |
|---|---|---|
| BIP32 public derivation composition | Define owned CKDpub semantics over delegated HMAC/ECC first. A focused external composition provider may be compared if justified; no complete BIP32 package is required. | No algorithm port from reference code. Retain public-only admission, invalid-child semantics, index limits, metadata and typed outcomes. The broad `bitwasp/bitcoin` package is reference-only, not a production option. |
| HASH160 | Owned operation composed from delegated SHA-256 and RIPEMD-160, or an equivalent vetted provider operation | Composition is ours; neither hash implementation is ours. A convenience method does not require another dependency. |
| Double SHA-256 and checksum support | May be provided within the selected Base58Check codec or by delegated hashing capabilities | No custom checksum/encoding algorithm implementation. Preserve the protocol contract regardless of provider packaging. |
| Big integers, field elements and modular arithmetic | Evaluate as transitive backend requirements | No domain-wide arbitrary-precision math API is justified. GMP objects, provider scalars and point objects remain behind adapters. |
| Point, generator, curve and infinity representations | ECC provider internals | Primitives exposes semantic public-key values and typed outcomes, not these backend entities. |
| Binary buffers, parsers and serializers | Runtime/library mechanisms behind key and codec adapters | No dependency-specific buffer or parser type crosses a capability boundary. |
| Script serialization helpers | Investigate only if needed to express the three admitted address compositions | Do not import a full script interpreter, transaction subsystem or signing stack as a public capability. The Bitcoin script/witness semantics remain owned. |

BIP32 is a protocol composition, not an atomic hash or curve algorithm. Its
semantic ownership does not settle implementation sourcing. The feasibility
study must make that distinction explicit. Canonical §§10A and 12 permit owned
CKDpub semantic composition over delegated HMAC and ECC; a complete external
BIP32 provider is not a prerequisite. Do not reproduce cryptographic or codec
internals from reference code.

## What remains owned

- Semantic representations of public keys, extended public keys, derivation paths
  and results; exact PHP names and signatures remain open.
- Contract invariants, supported-policy admission, validation decisions and
  translation of provider failures into meaningful outcomes.
- Declarative network definitions and their validation boundary.
- Interpretation of public extended-key versions and address-policy semantics,
  including [SLIP-132](https://github.com/satoshilabs/slips/blob/master/slip-0132.md).
  A version table is data, not a cryptographic algorithm or reason for a new library.
- P2PKH, P2SH-P2WPKH and P2WPKH protocol composition using delegated machinery.
- Explicit implementation selection, adapter isolation and evidence linking
  requirements to contracts, dependencies and tests.

## Initial research leads — not selected dependencies

| Research group | Starting points | Questions to resolve |
|---|---|---|
| Hashing | PHP Hash runtime | Required algorithm availability, known-answer vectors and binary outputs on PHP 8.1/8.3 |
| ECC | [`paragonie/ecc`](https://github.com/paragonie/phpecc), [`simplito/elliptic-php`](https://github.com/simplito/elliptic-php), [`libsecp256k1`](https://github.com/bitcoin-core/secp256k1) | Actual public-key API, correctness at boundaries, runtime dependencies, maintenance, and PHP deployment feasibility. A native library still requires an independently evaluated binding; none is selected here. |
| Base58/Base58Check | [`tuupola/base58`](https://github.com/tuupola/base58); BitWasp reference codecs | Payload/version support, leading zeros, GMP independence where feasible, backend selection behavior, strict validation and dependency footprint |
| Bech32/SegWit v0 | [`bitwasp/bech32`](https://github.com/Bit-Wasp/bech32); BIP173 reference implementations as verification anchors | Official vectors, API limits, supported runtimes, maintenance, and ability to keep the admitted surface at witness v0 |
| Extended keys, public derivation and structural codecs | Owned composition and runtime conversion qualification; [`bitwasp/bitcoin`](https://github.com/Bit-Wasp/bitcoin-php) is comparison evidence only, excluded from production | Strict parsing, public-only exposure, invalid-child behavior and minimal delegated operations. Discover focused alternatives from those requirements. |

These links establish starting points, not claims that a current release is
maintained, secure, compatible, independently audited or appropriate for
production. Version-level research is still required. The repository currently
has no selected production crypto or codec package.

The [BitWasp investigation](../reviews/bitwasp-public-derivation/README.md)
already records candidate-relevant parsing and derivation discrepancies. They
must become evaluation cases rather than accepted reference behavior. Its
Paragonie-backed ECC is not an independent mathematical oracle for another
Paragonie-backed adapter.

## Candidate evaluation record

For every candidate, record:

1. Covered inventory IDs and admitted operations; any missing behavior.
2. Exact package/release/source revision, license, runtime/extensions, transitive
   dependencies, maintenance and security-history evidence.
3. Contract fit and adapter responsibilities; external types contained; unwanted
   private-key/signing features excluded from our exposed surface. Identify the
   actual executed dependency path, including any ECC or codec hidden inside a
   composite provider; unused adapters do not establish replacement boundaries.
4. Official-vector results, malformed-input and boundary results, controlled
   invalid-child tests, diagnostic behavior and reproducible commands.
5. Independent reference implementation and shared-dependency analysis.
6. Explicit backend-selection behavior, replacement route, runtime footprint
   and measured performance for the required public operations. Under the
   execution plan's local qualification commitment (not a canonical provider-count
   invariant), for each
   library-backed boundary, qualify two distinct libraries through separate
   adapters against the same contracts and consuming compositions; a missing
   second qualified provider remains an open gate.
7. Outcome: investigate, rejected with reason, or eligible for explicit selection.

GMP is permitted for the initial ECC backend. Its presence is a runtime property,
not a capability requirement. PHP 8.1 is the supported baseline; PHP 8.3 is also
part of the existing verification matrix. Lack of a suitable candidate requires
further investigation, not a local algorithm or codec implementation.

Suggested discussion order: ECC and BIP32 provider boundaries first; then strict
extended-key/Base58Check handling; then SegWit Bech32; runtime hashing can be
verified independently. This order addresses the uncertainties already exposed
by the reference investigation.

## Outside the admitted requirement

Private derivation, seeds, mnemonics, key generation, signing, ECDSA/Schnorr
signing or verification, signature recovery, Taproot, x-only keys, Bech32m,
PBKDF2, other curves and unrelated chain encodings are not required. Standalone
SHA-512 is not justified merely because HMAC-SHA512 is required. A generic
script interpreter, transaction serializer and address discovery/allocation
engine are also outside this capability.
