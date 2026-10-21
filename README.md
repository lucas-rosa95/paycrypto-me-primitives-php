# PayCrypto.Me Primitives PHP

**Open, auditable cryptocurrency protocol primitives for PHP.**

PayCrypto.Me Primitives is a focused library for building and verifying **public-key, non-custodial cryptocurrency operations** through small, explicit, composable capabilities.

It is designed as both practical software and a **reference implementation / reference architecture**: something you can run, inspect, test, challenge, and independently verify.

> **We own the composition, not the cryptography.**

Rather than hiding protocol behavior behind a large coin-specific abstraction, Primitives makes the important boundaries visible: protocol semantics, definitions, composition, cryptographic capabilities, and the implementations delegated behind them.

---

## Why this project exists

Cryptocurrency payment infrastructure often faces an uncomfortable trade-off.

You can depend on a large library that brings far more behavior than you need, build coin-specific implementations that duplicate the same protocol layers, or place critical wallet logic behind a black box and ask consumers to trust its output.

Primitives explores a different path:

```text
concrete requirement
        ↓
minimal capability
        ↓
explicit protocol composition
        ↓
replaceable low-level implementation
        ↓
independently verifiable result
```

The goal is not to create another everything-crypto SDK.

The goal is to make a deliberately small set of cryptocurrency protocol behaviors **understandable enough to audit, explicit enough to verify, and modular enough to replace without redefining the architecture**.

---

## What makes Primitives different?

### Capability-driven

The library grows from concrete protocol requirements rather than from a catalog of coins or algorithms.

A new blockchain, network, fork, asset, or format does not automatically earn a new architectural path.

**Identity is not divergence.**

If two protocols share behavior, they share capabilities. Specialization begins only where protocol semantics actually diverge.

### Composition-first

Primitives favors small capabilities that can be composed into protocol flows.

```text
HMAC-SHA512 ───────┐
                    ├── BIP32 public derivation
secp256k1 tweak ───┘

SHA-256 ──────┐
               ├── HASH160
RIPEMD-160 ───┘

HASH160 + address semantics + encoding
                    |
              public address
```

This avoids both coin-specific duplication and a universal configurable crypto manager.

### Implementation-independent

External libraries are useful implementation machinery, but they do not define the architecture.

Library-specific points, big integers, buffers, or backend objects should not leak through capability boundaries.

That makes low-level implementations replaceable and independently testable.

### Public-key-first

The initial scope is intentionally constrained to public material and read-only wallet behavior.

Examples include extended public-key handling, non-hardened public derivation, public-key transformations required by supported protocols, public address construction, hashing/encoding composition, and validated network/protocol definitions.

Private keys, seeds, mnemonics, signing, and transaction signing are outside the initial security surface.

### Data where behavior does not change

Network constants are definitions, not subclasses.

When two networks differ only by parameters, Primitives represents that difference as validated data. A new capability is justified only when the behavior itself diverges.

### Clear results and failures

Every operation defines what its return values mean and how it reports failure. Booleans, nullable values, objects, and explicit internal outcomes are valid when they express the operation's documented semantics. An error must not masquerade as an undocumented `false`, `null`, empty value, or partial success. Public capability failures use Primitives-owned PHP exceptions, with provider-specific behavior normalized at the boundary.

---

## Auditable by design

Open source is useful, but visibility alone is not the goal.

Primitives is structured so important protocol behavior can be independently checked against multiple anchors:

```text
official/reference vectors
          +
project regression vectors
          +
known-good implementation outputs
          +
boundary and invalid-input tests
          +
independent differential verification
```

A disagreement is something to investigate — not something to resolve by majority vote.

> **You should be able to inspect what the library does, understand why the composition exists, reproduce deterministic behavior, and verify the result without trusting a black box.**

Architecture, tests, definitions, dependency boundaries, and documentation are therefore all part of the audit surface.

---

## Current architectural direction

The first concrete capability that grounded the architecture is:

```text
XPUB
  ↓
public child derivation
  ↓
public key
  ↓
address construction
  ↓
public receiving address
```

Bitcoin provides the initial evidence base, including BIP32 public derivation and common public address constructions.

That does **not** make the architecture Bitcoin-shaped.

Shared behavior is modeled as reusable capabilities; protocol-specific behavior forks only at the point where semantics actually change.

