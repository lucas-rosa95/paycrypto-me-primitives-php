# PIP-0003 — Public Address Derivation Execution Plan

This is the single current HOW, ORDER, and EXECUTION reference for
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3).
The [proposal](PIP-0003.md) states WHAT, WHY, and BOUNDARIES. The Issue records
coordination and decisions; PRs, commits, tests and reviews establish execution
evidence. New work follows the [PIP Specification v1.0](../../specifications/paycrypto-pip-specification-v1.0.md),
the [Primitives Canonical v1.8](../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md),
the [Public Architecture v1.1](../../canonicals/public-architecture/paycrypto-public-architecture-canonical-v1.1.md),
and the [Counter-Proof Protocol v1.5](../../protocols/primitives/COUNTER-PROOF-REPORT-v1.5.md)
within their respective authority boundaries. This one-time reconciliation does
not impose a new process on other PIPs.

PIP-0003 predates the current Issue-origin model: Issue #3 is retrospective.
On 2026-10-10 a [present-day maintainer decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555)
recorded `PIP Decision = Approved` and pinned the [approved proposal revision
`72c4fa1f0a2c9449a93071b55bf20f85b0008621`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md).
This does not backdate approval. The approved proposal remains unchanged; its
pre-decision statement that no approval was then recorded is historically true.
The later Issue decision supplies current status. Any material departure from
approved boundaries or accepted Canonical decisions returns to the Materiality
Gate, not a quiet edit to this plan.

## Current state — 2026-10-11

The approved capability is a locally usable, public-only extended-key-to-Bitcoin-
address operation for P2PKH, P2SH-P2WPKH and P2WPKH on mainnet and testnet.
On local `main` at `3f82141`, `src/` contains only `.gitkeep`, `composer.json` has no
production provider and `tests/SetupTest.php` tests autoload registration only.
**No capability implementation or behavioral verification exists on `main`.**

