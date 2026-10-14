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