Future protocol support should therefore cost roughly in proportion to what is genuinely new about that protocol.

---

## OWN / COMPOSE / DELEGATE

A simple rule guides the library.

**We own** capability contracts, protocol composition, invariants, validated definitions, semantic value objects, and protocol-specific orchestration.

**We compose** larger protocol behavior from smaller capabilities.

**We delegate** cryptographic algorithms, elliptic-curve mathematics and standardized encoders/decoders to researched and qualified libraries or runtime facilities.

For example, BIP32 public-child derivation can own CKDpub semantics while composing HMAC-SHA512 and a secp256k1 public-key tweak capability.

Missing suitable providers require further research or explicit scope reconsideration, not a local algorithm or codec implementation.

> **Primitives does not eliminate dependencies. It prevents dependencies from defining the architecture.**

---

## PayCrypto.Me Public Architecture

Before reading or changing this repository's domain architecture, start with
[PayCrypto.Me Public Architecture](docs/canonicals/public-architecture/paycrypto-public-architecture-canonical-v1.1.md).
It is the shared entry point and authority map: it establishes the public
architecture's scope, the relationship between its domains, and the context in
which Primitives operates.

Then read the current Primitives canonical architecture. The public
architecture document provides context and navigation; the Primitives canonical
remains the authority for Primitives internals and its evolution.

For the first public-address capability, start with the [PIP-0003 Execution
Plan](./docs/pips/PIP-0003/EXECUTION-PLAN.md) for current status, the next review
step and the ordered roadmap. The [approved proposal revision](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/72c4fa1f0a2c9449a93071b55bf20f85b0008621/docs/pips/PIP-0003/PIP-0003.md)
defines scope; [Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3)
records approval and coordination. The plan links the M00/M01 research,
contract draft and assessment evidence where needed.

For human and AI-agent work, preserve this repository-first context. Material work is additionally identified by the current PayCrypto.Me Improvement Proposal (PIP), when applicable:

```text
PUBLIC-ARCHITECTURE.md
        +
current Primitives canonical
        +
current PIP when the work is material
```

Load other canonicals and evidence only when the work crosses or may affect
their architectural boundaries.

---

## Canonical execution and verification

Primitives uses a small project-local terminology where ordinary professional meaning is not sufficient:

- **PIP — PayCrypto.Me Improvement Proposal:** project-wide identity for a material proposed change. Its lifecycle and conventions belong to the PayCrypto.Me PIP Specification.
- **CP — Counter-Proof:** a Primitives canonical architectural challenge identified by a stable `CP-NN` identifier. Counter-Proofs test whether a material result preserves required Primitives architectural properties; they do not prescribe implementation techniques.

For material Primitives work, the execution path is conceptually:

```text
Originating Issue
  identity + lifecycle + historical relationships
        ↓
PIP (WHAT + WHY + BOUNDARIES)
        │
        │ APPROVED → proposal freezes
        ↓
Plan (when justified)
  HOW + ORDER + EXECUTION within approved boundaries
        ↓
PR(s) / implementation artifacts
        ↓
codebase / resulting repository state
```

Counter-Proof self-challenge and independent review provide Primitives verification evidence along this traceability path. Their reports are operational/provenance records, not a second architecture. The current Primitives canonical remains authoritative for the meaning, applicability, required properties, and deep rationale of each CP.

Project-wide PIP conventions are defined by the [PayCrypto.Me PIP Specification v1.0](./docs/specifications/paycrypto-pip-specification-v1.0.md). Primitives-specific Counter-Proof reporting conventions are defined by the [Counter-Proof Report Protocol v1.5](./docs/protocols/primitives/COUNTER-PROOF-REPORT-v1.5.md).

---

## Architecture is part of the project

The code tells you what currently exists.

The canonical architecture explains **why it exists, which decisions are intentional, what remains open, what has been rejected, and how the project may legitimately evolve**.

That distinction matters for humans and coding agents alike.

Before introducing a new protocol path or capability:

```text
What existing capabilities can be reused?
        ↓
Is the difference only data?
        ├── yes → definition
        └── no
             ↓
Where does behavior actually diverge?
             ↓
Can composition express it?
        ├── yes → compose
        └── no  → justify a new capability
```

The canonical architecture is therefore a contribution contract, not background reading.

---

## Reference implementation, not a crypto catalog

