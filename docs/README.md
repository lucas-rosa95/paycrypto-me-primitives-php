# PayCrypto.Me Primitives — Documentation Map

This file is a navigation map for the repository documentation. It is **not**
an architecture canonical and does not create architectural authority.

## Repository operations

[GitLab recovery mirror](operations/gitlab-mirror.md) documents synchronization
policy, activation, verification and recovery. Live operation must be confirmed
by a successful Actions run and remote-reference verification.

## Current structure

```text
README.md
docs/
├── canonicals/
│   └── primitives/
│       └── paycrypto-primitives-canonical-architecture-reference-v1.8.md
├── specifications/
│   └── paycrypto-pip-specification-v1.0.md
├── protocols/
│   └── primitives/
│       └── COUNTER-PROOF-REPORT-v1.5.md
├── pips/
│   ├── PIP-0003/
│   │   ├── PIP-0003.md              # capability proposal and boundaries
│   │   ├── EXECUTION-PLAN.md        # current execution reference
│   │   ├── M00/                     # early research, reviews and planning evidence
│   │   ├── M01/                     # contract draft, report and diagrams
│   │   ├── M02/                     # accepted protocol record and provider research
│   │   ├── M03/                     # completed, independently reviewed research handoff
│   │   ├── M04/                     # reviewed research; Issue #25 correction merged in PR #36
│   │   ├── M05/                     # current provider/runtime assessment; joint selection open
│   │   └── reviews/                 # pre-M04 execution audit
│   ├── PIP00010/                    # approved receiving-entry proposal
│   └── PIPxxxxx/                    # Issue-derived identity for a new governed PIP

archives/                              # ignored, local to the archiving checkout
└── pips/
    ├── PIP-0001/
    └── PIP-0002/

tools/
└── skills/
    ├── domain-canonical-engineering/
    └── canonical-document-governance/
```

## Authority and purpose

This map points to the owning records; it does not repeat their decision history.