M00 recorded the planning baseline. M01 delivered the [contract draft](milestones/M01-CONTRACTS.md),
[handoff](milestones/M01.md) and [diagrams](milestones/diagrams/README.md).
Its [executor report](cp-executor-2026-10-06-210959.md) left CP-07, CP-10
and CP-11 unresolved; independent review is pending. These are documentation
deliverables, not a verified runtime capability.
[Merged PR #5](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/5)
separated the proposal and historical plan, without implementation.

[Open PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9),
head `24cc739`, contains an [M02 protocol record](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/24cc739/docs/pips/PIP-0003/milestones/M02-PROTOCOL.md)
and isolated PHP 8.1/GMP and SegWit codec probes. It proposes O01–O06
dispositions; these are branch evidence, not merged `main` behavior, qualified
providers or frozen production contracts. O07 and the complete M02 candidate
comparison remain open. M03–M09 are pending.

**Next:** review PR #9 against the approved proposal and Canonical; accept or
revise its decisions, complete O07's candidate/qualification map, then exercise
M03/M04 research adapters. Do not infer completion from Issue checklist boxes,
old handoff labels, merge status or a passing setup test.

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
| M02 — in progress on open PR #9 | From M01, pin versioned BIP32, SLIP-132, network and codec sources; decide invalid-child/effective-path semantics, compatibility and limits; compare focused providers by required operation, runtime, license, security, footprint and actual dependency path. | Review/accept or revise PR #9's O01–O06; complete O07 with candidate dispositions and executable qualification procedures. Missing suitable providers remain explicit gaps. Eligibility is not selection. |
| M03 — pending | After relevant M02 decisions, exercise two real ECC semantic adapters and one unchanged CKDpub consumer; establish independently implemented ECC reference and PHP 8.1/8.3 evidence. Research spike only. | Run Q04–Q08/Q12: valid/invalid points, zero, n or greater, infinity, invalid first/intermediate/last child, effective path, depth, retry exhaustion, metadata and actual dependency paths. Record shared mathematical lineage. |
| M04 — pending | After relevant M02 decisions, qualify hashing, conversion, Base58Check, SegWit codecs, strict key admission and immutable data-only definitions. Key validity closure also needs M03. | Run Q02–Q03/Q08–Q12 on both runtimes: all public versions, exact length/trailing/checksum, codec negatives, incompatible intent, two real adapters per suitable library-backed boundary and unchanged consumers. Disclose any missing alternative. |
| M05 — pending | After M03/M04, jointly record selected versions, rejected alternatives, runtime/Composer graph, semantic contracts, errors, wiring and first branch. | Selection cites executed evidence; requalify changed contract, provider or dependency. Preliminary probes do not count. |
| M06 — pending | After M05, implement one complete public request-to-address path on both networks, with admission, derivation, adapters, validated definitions and local example. | Independently justified outputs and negative cases pass on PHP 8.1/8.3; no vendor or higher-domain leakage. Record unsupported policies. |
| M07 — pending | After M06, add remaining policies and shared lower capabilities; assess Bitcoin Cash reuse only as design fitness. | Six policy/network combinations, direct-composition parity, distinct terminal semantics, failures and provider replacement pass. No CashAddr implementation. |
| M08 — pending | After M07, verify assembled candidate, with exact source/dependency/definition/runtime revisions and requirement-to-evidence map. | Run `composer ci` on PHP 8.1/8.3 plus conformance/differential suites; challenge all applicable CPs against actual artifact. Unresolved applicable CPs prevent readiness claim. |
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

Historical delivery choices retained here include PHP 8.1 baseline plus PHP
8.3 verification, explicit GMP requirements if selected, and two suitable
distinct libraries per library-backed boundary using real semantic adapters,
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

### M02 branch proposals: review before contract freeze

The [open PR #9 protocol record at `24cc739`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/24cc739/docs/pips/PIP-0003/milestones/M02-PROTOCOL.md)
pins BIP32, SLIP-0132, BIP49/84, BIP141/173 and Bitcoin Core sources. The
following is its **proposed** O01–O06 disposition, not executed/accepted
production behavior. Review it at M02 and challenge it through M03–M05.

- **O01:** one exact-index CKDpub attempt returns the child at that index or a
  distinguished invalid-child outcome. Outer orchestration retries the next
  public index at each path level using the same parent until a valid child,
  then derives the next requested level from that effective child. Return both
  requested and effective relative paths. HMAC-SHA512 takes the 32-byte parent
  chain code as key and compressed parent key plus four-byte big-endian index
  as message; split the 64-byte output. A left half at/above group order or
  resulting infinity is invalid. **Zero is valid**: retain the point but update
  chain code and child metadata. Only successful right halves become chain code.
  Parent fingerprint comes from the immediate effective parent's HASH160.
- **O02:** compare all four decoded version bytes, with explicit policy/network.
  Proposed initial allowlist is mainnet `xpub/0488b21e` P2PKH,
  `ypub/049d7cb2` P2SH-P2WPKH, `zpub/04b24746` P2WPKH; testnet
  `tpub/043587cf`, `upub/044a5262`, `vpub/045f1cf6` in the same order.
  Reject private, unknown, multisignature and cross-network/policy intent.
  This is initial admission policy, not a cryptographic property of BIP32.
  Proposed address definitions use P2PKH/P2SH bytes `00/05`, HRP `bc` on
  mainnet; `6f/c4`, HRP `tb` on testnet. `testnet` names address parameters,
  not full chain identity. Do not infer account path/depth from SLIP examples.
- **O03–O04:** proposed public request takes serialized extended public key,
  array of integer relative child indices, validated network definition and
  explicit policy. Result contains address, network, policy, requested and
  effective paths. Empty path addresses imported node. Admit indices
  `0..2^31−1`, length no greater than `255 − imported depth`, on a 64-bit PHP
  host. Permit at most 256 candidate attempts per level, with distinct budget
  and public-range exhaustion. The budget is operational, not a BIP32 constant.
- **O05–O06:** proposed immutable definition holds identifier, one-byte address
  versions, lowercase HRP and four-byte public-version/policy map; reject
  missing/extra/duplicate/conflicting/cross-network fields. Proposed failures
  distinguish malformed input, unsupported or incompatible intent, bad
  definition, depth/range/budget exhaustion and provider/environment failure.
  Validate definition/request shape, checked decode and structure,
  compatibility, then derivation/encoding. No partial address or diagnostics
  containing keys, chain codes or full wallet paths. Final PHP failure form
  and physical definition representation remain M05 choices.

The 256-attempt choice, 64-bit requirement and narrow version-policy allowlist
need consuming-use review before public contract freeze. A merge of PR #9 would
record the decision proposal and probes; it would not make Q tests pass.

**O07 candidate position (dated research, no production selection):**

| Delegated need | Candidate evidence and remaining gate |
|---|---|
| D01–D03 and D11 | PHP `hash`, `hash_hmac`, `pack` and `unpack` have bounded PHP 8.1/8.3 probes. Complete binary/length, long-HMAC, argument-order and width/endianness conformance is pending. |
| D04–D05 ECC | Paragonie ECC 2.6.0 and Simplito elliptic-php 1.0.12 have different raw APIs and bounded PHP 8.1+GMP probes. Both require strict adapter scalar admission; exact point corpus, unchanged CKDpub consumer, dependency graph and independent mathematical reference remain pending. Native libsecp256k1 is a reference lead, not a selected PHP backend. |
| D06–D07 Base58Check | Tuupola Base58 2.2.0 has bounded checked-key/address results, but its checked API uses a one-byte option while extended-key versions are four bytes. StephenHill Base58 2.1.0 was compared for **raw** Base58 only; it is not a second qualified Base58Check codec. A complete second checked-codec candidate remains open. |
| D08–D09 SegWit | Focused BitWasp Bech32 0.0.1 has bounded BIP173 probes and a maintenance question. cjpgdk Bech32 0.1.2 is a second lead probed in PR #9; explicit HRP/v0 length/checksum semantics need adapter tests. Both need complete comparison, actual dependency paths and qualification. |
| D10 structural decode | Runtime fixed-width conversion plus Primitives-owned exact structure/admission is the research lead. No bespoke standardized decoder or broad Bitcoin package is selected. |

Cardano PHP Bech32 1.1.0 and uma/secp256k1-php 0.1.1 declare PHP 8.2+,
so they do not directly satisfy the PHP 8.1 baseline. The older BitWasp
secp256k1 PHP binding declares PHP 7 only. Furqan Siddiqui Base58Check remains
a footprint/API research lead, not a qualified fallback. Version/requirement
observations are from the dated [PR #9 record](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/24cc739/docs/pips/PIP-0003/milestones/M02-PROTOCOL.md)
and [provider research](../../research/public-address-provider-research.md);
recheck before M05 selection. An empty user-candidate worksheet cell neither
blocks independent search nor grants selection approval.

### R/Q responsibility and acceptance map

The [assessed M01 draft](milestones/M01-CONTRACTS.md) supplies precise R01–R12,
Q01–Q12 and O01–O07 identities. No row mandates its own class or package.

| Requirement | Owned semantics and delegated operations | Required qualification |
|---|---|---|
| R01–R02 | Public request/result; immutable validated network definitions; D11 conversions as needed. | Q01 all six combinations, standalone use and direct-composition parity; Q02 two valid definitions, missing/extra/invalid fields and non-mutation. |
| R03–R04 | Exact extended-public-key structure, metadata/version/material admission and bounded relative path; delegate D04, D06–D07, D10–D11. | Q03 valid/invalid BIP32 public fixtures, private/unknown/mismatched versions, checksum, exact length, trailing data, root metadata and point validity; Q04 empty path, index/depth/resource bounds and imported hardened ancestry. |
| R05–R07 | CKDpub composition, child metadata, valid compressed key and public tweak; delegate D03–D05/D11, D01–D02 for fingerprint. | Q05 official public edges, invalid first/intermediate/last candidate, zero and exhaustion; Q06 exact 33 bytes, prefix and curve validity; Q07 tweak 0, 1, n−1, n+, wrong width, infinity and replacement through real ECC adapters. |
| R08 | HASH160 and BIP32 message construction over delegated SHA-256, RIPEMD-160, HMAC-SHA512 and fixed-width conversion. | Q08 sourced known answers, empty/binary inputs, long HMAC keys, argument order, exact lengths and unsigned/endian extremes. |
| R09–R11 | P2PKH key HASH160 + version; P2SH-P2WPKH HASH160 of exact witness-v0 redeem script `0014` + 20-byte key hash + P2SH version; P2WPKH v0/20-byte witness program + HRP. Delegate D06–D09 codecs. | Q09 P2PKH, leading zeros/alphabet/checksum/length; Q10 exact redeem-script preimage, distinct key/redeem hashes; Q11 BIP173 valid/invalid, case, HRP, checksum, padding, v0 size and incompatible policies. |
| R12 | Stable failure meanings, bounded work, explicit wiring and vendor containment. | Q12 missing runtime facilities, malformed provider outputs, vendor exceptions, no fallback and safe diagnostics. |

The [delegation inventory](../../research/delegation-inventory.md) identifies
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
after retries. Investigate disagreement rather than voting. The
[BitWasp investigation](../../reviews/bitwasp-public-derivation/README.md)
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
independence. The [provider research](../../research/public-address-provider-research.md),
[candidate worksheet](../../research/public-address-candidate-worksheet.md),
[composition proposal](../../research/public-address-composition-proposal.md) and
[pinned probe pack](../../research/provider-refresh-2026-10-02/README.md) are
investigation inputs, not frozen implementations.

### Runtime and Counter-Proof execution

Run the repository's `composer ci` workflow on PHP 8.1 and 8.3 for M06–M08;
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
| [Independent plan-alignment review](../../reviews/plan-first-capability-canonical-alignment.md), [2026-10-03](cp-executor-2026-10-03-171445.md) and [2026-10-04](cp-executor-2026-10-04-011735.md) executor reports | Plan/research and package-document assessment at stated revisions, not provider or capability review. |
| [M01 draft](milestones/M01-CONTRACTS.md), [handoff](milestones/M01.md), [diagrams](milestones/diagrams/README.md), [issued M01 report](cp-executor-2026-10-06-210959.md) | R/Q/O map and draft boundary self-challenge at assessed SHA-256 `0224c493f2b507408f35207d6d0b5577e7206611be76644cfde17794414e5d41`. CP-07/10/11 remain unresolved; no runtime or independent verdict. |
| [Merged PR #5](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/5), [merged PR #6](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/6), [Issue decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555) | Proposal/plan separation, removal of obsolete top-level navigation, and present-day approval of pinned proposal revision. No runtime verification. |
| [Open PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9) at `24cc739` | Proposed M02 O01–O06 dispositions and bounded probe artifacts. No merge, O07 closure, adapter qualification, final selection or production tests. |

## Risks, gaps and decisions requiring judgment

- Maintainers should review PR #9's six-version/policy allowlist, 256-attempt
  operational budget, 64-bit host requirement, testnet label and public failure
  meanings before M05 freezes a contract. Any actual material departure from
  the approved proposal/Canonical needs the current Materiality Gate.
- Complete checked-Base58Check and SegWit alternatives, independent ECC oracle,
  Composer/runtime graph, license/security/maintenance evidence and real
  replacement exercise are missing. If two suitable libraries cannot qualify,
  document the affected plan-level gate and an explicit tradeoff; do not claim
  an unexercised alternative passed.
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
an assessed artifact with an issued hash; its handoff and diagrams explain
completed work. The [execution context](EXECUTION-CONTEXT.md),
[report templates](COUNTER-PROOF-TEMPLATES.md), [handoff template](MILESTONE-HANDOFF-TEMPLATE.md),
[snapshots](snapshots/README.md), [2026-10-03 checksum manifest](SHA256SUMS-2026-10-03-milestones),
and [2026-10-04 manifest](SHA256SUMS-2026-10-04-execution-package) are historical
package evidence, not active instructions. Git preserves the already-retired
top-level `docs/PLAN-FIRST-CAPABILITY.md` after PR #6. If PR #9 merges, its
M02 protocol file remains decision/probe evidence; execution direction stays here.

No additional PIP-0003 file is retired in this reconciliation. Selective
deletion would remove independently assessed or checksum-covered historical
records; [PIP Specification v1.0 §9](../../specifications/paycrypto-pip-specification-v1.0.md#9-history-supersession-and-archival)
protects issued records and reserves workspace archival for a closed Issue as
an atomic operation. The proposal and this plan are the two active governance/
operational documents; other files have evidence roles, not competing plan
authority. Old references naming `PIP-0003.md` as a plan refer to its assessed
Git revision, never the current proposal. Continue from this plan and the
applicable authorities, consulting historical files only to verify or reproduce
a claim.
