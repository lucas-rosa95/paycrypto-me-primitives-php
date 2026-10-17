# Documentation language

English is the standard language for repository documentation, including plans,
architecture documents, diagrams, and captions. Conversation may follow the
user's language; this does not change the documentation language.

# Implementation boundary

Primitives owns its capability contracts, protocol semantics, invariants,
definitions, operations and composition. Do not implement cryptographic
algorithms or standardized encoders/decoders in this domain, regardless of how
simple they appear. Research and validate suitable libraries or runtime
facilities before selecting implementations. Reference implementation internals
are evidence to study, not code or architecture to reproduce.

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
verified. Keep approved PIP proposals and issued historical reports intact;
link to their exact revisions instead of rewriting them. Do not create a new
status, handoff, or review artifact solely to repeat information already held
by the Plan, milestone README, PR, Issue, or CI.
