# PIP-0003 — Public Address Derivation Execution Plan

This is the current HOW, ORDER, and EXECUTION reference for
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3).

## Current state — checked 2026-10-16

**Goal:** provide a locally usable, public-only operation that derives Bitcoin
P2PKH, P2SH-P2WPKH and P2WPKH addresses from an extended public key, a bounded
relative path, an explicit network and an address policy.

**Product status:** no capability implementation or behavioral verification
exists on `main`. At checked revision `6e5c5be`, `src/` contains only `.gitkeep`,
`composer.json` has no production provider, and `tests/SetupTest.php` verifies
autoload registration only. Documentation and research progress must not be
read as a working library feature.

| Stage | What has actually been delivered | Verification and remaining gate |
|---|---|---|
| M00 — recorded | Early scope, planning, provider research and reference probes. | Planning and feasibility evidence only; no implementation qualification. |
| M01 — draft delivered | [Contract and qualification draft](M01/M01-CONTRACTS.md), seven open questions and [diagrams](M01/diagrams/README.md). | [Executor assessment](M01/cp-executor-2026-10-06-210959.md) leaves replaceability, protocol correctness and independent evidence unresolved (CP-07/10/11); independent draft review is pending. |
| M02 — merged | [Merged PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9) records accepted O01–O06 protocol and boundary decisions, the O07 candidate/qualification map, and the PHP 8.3 minimum decision while retaining the generic relative-path contract. It includes historical PHP 8.1 Composer and bounded probe evidence. | The additional entry point is approved separately in [PIP00010](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/10). M03/M04 must qualify accepted behavior and candidates on PHP 8.3/8.4. No provider is selected or concrete PHP type frozen. |
| M03 — independently reviewed, pending merge | The [ECC, unchanged CKDpub, native-false and runtime-hash probes](M03/README.md) exercise two Composer-installed PHP adapters, a pinned native libsecp256k1 reference and PHP facilities on PHP 8.3/8.4. Six sourced public-path payloads and forced exact-index, path/depth, metadata and fault cases agree across all six composition runs. Pinned BIP32 xpub text counter-checks those payloads and three published invalid public points through two Base58 decoders; all three ECC implementations agree on the expanded 13-point/nine-scalar/two-cross-point boundary corpus. A focused classifier distinguishes confirmed infinity from forced native `false` on finite sums; injected comparator and confirmation faults expose its same-library trust limit. Q08 runtime answers match NIST, RIPEMD-160 author, RFC 4231 and BIP32 sources; controlled missing-facility, malformed-result, PHP parser-fault and shared-consumer negative controls add bounded Q12 evidence. | Q04/Q05 research path/depth and sourced-public-edge comparison is expanded; Q06/Q07 point/scalar boundaries have published BIP32 counter-proofs where available. Q08 runtime/conversion subset passes. The independent review returned the prior revision to execution for R1 (CP-07/CP-10). The native admission correction and R3 negative-control improvement passed the independent follow-up at `2aa0194`; maintainer merge remains pending. Provider selection and assembled qualification remain downstream. No provider is selected. |
| M04–M09 — pending | Codec qualification, joint selection, implementation and assembled/independent verification. | No production behavior has passed these gates. See the roadmap for order and exit criteria. |

