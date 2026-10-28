# Documentation language

English is the standard language for repository documentation, including plans,
architecture documents, diagrams, and captions. Conversation may follow the
user's language; this does not change the documentation language.

# Human–agent collaboration

Work as engineering peers. Material decisions about architecture, structure,
governance, scope or technical direction require explicit human–agent agreement
before consolidation or implementation, unless the human has explicitly
delegated that decision. Permission to investigate, draft or probe alternatives
is not approval of a resulting choice. Continue routine work within agreed
boundaries without repeatedly requesting permission; bring unresolved material
choices back to discussion. Do not silently turn an inference into a requirement
or an existing example into an approved design.

Keep proposals, assumptions, evidence, open questions and agreed decisions
distinct. Present relevant ideas and concerns for discussion. Defend objections
with reasoning, evidence, counterexamples or concrete alternatives; evaluate
trade-offs together and revise positions when the evidence warrants it.
Disagreement serves the shared engineering objective and must have a technical
or governance basis.

Discuss at the depth expected between senior software engineers. Use precise
technical concepts and established terminology when useful, explain reasoning
clearly, and let the human request clarification. Expose uncertainty and
conceptual ambiguity rather than presenting judgment as settled fact.

Before each material discussion, provide a concise reading path: the relevant
repository file and section, why it matters, and the question to resolve.
Proceed point by point so both peers can evaluate the decision from the same
context. Add focused external references when they contribute evidence or a
useful counterpoint; distinguish their claims from local interpretation. Avoid
unrelated reading detours or requiring the entire project history for a local
decision.

Use the owning Issue for the history of relevant discussions, decisions and
their rationale. Consolidate agreed technical outcomes in the appropriate
existing repository documents, clearly marking preliminary material and open
questions. Preserve traceability without duplicating the discussion history
across documents. Record an agreement only after it has actually been reached.

# Implementation boundary

Primitives owns its capability contracts, protocol semantics, invariants,
definitions, operations and composition. Do not implement cryptographic
algorithms or standardized encoders/decoders in this domain, regardless of how
simple they appear. Research and validate suitable libraries or runtime
facilities before selecting implementations. Reference implementation internals
are evidence to study, not code or architecture to reproduce.

# Resource scope and reuse

Choose a component's scope from its responsibility and semantic contract, not
from a required number of consumers. A cohesive, candidate-independent
capability or composition may be shared from its first concrete use; it does
not need a second consumer to prove that its contract is general. Keep a
component local when its purpose or invariants belong to a specific flow.
When a new concrete use reveals genuinely compatible semantics, promote the
local component to a shared resource and update its consumers without making
one protocol candidate impersonate another. Do not extract helpers into shared
declared steps merely to anticipate reuse. Protocol membership does not force
one execution root: policies with distinct workflows may have independent
complete flow declarations and directly reuse compatible resources, preserving
their protocol/network/policy identity. This refines the existing
evidence-driven composition guidance in the root README and canonical
architecture; it does not make cryptographic algorithms or standardized codecs
Primitives-owned implementations.

# Capability responsibility

Give each capability one specific, complete semantic responsibility. Granularity
is defined by that objective, not by a function count or a single instruction;
several internal actions may be necessary to fulfill it. Related or cohesive
work does not justify combining independently meaningful responsibilities into
one capability merely because they serve a broader result. Connect such
responsibilities through composition and retain the contracts of reused
resources. A dependency need not become a separately configurable flow step.
For material boundary choices, record the purpose, inputs, complete outcome,
invariants and why work belongs inside the capability or in composition.
Follow the agreed boundaries; unresolved material changes require joint review
rather than an executor's private interpretation of cohesion.

# Operation results and failures

Give each Primitives-owned operation a documented meaning for its return values
and failures. Boolean, nullable, object, and explicit outcome values are allowed
when their meanings are clear. Do not use an undocumented sentinel or partial
success for a failure, or alternate between return values and exceptions for
the same failure. Public capability failures use Primitives-owned PHP exceptions;
normalize provider conventions at that boundary and do not expose vendor
exception types as the domain contract.

# Material work and PIPs

Follow the current [PayCrypto.Me PIP Specification](docs/specifications/paycrypto-pip-specification-v1.0.md)
for material work. A PIP MUST originate from an Issue in the repository that
owns the work, and its `PIPxxxxx` identity MUST be derived from that Issue
number; do not allocate an independent PIP sequence. The Issue carries identity,
lifecycle, coordination and historical relationships. The PIP records the
proposal's WHAT, WHY and BOUNDARIES.

Do not begin an Execution Plan before its PIP is approved. Approval freezes the
proposal; a separate Plan is optional and is created only when execution
complexity justifies it. A Plan records HOW, ORDER and EXECUTION within the
approved boundaries and may evolve within them. Keep durable traceability from
the originating Issue through the PIP, any justified Plan, PRs and resulting
repository changes. Create additional artifacts only for a distinct justified
responsibility or when an applicable protocol requires them.

# Execution status synchronization

When work changes a milestone's evidence, review conclusion, or next gate,
update the existing mutable status records in the same PR before declaring an
executor handoff or a favorable independent review. Check the current Execution
Plan's state and roadmap, the affected milestone README or handoff, the docs
index, and the PR description. Make their stage, exact evidence or reviewed
revision, remaining limits, and next action agree. For PIP-0003, these are
`docs/pips/PIP-0003/EXECUTION-PLAN.md`, the relevant `Mxx/README.md`,
`docs/README.md`, and the PR description. Update only a source whose content or
navigation actually changed; state in the PR when no status-document edit is
needed and why.

The executor performs this reconciliation before requesting review. The
independent reviewer checks it against the branch and PR, and resolves stale
current-state text before a favorable conclusion. A review or merge changes
status even if the probe or production code did not change. Distinguish
research reviewed, PR open, PR merged, provider selected, and capability
verified. Link the PR for its live open/merge state instead of committing
transient phrases such as "merge pending" into status documents; record the
stable merge reference when the milestone state is next updated. Keep the
approved proposal revision and issued historical reports intact. If the
current copy of an approved PIP needs navigation-only repairs, retain a link
to the exact approved revision and leave its WHAT, WHY and BOUNDARIES
unchanged. Do not create a new status, handoff, or review artifact solely to
repeat information already held by the Plan, milestone README, PR, Issue, or CI.

# Documentation maintenance gate

When a PR adds or moves a document, changes a milestone or review conclusion,
or changes a reader's next action, check the affected current-state text and
navigation before requesting review. Start from the changed artifact and follow
its links outward: the relevant Plan and milestone handoff, `docs/README.md`,
the root `README.md` if its entry path changed, the originating Issue body, and
the PR description. Repair stale paths, stage labels, evidence references and
next-gate wording in the same PR or Issue update. Link to the authoritative
record instead of copying its detailed history into each surface. Historical
reports and approved revisions remain revision-pinned evidence, not mutable
status pages.

Run `python3 scripts/check-doc-links.py` before a documentation handoff; CI runs
it through `scripts/check-release.sh`. The check covers current local Markdown
targets and this repository's `blob/main` links. Inspect changed anchors,
external URLs, revision-pinned targets and Issue/PR links that the local check
cannot prove. Reviewers repeat the affected-path and status check against the
submitted revision before a favorable conclusion. This is a change-scoped gate,
not a requirement to reread every historical report on each PR.
