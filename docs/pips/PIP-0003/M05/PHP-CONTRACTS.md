# M05 — Concrete PHP contract proposal

Status: **executor proposal for joint M05 review; not frozen or implemented**.
Origin: [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41).
The [M05 assessment](README.md) owns provider/runtime evidence and selection;
the [Execution Plan](../EXECUTION-PLAN.md) owns ordering and the M06 handoff.
This document proposes PHP representations within the accepted
[M02 O01–O06 decisions](../M02/M02-PROTOCOL.md) and approved
[PIP00010 receiving boundary](../../PIP00010/PIP00010.md). It does not replace
the historical M01 draft or reopen exact-path derivation and public exceptions.

The proposal keeps raw input admission explicit, uses one owned exception with
stable reasons, and introduces immutable objects where retained invariants or
returned context require them. No class is required solely because M01 has a
requirement row. Names below are proposed public identifiers, rooted in
`PayCryptoMe\Primitives`; final file placement follows the concrete capability
responsibility rather than a provider or network hierarchy.

## Agreed composition direction

The joint design discussion on 2026-10-22 established the following direction
for refining M05. This agreement concerns architecture, not provider/runtime
selection or a freeze of the PHP signatures and schema representation below.
It applies the accepted M02 O05/O06 decisions and the
[Canonical composition and definition boundaries](../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md#8-primitive-composition-principle).
The exploratory cross-protocol examples inform design fitness; they do not
add supported networks, policies or operations.

- Equivalent behavior reuses one composition declaration; parameter differences
  enter as validated data. Do not hide the entire flow in a specialized
  function, method or composition class for each coin/network.
- A composition is inspectable data describing capability connections and
  parameter bindings. Each connection must satisfy the next capability's
  semantic input contract, not merely match a PHP type or byte length.
- Capabilities retain cohesive behavior and their own invariants. Declaring
  composition does not require turning every internal instruction into data.
- An exception aborts the whole operation. No subsequent stage, substitute
  result, alternate provider, policy, key or index is attempted.

Names are not the architectural constraint. A name such as `BitcoinDefinition`
may describe an appropriate shared protocol schema/value; it is not prohibited
because it contains a coin name. Assess its responsibility and reuse. The
accepted rule still excludes a class per network merely to carry constants,
and a descriptive name must not conceal duplicated behavior or composition.
Ordinary methods may validate data or execute admitted capabilities; their
existence is not the problem.

### Separate data responsibilities

| Record | Meaning and boundary |
|---|---|
| Parameter definition | Immutable identifiers, versions, HRPs and admitted version/policy mappings. No callbacks, providers or execution graph inside `NetworkDefinition`. |
| Composition declaration | Data identifying owned capabilities or reusable subcompositions, their named input/output connections, parameter requirements and operation result. It contains no executable callback or vendor class. |
| Network/policy binding | An explicit association between an admitted intent, a composition and the required parameter definition. Mainnet/testnet reuse the same policy composition. A matching prefix never selects intent. |
| Provider wiring | Explicit setup binds an owned capability contract to one qualified implementation. This remains separate from network data and composition semantics; no availability-based selection. |

Capability contracts supply the meaning and failure rules that declarations
reference. Schemas validate data structure; semantic validation checks the
connections and operation constraints. A declaration cannot override the
contract it references. Its validated representation is immutable and must
not retain mutable caller references.

The initial scope is the compositions required by the approved operations and
six network/policy combinations. This is not approval for an arbitrary public
workflow language or a public extension registry. PHP arrays, typed builders,
schema representation and the execution mechanism remain concrete M05 design
choices. They must preserve visible composition rather than reducing every
declaration to a pointer to a coin-specific implementation.

### Concrete flow and semantic connections

The shared public flow has ordered request/definition admission, extended-key
admission, remaining-depth validation, exact-path public derivation and key
HASH160 before the selected terminal composition. Extended-key admission
retains the visible substeps in [validation order](#failure-contract-and-validation-order):
checked decoding and common metadata, version eligibility, point validation,
then intent compatibility. Neither a graph scheduler nor a builder may reorder
them. Only the explicitly selected policy branch runs.

| Connection | Meaning required by its consumer |
|---|---|
| Request to key admission | Original serialized public key and validated explicit network/policy intent; no inferred intent or coerced path values. |
| Key admission to exact-path derivation | A complete admitted public node, including chain code, version and metadata; a compressed key alone is insufficient. The validated path must also fit remaining depth. |
| Derivation to key hashing | The final admitted compressed public key from the exact requested path. The full node remains the derivation result; hashing uses its public-key field. |
| Key hashing to terminal composition | A 20-byte HASH160 of that derived compressed key, with that semantic origin. An arbitrary 20-byte digest is not interchangeable. |
| Terminal composition to public result | A complete address for the same admitted intent and path; result context comes from the validated request, not encoding-based inference. |

HASH160 is a reusable composition of delegated SHA-256 followed by delegated
RIPEMD-160, with binary 32-byte and 20-byte results respectively. Reusing it
does not erase whether its input is a public key or a witness redeem script.
Those semantic origins remain explicit at the surrounding connections.

| Policy | Shared input | Owned construction and parameter binding | Delegated terminal encoding |
|---|---|---|---|
| `p2pkh` | Derived compressed-key HASH160 | The admitted network's P2PKH version byte followed by the key hash. | Base58Check of the complete 21-byte payload. |
| `p2sh-p2wpkh` | The same key-hash semantics | Form the 22-byte witness redeem script `0x00 0x14` followed by the key hash; apply HASH160 to that script; prepend the admitted P2SH version byte. | Base58Check of the complete 21-byte payload. |
| `p2wpkh` | The same key-hash semantics | Bind that hash as the witness-v0 20-byte program and the admitted network's HRP. | The qualified SegWit-v0 key-hash encoder. |

This table exposes the shared prefix and actual terminal divergence without
making one class per policy a requirement. P2PKH key hash, P2SH script hash and
witness program can have the same byte length while requiring different
connections. The supported compositions fix these relationships; a generic
byte concatenator or `mixed -> mixed` stage interface cannot establish them.
Standardized hashing and encoding machinery remains delegated. Owned fixed
payload/script construction does not introduce a general script engine.

The receiving operation adds ordered account/index admission and exact
network/policy/account source selection, then invokes the same generic
operation with the selected serialized key and `[0, index]`. Its result adds
the logical account, branch `0` and index. This boundary is itself inspectable;
it must not hide another derivation flow or bypass generic key admission.

### Schema and execution obligations

The schema must make these fields or equivalent semantics reviewable: a
composition identifier, required inputs/parameters, capability or
subcomposition references, named connections, ordering constraints and result
bindings. Exact PHP field names and syntax are not frozen by this list.

1. At setup, reject duplicate identifiers/bindings, unknown references or
   fields, missing inputs, cycles and connections with incompatible semantic
   types. Reject partial or ambiguous assembly before provider execution.
2. Check operation invariants beyond structural validity: the exact admission
   order, complete-node derivation, policy-specific payload construction,
   matching parameter roles, and same-request result context. No declaration
   may skip validation, convert a script hash into a key hash by relabeling,
   introduce an extra executable branch or change exact-path semantics.
3. Freeze the fully validated composition before serving requests. Bind one
   provider per required contract explicitly. Parameter-only changes reuse
   the same declaration; provider replacement leaves its semantics unchanged.
4. At execution, enforce actual value contracts at the capability boundaries.
   Static connection validation is not proof that a provider returned correct
   bytes. Independently anchored qualification still detects plausible wrong
   outputs that runtime shape checks cannot establish as wrong.

Composition and binding data errors use the proposed `INVALID_DEFINITION`
category at setup. Provider/environment failures retain their categories.
Malformed request data keeps its request-stage category; declaration errors
must not become delayed request failures.

Failure abort is an operation invariant, not a configurable `on_error` policy.
Adapters normalize vendor conventions at their owned boundary; the executor
propagates owned failures without replacing their semantic reason. No catch
handler may turn failure into `null`, a default address, a partial result or
another execution attempt. The caller handles the public exception. No
failure-path schema fields enable fallback, skip, retry or index advancement.

The documented point capability's `null` for confirmed infinity is a specific
internal outcome, not a continuation value. CKDpub immediately translates it
to `INVALID_CHILD` and terminates; it never reaches an address-construction
stage. Zero tweak remains a valid input with complete child metadata updates.

## Entry points and results

Proposed entry-point signatures; namespace placement remains open under the
capability responsibilities above (`Bitcoin\Address` was the initial sketch):

```php
final class DerivePublicAddress
{
    public function derive(
        #[\SensitiveParameter] mixed $extendedPublicKey,
        #[\SensitiveParameter] mixed $relativePath,
        mixed $network,
        mixed $policy,
    ): PublicAddressResult;
}

final class DeriveReceivingAddress
{
    public function derive(
        #[\SensitiveParameter] mixed $account,
        #[\SensitiveParameter] mixed $index,
        mixed $network,
        mixed $policy,
    ): ReceivingAddressResult;
}
```

These are signature sketches, not loadable class declarations. The methods
accept the values described below and return a complete result or throw
`Failure\PrimitivesException`. They do not return an error object, `false`,
`null`, a partial address or a partial path. All arguments are required;
receiving has no branch/path argument. PHP call-syntax errors such as missing
arguments or an unknown named argument occur before method entry; they are
not provider failures or successfully admitted requests.

`mixed` deliberately preserves the original value at raw admission. An `int`
parameter can coerce a weak caller's numeric string, boolean or float before
the method can reject it. PHPDoc will describe the accepted value shapes for
tools; runtime checks establish them. Do not accept `Stringable`, iterators,
path strings, automatic case conversion or defaults as alternate input forms.

| Argument | Accepted representation and meaning |
|---|---|
| `extendedPublicKey` | A PHP string of 1–160 bytes, unchanged, containing the serialized public extended key. The 160-byte pre-decode resource bound retains the exercised research bound; it does not relax the exact decoded 78-byte structure or checksum. No trimming, private-to-public conversion or prefix inference. |
| `relativePath` | A PHP list of 1–255 actual integers, each `0..2147483647`. No sparse/associative array or coercion. Copy validated scalar elements into an owned list. The global count bound is a proposed request-resource guard; the tighter `count <= 255 - importedDepth` check follows key compatibility. |
| `account`, `index` | Separate actual PHP integers in `0..2147483647`, including zero. The account selects a provisioned mapping; it does not request hardened derivation. The index is attempted exactly once at the final edge. |
| `network` | An immutable `NetworkDefinition` produced by the validated definition loader for the admitted catalogue. Initial public profiles are `mainnet` and `testnet`. The object carries parameters and identifier, with no behavioral methods or provider selection. |
| `policy` | An exact PHP string: `p2pkh`, `p2sh-p2wpkh` or `p2wpkh`. Wrong type is malformed input; another string is unsupported intent. No policy is inferred from the key. |

`PublicAddressResult` is a final immutable value with read-only properties:
`string $address`, `string $network`, `string $policy`, and
`array $relativePath` documented as `non-empty-list<int>`. The path equals the
requested list exactly. `network` is the validated context identifier, not a
claim about cryptographically authenticated chain provenance.

`ReceivingAddressResult` is a final immutable value with read-only properties:
`string $address`, `string $network`, `string $policy`, `int $account`,
`int $branch` (always zero), and `int $index`. It does not reuse a generic path
field for logical account coordinates. Its successful address equals a direct
generic call with the selected key and `[0, index]`. Neither result retains the
input key, chain code, provider object, diagnostic payload or allocator state.
There is no implicit string/JSON serialization contract; consumers access the
documented properties and choose their own transport representation.

The generic operation executes the validated declaration connecting shared
key-admission, CKDpub, hashing and terminal address capabilities. Its entry-point
class does not replace that declaration with a hard-coded per-coin flow. The
receiving operation is wired with one immutable account-key source and that
same generic operation.
It contributes request admission, exact mapping selection and result
coordinates, not a second derivation or address algorithm.

## Definitions and immutable provisioning

PHP lists of field/value pairs are the current provisioning candidate, not a
requirement for the final authoring syntax or the composition schema. A
duplicate field must remain observable before normalization; an associative
array or JSON object that has already overwritten duplicate keys cannot prove
their absence. The loader accepts data already in memory and performs no file,
network, database or application-container lookup.

Proposed `NetworkDefinitions::fromEntries(mixed $entries): NetworkDefinitions`
takes a list of definition rows, each a list of exact two-element field/value
pairs. A mainnet row is:

```php
[
    ['identifier', 'mainnet'],
    ['p2pkhVersion', '00'],
    ['p2shVersion', '05'],
    ['segwitHrp', 'bc'],
    ['publicVersions', [
        ['0488b21e', 'p2pkh'],
        ['049d7cb2', 'p2sh-p2wpkh'],
        ['04b24746', 'p2wpkh'],
    ]],
]
```

Version strings here are exact lowercase hexadecimal with widths two/eight;
conversion to one/four binary bytes uses PHP facilities after shape validation.
The semantic view exposes bytes, not a raw configuration dictionary. All five
fields are required exactly once; every public-version entry is an exact pair.
Reject unknown fields, duplicate identifiers/fields/versions, non-list input,
wrong types/widths and mismatched profile parameters. Reordered equivalent
fields/version entries remain equivalent. Neither configuration order nor
matching HRP/address bytes establishes profile identity.

The loader uses an explicit data catalogue of admitted profiles, initially
the exact M02 mainnet/testnet values and six version/policy pairs. The initial
public loader does not accept a caller-supplied expanded catalogue or a
`testMode` flag. The lower loader/composition qualification can supply a
test-only catalogue admitting Signet under a separate identifier, with the
same testnet parameters and unchanged algorithms. Such fixture wiring does
not add Signet to the shipped public catalogue. A profile with changed
behavior cannot enter merely by adding constants to the data catalogue.

`NetworkDefinitions::get(mixed $identifier): NetworkDefinition` accepts an
exact string identifier and returns its immutable view; wrong type is
malformed input and an absent identifier is unsupported intent. The initial
standard assembly provisions both mainnet and testnet definitions explicitly.
`testnet` means the shared address-parameter profile; it does not distinguish
testnet3, testnet4 or Signet consensus rules.

Proposed `AccountPublicKeySource::fromEntries(mixed $entries,
NetworkDefinitions $definitions, ExtendedPublicKeyAdmission $admission):
AccountPublicKeySource` takes a list of mapping rows. Each row contains
exactly one of each field below, again as field/value pairs:

```php
[
    ['network', 'mainnet'],
    ['policy', 'p2wpkh'],
    ['account', 0],
    ['extendedPublicKey', 'the provisioned account-level zpub text'],
]
```

The example key text is a placeholder, not a valid fixture. Provisioning is
**eager and atomic**: validate all row shapes, duplicate fields and duplicate
network/policy/account tuples before any key-provider work; then validate
each serialized key's structure, version, point and intent using the same
admission capability as generic derivation. Duplicate tuples fail even when
their key texts agree. Only a fully validated source is returned; an unrelated
bad row cannot remain latent inside an accepted source. An empty list is a
valid empty source and every lookup then reports a missing mapping.

Store an owned scalar snapshot of each validated key text and tuple. An
associative lookup index may be built only after duplicate detection. Account
is an integer field value, never inferred from an array key that PHP may have
coerced. Retain the original serialized key so receiving calls the actual
generic operation with that key, including its usual admission boundary.
Do not substitute a prevalidated-node fast path with different failure rules.

`AccountPublicKeySource::keyFor(mixed $network, mixed $policy,
mixed $account): string` checks the same network/policy/account shape rules
and returns the one exact key. Missing tuple throws `MISSING_ACCOUNT_MAPPING`; it never returns
`null`, guesses another key or selects by matching encoding parameters alone.
Lookup verifies semantic definition equivalence with the provisioned network
context, not object identity or source-field order. A conflicting definition
throws `INVALID_DEFINITION` and cannot borrow an existing mapping merely by reusing its identifier.

Malformed, unsupported or incompatible provisioned data throws
`INVALID_ACCOUNT_SOURCE` during construction. A provider/environment failure
during construction retains that failure category; do not blame source data
for unavailable or malfunctioning machinery. The source does not impose a
fixed BIP44 depth or infer account ancestry. Provisioning is responsible for
the asserted account-level binding. The generic operation checks the selected
key's remaining capacity for `[0, index]` at request time; a depth failure has
the same meaning as the direct generic call.

Immutable construction must rebuild nested scalar arrays rather than retain
caller references. PHP `readonly` prevents assigning the property but does
not detach references inside an input array. Views have no mutators or
public unvalidated construction path. Serialization/unserialization must not
be an alternate admission path; reject reconstruction that bypasses the
loader. Hostile reflection or modified runtime code is outside this API's
integrity guarantees. No source can contain callbacks, provider instances,
executables, live references to mutable consumer objects or fallback rules.

## Failure contract and validation order

Propose a single final owned `Failure\PrimitivesException` extending
`\RuntimeException`, with `public readonly FailureReason $reason` and
`FailureReason: string` as the stable machine-readable category. The exception
constructor accepts only the reason, uses that fixed reason text as its
message, code zero and no previous exception. No public constructor argument
accepts vendor messages, input context or `Throwable`. The enum avoids a
class for every failure while preserving meaningful caller handling.

| `FailureReason` case/value | Meaning |
|---|---|
| `MALFORMED_INPUT / malformed_input` | Wrong original PHP type or malformed serialized public-key envelope, metadata or recognized-version public point. |
| `UNSUPPORTED_INTENT / unsupported_intent` | A well-typed network/profile or policy name is outside the admitted set. |
| `UNSUPPORTED_VERSION / unsupported_version` | A checksum-valid 78-byte envelope with valid common metadata carries an unknown, private or multisignature version. Point interpretation has not occurred. |
| `INCOMPATIBLE_INTENT / incompatible_intent` | An admitted public key's version/policy/network meaning conflicts with the explicit validated intent. |
| `PATH_BOUNDS / path_bounds` | Empty/non-list path, non-integer or out-of-range path/account/index, proposed global path count bound, or insufficient remaining BIP32 depth. |
| `INVALID_CHILD / invalid_child` | At the exact attempted edge, HMAC left half is at/above the group order or delegated tweak confirms infinity. No child/address is emitted. |
| `INVALID_DEFINITION / invalid_definition` | Parameter/composition/binding schema, duplicate data, provenance catalogue or semantic invariants fail. Composition/binding admission occurs at setup; a conflicting supplied definition also fails at request/source lookup. |
| `INVALID_ACCOUNT_SOURCE / invalid_account_source` | Provisioned rows contain malformed/duplicate/unsupported/incompatible mapping data. |
| `MISSING_ACCOUNT_MAPPING / missing_account_mapping` | No key exists for the exact admitted network/policy/account tuple. |
| `ENVIRONMENT_FAILURE / environment_failure` | A required PHP facility, algorithm, integer width or selected provider dependency is unavailable at its setup/use gate. |
| `PROVIDER_FAILURE / provider_failure` | A delegated operation throws unexpectedly, returns the wrong type/width/shape, contradicts its contract or cannot establish its promised outcome. |

`MALFORMED_INPUT` excludes path/account/index errors, which consistently use
`PATH_BOUNDS`. Source/definition construction remaps data failures to its
documented construction reason, preserving provider/environment failures.
Research strings such as `malformed_key`, `unsupported_policy` and
`invalid_witness` are observations, not an existing public ABI to preserve.

There are separate lifecycle gates. Invalid configuration cannot produce a
wired operation/source. Dependency availability is checked at assembly and
again at a required facility's use when necessary; construction can therefore
fail before any request exists. Within a successfully constructed operation:

1. Validate the network value/admitted definition, then path (or account,
   then index), policy type/allowlist, and generic key text type/resource bound.
   No codec/ECC call occurs at this stage. The proposed absolute 255-element
   path guard precedes per-element iteration; it is separate from depth.
2. Receiving selects the exact account mapping, then calls generic derivation
   with `[0, index]`; it adds no branch choice, retry or account-key search.
3. Delegate checked decode; require exactly 78 bytes and valid common metadata.
   A root must have zero parent fingerprint and zero historical child number.
4. Check full public-version eligibility before compressed-point interpretation.
5. For a recognized version, validate point shape and delegated finite curve
   validity; then check explicit network/policy compatibility.
6. Check remaining depth; derive each requested component once, maintaining the
   complete node and emitting no partial result on failure.
7. Use the terminal policy composition and delegated encoder; return the full
   semantic result only after success.

First failing stage wins. Inside request shape, the order above settles the
otherwise unspecified simultaneous-fault case. Unknown version plus a bad point
remains `UNSUPPORTED_VERSION`; a recognized-version bad point plus incompatible
intent remains `MALFORMED_INPUT`; incompatible intent precedes insufficient
remaining depth. None of these permits remapping a provider malfunction to
malformed input without evidence of the provider's invalid-input signal.

All adapters translate their conventions at the owned boundary. The public
entry point must not indiscriminately catch all internal exceptions and erase
known semantic reasons. Conversely, a vendor exception with a familiar PHP
class or message is not sufficient to treat an arbitrary fault as bad input.
Retain the pinned invalid-signal and same-library classification limitations
from M03/M04; the API cannot promise to distinguish observationally identical
signals. A plausible wrong address remains a qualification trust limit.

`SensitiveParameter` applies to input-bearing public/helper calls that can
appear in library-owned exception traces, including provisioning rows and
serialized keys. Test with `zend.exception_ignore_args=0`; do not rely on a
deployment default. Public exception message, reason and previous chain must
not expose keys, chain codes, full wallet paths or raw vendor text. No library
logging, automatic input dumping or public mutable diagnostic context is added.
PHP's [redaction wrapper](https://www.php.net/manual/en/class.sensitiveparametervalue.php)
still retains an introspectable value: standard trace
redaction is not erasure. The semantic exception fields and normal rendered
diagnostics have the containment contract; this proposal does not claim that
PHP exception internals contain no recoverable input.
This does not promise to scrub arbitrary consumer stack frames, logging or
debuggers; the library must contain the data in its own diagnostic surface.

## Reusable lower capabilities

The concrete production adapter code remains M06 work. Proposed semantic
boundaries below constrain that implementation and its replacement tests;
they are not vendor APIs copied into the domain. Raw external ingress retains
original types; strict typed calls inside the admitted composition use the
documented byte/int values.

| Boundary | Return and failure meaning |
|---|---|
| `PublicPointOperations::admit(mixed $compressed): string` | Return identical 33-byte compressed encoding only after finite curve validation. Invalid input is malformed input; unexpected parse/serialization failure is provider failure. No point/GMP object escapes. |
| `PublicPointOperations::addScalar(mixed $compressed, mixed $tweak): ?string` | Validate the raw compressed key through the same admission contract and require exactly 32 big-endian bytes in `0..n-1`. Return a validated compressed result, or `null` only for provider-confirmed infinity. Zero is identity. Invalid arguments fail; no modular reduction or vendor `false` as infinity. |
| `ExtendedPublicKeyAdmission::admit(mixed $text, mixed $network, mixed $policy): ExtendedPublicNode` | Apply the ordered checked-decode/structure/version/point/intent stages. Return an immutable complete node: four version bytes, integer depth, four fingerprint bytes, integer historical child number, 32 chain-code bytes and 33 admitted point bytes. Historical hardened child numbers are allowed. |
| `CkdPublicDerivation::derive(ExtendedPublicNode $parent, mixed $relativePath): ExtendedPublicNode` | Return the complete final node for the exact path. Throw owned `INVALID_CHILD` for an invalid edge or another documented failure; do not expose a partially successful node. No advancing variant or nullable child result. |
| PHP hashes/HMAC and `Hash160` composition | Binary outputs of 32/20/64 bytes for SHA-256/RIPEMD-160/HMAC-SHA512; HASH160 is SHA-256 then RIPEMD-160. HMAC key and message have distinct positions. Missing facility is environment failure; unexpected result is provider failure. No silent hex conversion. |
| `Base58Check::decode(mixed $text): string` / `encode(mixed $payload): string` | For this slice, decode admits 1–160 text bytes and returns nonempty checksum-free bytes including all version bytes; encode admits 1–78 binary payload bytes and returns complete checked text. Empty input/payload is malformed input, never a failure sentinel. The selected library owns checksum/alphabet machinery; extended-key structure remains in admission. |
| `SegwitV0KeyHashEncoder::encode(mixed $hrp, mixed $program): string` | Admit the selected lowercase HRP and exactly 20 binary bytes; return lowercase v0 Bech32 text. No decoder call in generation and no public SegWit decoding contract in this slice. |

For point/codec direct composition, input shape failures are owned exceptions;
provider outputs violating the same shape are provider failures, not caller
errors. Internal provider interfaces may use typed parameters because callers
have already established their raw types. Public façades must not rely on
weak PHP scalar coercion to establish byte/index admission.

CKDpub owns message layout, HMAC split, scalar-range admission, no-advance
behavior and metadata. A zero left half still changes chain code/depth/child
number/fingerprint. Parent fingerprint uses the immediate parent's HASH160.
An invalid left half does not become a reusable tweak value. The point
capability's `null` infinity is immediately converted into `INVALID_CHILD`;
no public address method returns it. Fixed-width conversions use PHP facilities.

P2PKH and P2SH-P2WPKH reuse key HASH160, diverging at their actual payload
semantics: direct key hash versus HASH160 of `0014` plus that hash. P2WPKH
passes the original key hash as v0 witness program. Byte construction expresses
those owned semantics; it does not introduce a script engine, cryptographic
algorithm or standardized codec implementation.

## Contract review and downstream acceptance

The [language probe](php-boundary-probe.php), its
[strict callee](php-boundary-callee.php) and
[four-runtime results](php-boundary-results.json) reproduce weak-caller
coercion, caller-reference mutation through a readonly array, scalar-leaf
detachment, and `SensitiveParameter` rendering versus introspection on
PHP 8.2–8.5. They establish language behavior only. M05 must review the concrete choices
against the accepted semantics, resolve feasibility questions and assign their
acceptance cases before freezing. The production tests below execute when M06
or M07 exposes the corresponding capability; requiring an assembled receiving
API before M05 would invert the approved milestone order.

Reproduce with the images
from the [M05 record](README.md#executed-evidence-and-limits), repository mounted
read-only at `/repo`, no network, and:

```sh
php -d error_reporting=E_ALL -d display_errors=stderr \
  -d zend.exception_ignore_args=0 \
  /repo/docs/pips/PIP-0003/M05/php-boundary-probe.php
```

- Execute raw ingress from strict and weak PHP callers: numeric strings,
  booleans, floats, sparse paths, count bounds, integer extrema and unchanged
  copies. The proposed global 255-entry guard and within-shape failure order
  need named mixed-invalid cases; earlier probes do not establish them.
- Test pair-list definitions and source provisioning with duplicates before
  normalization, reordered equivalent data, aliased caller arrays, unknown
  fields, mismatched profiles and bypass attempts. The proposal's eager source
  lifecycle is new contract work, not something M04 has already implemented.
- Verify owned exception class/reason/message/chain behavior through both entry
  points and source construction, including faults during setup and use and
  library-owned trace arguments. No favorable public containment claim follows
  from research `category()` output.
- At M06, challenge declaration admission with cycles, unknown capabilities,
  duplicate bindings, missing ports and same-width semantic substitutions.
  Reject reordered admission, incorrect parameter roles and failure-path
  overrides before execution. Confirm mainnet/testnet reuse one declaration.
- At M06/M07, inject a failure at each executable stage and record calls:
  no later stage, unselected policy, alternate provider or replacement result
  may occur. Preserve the original owned reason and diagnostic containment;
  confirm infinity aborts derivation while zero tweak completes correctly.
  Cover receiving source-selection failure before generic execution and
  generic failures without address/result construction in the receiving flow.
- Preserve normative complete-node/address anchors and two real adapter
  replacement exercises. Test valid-looking wrong outputs against independent
  expected values rather than requiring generation to decode its own output.
- Compare receiving/direct generic success and failure for multiple accounts,
  policies and network contexts, including missing mapping, insufficient depth,
  invalid first/final edge and changed provider conditions after provisioning.
  Tests must verify exactly one tuple and `[0, index]`, with no fallback.

The agreed composition direction still needs concrete schema/representation
and execution-boundary review; no schema validator or executor has been
implemented or qualified by this document. M06 applies the assigned cases to
the first complete branch; M07 extends them to all policies and receiving.

The provider/runtime decision, these proposed signatures/data/failure choices,
feasibility evidence and assigned acceptance obligations must converge before
the M05 freeze. M06 then
implements the first complete branch; M07 adds remaining policies and the
receiving implementation. M08/M09 still own assembled verification and its
independent challenge. No applicable CP is closed by this proposal alone.