**Next reviewable step:** M02's accepted decisions are on `main` through PR #9.
[PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12)
begins M03's bounded ECC adapter, native-reference and unchanged CKDpub
composition probes; a focused native-false classification spike now adds
failure evidence. The [independent report](M03/cp-reviewer-2026-10-15-204006.md)
returned revision `f3afccb` to execution for native admission finding R1. The
[executor response](M03/README.md#independent-review-findings-and-executor-response--2026-10-16)
records the correction and R3 verification improvement. The
[follow-up review](M03/cp-reviewer-2026-10-16-074200.md) passes CP-07/CP-10
at `2aa0194`; the maintainer's manual merge decision remains. M04 can
proceed from settled M02 decisions, with no provider selected yet. O03 retains
the approved generic `relativePath` contract. Its separately approved [PIP00010 receiving entry
point at `badb9cd`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/badb9cd4a5f4579174c8c7121558e9b38d566349/docs/pips/PIP00010/PIP00010.md)
takes separate integer `account` and `index` fields, selects the account-level
key from an explicit immutable source, and supplies `[0, index]` to the generic
operation. Implementation and qualification remain. Generic qualification may
proceed under PIP-0003. Before M05 freezes contracts, review the testnet label
and concrete public exception types. M01's independent review is still open.
When evidence changes, update this status and the roadmap together with its
exact PR, commit or test reference.

## Reading path and authority

| To answer | Read | Role |
|---|---|---|
| What is happening and what follows? | This section, then the [Execution roadmap](#execution-roadmap). | The only current execution-status and ordering reference. |
| What was approved? | The [approved proposal revision at `72c4fa1`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md) and [Issue decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555). | Proposal defines WHAT, WHY and BOUNDARIES; the Issue records approval and coordination. |
| What is under review now? | [Draft PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12), [M03 research](M03/README.md), then [M02 protocol record](M02/M02-PROTOCOL.md). | Bounded ECC evidence and the accepted protocol/qualification inputs; no production selection. |
| Which contracts and evidence support execution? | [M01 draft](M01/M01-CONTRACTS.md) and the [technical guidance below](#technical-decisions-and-detailed-execution-guidance); consult [M00 research](M00/research/public-address-provider-research.md) or [reviews](M00/reviews/plan-first-capability-canonical-alignment.md) for a specific historical claim. | Detailed inputs and provenance; M00/M01 files do not carry current progress status. |

New work follows the [PIP Specification v1.0](../../specifications/paycrypto-pip-specification-v1.0.md),
[Primitives Canonical v1.8](../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md),
[Public Architecture v1.1](../../canonicals/public-architecture/paycrypto-public-architecture-canonical-v1.1.md),
and [Counter-Proof Protocol v1.5](../../protocols/primitives/COUNTER-PROOF-REPORT-v1.5.md)
within their respective authority boundaries. PRs, commits, tests and reviews
establish execution evidence. PIP-0003 predates the current Issue-origin model;
Issue #3 is retrospective. The maintainer approved the pinned proposal on
2026-10-10, without backdating that decision. The frozen proposal's statement
that approval was not yet recorded describes its earlier revision; the Issue
decision supplies current approval status. Any material departure from the
approved boundaries or accepted Canonical decisions returns to the Materiality
Gate. This one-time historical reconciliation adds no process for other PIPs.

## Execution roadmap

Each increment needs an exact source/dependency revision and focused validation.
Every merged increment must preserve a valid governed state even if the full
capability is incomplete. Combine increments in one PR when practical; use
several for a concrete review or execution benefit. Status below is an
engineering observation, not a new PIP decision state.

| Work / status | Dependencies, objective, scope and deliverables | Acceptance and verification |
|---|---|---|
| M00 — recorded | Establish PIP identity, scope, research and planning baseline. | Historical plan and plan-alignment review are recoverable; neither verifies implementation. |
| M01 — delivered draft, not independently verified | Define R01–R12, Q01–Q12, O01–O07, D01–D11 mapping and diagrams. | Assessed contract hash and executor report exist; CP-07/10/11 remain unresolved. |
| M02 — merged in PR #9 | From M01, pin versioned BIP32, SLIP-132, network and codec sources; record accepted O01–O06 behavior and boundaries without replacing the generic contract; accept the O07 focused-provider/runtime candidate and qualification map by required operation, runtime, license, security, footprint and dependency path. | PIP00010 governs the additional receiving entry point. Qualify the accepted generic capability in M03/M04, with missing suitable providers explicit. Candidate eligibility is not selection. |
| M03 — independently reviewed, pending merge | After relevant M02 decisions, exercise two real ECC semantic adapters and one unchanged CKDpub consumer; establish independently implemented ECC reference and PHP 8.3/8.4 evidence. The bounded ECC/CKDpub, native-false and confirmation-fault, PHP parser-fault, shared-consumer negative-control and hash/conversion probes and six-package ECC Composer graph are recorded under [M03](M03/README.md). Research spike only. | Q04/Q05 has six sourced public cases and path/depth boundaries, including full depth capacity and no HMAC on rejected paths. Q06/Q07 adds three BIP32 invalid-public-key examples and controlled field/order/infinity cases across two PHP adapters and native libsecp256k1. Q08 runtime/conversion subset passes sourced known answers, binary/empty/long-key cases and unsigned extremes on both runtimes. Q12 has bounded missing-extension/algorithm, malformed-result, parser-error and shared-consumer challenges plus an ECC lineage assessment; controlled provider-fault and native common-mode challenges are recorded with explicit trust limits. The first independent report failed CP-07/CP-10 for R1; the follow-up review passes both at corrected revision `2aa0194`. R2 public exception sanitization remains required before M05/M06 public exposure. These probes do not select a provider or qualify an assembled capability. |
| M04 — pending | After relevant M02 decisions, qualify hashing, conversion, Base58Check, SegWit codecs, strict key admission and immutable data-only definitions. Key validity closure also needs M03. | Run Q02–Q03/Q08–Q12 on both runtimes: all public versions, exact length/trailing/checksum, codec negatives, incompatible intent, two real adapters per suitable library-backed boundary and unchanged consumers. Counter-prove future Signet profile extension with shared address bytes and a distinct context identifier in test-only fixtures; do not expose it as initially supported. Disclose any missing alternative. |
| M05 — pending | After M03/M04, jointly record selected versions, rejected alternatives, runtime/Composer graph, semantic contracts, errors, wiring and first branch. | Selection cites executed evidence; requalify changed contract, provider or dependency. Preliminary probes do not count. |
| M06 — pending | After M05, implement one complete public request-to-address path on both networks, with admission, derivation, adapters, validated definitions and local example. | Independently justified outputs and negative cases pass on PHP 8.3/8.4; no vendor or higher-domain leakage. Record unsupported policies. |
| M07 — pending | After M06, add remaining policies and shared lower capabilities; assess Bitcoin Cash reuse only as design fitness. | Six policy/network combinations, direct-composition parity, distinct terminal semantics, failures and provider replacement pass. No CashAddr implementation. |
| M08 — pending | After M07, verify assembled candidate, with exact source/dependency/definition/runtime revisions and requirement-to-evidence map. | Run `composer ci` on PHP 8.3/8.4 plus conformance/differential suites; challenge all applicable CPs against actual artifact. Unresolved applicable CPs prevent readiness claim. |
| M09 — pending | After M08, obtain independent challenge and correct findings on new revisions. | Reviewer identifies exact artifact/evidence; no applicable CP remains failed or unresolved before compliance claim. Preserve issued reports. |

M03 and M04 can overlap only where their M02 inputs are settled; M04 key-admission
closure still needs M03 validity evidence. One branch at M06 is an incomplete
capability, so any merged increment must independently preserve a valid
architectural state. The table describes the historical sequence, not a new
automatic assignment.

## Technical decisions and detailed execution guidance

### Governing boundary and retained plan choices

The approved proposal and Canonical require public-only admission, relative
non-hardened derivation, explicit network/policy intent and local use. Primitives
owns contracts, protocol semantics, validation, errors, definitions and
composition. Algorithms, curve arithmetic and standardized codecs remain
delegated. Core, SDK, Consumer, seeds, private keys, signing, transactions,
allocation, persistence, hosted integration, Taproot, Bech32m, Lightning,
other chains and CashAddr delivery are outside this repository increment.
Shared behavior runs until distinct terminal address semantics diverge. Network
variation is validated data, never executable configuration. No implicit account
path, depth, policy or network conversion comes from the reference application.

The M02 review on 2026-10-12 raised the supported consumer minimum to PHP 8.3,
with PHP 8.3/8.4 verification. The earlier PHP 8.1 trials remain historical
feasibility evidence, not support claims. Other delivery choices retained here
include explicit GMP requirements if selected, and two suitable distinct
libraries per library-backed boundary using real semantic adapters,
one contract suite and unchanged consuming compositions. The two-library rule
is a **slice-level plan choice**, not a universal Canonical rule or proof of
mathematical independence. Runtime facilities need conformance without fake
duplicate wrappers. One deliberately selected provider ships per capability;
there is no automatic fallback. If an alternative is unavailable, document the
affected gate and bring evidence for an explicit plan-level reconsideration.
The broad `bitwasp/bitcoin` package remains reference-only for this slice due
to footprint; focused `bitwasp/bech32` remains an unselected candidate.

Avoid a coin class hierarchy, universal manager, configurable protocol
interpreter, or chain of provider fallbacks without a concrete admitted need.
No proposed PHP class name, namespace, storage format or example syntax is a
frozen API. Definitions contain semantic data, not vendor objects, callbacks,
scripts or fallback rules. Vendor points, scalars, buffers, GMP objects and
exceptions stay behind adapters. A whole BIP32 package is not a prerequisite:
CKDpub is an owned protocol composition over delegated HMAC/ECC. HASH160 composes
delegated SHA-256 and RIPEMD-160.

### M02 decisions: qualification before contract freeze

The [M02 protocol record merged in PR #9](M02/M02-PROTOCOL.md)
pins BIP32, SLIP-0132, BIP49/84, BIP141/173 and Bitcoin Core sources. The
maintainer accepted O01's exact-path behavior, O02's strict initial
version/network/policy mapping and O03's receiving-only account/index use case
on 2026-10-12 after the earlier unsupported O01 acceptance label was corrected.
O03 retains the generic relative-path contract; its additional receiving entry
point is separately approved in PIP00010 but not implemented. O04's bounded
nonempty generic path, O05's validated data-definition/composition rule and
O06's public exception/failure rule are accepted. None is executed
or qualified production behavior; challenge them
through M03–M05.

- **O01 (accepted M02 behavior; qualification pending):** one exact-index
  CKDpub attempt reports the child at that index or a distinguished
  invalid-child outcome; M05 chooses its internal PHP representation. The public
  address operation attempts each requested
  path component once. An invalid child at
  any level fails the request without an address or automatic advancement.
  A successful derived path equals the requested path. The operation keeps
  no allocation or consumer state. HMAC-SHA512 takes the 32-byte parent
  chain code as key and compressed parent key plus four-byte big-endian index
  as message; split the 64-byte output. A left half at/above group order or
  resulting infinity is invalid. **Zero is valid**: retain the point but update
  chain code and child metadata. Only successful right halves become chain code.
  Parent fingerprint comes from the immediate parent's HASH160.
- **O02 (accepted initial admission; qualification pending):** compare all four
  decoded version bytes, with explicit policy/network and no inferred defaults.
  The initial allowlist is mainnet `xpub/0488b21e` P2PKH,
  `ypub/049d7cb2` P2SH-P2WPKH, `zpub/04b24746` P2WPKH; testnet
  `tpub/043587cf`, `upub/044a5262`, `vpub/045f1cf6` in the same order.
  Reject malformed, private, unknown, multisignature and cross-network/policy
  intent with a failure and no address or fallback. Malformed, unsupported and
  incompatible inputs retain distinct meanings; O06/M05 sets their public PHP
  representation. Legacy BIP49 xpub/tpub nested-SegWit exports are excluded
  initially. This is admission policy, not a cryptographic property of BIP32.
  Proposed address definitions use P2PKH/P2SH bytes `00/05`, HRP `bc` on
  mainnet; `6f/c4`, HRP `tb` on testnet. `testnet` names address parameters,
  not full chain identity. Do not infer account path/depth from SLIP examples.
- **O03 (consumer use case accepted; additional entry point approved in
  PIP00010):** retain the generic public operation that takes an admitted
  xpub and explicit relative-index sequence `relativePath`, with network and
  policy. M05 sets its concrete PHP representation.
  The receiving-only entry point takes separate integer `account` and `index`
  fields, plus explicit network and policy; it selects the account-level xpub
  from an explicitly provisioned immutable source keyed by network, policy and
  account, then calls the generic operation with `[0, index]`. Logical
  coordinates `[0, 0, 1000]` and `[1, 0, 5]` mean account/receiving
  branch/index, not a three-component request field. The respective generic
  relative paths are `[0, 1000]` and `[0, 5]` from different account keys.
  Missing, duplicate, malformed or incompatible mappings fail without fallback.
  The provisioning party owns the key-to-account binding; serialized metadata
  cannot authenticate wallet ancestry. Store-to-account mapping remains with
  the consumer. The generic operation proceeds under PIP-0003; the additional
  Primitives-owned receiving entry point is governed by the [approved PIP00010 revision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/badb9cd4a5f4579174c8c7121558e9b38d566349/docs/pips/PIP00010/PIP00010.md)
  and still requires implementation and qualification.
- **O04 (semantic decision accepted; qualification pending):** the generic
  public operation requires a nonempty ordered sequence of integer relative
  child indices. An empty sequence fails; index `0` is valid. Indices are
  `0..2^31−1`, and path length is no greater than `255 − imported depth`.
  Attempt each requested component once and fail at an invalid child without
  choosing another path.
  These path rules apply to the generic operation only; M05 sets its concrete
  PHP representation. The approved PIP00010 receiving entry point requires
  separate explicit integer `account` and `index` fields, both allowing `0`,
  and never accepts a caller path or branch. Missing or malformed fields fail
  without defaults. It always calls the generic operation with `[0, index]`.
  Its selected account key needs capacity for two further derivation levels;
  insufficient capacity fails before derivation. Qualify the generic `[]`
  rejection and receiving `(0, 0)`
  parity with a direct generic `[0, 0]` call.
- **O05 (accepted definition/composition rule; qualification pending):** a
  validated immutable definition holds an identifier, one-byte address
  versions, lowercase HRP and four-byte public-version/policy map. Its initial
  mainnet/testnet values match O02 exactly; reject missing, extra, duplicate,
  conflicting and cross-network fields. Shared derivation and the three
  distinct address-policy compositions consume validated parameters. A
  parameter-only network variation does not introduce a network-specific class
  or execution path. The identifier, not matching address bytes or HRP, keys
  PIP00010's account source with policy and account. Test-only Signet may reuse
  testnet encoding under its own validated identifier; initial Signet intent
  fails. The source format and shared PHP semantic type remain M05 choices.
- **O06 (accepted consistent public exception rule; qualification pending):**
  both public-address entry points return a complete result or throw a
  Primitives-owned PHP exception with a stable semantic category. Internal
  helpers may use documented boolean, nullable, object or explicit outcome
  values; they normalize failures at the public boundary. No undocumented
  failure sentinel or partial address is returned. Failures distinguish
  malformed input, unsupported or
  incompatible intent, invalid network definition, exact-index invalid child,
  path/depth bounds and provider/environment failure. The receiving entry point
  additionally distinguishes a missing exact account mapping from invalid
  source data. Validate definition/request shape, exact account-key selection
  where applicable, checked decode and structure, compatibility and depth,
  then derivation/encoding; the first failing stage determines the category.
  No account, key or provider fallback, partial address or diagnostics exposing
  keys, chain codes, full wallet paths or raw vendor details. M05 sets concrete
  public exception classes and signatures, not a second public error-result
  channel. Q12 checks that documented provider failure returns become owned
  public exceptions.

The maintainer raised Signet as an O05/Q02 extension challenge during the O02
review. [Bitcoin Core's pinned chain parameters](https://github.com/bitcoin/bitcoin/blob/228877d7836a55c1d6fde90b0b7ba2d4c90248ab/src/kernel/chainparams.cpp)
give Testnet3, Testnet4 and Signet identical address prefixes, `tb` HRP and
test extended-public-key version. M04 must show that a test-only, separately
identified Signet profile can reuse validated address parameters and the same
composition without assuming encodings uniquely identify a chain. The same
key/path/policy should yield the same address text under either profile, while
the result identifies only caller-supplied validated context. The initial
public allowlist must reject an explicitly Signet-labeled definition; the
test-only expanded registry must reject a malformed Signet definition.
See the [M02 counter-proof](M02/M02-PROTOCOL.md) for the full cases. The Signet
challenge is a consensus parameter outside this address-encoding definition.

The accepted exact-path, strict version-policy, bounded-path and data-only
definition/composition choices still need consuming-use and adapter review.
PR #9 recorded the O01–O07 M02 dispositions; it did not make
Q tests pass or select a provider.
The assessed M01 Q05 draft contains advancing-path cases, and its Q04 leaves
the empty-path disposition open. M03/M04 must execute the revised O01 and O04
cases in the M02 record rather than treating old expectations as accepted
behavior.

**O07 accepted investigation map (2026-10-14; dated research, no production selection):**

| Delegated need | Candidate evidence and remaining gate |
|---|---|
| D01–D03 and D11 | PHP `hash`, `hash_hmac`, `pack` and `unpack` passed the [M03 Q08 runtime/conversion probe](M03/README.md#q08-runtime-hashconversion-and-q12-facility-challenge) on PHP 8.3/8.4 against sourced NIST, RIPEMD-160 author, RFC 4231 and BIP32 answers, including empty/binary/long-key inputs, exact lengths, argument order and unsigned/endian extremes. The qualification is bounded to the tested 64-bit runtimes; M04 still needs its end-to-end codec/composition checks. |
| D04–D05 ECC | Paragonie ECC 2.6.0 and Simplito elliptic-php 1.0.12 have different raw APIs and bounded historical PHP 8.1+GMP probes. Current [M03 evidence](M03/README.md) runs both against the same CKDpub consumer and a source-pinned uma/secp256k1-php 0.1.2/libsecp256k1 reference on PHP 8.3/8.4. Six sourced public derivations, three published BIP32 malformed points and controlled point/scalar boundaries agree; a focused native classifier confirms infinity before mapping `false` to invalid child and contains injected comparator faults. A raw-result check confirms both PHP providers return point objects for finite and infinity sums; `null` is the adapter's documented mapping after `isInfinity()`, and a synthetic adapter that lies about it is a contract breach rather than a provider bug. A focused parser-fault challenge now separates pinned off-curve signals from unrelated faults in both PHP research adapters; an identical mimicked signal remains indistinguishable. A controlled double-fault can still fool same-library native confirmation. Broader provider faults and suitability of the native confirmation in a public-only production adapter remain to review. The PHP candidates require strict adapter scalar admission. Actual dependency paths are in the M03 focused lock; no backend is selected. |
| D06–D07 Base58Check | User-contributed leads StephenHill Base58 2.1.0 and Tuupola Base58 2.2.0 were already mapped: StephenHill is raw binary Base58 only; Tuupola has bounded checked-key/address results, but its checked API uses a one-byte option while extended-key versions are four bytes. The third lead, mattallty/base58, is an integer/number codec without binary or checked support and uses PHP 8-incompatible string-offset syntax, so it cannot serve D06–D07 on PHP 8.3. Muvon Base58 Codec 0.1 remains a second conditional checked-codec candidate with a bounded BIP32 public-key round-trip probe; its undeclared BCMath use and empty-string invalid decode require explicit adapter handling. No two checked codecs have passed semantic qualification. |
| D08–D09 SegWit | Focused BitWasp Bech32 0.0.1 has bounded BIP173 probes and a maintenance question. cjpgdk Bech32 0.1.2 is a second lead probed in PR #9; explicit HRP/v0 length/checksum semantics need adapter tests. Both need complete comparison and qualification. Their historical PHP 8.1 Composer dependency paths are recorded. Cardano PHP Bech32 1.1.0 is now eligible for PHP 8.3 investigation as a generic codec lead, with Bitcoin SegWit admission and BitWasp lineage still to assess. |
| D10 structural decode | Runtime fixed-width conversion plus Primitives-owned exact structure/admission is the research lead. No bespoke standardized decoder or broad Bitcoin package is selected. |

Cardano PHP Bech32 1.1.0 requires PHP `^8.2`, and the user-supplied
uma/secp256k1-php 0.1.2 native binding requires PHP `>=8.2`; both meet the
new PHP 8.3 version floor, with actual API/runtime qualification still pending.
The older BitWasp secp256k1 binding has an unresolved PHP 8 build report.
The [M02 record](M02/M02-PROTOCOL.md) explains the baseline decision and
candidate limits. A historical Composer trial rejected Furqan Siddiqui
Base58Check 0.1.5 on PHP 8.1.34 because it requires `php-64bit: ^7.2`.
Version/requirement observations are from the dated [M02 record](M02/M02-PROTOCOL.md)
and [provider research](M00/research/public-address-provider-research.md);
recheck before M05 selection. An empty user-candidate worksheet cell neither
blocks independent search nor grants selection approval.

### R/Q responsibility and acceptance map

The [assessed M01 draft](M01/M01-CONTRACTS.md) supplies precise R01–R12,
Q01–Q12 and O01–O07 identities. No row mandates its own class or package.

| Requirement | Owned semantics and delegated operations | Required qualification |
|---|---|---|
| R01–R02 | Public request/result; immutable validated network definitions; D11 conversions as needed. | Q01 all six combinations, standalone use and direct-composition parity; Q02 two valid initial definitions, missing/extra/invalid fields and non-mutation, plus the same composition with different admitted network data and a test-only Signet profile with duplicate testnet encoding parameters and a distinct context identifier. Unknown public profiles fail until admitted; parameter-only variation introduces no network-specific behavioral class. |
| R03–R04 | Exact extended-public-key structure, metadata/version/material admission and bounded relative path; delegate D04, D06–D07, D10–D11. | Q03 valid/invalid BIP32 public fixtures, private/unknown/mismatched versions, checksum, exact length, trailing data, root metadata and point validity; Q04 rejects empty path, accepts `[0]`, checks index/depth/resource bounds and imported hardened ancestry; receiving `(0, 0)` matches direct generic `[0, 0]`. |
| R05–R07 | CKDpub composition, child metadata, valid compressed key and public tweak; delegate D03–D05/D11, D01–D02 for fingerprint. | Q05 official public edges, invalid first/intermediate/last candidate, zero and exhaustion; Q06 exact 33 bytes, prefix and curve validity; Q07 tweak 0, 1, n−1, n+, wrong width, infinity and replacement through real ECC adapters. |
| R08 | HASH160 and BIP32 message construction over delegated SHA-256, RIPEMD-160, HMAC-SHA512 and fixed-width conversion. | Q08 sourced known answers, empty/binary inputs, long HMAC keys, argument order, exact lengths and unsigned/endian extremes. |
| R09–R11 | P2PKH key HASH160 + version; P2SH-P2WPKH HASH160 of exact witness-v0 redeem script `0014` + 20-byte key hash + P2SH version; P2WPKH v0/20-byte witness program + HRP. Delegate D06–D09 codecs. | Q09 P2PKH, leading zeros/alphabet/checksum/length; Q10 exact redeem-script preimage, distinct key/redeem hashes; Q11 BIP173 valid/invalid, case, HRP, checksum, padding, v0 size and incompatible policies. |
| R12 | Stable failure meanings, bounded work, explicit wiring and vendor containment. | Q12 missing runtime facilities, malformed provider outputs, vendor exceptions, no fallback and safe diagnostics. |

The [delegation inventory](M00/research/delegation-inventory.md) identifies
D01 SHA-256, D02 RIPEMD-160, D03 HMAC-SHA512, D04 point parsing/validation/
serialization, D05 public tweak, D06 Base58, D07 Base58Check, D08 Bech32,
D09 SegWit-v0 codec, D10 extended-key mechanical decoding and D11 fixed-width
conversion. D04/D05, D06/D07 and D08/D09 may share focused providers.
D10 structure/admission remains owned even if runtime `unpack` converts fields.
D11 does not justify a generic buffer framework. Do not implement standardized
script serialization or a codec locally.

The checksum-free extended public-key structure is exactly 78 bytes: version 4,
depth 1, parent fingerprint 4, child number 4, chain code 32 and compressed
public key 33. Root depth requires zero fingerprint/child number. Preserve
leading zero bytes. An imported hardened child number is historical ancestry,
never permission for new hardened derivation. A compressed key needs exact
33-byte length, `02`/`03` prefix **and** delegated finite point validation.
The tweak is exactly 32 unsigned big-endian bytes in `0..n−1`; do not reduce an
out-of-range input modulo n. Infinity is a distinguished invalid-child result.

Build evidence as separate classes: version-pinned normative vectors,
independently justified expected outputs, admitted regressions, malformed
boundaries, controlled rare outcomes and differential checks. Force invalid
children at every path position, zero, n-or-greater, infinity and exhaustion;
label forced outcomes distinctly from naturally observed cases. Test metadata
after successful exact-index edges and verify no child is emitted after an
invalid attempt. Investigate disagreement rather than voting. The
official Bitcoin BIP vectors are the primary expected-output source wherever
they cover a case; record the exact source snapshot and compare their published
text through an independently checked decode path. External implementations
and their fixture bytes provide secondary counter-proofs, never the sole oracle
for a case with a published BIP vector. For cases without a normative vector,
use independently justified calculations or a separately implemented reference
and label synthetic fault injections explicitly. The
[BitWasp investigation](M00/reviews/bitwasp-public-derivation/README.md)
provides 17 round trips, six public edges and 30 address regressions, plus
known bad reference behavior: invalid root metadata admitted, trailing payload
ignored, zero tweak rejected and a controlled infinity result. These become
negative challenges, not rules to reproduce. A Paragonie-backed reference is
not independent of another Paragonie-backed implementation.

PR #9's PHP 8.1/GMP probe agrees with selected PHP 8.3 raw observations but
shows both tested ECC APIs accepting an order-sized tweak unless the semantic
adapter rejects it; one accepts oversized compressed material. Its cjpgdk
probe shows that checking an array alone can misclassify a bad Bech32 checksum,
and raw SegWit decoding can admit an unapproved HRP or wrong v0 program length.
Test these adapter obligations. Probe success is not provider selection or an
independent ECC oracle. Research dependencies stay isolated from production
Composer dependencies until M05. For each selected candidate pin release/source,
license, maintenance/security evidence, PHP/extensions, complete Composer graph,
actual executed dependency path, unsupported behavior and replacement/reference
independence. The [provider research](M00/research/public-address-provider-research.md),
[candidate worksheet](M00/research/public-address-candidate-worksheet.md),
[composition proposal](M00/research/public-address-composition-proposal.md) and
[pinned probe pack](M00/research/provider-refresh-2026-10-02/README.md) are
investigation inputs, not frozen implementations.

### Runtime and Counter-Proof execution

Run the repository's `composer ci` workflow on PHP 8.3 and 8.4 for M06–M08;
it includes Composer metadata/platform checks, PHP lint, PHPUnit and PHPStan.
Supplement it with the semantic corpus and a standalone example. Pin exact
source, dependency, definition and runtime revisions plus commands/results in
PR/CI or another suitable existing evidence surface. A passing setup test,
loaded class or mock-only replacement result does not verify the capability.

At M08/M09 assess the **assembled artifact** against Canonical CP-01–CP-12:
CP-01–04 need admitted purpose, reuse and data-only variation; CP-05–07 need
ownership, real adapter execution, replacement and containment; CP-08–09 need
local public-only use and domain isolation; CP-10–11 need protocol vectors,
boundary and independent evidence; CP-12 needs justified scope and disposition
of open decisions. Use current Counter-Proof Protocol coverage and verdict
meanings. An applicable executor `UNRESOLVED` or reviewer `FAIL` prevents a
compliance claim. A report on a planning draft cannot establish an implementation
PASS. Issued reports retain their original artifact identity and verdict;
corrections get new revisions and evidence. PR reviews, CI or Issue discussion
can carry adequate evidence; standalone reports are used when a distinct role
requires them.

## Completed work and verification evidence

| Source | Established result and limit |
|---|---|
| [Early first-capability plan at `83ccd55`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/83ccd5571843eed57726e140e493868f0f152b5f/docs/PLAN-FIRST-CAPABILITY.md), [embedded PIP plan at `247f1f1`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/247f1f11c484675bcc8f56aeb0a20c68edde4337/docs/pips/PIP-0003/PIP-0003.md), [package commit `95e8384`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/95e83842ed9f4a577998e49851449230f56f56b2) | Historical scope, sequence and delivery gates; no current approval or implementation was established by those records. |
| [Independent plan-alignment review](M00/reviews/plan-first-capability-canonical-alignment.md), [2026-10-03](M00/cp-executor-2026-10-03-171445.md) and [2026-10-04](M00/cp-executor-2026-10-04-011735.md) executor reports | Plan/research and package-document assessment at stated revisions, not provider or capability review. |
| [M01 draft](M01/M01-CONTRACTS.md), [diagrams](M01/diagrams/README.md), [issued M01 report](M01/cp-executor-2026-10-06-210959.md) | R/Q/O map and draft boundary self-challenge. The report's SHA-256 `0224c493f2b507408f35207d6d0b5577e7206611be76644cfde17794414e5d41` identifies [the pre-relocation draft at `96707dd`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/96707dd/docs/pips/PIP-0003/milestones/M01-CONTRACTS.md), not the current file after link repair. CP-07/10/11 remain unresolved; no runtime or independent verdict. |
| [Merged PR #5](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/5), [merged PR #6](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/6), [Issue decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555) | Proposal/plan separation, removal of obsolete top-level navigation, and present-day approval of pinned proposal revision. No runtime verification. |
| [Merged PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9), with [M02 protocol record](M02/M02-PROTOCOL.md) | Accepted O01 exact-path, O02 strict admission, O03 account/index consumer use case, O04 bounded nonempty generic path, O05 data-only validated definitions and O06 public exception/failure rules, while retaining the generic relative-path contract; O07 candidate/qualification map, PHP 8.1 Composer graph and bounded probes. The additional receiving entry point is approved in PIP00010 but unimplemented. No adapter qualification, final selection or production tests. |

## Risks, gaps and decisions requiring judgment

- Maintainers accepted PR #9's exact-path derivation behavior and strict
  six-version/policy allowlist, including initial rejection of legacy BIP49
  xpub/tpub nested-SegWit requests. M03 has checked exact CKDpub behavior,
  including empty-path rejection, against its bounded sourced and controlled
  corpus; M04 still needs version/policy and codec admission evidence. Review
  the testnet label and concrete public exception types before M05 freezes
  a contract. Any
  material departure from the approved proposal/Canonical needs the current
  Materiality Gate.
- Second checked-Base58Check and SegWit candidates have bounded probes, but
  neither boundary has two qualified semantic adapters. A bounded independent ECC
  reference, PHP 8.3/8.4 focused Composer graph and real PHP ECC replacement
  exercise now exist in M03. Complete joint license/security/maintenance
  assessment and production replacement qualification remain for M05. The PHP 8.1 trial lock records
  candidate dependencies, not a production selection. If two suitable
  libraries cannot qualify, document the affected plan-level gate and an
  explicit tradeoff; do not claim an unexercised alternative passed.
- M01 CP-07/10/11 remain unresolved. PR #9's source interpretation has not
  passed the full Q corpus or independent implementation review. Forced rare
  outcomes need labels distinct from observed behavior.
- The frozen approved proposal says no decision was recorded at its revision;
  the later Issue comment records approval. Older execution context cites
  v0.1/v0.2 governance and older Canonical/Counter-Proof revisions. The current
  authorities at the top of this plan govern new work.
- PHP API shape, physical definition format, provider choice and final failure
  representation remain open until supported M05 selection. Bitcoin Cash remains
  a reuse fitness check, not a delivered feature.

## Historical reconciliation and retained artifacts

This inventory covers the first-capability plan revisions `61c2e48` (WIP),
`4e77140` (canonical boundary consolidation), `55b4a7b` (rewrite),
`83ccd55` (delegation-led research), `56fe0f0` (PIP identity), `95e8384`
(milestone package), `2aca94d` (v0.2 transition), `96707dd` (M01 delivery),
`247f1f1` (last embedded plan), `758716a` (consolidation), `b1529f3` and
`72c4fa1` (proposal/plan split), `f25d537` (reference correction),
`626f952` (navigation retirement), and `10260e4`/`24cc739` (M02 branch);
the current proposal/plan and `docs/README.md`; all
M01 contracts/handoffs/diagrams, execution context, templates, reports,
snapshots and manifests; the related PIP-0001/PIP-0002 Git records, provider
research and independent plan review; Issue #3, PRs #5/#6/#9; current source
and tests; and the M02 branch's protocol/probe files. Earlier unique contract,
qualification, provider and verification information is integrated into the
roadmap and guidance above. Completed work and its limits appear in the current
state and evidence table. Unresolved matters appear in the risks section.

The earliest plan's proposed separate implementation-PIP creation was overtaken
by PIP-0003's identity and later Issue approval; no new PIP number is required
for execution inside the approved boundary. The package's mandatory milestone
handoffs, snapshots, template use and canonical-reading shortcut were historical
process choices. PIP Specification v1.0 favors existing Issue/PR/Git/CI evidence
and permits extra artifacts only for distinct responsibilities. The M01 draft is
an assessed artifact with an issued hash; its diagrams explain completed work.
The [snapshot](M00/snapshots/milestones-2026-10-03-171445.tar), [2026-10-03 checksum manifest](M00/SHA256SUMS-2026-10-03-milestones),
and [2026-10-04 manifest](M00/SHA256SUMS-2026-10-04-execution-package) are historical
package evidence, not active instructions. Git preserves the already-retired
top-level `docs/PLAN-FIRST-CAPABILITY.md` after PR #6. PR #9's merged M02
protocol file remains decision/probe evidence; execution direction stays here.

The workspace now groups early public-address research, its reference probes,
the independent first-plan review, two planning assessments, issued manifests and
the snapshot under `M00/`; the contract draft, executor report and diagram
sources/exports under `M01/`. Only the proposal and this plan remain at the PIP
root. The research and reference probes began under PIP-0002; their M00 placement
does not relabel their origin or turn feasibility observations into qualification.
These are relocations for scope/navigation, not a new assessment. The approved
proposal is restored byte-for-byte to its pinned `72c4fa1` revision; its
historical relative links resolve at that revision. This plan supplies current
navigation to relocated evidence. The redundant execution context, templates
and snapshot instructions remain recoverable at `95e8384`;
the M01 handoff remains recoverable at `96707dd`. The M00 snapshot also retains
the original early handoff template. Links in current Markdown copies outside
the frozen proposal were repaired; the two M00 checksum manifests still name
the original repository paths and hashes because they identify the **issued
historical package**.
To verify the M00 snapshot, extract its tar into a temporary directory and run
`sha256sum -c` there against the M00 2026-10-03 manifest; the manifest's paths
refer to paths **inside the tar**. The later package can be checked against Git
revision `95e8384`. Verify the M01 contract hash from
`96707dd:docs/pips/PIP-0003/milestones/M01-CONTRACTS.md`. Never interpret a changed
live Markdown hash as a corrected old verdict. The exact issued reports and
assessed content remain recoverable through Git, while current copies preserve
their verdicts and use working links.

The moved probe and BitWasp investigation packs retain their issued `SHA256SUMS`
files. Their README hashes identify Git revisions `55b4a7b` and `61c2e48`,
respectively. The provider README was already revised after its issued hash;
both current README copies now carry working paths. All other listed evidence
files remain byte-for-byte unchanged. Verify the complete issued packs at those
revisions, or verify their unchanged data/script entries in the current
directories while excluding `README.md`. The checksum files are evidence
identifiers, not new checksums for the reorganized workspace.

[PIP Specification v1.0 §9](../../specifications/paycrypto-pip-specification-v1.0.md#9-history-supersession-and-archival)
reserves whole-workspace archival for a closed Issue. The proposal and this plan
are the active governance/operational documents; milestone files have evidence
roles, not competing plan authority. Old references naming `PIP-0003.md` as a
plan refer to its assessed Git revision. PR #9 relocated its M02 files to
`M02/` and merged that layout into `main`.