| Question | Owning record | Current scope |
|---|---|---|
| Approved capability and boundaries | [PIP-0003 at its approved revision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md) and [receiving PIP00010](pips/PIP00010/PIP00010.md) | Public-only generic address derivation and the separately approved receiving entry point. |
| Execution, reviewed milestones and next gate | [Execution Plan](pips/PIP-0003/EXECUTION-PLAN.md#current-state) | M00–M04 bounded research/reviews are recorded; M05 is in progress. M06–M09 implementation/assembled verification remain downstream. |
| Accepted protocol semantics | [M02 O01–O06](pips/PIP-0003/M02/M02-PROTOCOL.md) | Exact path without index advancement, strict initial admission, complete nodes, receiving `[0, index]` and owned failures. |
| Composition decisions and PHP proposals | [M05 contract record](pips/PIP-0003/M05/PHP-CONTRACTS.md#reading-and-decision-status) and [C2 authoring laboratory](pips/PIP-0003/M05/PHP-CONTRACTS.md#c2-authoring-laboratory--exploratory-findings) | **C1 agreed and merged; C2 investigation active, decisions open.** Three source candidates and scoped-binding experiments have bounded structural evidence. Next: joint fixed-record/ordered-collection discussion. C3–C6, public PHP contracts and qualification remain open. |
| Provider/runtime comparison and reproduction | [M05 assessment](pips/PIP-0003/M05/README.md) | Three pinned graphs, 84 bounded graph commands and four language controls; provider/runtime selection and consumer constraints remain open. |
| Visual explanations and fitness limits | [M05 visual index](pips/PIP-0003/M05/PHP-CONTRACTS.md#visual-counter-proofs-for-joint-review) and [fitness evidence](pips/PIP-0003/M05/PHP-CONTRACTS.md#exploratory-reuse-fitness-challenges--outside-pip-0003-delivery) | Lifecycle, admission, policy flows and compatible reuse. Symbolic probes do not establish complete future Taproot/CashAddr support. |

[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3)
coordinates the capability. C1 [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41)
concluded with [PR #44](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/44)
at [`ee542e9`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/ee542e905b1b83f662356091bb2b4e59254186f5).
[Issue #45](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/45)
coordinates C2 and links its own work branch and PR; subsequent steps are tracked separately.
The Plan links exact approval, merge, corrective-review and resumption evidence.
Historical M01 contracts/diagrams and issued reports remain revision-specific
inputs rather than current implementation or provider-selection claims.

The [earlier plan review](pips/PIP-0003/M00/reviews/plan-first-capability-canonical-alignment.md)
and [pre-M04 audit](pips/PIP-0003/reviews/pre-m04-audit-2026-10-17.md) retain their
historical scopes. The latter's forward-looking criteria are read with the
merged M04 handoff and subsequent corrections linked in the Plan.

## Drafting and maintaining documentation

Write new milestone and research documents in English. Put detailed evidence in
the document that owns it, and point the Issue, Execution Plan and documentation
map to that record. During a draft, check the links from the new page and the
pages that lead readers to it. When moving a file, repair its incoming links in
the same PR; use a revision-pinned link for an issued historical artifact whose
original path must remain recoverable.

Before requesting milestone review, reconcile the Plan's current state and
roadmap, the affected Mxx handoff, this map, the originating Issue body and the
PR description. State the reviewed revision, what the evidence establishes, its
limits and the next gate consistently. Repeat that focused check when a review
changes the conclusion or a PR merges. Keep the exact approved proposal revision
as the decision reference; navigation repairs in a current reader copy must not
change the approved capability boundaries. Do not create a separate status page
to repeat these records.

Run [`python3 scripts/check-doc-links.py`](../scripts/check-doc-links.py) while
drafting and before handoff. CI also runs it via `scripts/check-release.sh`.
It checks local Markdown targets and this repository's `blob/main` paths; inspect
changed section anchors, external pages, revision-pinned links and Issue/PR
links separately. This keeps each PR's check limited to the surfaces it affects
while catching broken local navigation across the current tree automatically.

## Approved PIP-0003 revision navigation

The [approved PIP-0003 revision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md)
preserves its historical text, including relative links to files later relocated.
The [current reader copy](pips/PIP-0003/PIP-0003.md) repairs those links and
points to the Issue decision and current execution status without changing the
approved capability boundary. Use this map when reading the exact approved
revision; issued reports retain their assessed text.

| Historical target in PIP-0003 | Current location or pinned historical revision |
|---|---|
| `milestones/M01-CONTRACTS.md`, `milestones/diagrams/README.md` | [M01 contract draft](pips/PIP-0003/M01/M01-CONTRACTS.md), [M01 diagrams](pips/PIP-0003/M01/diagrams/README.md) |
| `milestones/M01.md` | [M01 milestone at `96707dd`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/96707dd/docs/pips/PIP-0003/milestones/M01.md) |
| `../../research/public-address-provider-research.md`, `../../research/delegation-inventory.md`, `../../research/provider-refresh-2026-10-02/README.md`, `../../research/public-address-candidate-worksheet.md`, `../../research/public-address-composition-proposal.md` | [Provider research](pips/PIP-0003/M00/research/public-address-provider-research.md), [delegation inventory](pips/PIP-0003/M00/research/delegation-inventory.md), [probe pack](pips/PIP-0003/M00/research/provider-refresh-2026-10-02/README.md), [candidate worksheet](pips/PIP-0003/M00/research/public-address-candidate-worksheet.md), [composition proposal](pips/PIP-0003/M00/research/public-address-composition-proposal.md) |
| `../../reviews/bitwasp-public-derivation/README.md`, `../../reviews/plan-first-capability-canonical-alignment.md` | [BitWasp investigation](pips/PIP-0003/M00/reviews/bitwasp-public-derivation/README.md), [plan review](pips/PIP-0003/M00/reviews/plan-first-capability-canonical-alignment.md) |
| `cp-executor-2026-10-03-171445.md`, `cp-executor-2026-10-04-011735.md`, `cp-executor-2026-10-06-210959.md` | [First M00 report](pips/PIP-0003/M00/cp-executor-2026-10-03-171445.md), [second M00 report](pips/PIP-0003/M00/cp-executor-2026-10-04-011735.md), [M01 report](pips/PIP-0003/M01/cp-executor-2026-10-06-210959.md) |
| `EXECUTION-CONTEXT.md`, `COUNTER-PROOF-TEMPLATES.md`, `MILESTONE-HANDOFF-TEMPLATE.md`, `snapshots/README.md` | [Execution context](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/95e83842ed9f4a577998e49851449230f56f56b2/docs/pips/PIP-0003/EXECUTION-CONTEXT.md), [report templates](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/95e83842ed9f4a577998e49851449230f56f56b2/docs/pips/PIP-0003/COUNTER-PROOF-TEMPLATES.md), [handoff template](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/95e83842ed9f4a577998e49851449230f56f56b2/docs/pips/PIP-0003/MILESTONE-HANDOFF-TEMPLATE.md), [snapshot index](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/95e83842ed9f4a577998e49851449230f56f56b2/docs/pips/PIP-0003/snapshots/README.md) |

- `README.md` is the public entry point for the Primitives repository.
- `docs/canonicals/primitives/` contains the current authoritative Primitives
  architecture reference.
- `docs/specifications/` contains project-wide specifications used by this
  repository. The PIP specification governs PIP identity and process; it does
  not replace a domain canonical.
- `docs/protocols/primitives/` contains Primitives-specific operational
  protocols. Counter-Proof reporting is operational/provenance machinery; the
  Primitives canonical owns the architectural meaning of each CP.
- `docs/pips/` contains the consolidated historical capability workspace and any new
  material-work workspace admitted under the current PIP Specification.
- PIP archival uses the ignored project-root `archives/pips/` after the
  originating Issue closes, with the whole workspace moved in a separate
  post-execution housekeeping commit.
- New material work starts from its originating Issue. Derive the `PIPxxxxx`
  identity from that Issue; after approval, create a separate Execution Plan
  only when execution complexity justifies one. Preserve traceability through
  PRs to the resulting repository changes.
- Historical PIP-0001 and PIP-0002 predate v0.2. After their Issues closed, their
  complete workspaces were copied to the ignored local archive and removed from
  the tracked current tree. Git history is their durable, shared recovery path.
  Their issued `SHA256SUMS`
  manifests identify the files assessed at the time, so checking them against
  today's working tree is not a valid historical verification. Verify PIP-0001's
  five files against its [assessed snapshot](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/55b4a7bcbee1916801ef2c8387b11ee2650d456e/docs/pips/PIP-0001/SNAPSHOT.md), and
  PIP-0002's 21 files against Git revision
  `55b4a7bcbee1916801ef2c8387b11ee2650d456e`. Keep the issued manifests
  unchanged when current documentation evolves.
- The PIP-0003 execution package also predates v0.2. Its issued package manifest
  identifies the baseline at Git revision `95e83842ed9f4a577998e49851449230f56f56b2`;
  the exact former plan is recoverable at `247f1f1`. M00's unchanged manifests
  retain their original repository-relative paths, so verify those identities
  against the archived snapshot or pinned Git revision, not today's layout.
  The current Execution Plan reconciles that history and the later Issue decision.
- `docs/pips/PIP-0003/M00/research/` and `M00/reviews/` hold the early
  public-address investigations and plan review. Some originated under PIP-0002;
  their placement makes them accessible to PIP-0003 execution without changing
  their original authorship, assessment scope or authority.
- `tools/skills/` contains reusable engineering/governance methods. Skills
  govern how architectural documents are engineered or revised; they are not
  Primitives architecture.

## Current authoritative set included in this package

1. Primitives Canonical Architecture Reference v1.8
2. PayCrypto.Me PIP Specification v1.0
3. Primitives Counter-Proof Report Protocol v1.5
4. Domain Canonical Engineering Skill v1.1
5. Canonical Document Governance Skill v1.1

Superseded Primitives canonicals, the former cross-domain architecture
canonical v1.0, earlier skill revisions, earlier Counter-Proof protocol
revisions, and exploratory conceptual reports are intentionally excluded from
this current-state package.

Repository-specific files already produced by local specialists (for example
execution plans, research artifacts, release guides, source code, CI files, and
the Public Architecture entry point referenced by the root README) are not
recreated here when their exact current contents were not part of the
consolidation input. Keep those existing files in the repository unless their
own authority/process explicitly supersedes them.
