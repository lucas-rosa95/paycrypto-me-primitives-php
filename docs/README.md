# PayCrypto.Me Primitives — Documentation Map

This file is a navigation map for the repository documentation. It is **not**
an architecture canonical and does not create architectural authority.

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
│   │   ├── M04/                     # merged, independently reviewed codec/admission research
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

The [approved PIP-0003 proposal](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md)
defines the public address capability boundary. Its
[Execution Plan](pips/PIP-0003/EXECUTION-PLAN.md) is the
current operational reference. Start with its Current state, Reading path and
Execution roadmap; consult the linked M00–M04 records for the specific research,
decisions, draft contract and assessment evidence.
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3)
is its retrospective coordination anchor. A
[present-day maintainer decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555)
approved the exact proposal revision on 2026-10-10; the old package and M01
draft did not establish that approval or verify the capability.
The [M02 protocol record](pips/PIP-0003/M02/M02-PROTOCOL.md) contains accepted
protocol decisions and a conditional provider qualification handoff; it was
merged in [PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9).
The [M03 research handoff](pips/PIP-0003/M03/README.md) is complete, merged in
[PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12),
and [independently reviewed](pips/PIP-0003/M03/cp-reviewer-2026-10-16-074200.md)
within its bounded research scope. [M04 codec, admission and definition research](pips/PIP-0003/M04/README.md)
has also [passed independent follow-up review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21#issuecomment-6243150128)
within its stated limits and [merged in PR #21](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/21). A subsequent
[corrective re-review in Issue #25](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/25)
adds research-probe classification and assertion controls that require their
own independent check before M05. BitWasp's uninstrumented native decode
exception remains a selection gate for any decoder boundary; production
implementation remains open. The separately approved
[PIP00010 receiving entry point](pips/PIP00010/PIP00010.md)
originates from [Issue #10](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/10);
its implementation and verification are assigned in the Execution Plan under
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3).
The [independent plan review](pips/PIP-0003/M00/reviews/plan-first-capability-canonical-alignment.md)
assesses an earlier plan only, recoverable at [revision `83ccd557`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/83ccd5571843eed57726e140e493868f0f152b5f/docs/PLAN-FIRST-CAPABILITY.md).
The [pre-M04 audit](pips/PIP-0003/reviews/pre-m04-audit-2026-10-17.md)
records planning and navigation findings closed through
[PR #20](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/20);
its forward-looking M04 criteria should be read alongside the merged M04
handoff above.

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
