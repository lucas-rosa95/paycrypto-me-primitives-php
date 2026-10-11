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
│   │   └── M01/                     # contract draft, report and diagrams
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

The [PIP-0003 proposal](pips/PIP-0003/PIP-0003.md) defines the public address
capability boundary. Its [Execution Plan](pips/PIP-0003/EXECUTION-PLAN.md) is the
current operational reference. Start with its Current state, Reading path and
Execution roadmap; consult M00 and M01 for the specific research, draft contract
or assessment evidence linked from the plan.
[Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3)
is its retrospective coordination anchor. A
[present-day maintainer decision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3#issuecomment-6102704555)
approved the exact proposal revision on 2026-10-10; the old package and M01
draft did not establish that approval or verify the capability.
The [independent plan review](pips/PIP-0003/M00/reviews/plan-first-capability-canonical-alignment.md)
assesses an earlier plan only, recoverable at [revision `83ccd557`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/83ccd5571843eed57726e140e493868f0f152b5f/docs/PLAN-FIRST-CAPABILITY.md).

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
