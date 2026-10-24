# PIP-0003 — Public Address Derivation Execution Plan

This is the current HOW, ORDER, and EXECUTION reference for
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3).
[M05 execution Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41)
coordinates provider, consumer runtime and concrete PHP contract selection.

## Current state

Checked 2026-10-22. The [M05 resumption checkpoint](#m05-resumption-checkpoint)
records the recovered decisions and repeated evidence. The [M05 assessment](M05/README.md)
adds current suitability research and three isolated graphs tested on PHP 8.2–8.5;
the [PHP contract proposal](M05/PHP-CONTRACTS.md) adds explicit signatures,
immutable provisioning and failure choices. The joint design discussion agreed
[inspectable composition data, semantic connections and failure abort](M05/PHP-CONTRACTS.md#agreed-composition-direction);
the proposal maps the three policy flows and assigns schema/abort acceptance
cases to M06/M07. The [preliminary schema/execution design](M05/PHP-CONTRACTS.md#preliminary-schema-and-execution-proposal)
now supplies C1–C6 choices, source grammar, complete flow/binding tables and
semantic admission/execution rules, explicitly advanced before joint discussion.
The [symbolic terminal-junction probe](M05/PHP-CONTRACTS.md#synthetic-terminal-junction-comparison--c1-decision-evidence)
compares named-slot and direct-step binding under the same six-intent and abort
cases. Joint C1 review selected direct binding at the delivered Bitcoin
`encode` step; the probe adds no address qualification or production loader.
The remaining choices are unaccepted and unimplemented; provider/runtime selection,
affected feasibility/contract qualification and the freeze remain open.
Development/setup/CI/release image pins were refreshed to PHP 8.3.36/8.4.27
for the 2026-10-21 security fixes;
the configured minor-version floor and matrix remain 8.3 and 8.3/8.4.

**Goal:** provide a locally usable, public-only operation that derives Bitcoin
P2PKH, P2SH-P2WPKH and P2WPKH addresses from an extended public key, a bounded
relative path, an explicit network and an address policy.

**Product status:** no capability implementation or behavioral verification
exists on `main`. At the resumption baseline `f525f14`, `src/` contains only `.gitkeep`,
`composer.json` has no production provider, and `tests/SetupTest.php` verifies
autoload registration only. Documentation and research progress must not be
read as a working library feature.

| Stage | What has actually been delivered | Verification and remaining gate |
|---|---|---|
| M00 — recorded | Early scope, planning, provider research and reference probes. | The [second review](M00/cp-reviewer-2026-10-17-141300.md) confirms the planning baseline; the [Issue #17 reproduction](M00/issue-17-runtime-reproduction-2026-10-17.md) confirms the historical provider outputs byte for byte and BitWasp's behavioral results with documented generated-file differences. The [Issue #28 deep review](M00/reviews/issue-28-deep-review.md) identifies a low-severity Bech32 observation-check and wording gap; [PR #34](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/34) carries the focused correction. The [independent check of `0eb8cbe`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255021386) found no remaining defect; the [shared Issue #28 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255212773) accepts a bounded favorable M00 conclusion. Feasibility evidence only; no implementation qualification. |
| M01 — delivered, independently reviewed and deep re-reviewed | [Contract and qualification draft](M01/M01-CONTRACTS.md), seven open questions and [diagrams](M01/diagrams/README.md); [review merged in PR #13](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/13) at [`9d04519`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/9d04519d809a5f6a7cff7544cd411850dee76814). | The [first review](M01/cp-reviewer-2026-10-17-135005.md) accepted the bounded draft without rework; the [second review](M01/cp-reviewer-2026-10-17-141301.md) and [Issue #15 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/15#issuecomment-6237800690) clarify CP states for that scope. The [Issue #29 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/29#issuecomment-6254856342) examined the draft and diagrams at `de326cf` and reported no confirmed M01 defect. Executed provider replaceability, protocol correctness and independent behavioral evidence remain obligations for later milestones, not defects in the accepted M01 draft. |
| M02 — merged; [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) correction accepted and merged | [Merged PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9) records accepted O01–O06 protocol and boundary decisions, the O07 candidate/qualification map, and the PHP 8.3 minimum decision while retaining the generic relative-path contract. It includes historical PHP 8.1 Composer and bounded probe evidence. [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) clarifies public-version versus point-validation precedence and repairs research-probe invariants and evidence wording. | The additional entry point is approved separately in [PIP00010](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/10). M03 has completed bounded PHP 8.3/8.4 research qualification; M04's older accepted review does not cover the corrected probes. The [maintainer acceptance](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) covers the reviewed correction at `fdb0a616`; [PR #35 merged at `56d9116`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/56d9116ab41104486a24c208724f643af3a46ab9). This acceptance covers the bounded M02 correction, not provider selection or a concrete PHP type. |
| M03 — PR #12 and Issue #31 correction merged; bounded independent checks recorded | The [ECC, unchanged CKDpub, native-false and runtime-hash probes](M03/README.md) exercise two Composer-installed PHP adapters, a pinned native libsecp256k1 reference and PHP facilities on PHP 8.3/8.4. Six sourced public-path payloads and forced exact-index, path/depth, metadata and fault cases agree across all six composition runs. Pinned BIP32 xpub text counter-checks those payloads and three published invalid public points through two Base58 decoders; all three ECC implementations agree on the expanded 13-point/nine-scalar/two-cross-point boundary corpus. A focused classifier distinguishes confirmed infinity from forced native `false` on finite sums; injected comparator and confirmation faults expose its same-library trust limit. Q08 runtime answers match NIST, RIPEMD-160 author, RFC 4231 and BIP32 sources; controlled missing-facility, malformed-result, PHP parser-fault and shared-consumer negative controls add bounded Q12 evidence. [PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12) merged this bounded research handoff at [`7ecfce4`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7ecfce42e16e98819122d2ccc28ed2aa6a4bf959). | Q04/Q05 research path/depth and sourced-public-edge comparison is expanded; Q06/Q07 point/scalar boundaries have published BIP32 counter-proofs where available. Q08 runtime/conversion subset passes. The independent review returned the prior revision to execution for R1 (CP-07/CP-10); the [follow-up review](M03/cp-reviewer-2026-10-16-074200.md) passed the corrected research handoff at `2aa0194`. Provider selection and assembled qualification remain downstream. No provider is selected. The [Issue #31 correction](M03/README.md#issue-31-corrective-research--2026-10-19) at technical revision `85fdae149c803c713795eea7a1bb6ed4b9dc118e` [merged in PR #37 at `7696ba6`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7696ba66a7fb328bb435b6cb45319b7ab2db3795). The [separate independent check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050) accepted the bounded corrected code and evidence at `762ec02eb075668b133fa1d064a98a129a15b1eb`; status reconciliation followed the merge. |
| M04 — merged and independently reviewed in [PR #21](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21); correction merged in [PR #26](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/26), independently checked at `fcd71d5` under [Issue #25](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25) and [merged in PR #36 at `2d00923`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/2d00923ef4a8d6025a6292fb253f325c7f3cc302) | [Bounded M04 codec, admission and definition research](M04/README.md) records four pinned codec candidates, two-runtime results, full BIP49 HASH160/address counterchecks, mixed-invalid/fault controls and combined ECC/key/path composition against BIP49/BIP84/SLIP-0132. The [follow-up independent review of `6e8a512`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21#issuecomment-6243150128) accepted the executor's R1 correction and bounded R2 disposition; [PR #21 merged at `85dbc5d`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/85dbc5d2a01794ddeb085dd3026a36df0b1bb9cd). [PR #26 merged the corrected probe at `1c43efb`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/1c43efb4e98c1dac8c85c8cedc5c1c04bdab6511). | R1's reordered equivalent definition passes on both runtimes. The post-merge re-review found classification, assertion and encode/decode responsibility gaps in both terminal codecs; the independent Issue #25 check reproduced PR #26's head `33a3294` but found a qualification-only cjpgdk decoder exception-classification gap. PR #36 corrects that research adapter; its code and evidence at `fcd71d5` passed the bounded independent Issue #25 check. The earlier BitWasp selection block was overbroad for address generation: its native decode exception remains ambiguous only for a decoder boundary. Base58Check input decoding remains required for key admission. Plausible wrong encoder output is caught only in qualification. [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) further corrects the Signet counter-proof, point-admission exception classification and definition invariant; the [maintainer acceptance of `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) applies to this bounded correction, separately from the Issue #25 check. No production provider or API is selected. |
| M05 — in progress; M06–M09 — pending | [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41), the [resumption checkpoint](#m05-resumption-checkpoint) and [M05 assessment](M05/README.md) consolidate accepted decisions, current suitability and three candidate graphs. [PR #44](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/44) carries 84 bounded commands on PHP 8.2–8.5, four language controls and a [concrete PHP contract proposal](M05/PHP-CONTRACTS.md), plus the earlier baseline reproduction. | Joint provider/runtime selection, consumer deployment evidence and concrete contracts remain open. C1 affirms declared execution order, inspectable connections, independent candidate flows, compatible shared resources and one common route with direct intent binding of the selected terminal at the delivered Bitcoin `encode` step. Concrete resource qualification, C2 source syntax and other unresolved C1–C6 choices stay open. Review the [choice table](M05/PHP-CONTRACTS.md#preliminary-choices-for-joint-discussion), trust limits and [synthetic CashAddr/Taproot fitness challenges](M05/PHP-CONTRACTS.md#exploratory-reuse-fitness-challenges--outside-pip-0003-delivery), then resolve feasibility questions before the joint freeze and M06 handoff. Schema and failure-abort acceptance cases are assigned to M06/M07; they are not executed evidence. No provider or minor-version baseline has changed; development patch pins are 8.3.36/8.4.27. |

The prior M00–M03 verdicts retain their exact bounded scopes. The [Issue #29 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/29#issuecomment-6254856342)
examined the M01 contract and diagrams at `de326cf` without confirming a draft
defect. The [Issue #28 deep review](M00/reviews/issue-28-deep-review.md)
records finding F01 and its focused correction in [PR #34](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/34).
The [independent check of `0eb8cbe`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255021386)
found no remaining defect, and the [shared disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255212773) accepts a
bounded favorable M00 conclusion. M00's recorded planning status is unchanged.
The M02 deep re-review in [Issue #30](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30)
produced [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35); the [maintainer acceptance of `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) records the reviewed correction. The follow-up status commit changes no probe, result or protocol semantics.
The [M03 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31)
found research-boundary gaps at `b07e608`; the technical correction at `85fdae1`
[merged in PR #37 at `7696ba6`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7696ba66a7fb328bb435b6cb45319b7ab2db3795).
The [separate check of `762ec02`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050)
accepted its bounded code and evidence. Its requested status-only update was
not made before merge, so the subsequent reconciliation records the actual
sequence. The [M04 corrective check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25)
accepted PR #36 code and evidence at `fcd71d5` within its bounded scope.

**Next step:** Continue M05 under [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41) using the [provider/runtime assessment](M05/README.md). Consider its proposed Paragonie/Tuupola/BitWasp graph and PHP 8.3 minimum against alternatives, current consumer constraints and explicit trust limits; review the [concrete PHP proposal](M05/PHP-CONTRACTS.md), continue joint review of the [partially agreed C1 and remaining preliminary C1–C6 choices](M05/PHP-CONTRACTS.md#preliminary-choices-for-joint-discussion) (including admission/body separation and owned-code failure classification), and complete affected feasibility/contract qualification before the joint selection and M06 handoff. The proposed 8.3/8.4/8.5 verification matrix has not replaced the configured 8.3/8.4 matrix. Provider, runtime and concrete contract decisions remain open. The [Issue #25 correction](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25#issuecomment-6279899487) was independently checked at `fcd71d5` and [merged in PR #36 at `2d00923`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/2d00923ef4a8d6025a6292fb253f325c7f3cc302). The independently checked PR #26 generation path does not call the SegWit decoder; the cjpgdk fault challenges decoder classification only. The [Issue #31 correction](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279697215) and its status reconciliation in [PR #38](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/38) are complete. M05 then assesses
candidate suitability and [consumer runtime compatibility](#m05-provider-and-consumer-runtime-selection) together, starting from the merged
[M04 research handoff](M04/README.md), assesses the encoder boundary and any
separately required decoder boundary, selects providers and freezes public
contracts from executed M03/M04 evidence; M06–M09 implement and verify the
assembled capability. No production provider or address API exists yet. O03 retains
the approved generic `relativePath` contract. Its separately approved [PIP00010 receiving entry
point at `badb9cd`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/badb9cd4a5f4579174c8c7121558e9b38d566349/docs/pips/PIP00010/PIP00010.md)
takes separate integer `account` and `index` fields, selects the account-level
key from an explicit immutable source, and supplies `[0, index]` to the generic
operation. Implementation and qualification remain. Generic qualification may
proceed under PIP-0003. Before M05 freezes contracts, review the testnet label
and concrete public exception types, including the M03 R2 diagnostics finding.
When evidence changes, update this status and the roadmap together with its
exact PR, commit or test reference.

## Reading path and authority

| To answer | Read | Role |
|---|---|---|
| What is happening and what follows? | This section, then the [Execution roadmap](#execution-roadmap). | The only current execution-status and ordering reference. |
| What was approved? | The [approved proposal revision at `72c4fa1`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md) and [Issue decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555). | Proposal defines WHAT, WHY and BOUNDARIES; the Issue records approval and coordination. |
| What follows the merged M01–M04 research handoffs? | The completed [M00 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255212773) and [M01 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/29#issuecomment-6254856342), the [maintainer acceptance of `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) for [M02](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30) and the completed [M03 corrected-code check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050), the [Issue #25 corrective check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25), [M04 research](M04/README.md), then the [M05 roadmap row](#execution-roadmap). | The M00/M01 conclusions, M02 maintainer acceptance, bounded M03 corrected-code check, and accepted Issue #25 correction merged in PR #36 are recorded; a separate joint M05 decision remains. |
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
| M00 — recorded | Establish PIP identity, scope, research and planning baseline. | Historical plan and plan-alignment review are recoverable; neither verifies implementation. [Issue #28](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28) records the focused Bech32 evidence correction. Its [independent check of `0eb8cbe`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255021386) found no remaining defect; the [shared disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255212773) accepts a bounded favorable M00 conclusion. |
| M01 — delivered, independently reviewed in PR #13 and deep re-reviewed in Issue #29 | Define R01–R12, Q01–Q12, O01–O07, D01–D11 mapping and diagrams. | The [first review](M01/cp-reviewer-2026-10-17-135005.md) accepted the bounded draft; the [second review](M01/cp-reviewer-2026-10-17-141301.md) and [Issue #15 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/15#issuecomment-6237800690) distinguish its scoped CP states from later operational obligations. The [Issue #29 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/29#issuecomment-6254856342) reported no confirmed draft defect at `de326cf`; no provider or behavioral capability was verified by M01. |
| M02 — merged in PR #9 | From M01, pin versioned BIP32, SLIP-132, network and codec sources; record accepted O01–O06 behavior and boundaries without replacing the generic contract; accept the O07 focused-provider/runtime candidate and qualification map by required operation, runtime, license, security, footprint and dependency path. | PIP00010 governs the additional receiving entry point. Qualify the accepted generic capability in M03/M04, with missing suitable providers explicit. Candidate eligibility is not selection. [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) merged the Issue #30 precedence and probe corrections at `56d9116`; the [maintainer acceptance of `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) covers head `fdb0a616`, and the subsequent status-only commit does not change research evidence. |
| M03 — merged and independently reviewed in PR #12 | After relevant M02 decisions, exercise two real ECC semantic adapters and one unchanged CKDpub consumer; establish independently implemented ECC reference and PHP 8.3/8.4 evidence. The bounded ECC/CKDpub, native-false and confirmation-fault, PHP parser-fault, shared-consumer negative-control and hash/conversion probes and six-package ECC Composer graph are recorded under [M03](M03/README.md). Research spike only. | Q04/Q05 has six sourced public cases and path/depth boundaries, including full depth capacity and no HMAC on rejected paths. Q06/Q07 adds three BIP32 invalid-public-key examples and controlled field/order/infinity cases across two PHP adapters and native libsecp256k1. Q08 runtime/conversion subset passes sourced known answers, binary/empty/long-key cases and unsigned extremes on both runtimes. Q12 has bounded missing-extension/algorithm, malformed-result, parser-error and shared-consumer challenges plus an ECC lineage assessment; controlled provider-fault and native common-mode challenges are recorded with explicit trust limits. The [follow-up review](M03/cp-reviewer-2026-10-16-074200.md) passes the corrected research handoff at `2aa0194`; [PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12) merged at [`7ecfce4`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7ecfce42e16e98819122d2ccc28ed2aa6a4bf959). R2 public exception sanitization remains required before M05/M06 public exposure. These probes do not select a provider or qualify an assembled capability. The [Issue #31 correction](M03/README.md#issue-31-corrective-research--2026-10-19) contains parser, hash and research-diagnostic controls at `85fdae1`; [its corrected head `762ec02` passed a separate bounded check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050) and [merged at `7696ba6`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7696ba66a7fb328bb435b6cb45319b7ab2db3795). |
| M04 — merged and independently reviewed in [PR #21](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21); correction merged in [PR #26](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/26), independently checked at `fcd71d5` under [Issue #25](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25) and [merged in PR #36 at `2d00923`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/2d00923ef4a8d6025a6292fb253f325c7f3cc302) | After relevant M02 decisions, qualify hashing, conversion, Base58Check, SegWit codecs, strict key admission and immutable data-only definitions. The [bounded research handoff](M04/README.md) checks two codec adapters per boundary, published BIP49/BIP84/SLIP-0132 and BIP173 answers, all six public version encodings, combined checked decoding/ECC/path/address composition, test-only Signet context, injected malformed Base58Check/SegWit results and missing BCMath on both runtimes. The [R1/R2 follow-up review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21#issuecomment-6243150128) accepted its reviewed research scope at `6e8a512`; [PR #21 merged at `85dbc5d`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/85dbc5d2a01794ddeb085dd3026a36df0b1bb9cd). [PR #26 merged the corrected probe at `1c43efb`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/1c43efb4e98c1dac8c85c8cedc5c1c04bdab6511). Key validity closure also draws on M03. | R1's reordered definition and R2's injected native BitWasp fault reproduced on both runtimes. Issue #25 adds controls for an injected domain exception, impossible native return, unknown policy precedence and exact outside-policy rejection. It removes self-decoding from both terminal generation adapters while retaining Base58Check input decoding for key admission. The former blanket BitWasp selection block was a false negative for its encoder, while the native exception ambiguity still blocks a decoder boundary. Plausible wrong output from either encoder is caught only in qualification. The independent Issue #25 check reproduced head `33a3294` separately from the `6e8a512` conclusion but found a qualification-only cjpgdk decoder exception-classification gap. PR #36 corrects it; the bounded independent check accepted its code/evidence at `fcd71d5`. [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) adds separate Issue #30 corrections to M04 research probes; the [maintainer acceptance of `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121) covers head `fdb0a616`. No production selection follows. |
| M05 — in progress: [baseline revalidated](#m05-resumption-checkpoint); selection open under [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41) | After M03/M04, the completed independent [M00](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28) and [M01](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/29) re-reviews, the [Issue #25 corrective review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25), the [accepted M02 correction](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121), and the [bounded M03 corrected-code check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050), jointly record selected versions, rejected alternatives, runtime/Composer graph, semantic contracts, errors, wiring and first branch. Select the consumer PHP minimum and verification matrix together with providers under the [M05 runtime assessment](#m05-provider-and-consumer-runtime-selection). Freeze the concrete generic and PIP00010 receiving-entry contracts within their approved boundaries, including the immutable account-key source and public failure meanings. | Start from the [M04 provisional codec triage](M04/README.md#provisional-codec-candidate-disposition-for-m05), then justify every selection or rejection against executed evidence. BitWasp remains blocked on the uninstrumented Q12 exception ambiguity only for a decoder boundary; all encoders remain conditional. The [M05 comparison](M05/README.md) now proposes BitWasp for generation and adds bounded Cardano generation evidence; this is an executor recommendation, not a joint selection. Expand the search if no safe adapter boundary qualifies. Document consumer reach, required runtime features, support and maintenance tradeoffs, and executed compatibility evidence for the chosen minimum and matrix. Requalify changed contracts, providers, dependencies or runtimes. The [concrete PHP proposal](M05/PHP-CONTRACTS.md) supplies signatures, eager duplicate-safe provisioning, owned failure reasons and the agreed composition direction with three policy flows and schema/abort obligations. The [preliminary schema/execution section](M05/PHP-CONTRACTS.md#preliminary-schema-and-execution-proposal) supplies ordered declarations, exact source grammar, policy/receiving flows and explicit intent bindings; C1 has partial joint agreement on ordered connections and independent protocol flows; its remaining details, C2–C6 and earlier PHP choices require joint review and affected feasibility qualification. The receiving contract is proposed, not implemented or verified. |
| M06 — pending | After M05, implement one complete public request-to-address path on both networks, with admission, derivation, adapters, validated definitions and local example. | Independently justified outputs and negative cases pass on PHP 8.3/8.4; no vendor or higher-domain leakage. Record unsupported policies. |
| M07 — pending | After M06, add remaining generic policies and shared lower capabilities; implement PIP00010's separate receiving-only account/index entry point using an explicitly provisioned, immutable, public-key-only account-key source and the generic operation. Assess Bitcoin Cash reuse only as design fitness. | Six generic policy/network combinations, direct-composition parity, distinct terminal semantics, failures and provider replacement pass. For receiving, select exactly one account-level key by network, policy and account; reject missing, duplicate, malformed or incompatible mappings and cross-network/policy/account substitution. Validate separate non-hardened integer account/index inputs, forbid caller branch selection, always pass `[0, index]`, reject insufficient key depth and exact-index invalid children without advancing, and return address plus logical account/branch/index coordinates. Compare success and failure with direct generic calls using that key and path on PHP 8.3/8.4. No CashAddr implementation. |
| M08 — pending | After M07, verify the assembled generic and PIP00010 receiving candidate, with exact source/dependency/definition/runtime revisions and requirement-to-evidence map. | Run `composer ci` on PHP 8.3/8.4 plus conformance/differential suites; challenge all applicable CPs against the actual artifact. Verify receiving mapping isolation, fixed branch, depth/index failures, result coordinates and direct generic-call parity on both runtimes. Unresolved applicable CPs prevent readiness claim. |
| M09 — pending | After M08, independently challenge both assembled entry points and correct findings on new revisions. | Reviewer identifies exact artifact/evidence and independently checks PIP00010 positive and negative cases, source immutability, network/policy/account isolation and generic-call parity on PHP 8.3/8.4. No applicable CP remains failed or unresolved before compliance claim. Preserve issued reports. |

M03 and M04 overlapped only where their M02 inputs were settled; M04 key-admission
closure drew on M03 validity evidence. One branch at M06 is an incomplete
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
The three delivered Bitcoin policies share behavior through key HASH160 before
their distinct terminal semantics. Other protocol candidates retain independent
flow declarations and may each reference compatible reusable resources at any
position. Network variation is validated data, never
executable configuration. No implicit account path, depth, policy or network
conversion comes from the reference application.

The M02 review on 2026-10-12 raised the supported consumer minimum to PHP 8.3,
with PHP 8.3/8.4 verification. This remains the configured baseline while
[M05 reassesses consumer compatibility and provider selection together](#m05-provider-and-consumer-runtime-selection). The earlier PHP 8.1 trials remain
historical feasibility evidence, not support claims. Other delivery choices retained here
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
  representation. After a valid checked 78-byte envelope and common metadata,
  an unsupported public version is rejected before point interpretation;
  recognized public versions still require point validation before intent.
  The [Issue #30 clarification](M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility)
  fixes the mixed unknown-version/bad-point outcome as `unsupported_version`.
  Legacy BIP49 xpub/tpub nested-SegWit exports are excluded
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
  where applicable, checked decode, 78-byte length and common metadata,
  public-version eligibility, recognized-version point validity, compatibility
  and depth, then derivation/encoding; the first failing stage determines the
  category. An unknown requested policy fails during request validation.
  No account, key or provider fallback, partial address or diagnostics exposing
  keys, chain codes, full wallet paths or raw vendor details. M05 sets concrete
  public exception classes and signatures, not a second public error-result
  channel. Q12 checks that documented provider failure returns become owned
  public exceptions.

The maintainer raised Signet as an O05/Q02 extension challenge during the O02
review. [Bitcoin Core's pinned chain parameters](https://github.com/bitcoin/bitcoin/blob/228877d7836a55c1d6fde90b0b7ba2d4c90248ab/src/kernel/chainparams.cpp)
give Testnet3, Testnet4 and Signet identical address prefixes, `tb` HRP and
test extended-public-key version. M04's bounded probe showed that a test-only,
separately identified Signet profile can reuse validated address parameters and
the same composition without assuming encodings uniquely identify a chain. The
same key/path/policy yielded the same address text under either profile, while
the result identified only caller-supplied validated context. The initial
public allowlist rejected an explicitly Signet-labeled definition; the
test-only expanded registry rejected a malformed Signet definition.
See the [M02 counter-proof](M02/M02-PROTOCOL.md) for the full cases. The Signet
challenge is a consensus parameter outside this address-encoding definition.

The accepted exact-path, strict version-policy, bounded-path and data-only
definition/composition choices have bounded consuming-use and adapter evidence
from M03/M04. PR #9 recorded the O01–O07 M02 dispositions; it did not make
Q tests pass or select a provider. Assembled qualification remains pending.
The assessed M01 Q05 draft contains advancing-path cases, and its Q04 leaves
the empty-path disposition open. M03 exercised bounded exact-index and
empty-path cases under the revised O01/O04 decisions in the M02 record; M04 used
those expectations in bounded research, and assembled qualification must retain them.

**O07 accepted investigation map (2026-10-14; dated research, no production selection):**

| Delegated need | Candidate evidence and remaining gate |
|---|---|
| D01–D03 and D11 | PHP `hash`, `hash_hmac`, `pack` and `unpack` passed the [M03 Q08 runtime/conversion probe](M03/README.md#q08-runtime-hashconversion-and-q12-facility-challenge) on PHP 8.3/8.4 against sourced NIST, RIPEMD-160 author, RFC 4231 and BIP32 answers, including empty/binary/long-key inputs, exact lengths, argument order and unsigned/endian extremes. The qualification is bounded to the tested 64-bit runtimes; [M04's reviewed codec/composition research](M04/README.md) extends that evidence without selecting a provider. The [Issue #31 hash-fault record](M03/hash-fault-comparison.json) adds guarded two-stage HASH160, disabled-function and wrong-result controls at `85fdae1`; its [bounded independent check of `762ec02`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050) is recorded. |
| D04–D05 ECC | Paragonie ECC 2.6.0 and Simplito elliptic-php 1.0.12 have different raw APIs and bounded historical PHP 8.1+GMP probes. Current [M03 evidence](M03/README.md) runs both against the same CKDpub consumer and a source-pinned uma/secp256k1-php 0.1.2/libsecp256k1 reference on PHP 8.3/8.4. Six sourced public derivations, three published BIP32 malformed points and controlled point/scalar boundaries agree; a focused native classifier confirms infinity before mapping `false` to invalid child and contains injected comparator faults. A raw-result check confirms both PHP providers return point objects for finite and infinity sums; `null` is the adapter's documented mapping after `isInfinity()`, and a synthetic adapter that lies about it is a contract breach rather than a provider bug. A focused parser-fault challenge now separates pinned off-curve signals from unrelated faults in both PHP research adapters; an identical mimicked signal remains indistinguishable. A controlled double-fault can still fool same-library native confirmation. Broader provider faults and suitability of the native confirmation in a public-only production adapter remain to review. The PHP candidates require strict adapter scalar admission. Actual dependency paths are in the M03 focused lock; no backend is selected. The [Issue #31 parser correction](M03/php-parser-fault-comparison.json) classifies failed validation after a successful Simplito parse as a provider fault; its [bounded independent check of `762ec02`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050) is recorded. |
| D06–D07 Base58Check | User-contributed leads StephenHill Base58 2.1.0 and Tuupola Base58 2.2.0 were already mapped: StephenHill is raw binary Base58 only; Tuupola's checked API uses a one-byte option while extended-key versions are four bytes. The third lead, mattallty/base58, is an integer/number codec without binary or checked support and uses PHP 8-incompatible string-offset syntax, so it cannot serve D06–D07 on PHP 8.3. [M04's reviewed semantic-adapter research](M04/README.md) exercised Tuupola and Muvon on both runtimes; Tuupola is the stronger conditional lead. The corrected M04 probe separates terminal encoding from the decoder required for extended-key admission. Its injected Base58Check encode faults now distinguish boundary failures from plausible wrong output rejected only in qualification. Muvon's undeclared BCMath use and empty-string invalid decode remain M05 suitability concerns. Neither package is selected. |
| D08–D09 SegWit | [M04's reviewed research](M04/README.md) exercised BitWasp Bech32 0.0.1 and cjpgdk Bech32 0.1.2 against BIP173/BIP84 and bounded fault cases on both runtimes. The earlier BitWasp selection block conflated its decoder with address generation. The corrected probe keeps both encoders conditional; BitWasp’s native exception ambiguity remains a decoder-only gate, and cjpgdk’s raw decoder admissions remain qualification limits. Valid-looking wrong encoder output is detectable only in qualification. The [M05 assessment](M05/README.md) proposes BitWasp for generation and adds bounded Cardano PHP Bech32 1.1.0 generation evidence on PHP 8.2–8.5, including admission and lineage limits; no provider is selected. |
| D10 structural decode | Runtime fixed-width conversion plus Primitives-owned exact structure/admission is the research lead. No bespoke standardized decoder or broad Bitcoin package is selected. |

Cardano PHP Bech32 1.1.0 requires PHP `^8.2`, and the user-supplied
uma/secp256k1-php 0.1.2 native binding requires PHP `>=8.2`; both meet the
configured PHP 8.3 version floor. [M05](M05/README.md) now records bounded
Cardano generation qualification; the native binding has the bounded M03
reference qualification described above, while
production suitability and selection remain open.
The older BitWasp secp256k1 binding has an unresolved PHP 8 build report.
The [M02 record](M02/M02-PROTOCOL.md) explains the baseline decision and
candidate limits. A historical Composer trial rejected Furqan Siddiqui
Base58Check 0.1.5 on PHP 8.1.34 because it requires `php-64bit: ^7.2`.
Version/requirement observations are from the dated [M02 record](M02/M02-PROTOCOL.md)
and [provider research](M00/research/public-address-provider-research.md);
the [M05 metadata and graph assessment](M05/README.md) supplies the current
refresh, which must be reconsidered if the selected revisions change. An empty user-candidate worksheet cell neither
blocks independent search nor grants selection approval.

### M05 provider and consumer runtime selection

**Planning agreement (2026-10-20):** choose the minimum consumer PHP version
jointly with the production providers. The M02 decision recorded runtime support
and candidate eligibility; M05 must also establish the adoption impact on intended
consumers. A newer runtime can expand candidate options and support lifetime,
while a higher minimum can exclude consumers who cannot yet upgrade. Neither
recency nor broader compatibility alone determines the choice.

For each viable provider/runtime combination, record:

- Intended consumers' deployed PHP versions and feasible upgrade constraints,
  with evidence provenance and unknowns stated explicitly.
- The minimum actually required by Primitives code, the complete resolved
  production dependency graph, extensions and runtime facilities. Distinguish
  mandatory requirements from research, development and independent-reference
  tooling requirements.
- Consumers included or excluded, required migration effort, runtime support
  horizon, relevant security and maintenance considerations, and the cost of
  sustaining compatibility. Explain the concrete benefit of requiring a newer
  version and the limitations or additional obligations of retaining an older one.
- Executed installation and relevant semantic/failure qualification on the
  proposed consumer minimum and supported verification matrix, using pinned
  provider and dependency revisions. Historical probes or Composer constraints
  alone do not establish support for that combination.

The M05 handoff must justify the selected providers, minimum supported PHP
version, verification matrix and installation requirements, with rejected
alternatives, remaining limits and any consumer assumptions explicit. Keep the
consumer minimum distinct from development/tooling runtimes and the verification
matrix; a newer research reference does not by itself raise the consumer minimum.
Missing consumer data remains an explicit decision limitation, not assumed
adoption of either older or newer PHP versions.

PHP 8.3 and the PHP 8.3/8.4 matrix remain effective until an evidence-backed joint
M05 decision retains or revises them. Any revision must synchronize Composer
requirements/platform configuration, runtime images, CI, installation/release
guidance and the downstream M06–M09 verification criteria before implementation
handoff. Preserve the M02 decision and M03/M04 results as historical evidence for
their actual runtimes; a new baseline requires its own qualification. This
assessment refines execution within the approved proposals' boundaries.

### M05 resumption checkpoint

The 2026-10-21 executor check recovered Issue #41 and its initial planning
commit `8bff92a` on `work/issue-41-pip3-m05`, then integrated upstream
`f525f14` without rewriting that commit. The initial branch changed only this
Plan and the docs index; it contained no provider selection, concrete API or
production implementation to retain. The M00–M04 technical files are unchanged
from upstream `f525f14`. Issues #25 and #28–#31 are closed with bounded
dispositions; their closure does not qualify production providers. The
[additional independent M02 check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278697622)
assessed the corrected source and saved results without rerunning PHP locally;
the reproduction below supplies a separate executor run, not a replacement
independent verdict.

Use the following decision dependencies when preparing the M05 selection.
The linked records own the detail; accepted semantics are not reopened by
older draft questions or by a provider's accidental behavior.

| Decision source | Retained boundary | M05 decision and downstream qualification |
|---|---|---|
| [M01 R/Q/D map](M01/M01-CONTRACTS.md), [M02 O01/O04](M02/M02-PROTOCOL.md) | Exact requested nonempty public path; zero tweak is valid; invalid children fail without advancement. M01's advancing-path questions and PHP 8.1/8.3 matrix are historical. | Choose concrete path/node and invalid-child representations; retain complete-node, depth and no-advance challenges in production tests. |
| [M02 O02/O06 correction](M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility) | Explicit network/policy and strict six-version allowlist; common envelope/metadata precede version eligibility, then recognized-version point validity, intent and depth. | Freeze signatures, validation stages and owned exception categories, including mixed-invalid requests and provider faults. |
| [M02 O05](M02/M02-PROTOCOL.md#o03o06--minimal-operation-boundary), [corrected M04 definitions](M04/README.md#issue-30-protocol-handoff-corrections) | Immutable validated data; equivalent map order is immaterial; no constructor bypass. Signet is a test-only data-variation challenge, not public support or authenticated chain identity. | Choose a duplicate-safe source format and a semantic construction boundary; compositions consume data without a network-identity branch. |
| [Approved PIP00010](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/badb9cd4a5f4579174c8c7121558e9b38d566349/docs/pips/PIP00010/PIP00010.md) | Separate receiving entry point, exact immutable network/policy/account mapping and generic `[0, index]` composition; provisioning owns ancestry binding. | Define source provisioning, validation timing, missing/invalid mapping failures and complete result coordinates; later verify generic-call parity and isolation. |
| [M03 correction](M03/README.md#issue-31-corrective-research--2026-10-19) | Research parser/HASH160 faults and raw exception chains were corrected. Distinct ECC adapters share some runtime and consumer code. | Define and verify the public diagnostic boundary, including exception chains; preserve independent complete-node anchors and distinguish confirmed infinity from provider malfunction. |
| [M04 corrected codec triage](M04/README.md#provisional-codec-candidate-disposition-for-m05) | Base58Check decoding is required for key admission. Terminal generation does not require SegWit decoding. Plausible wrong output is a qualification/trust limit, not guaranteed runtime detection. | Compare providers per required operation, including decoder classification, undeclared facilities, PHP diagnostics, maintenance, security and complete dependency graphs. Do not transfer a decoder-only rejection to an encoder. |
| [Consumer-runtime agreement](#m05-provider-and-consumer-runtime-selection) | PHP 8.3 and verification on 8.3/8.4 remain configured; consumer deployment/upgrade data is still unknown. | Assess consumer reach and actual production requirements jointly with providers. Development and independent-reference requirements do not set the consumer floor. |

**Repeated evidence.** Fresh isolated installs used the unchanged M02 combined,
M03 ECC and M04 codec locks. Their SHA-256 identities matched the existing
handoffs (`afba6c34…`, `c576fbbc…`, `8aeca13e…`). Using the exact M04 images
`9b8f022024147b4af8ca77908dd8730e9076c3fe9d2ee7a1452e0ca46ec3d366`
and `fb0c1c6b3ae2e83464805771ce13682523cfe1ec0d5cbdc2e74907f8f1fb2aa1`,
42 offline commands passed on PHP 8.3.35/8.4.26: platform checks for each
graph; M03 ECC and runtime hashes; both PHP CKDpub adapters in ordinary,
`--parser-faults`, `--hash-faults`, `--common-mode`, `--expect-disabled-hash`
and `--expect-disabled-hmac` modes; the two disabled-function runtime controls;
and M04 codec/admission composition. Reproduction mounts and commands remain
in [M03](M03/README.md#reproduction) and [M04](M04/README.md#reproduction);
disabled-function modes require their documented `-d disable_functions` setting.
The eight ordinary ECC/hash/codec/admission JSON files and PHP 8.4 codec
diagnostics matched byte for byte. All 16 PHP CKDpub ordinary/parser/hash/common-mode
results matched their entries in the existing comparison records.

Both M00 saved-observation checks passed; all 16 unchanged entries in its two
provider/reference manifests matched (excluding the later-edited READMEs as
documented). The original M01 draft at `96707dd` retained its issued
`0224c493…` hash. These are identity and bounded reproduction checks. This run
did not rebuild or rerun the native ECC reference, rerun M00's historical PHP
providers, refresh security/maintenance evidence, qualify another runtime or
exercise a public API. Existing historical and independent conclusions retain
their exact scopes. CP-07/CP-10/CP-11 for the assembled capability remain open.

The subsequent [M05 assessment](M05/README.md) adds refreshed suitability and
new graph/runtime evidence beyond this initial reproduction. The next gate is
the joint provider/runtime/contract decision, retaining rejected alternatives
and explicit trust limits. Neither this checkpoint nor that executor proposal
freezes the decision or hands execution to M06. Research dependencies remain
isolated from production Composer requirements.

### R/Q responsibility and acceptance map

The [assessed M01 draft](M01/M01-CONTRACTS.md) supplies precise R01–R12,
Q01–Q12 and O01–O07 identities. No row mandates its own class or package.

| Requirement | Owned semantics and delegated operations | Required qualification |
|---|---|---|
| R01–R02 | Public request/result; immutable validated network definitions; D11 conversions as needed. | Q01 all six combinations, standalone use and direct-composition parity; Q02 two valid initial definitions, missing/extra/invalid fields and non-mutation, plus the same composition with different admitted network data and a test-only Signet profile with duplicate testnet encoding parameters and a distinct context identifier. Unknown public profiles fail until admitted; parameter-only variation introduces no network-specific behavioral class. |
| R03–R04 | Exact extended-public-key structure, metadata/version/material admission and bounded relative path; delegate D04, D06–D07, D10–D11. | Q03 valid/invalid BIP32 public fixtures, private/unknown/mismatched versions, checksum, exact length, trailing data, root metadata and point validity; Q04 rejects empty path, accepts `[0]`, checks index/depth/resource bounds and imported hardened ancestry; receiving `(0, 0)` matches direct generic `[0, 0]`. |
| R05–R07 | CKDpub composition, child metadata, valid compressed key and public tweak; delegate D03–D05/D11, D01–D02 for fingerprint. | Q05 official public edges, invalid first/intermediate/last candidate, zero and exhaustion; Q06 exact 33 bytes, prefix and curve validity; Q07 tweak 0, 1, n−1, n+, wrong width, infinity and replacement through real ECC adapters. |
| R08 | HASH160 and BIP32 message construction over delegated SHA-256, RIPEMD-160, HMAC-SHA512 and fixed-width conversion. | Q08 sourced known answers, empty/binary inputs, long HMAC keys, argument order, exact lengths and unsigned/endian extremes. [M04 counterchecked](M04/README.md) both complete 20-byte BIP49 HASH160 values, the redeem preimage and SHA-256 → RIPEMD-160 order against the published source; M03 had checked only a four-byte fingerprint for that use. |
| R09–R11 | P2PKH key HASH160 + version; P2SH-P2WPKH HASH160 of exact witness-v0 redeem script `0014` + 20-byte key hash + P2SH version; P2WPKH v0/20-byte witness program + HRP. Delegate D06–D09 codecs. | Q09 P2PKH, leading zeros/alphabet/checksum/length and checked key-hash preimage; Q10 exact redeem-script preimage and its full 20-byte HASH160, distinct from the key hash; Q11 BIP173 valid/invalid, case, HRP, checksum, padding, v0 size and incompatible policies. |
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
| [Second M00 review](M00/cp-reviewer-2026-10-17-141300.md) and [Issue #17 runtime reproduction](M00/issue-17-runtime-reproduction-2026-10-17.md) | M00 remains a recorded planning baseline. Both historical provider outputs reproduced byte for byte; BitWasp's behavior matched under the recovered lock and source manifests, while five generated Composer-file inventory entries differed. Original BitWasp checkout and container identities were unavailable. No production provider or capability was qualified. |
| [Issue #28 deep review](M00/reviews/issue-28-deep-review.md), [focused Bech32 evidence check](M00/reviews/issue-28-bech32-evidence-check.py), [independent check](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255021386) and [PR #34](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/34) | The pinned M00 inputs and raw observations reproduced. F01 narrows the generic Bech32 observation claim to acceptance/rejection; the issued probe and outputs remain intact. The independent check found no remaining defect at `0eb8cbe`; the [shared disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/28#issuecomment-6255212773) accepts a bounded favorable M00 conclusion. |
| [M01 draft](M01/M01-CONTRACTS.md), [diagrams](M01/diagrams/README.md), [executor report](M01/cp-executor-2026-10-06-210959.md), [first](M01/cp-reviewer-2026-10-17-135005.md) and [second](M01/cp-reviewer-2026-10-17-141301.md) independent reviews, [Issue #15 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/15#issuecomment-6237800690), [merged PR #13](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/13) at [`9d04519`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/9d04519d809a5f6a7cff7544cd411850dee76814) | R/Q/O map and bounded draft accepted without rework. The executor report's SHA-256 `0224c493f2b507408f35207d6d0b5577e7206611be76644cfde17794414e5d41` identifies [the pre-relocation draft at `96707dd`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/96707dd/docs/pips/PIP-0003/milestones/M01-CONTRACTS.md), not the current file after link repair. The first review's CP-07/10/11 `FAIL` rows address hypothetical full-capability claims; the second review and Issue #15 clarify the bounded M01 states. Operational CP-07/10/11 obligations remain for later milestones; M01 supplies no runtime verdict. |
| [Merged PR #5](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/5), [merged PR #6](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/6), [Issue decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555) | Proposal/plan separation, removal of obsolete top-level navigation, and present-day approval of pinned proposal revision. No runtime verification. |
| [Merged PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9), with [M02 protocol record](M02/M02-PROTOCOL.md) | Accepted O01 exact-path, O02 strict admission, O03 account/index consumer use case, O04 bounded nonempty generic path, O05 data-only validated definitions and O06 public exception/failure rules, while retaining the generic relative-path contract; O07 candidate/qualification map, PHP 8.1 Composer graph and bounded probes. The additional receiving entry point is approved in PIP00010 but unimplemented. [PR #35](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/35) corrects precedence and research probes; the [maintainer accepted code/evidence head `fdb0a616`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/30#issuecomment-6278516121). No final provider selection or production tests. |
| [M03 research](M03/README.md), [independent follow-up review](M03/cp-reviewer-2026-10-16-074200.md), [merged PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12) at [`7ecfce4`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7ecfce42e16e98819122d2ccc28ed2aa6a4bf959) | Bounded PHP 8.3/8.4 ECC, unchanged CKDpub and runtime/conversion qualification passed the corrected research handoff. R2 public exception sanitization remains for M05/M06; no production provider, address API or assembled verification follows from these probes. [PR #37](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/37) carries the Issue #31 research correction at `85fdae1`, with a [separate bounded check of `762ec02`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31#issuecomment-6279576050) and [merge at `7696ba6`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7696ba66a7fb328bb435b6cb45319b7ab2db3795). |
| [M04 research](M04/README.md), [initial independent review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21#issuecomment-6240336419), [accepted follow-up review of `6e8a512`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21#issuecomment-6243150128), [merged PR #21 at `85dbc5d`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/85dbc5d2a01794ddeb085dd3026a36df0b1bb9cd), [merged corrective PR #26 at `1c43efb`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/1c43efb4e98c1dac8c85c8cedc5c1c04bdab6511) | Bounded PHP 8.3/8.4 codec, admission, definition and address-composition evidence passed follow-up review at `6e8a512` and merged in PR #21. R1's equivalent-definition order defect is corrected. R2's injected native exception is classified by known provenance. The later Issue #25 review found terminal self-decoding in both codecs: the blanket BitWasp selection block was overbroad for SegWit generation, while Base58Check decoding is still required at key admission. The independent Issue #25 check reproduced PR #26 head `33a3294` but found a qualification-only cjpgdk decoder exception-classification gap. PR #36 corrects the research decoder; the bounded independent Issue #25 check accepted its code/evidence at `fcd71d5`. Native BitWasp exception ambiguity remains for a decoder boundary. The candidate triage is provisional; no production provider, address API or assembled verification follows from these probes. |

## Risks, gaps and decisions requiring judgment

- Maintainers accepted PR #9's exact-path derivation behavior and strict
  six-version/policy allowlist, including initial rejection of legacy BIP49
  xpub/tpub nested-SegWit requests. M03 has checked exact CKDpub behavior,
  including empty-path rejection, against its bounded sourced and controlled
  corpus; M04 has now added bounded version/policy and codec admission evidence.
  Review the testnet label and concrete public exception types before M05 freezes
  a contract. Any material departure from the approved proposal/Canonical needs the current
  Materiality Gate.
- Two checked-Base58Check and two SegWit adapters have bounded M04 evidence,
  but no codec provider is selected. Tuupola is a conditional Base58Check lead;
  Muvon has an undeclared BCMath requirement and empty-result classification
  concern. Both terminal codecs now keep output decoding in qualification;
  Base58Check still decodes the input key at admission. Both SegWit encoders
  remain conditional; the [M05 assessment](M05/README.md) now recommends
  BitWasp generation provisionally and records Cardano generation evidence.
  BitWasp’s uninstrumented Q12
  exception ambiguity concerns decoding, and cjpgdk has PHP 8.4/8.5 diagnostics.
  Plausible wrong encoder output is found only by qualification. A bounded
  independent ECC reference, PHP 8.3/8.4 focused Composer graph and real PHP
  ECC replacement exercise exist in M03.
  The [current suitability assessment](M05/README.md) and 84-command candidate
  graph matrix inform the joint M05 decision; production replacement
  qualification remains downstream. If no safe SegWit boundary qualifies, expand
  the search and record the affected plan-level gate and tradeoff.
- The bounded M01 draft was independently accepted; its first review's
  CP-07/10/11 `FAIL` rows concern hypothetical assembled-capability claims,
  as clarified by the [second review](M01/cp-reviewer-2026-10-17-141301.md)
  and [Issue #15 disposition](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/15#issuecomment-6237800690).
  Executed provider containment, complete protocol correctness and independent
  behavioral verification remain open for the assembled capability. PR #9's
  source interpretation has not passed the full Q corpus or independent
  implementation review. Forced rare outcomes need labels distinct from
  observed behavior.
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
These are relocations for scope/navigation, not a new assessment. The exact approved
proposal remains preserved at pinned revision `72c4fa1`; the current reader
copy has navigation-only repairs and links to that immutable decision reference.
Its historical relative links resolve at the approved revision. This plan supplies current
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