Primitives is intentionally useful for different kinds of readers.

If you build cryptocurrency payment infrastructure, it provides reusable protocol capabilities.

If you study wallet derivation and address construction, it provides an architecture intended to make those flows inspectable.

If you review or audit implementations, it provides explicit boundaries against which behavior can be tested.

If you contribute a protocol, it provides extension rules designed to resist duplication and speculative abstractions.

What it deliberately does **not** try to become is a generic catalog containing every cryptocurrency feature.

> **The architecture grows from evidence, not speculation.**

---

## Project status

The architecture is intentionally being established before broad implementation.

Some contracts, implementation choices, protocol edge cases, and dependency selections may remain open until verified by specification evidence, compatibility tests, or focused technical spikes.

That is deliberate. An unresolved question is preferable to a prematurely frozen abstraction.

For the current source of truth, read the [canonical architecture v1.8](./docs/canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md) document in this repository.

Material PayCrypto.Me changes are identified through a **PIP — PayCrypto.Me Improvement Proposal**. PIP is a project-wide work/proposal identity; it does not replace this domain canonical or redefine Primitives architecture. When a Primitives PIP reaches implementation assessment, the Primitives Counter-Proof protocol provides the domain-specific executor self-challenge and independent review mechanism.

Active PIPs live under `docs/pips/`. Historical/inactive PIPs may be retained under `docs/archives/pips/` according to the project-wide PIP specification.

---

## Development

This is a vanilla PHP library. The development environment is a small PHP CLI
and Composer container; it deliberately does not include WordPress, a web
server, or a database.

The tool container uses Docker's default bridge network (`network_mode: bridge`),
matching the release containers. It needs no Compose service discovery.

The supported consumer baseline is PHP 8.3. The Docker image runs PHP 8.3 and
Composer is configured with an 8.3 platform target, so dependency resolution
does not accidentally require a newer PHP release.

```bash
bash scripts/setup.sh
```

This builds the image, installs dependencies and runs `composer ci` in an
ephemeral container. Use `bash scripts/setup.sh 8.4` to check the newer runtime.
The shared runtime/image list for setup, CI and release is `scripts/php-images.sh`.
After selecting another runtime, rerun setup without arguments to restore PHP 8.3.

The complete CI command is:

```bash
docker compose run --rm app composer ci
```

For versioned releases, follow the [release guide](docs/GUIDE-RELEASE.md).
Preview the release flow with `bash scripts/release.sh -v 0.1.0 --git --dry-run`
after committing your changes on `main`. CI tests real PHP 8.3 and 8.4 runtimes.

It validates Composer metadata, syntax-checks PHP files, runs PHPUnit, and
runs PHPStan. GitHub Actions executes that same containerized sequence.

The package is prepared for Composer under the provisional name
`paycrypto-me/primitives`. Before publishing to Packagist, create/confirm that
vendor/package namespace and connect this repository there. The PSR-4 namespace
is intentionally provisional too, because the canonical architecture keeps its
final namespace open until concrete APIs are established.

---

## Contributing

Contributions should preserve the capability-driven model.

Before proposing a new abstraction, implementation path, or protocol-specific class, identify:

1. the concrete requirement;
2. existing capabilities that can be reused;
3. new definitions required;
4. the exact behavioral divergence;
5. why existing composition cannot express it, if proposing a new capability;
6. verification evidence for the behavior.

A new coin name is not architectural evidence by itself.

Architectural changes should update the canonical documentation rather than allowing implementation drift to redefine the project silently.

---

## Security

Cryptocurrency protocol code deserves conservative boundaries.

The public-key-only scope, test coverage, and architectural controls are not guarantees that a release is defect-free.

Before relying on the library for value-bearing workflows, review supported behavior, test vectors, dependency versions, known limitations, and release status appropriate to your use case.

Security issues that could put users at risk should be reported through the repository's designated private security-reporting channel rather than a public issue.

---

## License

PayCrypto.Me Primitives PHP is released under the [MIT License](./LICENSE).

---

## The guiding idea

> **The goal is not to predict every blockchain the project will support. The goal is to make the next legitimate requirement expensive only in proportion to what is genuinely new about it.**

PayCrypto.Me Primitives exists as an **open, auditable, and independently useful construction in its own right**.

**Inspect it. Test it. Challenge it. Build on it.**
