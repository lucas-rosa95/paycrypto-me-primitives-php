# M05 — Composition decisions and PHP contract proposals

Current record, 2026-10-28. C1: [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41)
and [PR #44](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/44),
merged at [`ee542e9`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/ee542e905b1b83f662356091bb2b4e59254186f5).
Current C2 coordination: [Issue #45](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/45),
with its own work branch and PR linked there.
**C1 is agreed and merged; C2 is the current discussion, still undecided.
M05 contracts are not frozen and no production schema validator, executor
or public address capability is qualified.**

This document owns composition agreements, concrete PHP proposals and their
acceptance obligations. The [M05 assessment](README.md) owns provider/runtime
comparison and reproduction; the [Execution Plan](../EXECUTION-PLAN.md) owns
ordering, milestone status and historical review references. Existing
[M02 O01–O06](../M02/M02-PROTOCOL.md), [PIP00010](../../PIP00010/PIP00010.md)
and [Canonical v1.8](../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md)
boundaries remain authoritative. No cryptographic algorithm or standardized
codec is implemented by this proposal.

## Reading and decision status

| Material | Authority and next action |
|---|---|
| [Agreed composition direction](#agreed-composition-direction) and current policy relationships | Joint C1 architecture, including independent complete policy flows, direct local bindings, resource scope, responsibility and audit criteria. |
| [Choice table](#preliminary-choices-for-joint-discussion) | C2 is the current separately tracked discussion: readable authoring, duplicate detection, scoped references and immutable representation. C3–C6 retain their stated open choices. |
| [Source example](#preliminary-source-schema-and-immutable-form--c2c5), PHP signatures, provisioning and failure representation | Executor proposals for joint review. Examples and names are not accepted APIs or instructions to implement unchanged. |
| [C2 authoring laboratory](#c2-authoring-laboratory--exploratory-findings) | Three source candidates and separate scoped-binding experiments, authorized for investigation. Bounded structural evidence; no candidate or API is jointly selected. |
| [Fitness evidence](#exploratory-reuse-fitness-challenges--outside-pip-0003-delivery) | Bounded symbolic checks and contract analysis. They support C1's direction; they do not qualify actual future Taproot/CashAddr admission, providers or execution. |
| [Downstream acceptance](#contract-review-and-downstream-acceptance) | Requirements for M06/M07 implementation and later assembled verification, not passed production tests. |

To continue, compare the C2 laboratory's source examples and findings against
the preliminary pair-list form. The discussion must choose an authoring form
that preserves agreed semantics without unnecessary verbosity.
Document accepted, revised or deferred choices explicitly. A documentation merge,
a probe passing or permission to draft does not approve an open design choice.

Each step has its own Issue, work branch and PR. Investigate adjacent-stage
dependencies when needed for C2, but record future-stage findings in their
appropriate existing sections without silently deciding them. Proposed changes
to an earlier agreement return to joint discussion with the human pair and the
agent responsible for that stage. Opening C2 changes coordination only, not any
source grammar, semantic checker, capability boundary or execution mechanism.

## Agreed composition direction

- **Inspectable data:** declarations identify capability/subcomposition
  references, ordered steps, inputs, parameters and outputs. A connection
  satisfies a semantic contract, including origin and parameter role; PHP type,
  byte width or a caller-written label alone is insufficient. Source data
  cannot invoke arbitrary callbacks, vendor classes or methods.
- **Independent flows:** protocol membership does not force one execution
  root. Distinct candidates and policies with different workflows may select
  their own complete declarations and directly reuse compatible resources.
  Each retains its protocol/network/policy identity and values. Reuse can occur
  at any position, including after divergent work, without joining executions
  or making one flow impersonate or mutate another. Equivalent behavior uses
  the same declaration with validated parameter data.
- **Ordered execution:** steps consume declared inputs/parameters or earlier
  outputs in their own scope. Setup resolves every advertised intent and
  rejects missing/ambiguous bindings, forward references and circular
  declaration/operation dependencies, including intent-completed cycles.
  Internal bounded iteration or recursion is not prohibited by that graph rule.
- **Binding:** the three current Bitcoin policies directly bind a terminal to
  their declared local `encode` step. A separate named slot added no guarantee
  in that comparison. Other justified binding points may occur at the beginning,
  middle or end and at nested scopes; fixed names, depth or a universal HASH160
  junction must not become executor rules. Nested occurrence identity remains C2.
- **Complete outcomes:** for admitted inputs, definitions and functioning bound
  capabilities, the operation is deterministic and returns a stable, complete
  immutable result or a documented owned failure. A throw aborts this invocation
  and its callers: no later stage, partial result, default, alternate provider,
  policy, key or index. Invalid CKDpub children fail at the requested index;
  zero tweak remains valid with updated child metadata. Atomic outcome does not
  promise rollback of arbitrary provider side effects or memory erasure.
- **Owned boundaries:** Primitives owns contracts, protocol meaning, invariants,
  composition and public PHP failures. Qualified runtime/library facilities
  supply cryptographic algorithms and standardized codecs. Provider conventions
  are normalized at their owned boundary, without runtime fallback.

The initial operations deliver P2PKH, P2SH-P2WPKH and P2WPKH on mainnet/testnet.
Taproot, CashAddr and Signet are fitness inputs only. Future capabilities,
admission relations and provider bindings require explicit review and
qualification. Reviewed definition revisions rebuild validated immutable
assemblies; a loaded operation is not mutated. A descriptive name such as
`BitcoinDefinition` is allowed, but cannot conceal a specialized per-coin
implementation or duplicated composition. No class per network is needed just
to carry constants.

### Capability responsibility and boundary decisions

A capability fulfills **one specific, complete semantic responsibility**.
Several actions or functions may be necessary; instruction count does not define
granularity. Cohesion or contribution to a larger result cannot merge
independently meaningful responsibilities. Composition connects those units.
Do not introduce an executable step merely to rename a value or project a field.

For each material C4 boundary, record its purpose, inputs, complete outcome,
invariants, failures, consumed resources and why the work belongs inside the
capability or in the enclosing declaration. A contracted dependency need not
become a configurable flow step. Internals remain inspectable and testable,
including at relevant lower boundaries. Executors follow agreed boundaries and
raise unresolved material changes for joint review. Remaining admission, CKDpub
and hypothetical Taproot output-key groupings are not approved by this criterion.

Resource scope follows responsibility and semantic compatibility, not a consumer
count. General resources may start shared on their first concrete use; local
components remain local while their purpose/invariants belong to a specific
flow. Promote a local resource when compatible use appears, updating consumers
without impersonation or duplicate-purpose helpers. Do not extract steps merely
to anticipate reuse. These rules also appear in [AGENTS.md](../../../../AGENTS.md).

### Separate data responsibilities

| Responsibility | Meaning and boundary |
|---|---|
| Parameter definition | Immutable identifiers, versions, HRPs and admitted compatibility data; no provider, callback or execution graph inside the network value. |
| Composition declaration | Inspectable references, ordered semantic connections, required parameters and exported outcome. It cannot redefine referenced contracts. |
| Intent binding | Explicit association from admitted network/policy intent to a complete flow, its parameters and any local target bindings. Prefixes never select intent. |
| Provider wiring | Explicit assembly binds one qualified implementation per required contract, separately from definitions and composition data. |

Schemas establish structure; semantic validation establishes compatible
connections and operation invariants. Validated records must not retain mutable
caller references. The initial scope does not approve a public workflow language,
arbitrary extension loader or universal `mixed -> mixed` stage interface.

![Composition lifecycle: validated assembly, raw request admission, one selected flow and a complete result or owned failure](diagrams/composition-lifecycle.png)

The view states lifecycle obligations, not a chosen compiler, interpreter, PHP
record layout or prelude packaging. Optional receiving-source provisioning may
perform eager key admission during setup. Structural source validation itself
performs no codec/crypto work. References and exports are in the
[visual index](#visual-counter-proofs-for-joint-review).

### Audit and reproducibility

Independent auditors can inspect declarations, resolved connections, contracts
and the mapping from every declared step to its capability and selected
implementation/dependency. They can reproduce pinned code, dependencies,
runtime, inputs, commands and independently anchored expected outputs, then
substitute test capabilities at existing contract boundaries to challenge
selection, ordering, validation and abort through the **same** validator and
executor used by the operation.

Auditors choose any owned, native or third-party boundary for deeper review;
the architecture neither dictates risk rankings nor uses them to route execution.
Native algorithm trust leaves our input bytes, invocation and connections to
verify. Unit tests and independent vectors/differential references check
capability behavior beyond routing. Test doubles do not qualify providers.
Ordinary consumers need only the documented result/failure, not an invocation
trace or the auditor's verification work.

Provenance, reproducible builds and checksums identify examined artifacts;
a checksum may pin an external specification/vector snapshot or test output.
They do not establish source authority or protocol correctness by themselves.
Record scope, reproduced evidence, challenges and limits in the existing
Issue/PR/CI trail where sufficient. No public trace API, checksum scheme,
audit labels or extra coupling is selected. Concrete inspection/instrumentation
is C6; execution-specific qualification evidence remains M06/M07 work. See the
[root audit principle](../../../../README.md#auditable-by-design) and
[CP-11](../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md#cp-11-----independent-evidence-and-verification).

### Concrete flow and semantic connections

For the three policies in the initial delivery scope, the shared public flow has ordered
request/definition admission, extended-key admission, remaining-depth
validation, exact-path public derivation and key HASH160 before the selected
terminal composition. Extended-key admission
retains the visible substeps in [validation order](#failure-contract-and-validation-order):
checked decoding and common metadata, version eligibility, point validation,
then intent compatibility. Neither a graph scheduler nor a builder may reorder
them. Only the explicitly selected policy branch runs.

| Connection | Meaning required by its consumer |
|---|---|
| Request to key admission | Original serialized public key and validated explicit network/policy intent; no inferred intent or coerced path values. |
| Key admission to exact-path derivation | A complete admitted public node, including chain code, version and metadata; a compressed key alone is insufficient. The validated path must also fit remaining depth. |
| Derivation to key hashing | The final admitted compressed public key from the exact requested path. The full node remains the derivation result; hashing uses its public-key field. |
| Key hashing to terminal composition | A 20-byte HASH160 of that derived compressed key, with that semantic origin. An arbitrary 20-byte digest is not interchangeable. |
| Terminal composition to public result | A complete address for the same admitted intent and path; result context comes from the validated request, not encoding-based inference. |

HASH160 is a reusable composition of delegated SHA-256 followed by delegated
RIPEMD-160, with binary 32-byte and 20-byte results respectively. Reusing it
does not erase whether its input is a public key or a witness redeem script.
Those semantic origins remain explicit at the surrounding connections.

| Policy | Shared input | Owned construction and parameter binding | Delegated terminal encoding |
|---|---|---|---|
| `p2pkh` | Derived compressed-key HASH160 | The admitted network's P2PKH version byte followed by the key hash. | Base58Check of the complete 21-byte payload. |
| `p2sh-p2wpkh` | The same key-hash semantics | Form the 22-byte witness redeem script `0x00 0x14` followed by the key hash; apply HASH160 to that script; prepend the admitted P2SH version byte. | Base58Check of the complete 21-byte payload. |
| `p2wpkh` | The same key-hash semantics | Bind that hash as the witness-v0 20-byte program and the admitted network's HRP. | The qualified SegWit-v0 key-hash encoder. |

This table exposes the shared prefix and actual terminal divergence without
making one class per policy a requirement. P2PKH key hash, P2SH script hash and
witness program can have the same byte length while requiring different
connections. The supported compositions fix these relationships; a generic
byte concatenator or `mixed -> mixed` stage interface cannot establish them.
Standardized hashing and encoding machinery remains delegated. Owned fixed
payload/script construction does not introduce a general script engine.

The receiving operation adds ordered account/index admission and exact
network/policy/account source selection, then invokes the same generic
operation with the selected serialized key and `[0, index]`. Its result adds
the logical account, branch `0` and index. This boundary is itself inspectable;
it must not hide another derivation flow or bypass generic key admission.

![Current Bitcoin policies share exact derivation and key HASH160, then use only their directly selected terminal](diagrams/bitcoin-current-policy-flow.png)

The terminal split is agreed: P2PKH payload → Base58Check; nested witness
script → HASH160 → P2SH payload → Base58Check; P2WPKH directly supplies the
derived key hash as its encoder program, with the selected HRP. No pass-through
`program` capability is added. The concrete names/grouping remain C2–C4
proposals, and this current-policy route is not a universal Bitcoin root.

### Independent policy-flow declarations — agreed C1 direction

C1's extension review concluded on 2026-10-28: an admitted intent may select a
complete flow independently of other policies in the same protocol. A Taproot
fitness flow uses compatible resources directly and never enters or adapts the
key-hash flow. It retains Bitcoin/P2TR identity; Bitcoin Cash retains its own
protocol identity. A composition fitness “candidate” need not mean a new coin.
A common operation-entry contract does not require a common executable root.

| Intent family | Complete selected flow | Compatible reuse and distinction |
|---|---|---|
| Bitcoin mainnet/testnet, three current policies | Shared key-hash public-address declaration | Admission/public derivation, HASH160, direct terminal binding and result construction. |
| Hypothetical Bitcoin P2TR key-path | Independent Taproot declaration | Compatible admission/public derivation, its output-key construction, fixed witness-v1/32 Bech32m encoding and result construction; no address key-hash input. |
| Bitcoin Cash fitness | Independent Bitcoin Cash declaration | Compatible resources only; no execution through either Bitcoin policy flow. No CashAddr qualification. |

#### Taproot policy flow — agreed direction

![Complete Bitcoin policy declarations reuse compatible resources without a mandatory shared execution root](diagrams/taproot-policy-divergence.png)

Repeated resource names mean independent invocations with each flow's own
values/context. The independent-flow relationship is agreed; the illustrative
public-derivation grouping and Taproot output-key unit still need C4 review.
The figure does not approve new admission or capability support. Current O02
rejects P2TR; future admission/provenance requires a separate decision.

### Schema and execution obligations

The schema must make these fields or equivalent semantics reviewable: a
composition identifier, required inputs/parameters, capability or
subcomposition references, named connections, ordering constraints and result
bindings. Exact PHP field names and syntax are not frozen by this list.

1. At setup, reject duplicate identifiers/bindings, unknown references or
   fields, missing inputs, cycles and connections with incompatible semantic
   types. Reject partial or ambiguous assembly before provider execution.
2. Check operation invariants beyond structural validity: the exact admission
   order, complete-node derivation, policy-specific payload construction,
   matching parameter roles, and same-request result context. No declaration
   may skip validation, convert a script hash into a key hash by relabeling,
   introduce an extra executable branch or change exact-path semantics.
3. Freeze the fully validated composition before serving requests. Bind one
   provider per required contract explicitly. Equivalent parameter variants
   within one protocol candidate reuse its declaration; provider replacement
   leaves its semantics unchanged.
4. At execution, enforce actual value contracts at the capability boundaries.
   Static connection validation is not proof that a provider returned correct
   bytes. Independently anchored qualification still detects plausible wrong
   outputs that runtime shape checks cannot establish as wrong.

Composition and binding data errors use the proposed `INVALID_DEFINITION`
category at setup. Provider/environment failures retain their categories.
Malformed request data keeps its request-stage category; declaration errors
must not become delayed request failures.

Failure abort is an operation invariant, not a configurable `on_error` policy.
Adapters normalize vendor conventions at their owned boundary; the executor
propagates owned failures without replacing their semantic reason. No catch
handler may turn failure into `null`, a default address, a partial result or
another execution attempt. The caller handles the public exception. No
failure-path schema fields enable fallback, skip, retry or index advancement.

The documented point capability's `null` for confirmed infinity is a specific
internal outcome, not a continuation value. CKDpub immediately translates it
to `INVALID_CHILD` and terminates; it never reaches an address-construction
stage. Zero tweak remains a valid input with complete child metadata updates.

## Preliminary schema and execution proposal

The following C2–C6 choices and PHP forms are executor proposals for joint
review. Agreed responsibilities above constrain them; they are not a frozen
source schema, public API or implementation handoff.

### Preliminary choices for joint discussion

| Choice | Status and proposed direction | Decision still required |
|---|---|---|
| C1 — Execution model | **Agreed.** Independent data-declared flows, compatible resources, direct local binding, ordered semantic connections, setup cycle rejection, complete outcomes, failure abort and audit support. | None exclusively in C1. Future contradictions must reopen the affected decision with evidence. |
| C2 — Authoring and representation | **Current discussion; undecided.** The [authoring laboratory](#c2-authoring-laboratory--exploratory-findings) compares pair lists, fixed source records and a restricted builder; scoped-binding candidates are tested separately. | Human readability, duplicate/unknown-field detection across construction paths, metadata ownership, explicit emptiness, scoped nested occurrence identity and private immutable resolved records. |
| C3 — Semantic compatibility | Required semantic contracts, lineage and parameter roles are agreed; a bounded owned catalogue/checker is proposed. | Minimum useful value types, relation-checking mechanism, whole definition versus narrow parameter bundles, and explicit versus nullable infinity outcome. No blanket nullable ban. |
| C4 — Composition granularity | Single complete responsibility and the three terminal splits are agreed. Admission/CKDpub units and shared/receiving decompositions remain proposed. | Justify remaining units against real adapters, and choose inspectable raw-prelude/body packaging. Neither each instruction nor a whole hidden address routine is the default unit. |
| C5 — Ownership and extension | Proposed closed internal catalogue, explicit provider assembly and consumer network/account provisioning. | Final ownership/extension surface and source lifecycle. No arbitrary public loader is approved. |
| C6 — Setup and runtime | Ordered selected-route execution and abort are agreed. Eager resolution plus a fresh synchronous invocation frame/interpreter is proposed. | Assembly/execution/auditor mechanism, feasibility and unexpected owned-code failure classification. Generated PHP, compilation or caching need benefit/equivalence review; no optimization technique is categorically banned. |

Exact signatures, failure classes/reasons/previous-chain representation,
provisioning and provider/runtime selection also await joint review. Raw public
`mixed` is proposed to preserve original types before admission; it is not an
unbounded internal stage contract. `?string` in the proposed point boundary has
one documented infinity meaning; its final representation remains C3 work.

### Preliminary source schema and immutable form — C2/C5

The proposed C2/C5 form must preserve the agreed independent-flow, ordering
and semantic-connection constraints.
Propose the following **internal** source grammar. `Record` means a PHP list
of exact `[field, value]` pairs; every listed field is required exactly once,
including empty lists where applicable. Unknown or duplicate fields fail.
Identifiers are nonempty ASCII strings matching `[a-z][A-Za-z0-9-]*`, scoped to
their containing catalogue/record. All collections below are lists, not maps
that may have overwritten duplicates. No JSON/YAML parser or executable PHP
file loader is part of this proposal; assembly receives data already in memory.

| Record | Exact fields and constraints |
|---|---|
| Composition | `id`: unique identifier; `contract`: owned catalogue identifier; `inputs`: port records; `parameters`: port records; `steps`: nonempty ordered step records; `outputs`: binding records. Contract input/parameter/output names must match exactly. |
| Port | `name`, `type`: identifiers. The type must exist in the owned catalogue and match the composition contract. A source cannot define new types or change their meaning. |
| Step | `id`; `target`: exact `[kind, identifier]`; `inputs`, `parameters`: binding-record lists. Kinds are `capability`, `composition`, `intent`, or `operation`. For `intent`, the identifier is the required owned subcomposition contract and the intent row must bind this step exactly once. The last kind permits only the receiving-to-generic call in this slice. |
| Binding | `port`: destination name; `source`: one reference below. Bind every required destination exactly once; reject extras. Output bindings use this same record and the composition's declared result ports. |
| Intent target | `step`, `composition`: identifiers. The named step must have an `intent` target, and the selected composition must satisfy its required contract. |
| Intent binding | `network`, `policy`, `composition`: identifiers; `targets`: list of intent-target records; `parameters`: binding-record list. Exactly one row per admitted network/policy tuple, with no duplicate rows even if identical. Each intent-target step binds exactly once; unknown, duplicate and absent targets fail. |

**C2 representation gap identified by the C1 review:** this original local-step grammar
does not yet specify how nested binding identity is authored or scoped. The
probe explores occurrence paths separately. Do not implement the table as a
complete schema until that C2/C6 question is resolved; do not interpret bare
step names as global identifiers.

Source references have only these exact tuple forms:

```text
['input', inputName]
['parameter', parameterName]
['output', earlierStepId, outputPortName]
['field', reference, advertisedFieldName]
['network']  // intent bindings only: the admitted NetworkDefinition
```

In `field`, `reference` is exactly an `input`, `parameter` or `output` tuple;
nested field traversal is disallowed. It projects one immutable field
advertised by its producer's contract,
not an arbitrary property path or method call. For example, the complete-node
contract advertises `publicKey`; its projection retains the node's derivation
origin. No literal-value, cast, expression, callback, environment-variable or
container-service reference exists in this grammar. The network reference resolves
the admitted definition; projection retains its field roles. Protocol constants
such as receiving branch `0` and witness version `0` belong to owned contracts,
not to changeable literals in composition data.

Each step can consume only declared inputs/parameters and outputs of earlier
steps in the same composition. Subcompositions have isolated names and receive
only their declared ports. Reject recursive composition/operation references,
self references, forward references and unknown fields/ports. Intent targets
are resolved before traversal, so they cannot conceal a cycle. Declaration
order is execution order; the loader does not repair or topologically reorder
a rejected declaration. Record-field order is immaterial; step order is not.

For human review, the HASH160 connections can be read conceptually as:

```text
input.bytes -> sha.bytes
sha.digest -> ripe.bytes
ripe.digest -> composition.digest
```

This is a view of the agreed output-to-input relationships, not a source syntax.
The following complete **source-shape example** is one preliminary C2 candidate,
not the definition of C1 or an executable implementation. Its verbosity is an
open design concern; it may be refactored or replaced during joint review:

```php
[
    ['id', 'hash160'],
    ['contract', 'hash160'],
    ['inputs', [[['name', 'bytes'], ['type', 'binary-bytes']]]],
    ['parameters', []],
    ['steps', [
        [
            ['id', 'sha'],
            ['target', ['capability', 'sha256']],
            ['inputs', [[['port', 'bytes'], ['source', ['input', 'bytes']]]]],
            ['parameters', []],
        ],
        [
            ['id', 'ripe'],
            ['target', ['capability', 'ripemd160']],
            ['inputs', [[['port', 'bytes'], ['source', ['output', 'sha', 'digest']]]]],
            ['parameters', []],
        ],
    ]],
    ['outputs', [[['port', 'digest'], ['source', ['output', 'ripe', 'digest']]]]],
]
```

The catalogue defines `sha256` as binary input to a 32-byte digest and
`ripemd160` as binary input to a 20-byte digest, with the delegated behavior
and failures already described below. `hash160` declares no new algorithm.
Its contract requires exactly this ordered connection, not any operation
ending in 20 bytes. A caller-written `contract = hash160` label cannot certify
an unrelated declaration. The semantic check inspects its resolved steps.

The loaded form would contain private immutable `Composition`, `Step`,
`PortBinding` and `IntentBinding` records (names provisional), with references
resolved to owned catalogue entries and intent-target/frame positions. Their constructors
are not a public bypass around admission. Rebuild scalar leaves before retaining
them; reject mutable objects in source data. Provider instances occupy a
separate explicit assembly, not these data records. No universal public
`Stage::run(mixed): mixed` contract is proposed: dispatch calls the narrow
capability interfaces, even if an internal interpreter uses a private frame.

#### C2 authoring laboratory — exploratory findings

**Investigation authorized; design decisions pending joint agreement.** The
[Issue #45 checkpoint](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/45#issuecomment-6476912656)
retains the six questions raised by the human pair and agent. The original
pair-list form above is a baseline to challenge, not the default winner.

The [probe](probe-authoring.php) compares three composition-source fixtures:
[pair lists](authoring-lab/pairs.php), [fixed declarative records](authoring-lab/records.php)
and a [restricted builder](authoring-lab/builders.php). Each contains the same
nine declarations: HASH160, the proposed admitted-request Bitcoin body, its
three terminals, and synthetic repeated-composition cases. References remain
inspectable data; constructors/builders execute no provider or protocol work.
The typed candidates obtain declared input/parameter signatures from the
fixture catalogue rather than repeat them in every flow. All candidates enter
the same bounded structural admission routine; this is not independent
semantic validation by three implementations.

Executed on **PHP 8.3.36 and 8.4.27: 73 controls pass per runtime**, with equal
results apart from the PHP version. Each form resolves eight cases: HASH160,
the six initial network/policy combinations and one independent-sibling case.
Resolved capability order, source connections, parameters and exported origins
agree across forms. No request or capability implementation is executed.

| Question | Observed evidence | Still open |
|---|---|---|
| Pair-list verbosity | Complete fixtures occupy 211 lines / 8,676 bytes for pairs, 148 / 6,089 for records, and 113 / 4,764 for builders. | These figures reflect this formatting and exclude adapter/catalogue/builder machinery. They measure neither readability nor runtime performance; no winner follows. |
| Fixed fields versus entry collections | Fixed-record direct calls reject unknown/repeated named arguments; ordered collections retain repeated flow, step and binding identifiers for rejection. A builder that appends also retains duplicates. | Final construction surface, owned failure normalization and author-location diagnostics. An associative map or indexed builder may already have discarded a duplicate. Named unpacking of such a map cannot restore it; native parameter typing also coerces scalars from a weak caller. |
| Repeated contract metadata and naming | Catalogue signatures remove repeated port/type annotations while preserving these structural results. Pair-source annotations that contradict the catalogue fail. | Who owns/versions those contracts and what signatures should be explicit for review. Existing step and port names remain in every candidate; implicit handles, reduced naming and final semantic types are not evaluated. Resolving a known contract does not certify HASH160 semantics. |
| Explicit emptiness | Pair fixtures spell 21 empty arrays; typed fixtures spell none. Constructors establish empty entry collections, but the common routine still rejects omitted required inputs/network bindings and extra parameters. | Whether this convenience belongs in the final source form. It supplies no required parameter value, successful result or failure fallback. |
| Nested binding identity | Flat occurrence paths and contextual binding trees resolve equally. Siblings cannot borrow bindings; duplicates, missing and unused targets fail. Inserting a wrapper requires updating both forms; old contextual bindings also fail. | Readability and refactoring trade-offs, source ownership and final notation. Contextual grouping does not remove structural coupling, and paths are not globally unique bare step names. |
| Validated immutability | Scalar snapshots detach caller aliases; changing exported arrays or reusing a builder does not change an admitted plan. Mutable leaves and ordinary direct/serialized construction bypasses are rejected. | Final record layout and complete admission coverage. A readonly-array negative control still changes through an external alias. No protection against hostile in-process reflection is claimed. |

**Limits:** fixture catalogues/adapters are trusted; only the selected root's
closure is expanded, not the entire catalogue or every advertised intent in
one atomic assembly. Field checks are deliberately shallow and structural.
Intent/provisioning rows remain driver fixtures, not a complete three-way
authoring comparison of those records.
A negative control admits a falsely labelled SHA256→SHA256 flow as `hash160`,
demonstrating that passing these controls is **not** C3 semantic qualification.
Malformed recursive PHP containers and arbitrary object graphs are not
qualified. No codec/crypto bytes, provider, production ingress, executor,
performance, consumer API or runtime policy is selected. C1's complete setup,
semantic compatibility and failure-abort obligations remain mandatory.

Reproduce from the repository root on either recorded PHP runtime:

```sh
php docs/pips/PIP-0003/M05/probe-authoring.php
```

**Round two — named references, both candidates retained.** The human pair
prefers clearer declarative records and authorized comparison of associative
records with typed named references; neither preference selects a final API.
Replacing `new` with a static factory changes construction syntax, not the
separation between authored data and the validated internal representation.

The [named-reference probe](probe-named-references.php) compares complete
[associative declarations](authoring-lab/associative.php) and
[named typed records](authoring-lab/named-records.php), using the research-only
[reference variants](authoring-lab/reference-records.php). Both contain the same
nine declarations and resolve the same eight cases as round one. The fixtures
were derived from the earlier declarations, not independently sourced semantic
vectors. Arrays use fixed named fields and ordered entry lists; typed records
use named constructor arguments and closed reference variants. Neither source
performs capability work. Both normalize into the same structural admission
routine; they do not require different execution semantics.

**PHP 8.3.36 and 8.4.27: all 73 baseline and 49 additional controls pass per
runtime**, with equal reports apart from version. These counts include negative
controls demonstrating limitations, not 122 independent correctness proofs.

| Property | Associative records | Named typed records |
|---|---|---|
| Reference readability | `kind`, `step` and `port` are explicit data fields. | `StepOutput(step: ..., port: ...)` states the variant and fields. |
| Unknown/missing fields | The schema adapter rejects them. | Direct calls fail with native PHP errors; domain normalization remains open. |
| Repeated fixed field | PHP overwrites before validation; the control demonstrates the loss. | Repeated direct named arguments fail, but unpacking an already overwritten map cannot recover the loss. |
| Repeated flow/step/binding | Ordered lists preserve occurrences for rejection. | Ordered lists preserve occurrences for rejection. |
| Scalar input | Adapter checks the original value without scalar coercion. | Strict calls reject wrong scalar types; a weak caller can coerce before admission. |
| Source validity | Unknown variants, callable leaves and explicit null fail. | Closed reference types reject callable/null sources; a valid `StepOutput` can still name a nonexistent step. |
| Omission and ordering | Omitted binding collections become empty; required connections still fail. Step order is never repaired. | Same behavior; constructor defaults supply collections, not required values. |

The associative fixture contains only literal arrays and scalar values. Loading
it with PHP `require` is still code execution: this experiment does not approve
a production loader or claim to restrict arbitrary PHP authoring. Typed
constructors here only build data; no arbitrary callable is an admitted source.
The existing semantic, catalogue-coverage, recursive-container, diagnostic and
performance limits remain. No prior duplicate-rejection requirement is waived.

```sh
php docs/pips/PIP-0003/M05/probe-named-references.php
```

**Next joint discussion:** compare the first HASH160 declaration in both new
fixtures, then choose which authoring boundary to investigate further: data-only
fixed records with schema validation, or typed construction with named fields.
Discuss fixed-field duplicate observability and scalar preservation explicitly
before selecting either. Contract-derived signatures, collection omission,
scoped-binding notation and final admitted representation remain open. C3–C6
dependencies are exposed, not settled here.

### Preliminary complete composition and intent bindings — C3/C4

The following tables specify the other declarations at port level. They are
readable expansions of the grammar, **not additional approved PHP APIs**.
`$name` denotes an input, `@name` a parameter and `step.port` an earlier output;
`derive.node.publicKey` abbreviates the permitted `field` reference. Every
unlisted parameter/target list is explicitly empty when encoded. Each table row
is one step in execution order.

Ports name semantic connections, not new overloads of the PHP signatures
below. For example, dispatch to `ExtendedPublicKeyAdmission::admit` extracts
the admitted request's original key text, definition and policy into its three
arguments. A fixed owned invocation mapping performs this projection; source
data cannot name a PHP class/method or invoke an arbitrary function. Similar
mapping supplies `CkdPublicDerivation` with the complete node and checked path.
Concrete internal helper names remain part of C4 review.

The proposed `public-address` declaration takes one `request` input of type
`admitted-public-request`, has an `encode` step requiring the
`key-hash-address` subcomposition contract, and a `network` parameter of type
`network-definition`. Each admitted intent binds this step directly to one
terminal composition. The operation's raw
request capture preserves original argument types; it performs no decoding
or coercion.
The public method cannot look up a plan using unvalidated caller values.
Its fixed admission prefix validates definition, path, policy and key-text
shape in the documented order. Only then does it select the matching binding
and execute this shared body, whose input is the admitted `request`:

This body is local to the three current policies. Its HASH160 is not a
requirement for other complete policy flows. Names below are local example
identifiers, not reserved executor stages or a topology mandated by C1.

| Step / target | Named inputs and parameters | Output and required meaning |
|---|---|---|
| `admit` / capability `extended-public-key-admission` | `request = $request` | `node`: complete imported node admitted for the request's explicit intent. Internally: checked decode/common metadata, version eligibility, point validity, intent compatibility, in that order. |
| `depth` / capability `remaining-depth` | `node = admit.node`, `request = $request` | `path`: the unchanged request path, checked against this imported node's remaining depth. No HMAC or ECC derivation. |
| `derive` / capability `ckd-public-path` | `parent = admit.node`, `path = depth.path` | `node`: complete final node from exactly that parent and path. No nullable/partial node. |
| `keyhash` / composition `hash160` | `bytes = derive.node.publicKey` | `digest`: HASH160 of that derived compressed public key. |
| `encode` / intent-bound `key-hash-address` | `keyHash = keyhash.digest`; `network = @network` | `address`: selected policy's complete address under the same network binding. |
| `result` / capability `public-address-result` | `request = $request`, `address = encode.address` | `result`: the complete immutable public result; output binding is `result.result`. |

**Representation detail still preliminary:** represent the fixed raw admission
prefix as an operation-contract prelude, referenced by `public-address`, and
the table as its admitted body. Its catalogue entry must show the prelude and
body together; it must not disguise admission as an implicit executor behavior.
The public operation contract binds the raw entry signature to that exact
prelude and body. The body cannot bypass the prelude through a public API.
An alternative is a top-level operation declaration containing both;
that would need an explicit selection construct. This prelude/body boundary
is a substantive C4 discussion point, not a settled exception to inspectability.

Each terminal implements `key-hash-address`: input `keyHash`, parameter
`network`, output `address`. It receives the immutable definition and projects
only the parameter roles it needs. Here `@network.p2pkhVersion` abbreviates
`['field', ['parameter', 'network'], 'p2pkhVersion']`; the other fields follow
the same rule. The catalogue distinguishes `p2pkh-version`, `p2sh-version` and
`segwit-hrp` by role and origin, not merely PHP string shape. Passing the whole
semantic definition keeps the terminal-step contract uniform but exposes more parameters
than each terminal needs; narrower role-specific bundles are an alternative
to discuss under C3. Neither choice permits an arbitrary parameter bag.

| Declaration | Ordered steps, exact input connections and output binding |
|---|---|
| `p2pkh-address` | `payload`: owned `p2pkh-payload(keyHash = $keyHash; version = @network.p2pkhVersion)` returns the 21-byte P2PKH payload. `encode`: delegated `base58check-encode(payload = payload.payload)` returns `address`; export `encode.address`. |
| `nested-witness-address` | `script`: owned `witness-v0-keyhash-script(keyHash = $keyHash)` returns the 22-byte `0014` redeem script. `scripthash`: composition `hash160(bytes = script.script)` returns `digest`. `payload`: owned `p2sh-payload(scriptHash = scripthash.digest; version = @network.p2shVersion)` returns the 21-byte P2SH payload. `encode`: the same `base58check-encode(payload = payload.payload)`; export `encode.address`. |
| `native-witness-address` | `encode`: delegated `segwit-v0-keyhash-encode(program = $keyHash; hrp = @network.segwitHrp)` returns `address`; export `encode.address`. The direct `keyHash -> program` connection is valid only for the selected P2WPKH terminal and the derived-key HASH160 lineage. |

The payload/script constructors own protocol meaning and fixed layout; they
are not configurable byte concatenators or reimplementations of codecs.
The witness-program role is a checked connection, not a standalone executable
conversion. Setup must verify that the source is the derived-key HASH160 bound
to the selected P2WPKH intent, and that the HRP comes from the selected
definition's SegWit role. Runtime checks still reject an invalid digest shape
at the HASH160/encoder boundaries. No extra failure or successful output is
introduced between those boundaries; M06/M07 must challenge this preserved
admission and failure order through the actual validator and executor.

Proposed intent-binding data, with no implicit rows or policy inference:

| Network | Policy | Shared composition | `encode` step target | Parameter projected inside terminal |
|---|---|---|---|---|
| `mainnet` | `p2pkh` | `public-address` | `p2pkh-address` | `version <- network.p2pkhVersion` |
| `testnet` | `p2pkh` | `public-address` | `p2pkh-address` | `version <- network.p2pkhVersion` |
| `mainnet` | `p2sh-p2wpkh` | `public-address` | `nested-witness-address` | `version <- network.p2shVersion` |
| `testnet` | `p2sh-p2wpkh` | `public-address` | `nested-witness-address` | `version <- network.p2shVersion` |
| `mainnet` | `p2wpkh` | `public-address` | `native-witness-address` | `hrp <- network.segwitHrp` |
| `testnet` | `p2wpkh` | `public-address` | `native-witness-address` | `hrp <- network.segwitHrp` |

In the preliminary pair-list notation, the shared `encode` step has
`['target', ['intent', 'key-hash-address']]`. A P2PKH intent row binds it with
`['targets', [[['step', 'encode'], ['composition', 'p2pkh-address']]]]`.
The step ID identifies the existing junction; no separately declared slot or
default target is involved. These tuples illustrate the agreed relationship,
not an accepted C2 authoring syntax.
This local-only target example is incomplete for the agreed nested-binding
fitness requirement. Repeated names such as `encode` in different compositions
must not collide or capture one another. The source format must identify the
intended scope; the probe's occurrence paths are one unaccepted notation for
doing so, not a requirement to expose verbose paths in human-authored data.

Every intent row supplies the shared composition's `network` parameter using
the binding record `[['port', 'network'], ['source', ['network']]]`.
The last column shows the terminal's subsequent projection, not another
intent-record field. At request admission the supplied definition must be
semantically equivalent to that binding's definition; identifier equality alone
does not admit different parameters. Equivalent independent instances remain valid.
No repeated policy implementation exists per network. Selection yields one
resolved plan; all other policy plans remain unexecuted. The future Signet
counter-proof supplies a test-only definition and three analogous bindings
to the same compositions, without expanding the shipped catalogue.

These six rows describe the completed M07 surface. An M06 assembly advertises
only its implemented first policy on both networks and explicitly rejects the
remaining policies. It must validate all bindings it advertises, rather than
claiming all six network/policy combinations with missing providers or lazy failures.
Adding the remaining supported rows is a later implementation increment, not
a runtime fallback or a change to the target semantics.

Receiving has its own raw admission prelude (network, account, index, policy)
and one admitted body, bound to the immutable source and generic operation
at assembly. It has no intent-bound terminal step and takes no caller branch parameter:

| Step / target | Named connections | Output and required meaning |
|---|---|---|
| `select` / capability `account-key-selection` | `request = $request`; source is an explicit assembly dependency | `key`: exact tuple's original serialized key, or missing-mapping failure. |
| `request` / capability `receiving-public-request` | `request = $request`, `key = select.key` | `generic`: raw generic arguments with the same network/policy, selected key and exact `[0, index]`; no decoding or derivation. |
| `generic` / operation `derive-public-address` | `request = request.generic` | `result`: invoke the same public operation including its full raw admission prelude and selected declared body. No admitted-node shortcut. |
| `result` / capability `receiving-address-result` | `request = $request`, `generic = generic.result` | `result`: complete address and original logical account/branch/index, after checking generic context/path agreement; export `result.result`. |

The receiving declaration is not replicated for six tuples. It delegates
selection of the generic body to the same explicit intent table. CKDpub owns bounded iteration over its exact path; this schema adds no loops, branching predicates, retry
instructions or arbitrary operation calls.

### Preliminary semantic admission and execution — C3/C6

Propose three admission passes followed by one execution rule:

1. **Source shape and references:** validate all records and duplicates before
   normalization; resolve catalogue references and intent-target bindings, reject cycles,
   missing/extra ports, forward references and unavailable required bindings.
   No cryptographic/codec work occurs. Check every shipped intent binding,
   not merely the first request's branch.
2. **Operation semantics:** check the resolved connections against the owned
   operation contract. Check each prelude, full-node and depth dependencies,
   selected terminal's protocol meaning and exact parameter roles, and result
   context. Reject unused executable steps and extra branches as well as
   missing steps. Contract labels alone never pass this gate.
3. **Explicit assembly:** bind one implementation per required lower contract
   and check its required environment. Bind the receiving source separately;
   its eager admission may invoke providers, as specified below. Return the
   immutable usable operation only after every required gate succeeds. No
   partially configured operation is returned. A generic-only assembly does
   not need an account source; receiving reuses an already valid generic one.
4. **Invocation:** preserve raw arguments, run the documented prelude, select
   the resolved binding, allocate a fresh private frame, and execute its steps
   synchronously. Publish a step's outputs to that frame only after successful
   return and boundary checks. A subcomposition must finish before the next
   step; no speculative, parallel or unselected work is scheduled. Expose only
   the final immutable result after complete success.

The semantic validator can remain bounded to known producers and consumers.
For example, retain these relations as private loaded-plan metadata, not as
caller-writable labels or a public generic type system:

| Produced value | Relation that the consuming connection must establish |
|---|---|
| `depth.path` | The same request path, checked against `admit.node`, not another node of the same depth. |
| `derive.node.publicKey` | Projection from the complete result of exactly `ckd-public-path(admit.node, depth.path)`, never the imported key or a separately supplied 33-byte string. |
| `keyhash.digest` | Exactly SHA-256 then RIPEMD-160 of that projection, with binary outputs. |
| `scripthash.digest` | Exactly HASH160 of the witness-v0 key-hash script built from that same `keyhash.digest`; not the key hash relabeled as a script hash. |
| Terminal parameters and result | Values from the selected definition's correct roles; address and returned coordinates belong to the same admitted request. Equal version bytes or hash widths do not establish this relation. |

Catalogue entries own these compatibility/lineage rules. Unfold known
subcompositions for checking while retaining their reusable declaration
identity; do not duplicate their behavior in a second handwritten executor.
This is a finite validation proposal, not a claim of a general theorem prover.
The exact division between reusable contract metadata and small owned
validators still needs implementation feasibility review. The validator
must not grow into a second address-generation implementation.

At runtime, actual capability arguments/results still undergo their promised
checks. Static lineage proves intended connections, not that a defective
provider computed the right point/hash/address. No extra encode/decode
round trip is introduced to pretend otherwise. Independent expected values
and real-provider replacement tests remain necessary.

When any stage throws, unwind this invocation and its callers immediately.
The frame cannot yield a public partial result, and result constructors have
not run if their predecessors failed. The executor does not catch an owned
failure to replace its reason, switch a binding or resume. Vendor failures
are normalized in the selected adapter before reaching it. Setup data failures
use `INVALID_DEFINITION`; source and environment/provider failures retain the
meanings below. There is no `on_error`, nullable address or default output in
the schema. Aborting guarantees no subsequent library stage, not rollback of
arbitrary provider side effects or memory erasure; no persistent writes or
application callbacks are introduced by this assembly.

**Unresolved failure-design consequence:** the earlier reason enum covers
caller/configuration faults and delegated provider/environment failures, but
does not explicitly classify an unexpected defect in the owned interpreter or
result construction. A candidate refinement is an owned `INTERNAL_FAILURE`
reason with the same fixed-message/no-previous containment, preserving already
owned exceptions while containing unexpected implementation exceptions at the
operation boundary. This is not selected or added to the earlier enum by this
draft. Resolve it under C6 before freeze; do not silently blame a caller or a
provider for an owned-code defect, leak vendor details, or resume execution.

### Preliminary challenge cases and limits

These are design checks and proposed downstream tests, **not executed results**.
They identify where the model should fail and what the eventual validator and
executor must demonstrate in addition to the existing acceptance list.

| Mutation or fault | Required outcome and responsible gate |
|---|---|
| Bind P2SH payload's `scriptHash` directly to `keyhash.digest` | Setup semantic rejection despite both being 20 bytes. |
| Hash `admit.node.publicKey` instead of `derive.node.publicKey` | Setup lineage rejection; a valid hash of the wrong key cannot satisfy the operation. |
| Use the P2PKH network field for the P2SH terminal | Setup role rejection even in a fixture with equal byte values. |
| Bind P2WPKH's program input to an unrelated 20-byte digest or wrong-HRP role, or insert a pass-through `program` execution step | Setup semantic/role or extra-step rejection; the direct connection is restricted to the derived-key HASH160 and selected SegWit HRP. |
| Duplicate an intent row, bind an incompatible terminal to `encode`, or create a recursive subcomposition | Setup rejection before provider work; no first-row-wins or lazy repair. |
| Add an unused step that executes another policy or validates the point before version eligibility | Reject extra execution or the broken admission contract. Internal key-admission order also needs mixed-invalid runtime tests; the loader cannot prove adapter code from a label. |
| Resolve an independent synthetic policy root with compatible shared resources | Validate and execute it through the same setup/execution mechanism without the current key-hash root, a fake key hash, hidden policy dispatch or candidate impersonation. Reject an incompatible root/input before invocation. No public P2TR admission is added. |
| Fail key admission, depth, a first/middle/final CKDpub edge, hash or encoder | Preserve the documented reason; observe no later stage, other policy/provider/index, or result construction. Instrument inside cohesive capabilities as well as at composition steps. |
| Fail exact receiving lookup or the generic call | No alternate tuple/key; generic admission never runs after failed lookup, and receiving result construction never runs after failed generic derivation. |
| Return a plausible but wrong digest/address from a provider | May pass runtime shape checks; independently anchored qualification must expose it. Do not claim static schema validation catches it. |
| Supply equivalent reordered definitions, then mutate the caller's source arrays | Equivalent bindings and results, with no retained alias changing the admitted plan. Reordering steps remains a different declaration and is checked separately. |

### Exploratory reuse fitness challenges — outside PIP-0003 delivery

These checks support the composition direction; they add no admitted policy,
network, algorithm implementation or selected provider. All fixture plans are
symbolic. Actual schema validation, semantic lineage, provider behavior,
cryptographic/codec correctness and O06 precedence need their own qualification.

| Evidence | Observed result and exact limit |
|---|---|
| [Terminal-junction comparison](probe-terminal-junction.php) | Named-slot and direct binding each admit six intents and reject 11 invalid fixtures, with selected-route/abort traces. A slot adds an identifier without an extra demonstrated guarantee at this local junction; direct binding is agreed. No C2 syntax or general slot ban follows. |
| [Divergence probe](probe-bitcoin-policy-divergence.php), earlier fixtures | Six reconstructed flat plans preserve normalized producer/input/parameter/export graphs and deterministic token results under nested reorganization. An eight-intent extension preserves those six plans exactly; 48 injected failures stop at exact prefixes and 15 invalid fixtures fail setup. The baseline uses an exploratory earlier frontier, not the old flat shape unchanged. |
| Same probe, name/depth/scope | Renamed steps and additional early/middle/late selection layers retain capability sequences. Repeated sibling occurrences retain independent bindings and exported origins; missing bindings cannot borrow a sibling target. No hard-coded stage names or fixed depth in the compiler/execution routine. Occurrence-path authoring is experimental. |
| Same probe, neutral recurrence | Five plans across four roots reuse resources before/after divergent work, including a differently shaped operation with no Bitcoin shell. Twenty exact-prefix aborts and two incompatible semantic reuse rejections pass. These are not Ethereum/Cardano/Solana models. |
| Same probe, independent Bitcoin policy roots | Delete the old common root and both selected inner routes. The same compiler resolves eight complete-flow plans with equal normalized graphs/token results, 48 further exact-prefix aborts and two incompatible root/input rejections. The shared public-derivation grouping is illustrative C4 material; it does not combine its responsibilities into one capability. |

The preserved graph/token equality checks wiring, not independently correct
protocol results. The fixture's hypothetical admission does not bypass actual
O02. Its `hash-fixture` is the final derived-key **address** hash boundary,
not a trace of hashes inside CKDpub.

### Synthetic terminal-junction comparison — C1 decision evidence

The comparison's scope is the delivered policies' single local `encode`
junction. Both candidates keep the same semantic input, network binding and
terminal contracts; direct binding identifies the already-declared step instead
of introducing another slot name. Duplicate bindings remain observable until
validation, no prefix/default selects intent, and failure never chooses another
terminal. The [initial interim record](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41#issuecomment-6390789895)
and [full comparison at the pre-consolidation revision](https://github.com/lucas-rosa95/paycrypto-me-primitives-php/blob/0c3f533af4a02a3336047f7bba82d8d4e660c9e4/docs/pips/PIP-0003/M05/PHP-CONTRACTS.md#synthetic-terminal-junction-comparison--c1-decision-evidence)
retain the exploratory detail. This settled C1's local relation, not C2 syntax.

### Bitcoin policy divergence counter-proof — research evidence

Taproot's post-derivation route consumes a constructed output key and uses
witness-v1/32 Bech32m, rather than the final derived-key HASH160 address input.
[BIP341](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0341.mediawiki)
and [BIP350](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0350.mediawiki)
anchor that challenge. [BIP86 vectors](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0086.mediawiki#test-vectors)
use xpub, while initial [O02](../M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility)
admits xpub only for P2PKH. A correct future route alone cannot enable the full
operation; the admission/provenance contract still needs review. Version bytes
cannot prove account ancestry.

<details>
<summary>View the current O02 rejection and future admission gate</summary>

![Current O02 rejects a future P2TR request; future compatibility needs its own reviewed contract](diagrams/taproot-admission-counterexample.png)

</details>

Bitcoin Cash needs an independent flow with its own admitted context and
CashAddr parameters. Compatible key-hash-producing resources can be shared;
[CashAddr](https://github.com/bitcoincashorg/bitcoincash.org/blob/3e2e6da8c38dab7ba12149d327bc4b259aaad684/spec/cashaddr.md)
encoding remains delegated and unqualified in this slice. Shared byte widths
or matching version bytes do not establish shared admission semantics.

![Independent Bitcoin and Bitcoin Cash declarations reference compatible resources, without transferring execution between candidates](diagrams/independent-candidate-resource-reuse.png)

Dashed arrows above mean resource references, not flow transfer. Independently
justified neutral subcompositions may be reused; local components need not be
extracted merely to make candidates appear similar.

The test-only Signet profile reuses testnet address parameters and unchanged
composition behavior. Equal key/path/policy/encoding inputs yield equal address
text, while the explicit result context remains distinct. It does not infer
chain provenance or add Signet support; see [M02's future-profile counter-proof](../M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility).

<details>
<summary>View testnet / test-only Signet parameter reuse</summary>

![Testnet and test-only Signet reuse one composition with equal encoding inputs and distinct admitted context](diagrams/parameter-variation-fitness.png)

</details>

<details>
<summary>View the neutral resource-reuse counter-proof</summary>

![Neutral independent roots reuse common resources before and after their own middle work](diagrams/resources-after-divergence.png)

</details>

The neutral recurrence view shows alpha's two selectable middle routes and
beta/gamma's independent roots. Common resource names denote separate invocations
with each flow's own values; arrows stay within that flow. Delta's different
root shape is covered by the probe rather than forced into this picture.
Sharing code/resources does not promise shared instances, cached outputs or
mutable execution state. Each throw aborts only its invocation and callers,
without later stages or a successful partial result.

Reproduce from the repository root with PHP 8.3+:

```sh
php docs/pips/PIP-0003/M05/probe-terminal-junction.php
php docs/pips/PIP-0003/M05/probe-bitcoin-policy-divergence.php
```

Earlier names/depth dispatch and the mandatory shared Bitcoin root were
exploratory candidates, not current requirements. Their exact history remains
in [the original probe at `03c6a2b`](https://github.com/lucas-rosa95/paycrypto-me-primitives-php/blob/03c6a2b/docs/pips/PIP-0003/M05/probe-bitcoin-policy-divergence.php),
[the superseded shared-root proposal at `0c3f533`](https://github.com/lucas-rosa95/paycrypto-me-primitives-php/blob/0c3f533af4a02a3336047f7bba82d8d4e660c9e4/docs/pips/PIP-0003/M05/PHP-CONTRACTS.md#earlier-shared-root-decomposition--superseded)
and [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41).
C1 closure approves the independent-flow relationships and bounded evidence;
concrete C2–C6 mechanisms and actual future capabilities remain open.

### Extended-public-key-to-address fitness — contract cross-check

**Review scope (2026-10-28):** the user's expanded diagram starts from serialized
extended public keys rather than already-derived points. It exposes admission,
path handling, CKDpub, full-node metadata and address construction. It is useful
counter-proof input, not an approved diagram to implement unchanged. The
following interpretations apply existing M02 O01–O06 decisions; they add no feature,
provider qualification or new C1 approval gate. C2 remains next.

| Expanded diagram element | Required interpretation or correction | Existing basis |
|---|---|---|
| Invalid CKDpub child → try the next index | Replace with operation failure at the exact requested index. If the left scalar is at/above the curve order or the child is infinity, abort without another index, later path level, address or partial result. A zero left scalar remains valid and still updates the child metadata. | [M02 O01](../M02/M02-PROTOCOL.md#o01--public-derivation-and-invalid-children). BIP32 describes advancing; the accepted Primitives contract deliberately makes that a separate consumer request. |
| “xpub” for every input context | Use “serialized extended public key” when naming the general family. Under current O02, mainnet P2WPKH admits the zpub version, not literal xpub; BIP84 specifies zpub/vpub. BIP86 publishes xpub vectors, but current P2TR admission remains unsupported. Bitcoin Cash needs its own future admission context, not Bitcoin's allowlist. | [M02 O02](../M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility), [BIP84 versions](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0084.mediawiki#extended-key-version) and [BIP86 vectors](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0086.mediawiki#test-vectors). |
| Define change/index after decoding | Generic derivation receives an explicit nonempty relative path; it does not choose a wallet branch. The receiving entry point always supplies `[0, index]`; change branch `[1, index]` is expressible through the generic operation, not a new receiving option. Preserve raw request checks before decode and remaining-depth checks after imported-node admission, before HMAC/derivation. | [M02 O03–O06](../M02/M02-PROTOCOL.md#o03o06--minimal-operation-boundary) and the [proposed concrete validation order](#failure-contract-and-validation-order). |
| Derived child represented only as public point K | Preserve the complete derived node: key, chain code, version/context and metadata including depth, immediate-parent fingerprint and child number. Each new child number equals the requested component; successful result context retains the exact requested relative path. | [M02 O01](../M02/M02-PROTOCOL.md#o01--public-derivation-and-invalid-children) and [complete-node connections](#concrete-flow-and-semantic-connections). |
| One common decode/validation block for BTC and BCH | Shared checked codecs and curve facilities do not establish shared admission semantics. A flow must explicitly apply its own admitted definition, version/intent compatibility and ordered failures; matching serialization bytes cannot infer protocol, policy or ancestry. Reuse a larger admission composition only where its full contract is compatible. | [M02 O02](../M02/M02-PROTOCOL.md#o02--version-network-and-policy-compatibility) and the [agreed independent-flow direction](#independent-policy-flow-declarations--agreed-c1-direction). |

**A deeper reuse point:** the complete BIP32 derivation resource uses HASH160
of the immediate parent's compressed public key to form its four-byte parent
fingerprint. That work can occur while deriving a key for a future Taproot
address. It is distinct from HASH160 of the final derived key used as an
address input by P2WPKH/P2PKH. “Taproot does not use the key-hash address route”
therefore must not be read as “the entire extended-key-to-Taproot operation
never uses HASH160.” [BIP32 key identifiers and serialization](https://github.com/bitcoin/bips/blob/100ba872c808946155b1eeb8c4568058f2eea0a7/bip-0032.mediawiki#key-identifiers)
provide the fingerprint basis; [M02 O01](../M02/M02-PROTOCOL.md#o01--public-derivation-and-invalid-children)
already requires that metadata. The probe's `hash-fixture` marker represents
the final derived-key address hash only; it does not expose or execute CKDpub
internals. Its P2TR trace cannot establish the absence of hashes inside derivation.

The Taproot tagged hash and ordinary SHA-256 within HASH160 can also reference
the same qualified SHA-256 resource, while preserving different messages,
contracts and outcomes. Shared curve machinery likewise does not make BIP32
child construction and Taproot output-key construction the same semantic
operation. Their responsibilities and invalid-result handling stay explicit.
None of these observations requires every algorithm call to become a declared
flow step; concrete granularity remains governed by the C4 responsibility criteria.

The expanded picture strengthens the existing architectural direction:
independent complete flow declarations may reference compatible resources
throughout their own chains, including internal derivation resources. Common
boxes summarize reusable behavior applied to separate values; they do not join
accounts, execution state or flow declarations into one mandatory root. Source
account ancestry is a provisioning assertion, not something version bytes or
historical depth can prove. No private or newly hardened derivation is added.

This cross-check does not run a full Taproot or CashAddr chain. The symbolic
probe and earlier M03/M04 real-provider research retain their recorded scopes;
actual assembly, semantic lineage and independently anchored complete results
remain downstream obligations. The supplied external diagram's retry behavior was a transcription of BIP32,
not a request to revise O01. The corrected admission/derivation view below
uses the accepted no-advance contract.

### Visual counter-proofs for joint review

The eight figures are embedded at the relevant contract or fitness discussion.
MMD is editable structure, SVG preserves detailed text, and PNG supports visual
inspection. Boxes do not mandate PHP classes or one capability per box. Branches
show alternative bindings, not concurrent policies; shared-resource references
do not join execution data. Textual decision status takes precedence over color.

| View | MMD | SVG | PNG |
|---|---|---|---|
| Assembly and invocation | [MMD](diagrams/composition-lifecycle.mmd) | [SVG](diagrams/composition-lifecycle.svg) | [PNG](diagrams/composition-lifecycle.png) |
| Admission and exact derivation | [MMD](diagrams/admission-and-exact-derivation.mmd) | [SVG](diagrams/admission-and-exact-derivation.svg) | [PNG](diagrams/admission-and-exact-derivation.png) |
| Three current Bitcoin policies | [MMD](diagrams/bitcoin-current-policy-flow.mmd) | [SVG](diagrams/bitcoin-current-policy-flow.svg) | [PNG](diagrams/bitcoin-current-policy-flow.png) |
| Independent policy flows | [MMD](diagrams/taproot-policy-divergence.mmd) | [SVG](diagrams/taproot-policy-divergence.svg) | [PNG](diagrams/taproot-policy-divergence.png) |
| Future P2TR admission gap | [MMD](diagrams/taproot-admission-counterexample.mmd) | [SVG](diagrams/taproot-admission-counterexample.svg) | [PNG](diagrams/taproot-admission-counterexample.png) |
| Bitcoin Cash independent reuse | [MMD](diagrams/independent-candidate-resource-reuse.mmd) | [SVG](diagrams/independent-candidate-resource-reuse.svg) | [PNG](diagrams/independent-candidate-resource-reuse.png) |
| Signet parameter variation | [MMD](diagrams/parameter-variation-fitness.mmd) | [SVG](diagrams/parameter-variation-fitness.svg) | [PNG](diagrams/parameter-variation-fitness.png) |
| Reuse after divergence | [MMD](diagrams/resources-after-divergence.mmd) | [SVG](diagrams/resources-after-divergence.svg) | [PNG](diagrams/resources-after-divergence.png) |

<details>
<summary>Diagram rendering and reproducible artifacts</summary>

Each adjacent MMD/SVG/PNG set is synchronized to its source revision. Five
sets retain their 2026-10-27 exports; the independent-policy view and
two lifecycle/admission views use 2026-10-28 sources. All use the local
`mermaid` MCP tool
`generate_mermaid_diagram`, `theme: default`, `backgroundColor: white` and
`outputType: svg`. The configured server image is pinned at
`sha256:8f1217b11502db25c34b05633bf9e338764f99724536ef5e3e49541a73e853c1`;
its package version is 0.2.1 and Mermaid is 11.12.0.

Sources disable HTML labels. The repository's
[artifact exporter](../../../../tools/skills/diagram-artifacts/scripts/export_artifacts.py)
finalized native-text SVGs with explicit dimensions, preserved spacing and a
white canvas, then rasterized them using `rsvg-convert` 2.63.2 at 1280 pixels
wide. `pngquant` 3.0.3 used quality 90–100, speed 1, no dithering, stripped
metadata and skip-if-larger. Palette quantization is lossy; SVG and MMD retain
the detailed representation. Each final PNG was visually inspected after
independent librsvg rendering for labels, arrowheads, grouping and correspondence
with its source. These tools are documentation facilities, not consumer
dependencies. Rendering success does not establish protocol correctness.

| Diagram basename | PNG dimensions | Fresh raster to optimized PNG |
|---|---|---|
| `bitcoin-current-policy-flow` | 1280 × 2031 | 250.7 → 75.0 KiB |
| `taproot-policy-divergence` | 1280 × 1399 | 216.8 → 63.8 KiB |
| `taproot-admission-counterexample` | 1280 × 1414 | 247.4 → 74.9 KiB |
| `independent-candidate-resource-reuse` | 1280 × 696 | 126.2 → 39.2 KiB |
| `parameter-variation-fitness` | 1280 × 1800 | 277.7 → 85.8 KiB |
| `resources-after-divergence` | 1280 × 1133 | 150.2 → 46.1 KiB |
| `composition-lifecycle` | 1280 × 2321 | 347.6 → 105.7 KiB |
| `admission-and-exact-derivation` | 1280 × 3777 | 445.5 → 137.0 KiB |

To regenerate, send an entire adjacent `.mmd` source to the same MCP tool,
save its returned native-text SVG outside the repository as a raw export,
then run from the repository root:

```sh
python3 tools/skills/diagram-artifacts/scripts/export_artifacts.py \
  /tmp/diagram-raw.svg --output-dir docs/pips/PIP-0003/M05/diagrams \
  --stem diagram-basename
```

Use the exact source basename and repeat visual inspection. Historical M01
diagrams describe their original draft and were not modified by this increment.

</details>

## Entry points and results

Proposed entry-point signatures; namespace placement remains open under the
capability responsibilities above (`Bitcoin\Address` was the initial sketch):

```php
final class DerivePublicAddress
{
    public function derive(
        #[\SensitiveParameter] mixed $extendedPublicKey,
        #[\SensitiveParameter] mixed $relativePath,
        mixed $network,
        mixed $policy,
    ): PublicAddressResult;
}

final class DeriveReceivingAddress
{
    public function derive(
        #[\SensitiveParameter] mixed $account,
        #[\SensitiveParameter] mixed $index,
        mixed $network,
        mixed $policy,
    ): ReceivingAddressResult;
}
```

These are signature sketches, not loadable class declarations. The methods
accept the values described below and return a complete result or throw
`Failure\PrimitivesException`. They do not return an error object, `false`,
`null`, a partial address or a partial path. All arguments are required;
receiving has no branch/path argument. PHP call-syntax errors such as missing
arguments or an unknown named argument occur before method entry; they are
not provider failures or successfully admitted requests.

`mixed` deliberately preserves the original value at raw admission. An `int`
parameter can coerce a weak caller's numeric string, boolean or float before
the method can reject it. PHPDoc will describe the accepted value shapes for
tools; runtime checks establish them. Do not accept `Stringable`, iterators,
path strings, automatic case conversion or defaults as alternate input forms.

| Argument | Accepted representation and meaning |
|---|---|
| `extendedPublicKey` | A PHP string of 1–160 bytes, unchanged, containing the serialized public extended key. The 160-byte pre-decode resource bound retains the exercised research bound; it does not relax the exact decoded 78-byte structure or checksum. No trimming, private-to-public conversion or prefix inference. |
| `relativePath` | A PHP list of 1–255 actual integers, each `0..2147483647`. No sparse/associative array or coercion. Copy validated scalar elements into an owned list. The global count bound is a proposed request-resource guard; the tighter `count <= 255 - importedDepth` check follows key compatibility. |
| `account`, `index` | Separate actual PHP integers in `0..2147483647`, including zero. The account selects a provisioned mapping; it does not request hardened derivation. The index is attempted exactly once at the final edge. |
| `network` | An immutable `NetworkDefinition` produced by the validated definition loader for the admitted catalogue. Initial public profiles are `mainnet` and `testnet`. The object carries parameters and identifier, with no behavioral methods or provider selection. |
| `policy` | An exact PHP string: `p2pkh`, `p2sh-p2wpkh` or `p2wpkh`. Wrong type is malformed input; another string is unsupported intent. No policy is inferred from the key. |

`PublicAddressResult` is a final immutable value with read-only properties:
`string $address`, `string $network`, `string $policy`, and
`array $relativePath` documented as `non-empty-list<int>`. The path equals the
requested list exactly. `network` is the validated context identifier, not a
claim about cryptographically authenticated chain provenance.

`ReceivingAddressResult` is a final immutable value with read-only properties:
`string $address`, `string $network`, `string $policy`, `int $account`,
`int $branch` (always zero), and `int $index`. It does not reuse a generic path
field for logical account coordinates. Its successful address equals a direct
generic call with the selected key and `[0, index]`. Neither result retains the
input key, chain code, provider object, diagnostic payload or allocator state.
There is no implicit string/JSON serialization contract; consumers access the
documented properties and choose their own transport representation.

For the initial policies, the generic operation executes the selected
validated key-hash declaration. Other qualified complete flows may be bound
by explicit intent under the agreed architecture. Its entry-point
class does not replace that declaration with a hard-coded per-coin flow. The
receiving operation is wired with one immutable account-key source and that
same generic operation.
It contributes request admission, exact mapping selection and result
coordinates, not a second derivation or address algorithm.

## Definitions and immutable provisioning

PHP lists of field/value pairs are the current provisioning candidate, not a
requirement for the final authoring syntax or the composition schema. A
duplicate field must remain observable before normalization; an associative
array or JSON object that has already overwritten duplicate keys cannot prove
their absence. The loader accepts data already in memory and performs no file,
network, database or application-container lookup.

Proposed `NetworkDefinitions::fromEntries(mixed $entries): NetworkDefinitions`
takes a list of definition rows, each a list of exact two-element field/value
pairs. A mainnet row is:

```php
[
    ['identifier', 'mainnet'],
    ['p2pkhVersion', '00'],
    ['p2shVersion', '05'],
    ['segwitHrp', 'bc'],
    ['publicVersions', [
        ['0488b21e', 'p2pkh'],
        ['049d7cb2', 'p2sh-p2wpkh'],
        ['04b24746', 'p2wpkh'],
    ]],
]
```

Version strings here are exact lowercase hexadecimal with widths two/eight;
conversion to one/four binary bytes uses PHP facilities after shape validation.
The semantic view exposes bytes, not a raw configuration dictionary. All five
fields are required exactly once; every public-version entry is an exact pair.
Reject unknown fields, duplicate identifiers/fields/versions, non-list input,
wrong types/widths and mismatched profile parameters. Reordered equivalent
fields/version entries remain equivalent. Neither configuration order nor
matching HRP/address bytes establishes profile identity.
The unique-version restriction here belongs to the exact initial O02 profiles;
it must not be presented as a universal version-to-policy cardinality. The
[Taproot admission counterexample](#independent-policy-flow-declarations--agreed-c1-direction)
requires an explicit future compatibility decision before this representation
could be broadened. This proposal does not admit overlapping relations now.

The loader uses an explicit data catalogue of admitted profiles, initially
the exact M02 mainnet/testnet values and six version/policy pairs. The initial
public loader does not accept a caller-supplied expanded catalogue or a
`testMode` flag. The lower loader/composition qualification can supply a
test-only catalogue admitting Signet under a separate identifier, with the
same testnet parameters and unchanged algorithms. Such fixture wiring does
not add Signet to the shipped public catalogue. A profile with changed
behavior cannot enter merely by adding constants to the data catalogue.

`NetworkDefinitions::get(mixed $identifier): NetworkDefinition` accepts an
exact string identifier and returns its immutable view; wrong type is
malformed input and an absent identifier is unsupported intent. The initial
standard assembly provisions both mainnet and testnet definitions explicitly.
`testnet` means the shared address-parameter profile; it does not distinguish
testnet3, testnet4 or Signet consensus rules.

Proposed `AccountPublicKeySource::fromEntries(mixed $entries,
NetworkDefinitions $definitions, ExtendedPublicKeyAdmission $admission):
AccountPublicKeySource` takes a list of mapping rows. Each row contains
exactly one of each field below, again as field/value pairs:

```php
[
    ['network', 'mainnet'],
    ['policy', 'p2wpkh'],
    ['account', 0],
    ['extendedPublicKey', 'the provisioned account-level zpub text'],
]
```

The example key text is a placeholder, not a valid fixture. Provisioning is
**eager and atomic**: validate all row shapes, duplicate fields and duplicate
network/policy/account tuples before any key-provider work; then validate
each serialized key's structure, version, point and intent using the same
admission capability as generic derivation. Duplicate tuples fail even when
their key texts agree. Only a fully validated source is returned; an unrelated
bad row cannot remain latent inside an accepted source. An empty list is a
valid empty source and every lookup then reports a missing mapping.

Store an owned scalar snapshot of each validated key text and tuple. An
associative lookup index may be built only after duplicate detection. Account
is an integer field value, never inferred from an array key that PHP may have
coerced. Retain the original serialized key so receiving calls the actual
generic operation with that key, including its usual admission boundary.
Do not substitute a prevalidated-node fast path with different failure rules.

`AccountPublicKeySource::keyFor(mixed $network, mixed $policy,
mixed $account): string` checks the same network/policy/account shape rules
and returns the one exact key. Missing tuple throws `MISSING_ACCOUNT_MAPPING`; it never returns
`null`, guesses another key or selects by matching encoding parameters alone.
Lookup verifies semantic definition equivalence with the provisioned network
context, not object identity or source-field order. A conflicting definition
throws `INVALID_DEFINITION` and cannot borrow an existing mapping merely by reusing its identifier.

Malformed, unsupported or incompatible provisioned data throws
`INVALID_ACCOUNT_SOURCE` during construction. A provider/environment failure
during construction retains that failure category; do not blame source data
for unavailable or malfunctioning machinery. The source does not impose a
fixed BIP44 depth or infer account ancestry. Provisioning is responsible for
the asserted account-level binding. The generic operation checks the selected
key's remaining capacity for `[0, index]` at request time; a depth failure has
the same meaning as the direct generic call.

Immutable construction must rebuild nested scalar arrays rather than retain
caller references. PHP `readonly` prevents assigning the property but does
not detach references inside an input array. Views have no mutators or
public unvalidated construction path. Serialization/unserialization must not
be an alternate admission path; reject reconstruction that bypasses the
loader. Hostile reflection or modified runtime code is outside this API's
integrity guarantees. No source can contain callbacks, provider instances,
executables, live references to mutable consumer objects or fallback rules.

## Failure contract and validation order

Propose a single final owned `Failure\PrimitivesException` extending
`\RuntimeException`, with `public readonly FailureReason $reason` and
`FailureReason: string` as the stable machine-readable category. The exception
constructor accepts only the reason, uses that fixed reason text as its
message, code zero and no previous exception. No public constructor argument
accepts vendor messages, input context or `Throwable`. The enum avoids a
class for every failure while preserving meaningful caller handling.

| `FailureReason` case/value | Meaning |
|---|---|
| `MALFORMED_INPUT / malformed_input` | Wrong original PHP type or malformed serialized public-key envelope, metadata or recognized-version public point. |
| `UNSUPPORTED_INTENT / unsupported_intent` | A well-typed network/profile or policy name is outside the admitted set. |
| `UNSUPPORTED_VERSION / unsupported_version` | A checksum-valid 78-byte envelope with valid common metadata carries an unknown, private or multisignature version. Point interpretation has not occurred. |
| `INCOMPATIBLE_INTENT / incompatible_intent` | An admitted public key's version/policy/network meaning conflicts with the explicit validated intent. |
| `PATH_BOUNDS / path_bounds` | Empty/non-list path, non-integer or out-of-range path/account/index, proposed global path count bound, or insufficient remaining BIP32 depth. |
| `INVALID_CHILD / invalid_child` | At the exact attempted edge, HMAC left half is at/above the group order or delegated tweak confirms infinity. No child/address is emitted. |
| `INVALID_DEFINITION / invalid_definition` | Parameter/composition/binding schema, duplicate data, provenance catalogue or semantic invariants fail. Composition/binding admission occurs at setup; a conflicting supplied definition also fails at request/source lookup. |
| `INVALID_ACCOUNT_SOURCE / invalid_account_source` | Provisioned rows contain malformed/duplicate/unsupported/incompatible mapping data. |
| `MISSING_ACCOUNT_MAPPING / missing_account_mapping` | No key exists for the exact admitted network/policy/account tuple. |
| `ENVIRONMENT_FAILURE / environment_failure` | A required PHP facility, algorithm, integer width or selected provider dependency is unavailable at its setup/use gate. |
| `PROVIDER_FAILURE / provider_failure` | A delegated operation throws unexpectedly, returns the wrong type/width/shape, contradicts its contract or cannot establish its promised outcome. |

`MALFORMED_INPUT` excludes path/account/index errors, which consistently use
`PATH_BOUNDS`. Source/definition construction remaps data failures to its
documented construction reason, preserving provider/environment failures.
Research strings such as `malformed_key`, `unsupported_policy` and
`invalid_witness` are observations, not an existing public ABI to preserve.

There are separate lifecycle gates. Invalid configuration cannot produce a
wired operation/source. Dependency availability is checked at assembly and
again at a required facility's use when necessary; construction can therefore
fail before any request exists. Within a successfully constructed operation:

1. Validate the network value/admitted definition, then path (or account,
   then index), policy type/allowlist, and generic key text type/resource bound.
   No codec/ECC call occurs at this stage. The proposed absolute 255-element
   path guard precedes per-element iteration; it is separate from depth.
2. Receiving selects the exact account mapping, then calls generic derivation
   with `[0, index]`; it adds no branch choice, retry or account-key search.
3. Delegate checked decode; require exactly 78 bytes and valid common metadata.
   A root must have zero parent fingerprint and zero historical child number.
4. Check full public-version eligibility before compressed-point interpretation.
5. For a recognized version, validate point shape and delegated finite curve
   validity; then check explicit network/policy compatibility.
6. Check remaining depth; derive each requested component once, maintaining the
   complete node and emitting no partial result on failure.
7. Use the terminal policy composition and delegated encoder; return the full
   semantic result only after success.

<details>
<summary>View detailed ordered admission and exact-path derivation</summary>

![Ordered request and key admission, exact CKDpub attempts and a complete derived node; any failure aborts](diagrams/admission-and-exact-derivation.png)

</details>

The diagram exposes the ordered responsibilities; a box is not an approved
new capability/interface. HASH160 for the immediate-parent fingerprint is
separate from any final derived-key hash used by address construction.

First failing stage wins. Inside request shape, the order above settles the
otherwise unspecified simultaneous-fault case. Unknown version plus a bad point
remains `UNSUPPORTED_VERSION`; a recognized-version bad point plus incompatible
intent remains `MALFORMED_INPUT`; incompatible intent precedes insufficient
remaining depth. None of these permits remapping a provider malfunction to
malformed input without evidence of the provider's invalid-input signal.

All adapters translate their conventions at the owned boundary. The public
entry point must not indiscriminately catch all internal exceptions and erase
known semantic reasons. Conversely, a vendor exception with a familiar PHP
class or message is not sufficient to treat an arbitrary fault as bad input.
Retain the pinned invalid-signal and same-library classification limitations
from M03/M04; the API cannot promise to distinguish observationally identical
signals. A plausible wrong address remains a qualification trust limit.

`SensitiveParameter` applies to input-bearing public/helper calls that can
appear in library-owned exception traces, including provisioning rows and
serialized keys. Test with `zend.exception_ignore_args=0`; do not rely on a
deployment default. Public exception message, reason and previous chain must
not expose keys, chain codes, full wallet paths or raw vendor text. No library
logging, automatic input dumping or public mutable diagnostic context is added.
PHP's [redaction wrapper](https://www.php.net/manual/en/class.sensitiveparametervalue.php)
still retains an introspectable value: standard trace
redaction is not erasure. The semantic exception fields and normal rendered
diagnostics have the containment contract; this proposal does not claim that
PHP exception internals contain no recoverable input.
This does not promise to scrub arbitrary consumer stack frames, logging or
debuggers; the library must contain the data in its own diagnostic surface.

## Reusable lower capabilities

The concrete production adapter code remains M06 work. Proposed semantic
boundaries below constrain that implementation and its replacement tests;
they are not vendor APIs copied into the domain. Raw external ingress retains
original types; strict typed calls inside the admitted composition use the
documented byte/int values.

| Boundary | Return and failure meaning |
|---|---|
| `PublicPointOperations::admit(mixed $compressed): string` | Return identical 33-byte compressed encoding only after finite curve validation. Invalid input is malformed input; unexpected parse/serialization failure is provider failure. No point/GMP object escapes. |
| `PublicPointOperations::addScalar(mixed $compressed, mixed $tweak): ?string` | Validate the raw compressed key through the same admission contract and require exactly 32 big-endian bytes in `0..n-1`. Return a validated compressed result, or `null` only for provider-confirmed infinity. Zero is identity. Invalid arguments fail; no modular reduction or vendor `false` as infinity. |
| `ExtendedPublicKeyAdmission::admit(mixed $text, mixed $network, mixed $policy): ExtendedPublicNode` | Apply the ordered checked-decode/structure/version/point/intent stages. Return an immutable complete node: four version bytes, integer depth, four fingerprint bytes, integer historical child number, 32 chain-code bytes and 33 admitted point bytes. Historical hardened child numbers are allowed. |
| `CkdPublicDerivation::derive(ExtendedPublicNode $parent, mixed $relativePath): ExtendedPublicNode` | Return the complete final node for the exact path. Throw owned `INVALID_CHILD` for an invalid edge or another documented failure; do not expose a partially successful node. No advancing variant or nullable child result. |
| PHP hashes/HMAC and `Hash160` composition | Binary outputs of 32/20/64 bytes for SHA-256/RIPEMD-160/HMAC-SHA512; HASH160 is SHA-256 then RIPEMD-160. HMAC key and message have distinct positions. Missing facility is environment failure; unexpected result is provider failure. No silent hex conversion. |
| `Base58Check::decode(mixed $text): string` / `encode(mixed $payload): string` | For this slice, decode admits 1–160 text bytes and returns nonempty checksum-free bytes including all version bytes; encode admits 1–78 binary payload bytes and returns complete checked text. Empty input/payload is malformed input, never a failure sentinel. The selected library owns checksum/alphabet machinery; extended-key structure remains in admission. |
| `SegwitV0KeyHashEncoder::encode(mixed $hrp, mixed $program): string` | Admit the selected lowercase HRP and exactly 20 binary bytes; return lowercase v0 Bech32 text. No decoder call in generation and no public SegWit decoding contract in this slice. |

For point/codec direct composition, input shape failures are owned exceptions;
provider outputs violating the same shape are provider failures, not caller
errors. Internal provider interfaces may use typed parameters because callers
have already established their raw types. Public façades must not rely on
weak PHP scalar coercion to establish byte/index admission.

CKDpub owns message layout, HMAC split, scalar-range admission, no-advance
behavior and metadata. A zero left half still changes chain code/depth/child
number/fingerprint. Parent fingerprint uses the immediate parent's HASH160.
An invalid left half does not become a reusable tweak value. The point
capability's `null` infinity is immediately converted into `INVALID_CHILD`;
no public address method returns it. Fixed-width conversions use PHP facilities.

P2PKH and P2SH-P2WPKH reuse key HASH160, diverging at their actual payload
semantics: direct key hash versus HASH160 of `0014` plus that hash. P2WPKH
passes the original key hash as v0 witness program. Byte construction expresses
those owned semantics; it does not introduce a script engine, cryptographic
algorithm or standardized codec implementation.

## Contract review and downstream acceptance

The [language probe](php-boundary-probe.php), its
[strict callee](php-boundary-callee.php) and
[four-runtime results](php-boundary-results.json) reproduce weak-caller
coercion, caller-reference mutation through a readonly array, scalar-leaf
detachment, and `SensitiveParameter` rendering versus introspection on
PHP 8.2–8.5. They establish language behavior only. M05 must review the concrete choices
against the accepted semantics, resolve feasibility questions and assign their
acceptance cases before freezing. The production tests below execute when M06
or M07 exposes the corresponding capability; requiring an assembled receiving
API before M05 would invert the approved milestone order.

Reproduce with the images
from the [M05 record](README.md#executed-evidence-and-limits), repository mounted
read-only at `/repo`, no network, and:

```sh
php -d error_reporting=E_ALL -d display_errors=stderr \
  -d zend.exception_ignore_args=0 \
  /repo/docs/pips/PIP-0003/M05/php-boundary-probe.php
```

- Execute raw ingress from strict and weak PHP callers: numeric strings,
  booleans, floats, sparse paths, count bounds, integer extrema and unchanged
  copies. The proposed global 255-entry guard and within-shape failure order
  need named mixed-invalid cases; earlier probes do not establish them.
- Test pair-list definitions and source provisioning with duplicates before
  normalization, reordered equivalent data, aliased caller arrays, unknown
  fields, mismatched profiles and bypass attempts. The proposal's eager source
  lifecycle is new contract work, not something M04 has already implemented.
- Verify owned exception class/reason/message/chain behavior through both entry
  points and source construction, including faults during setup and use and
  library-owned trace arguments. No favorable public containment claim follows
  from research `category()` output.
- At M06, challenge declaration admission with cycles, unknown capabilities,
  duplicate bindings, missing ports and same-width semantic substitutions.
  Reject reordered admission, incorrect parameter roles and failure-path
  overrides before execution. Confirm mainnet/testnet reuse one declaration.
- At M06/M07, inject a failure at each executable stage and record calls:
  no later stage, unselected policy, alternate provider or replacement result
  may occur. Preserve the original owned reason and diagnostic containment;
  confirm infinity aborts derivation while zero tweak completes correctly.
  Cover receiving source-selection failure before generic execution and
  generic failures without address/result construction in the receiving flow.
- Preserve normative complete-node/address anchors and two real adapter
  replacement exercises. Test valid-looking wrong outputs against independent
  expected values rather than requiring generation to decode its own output.
- Compare receiving/direct generic success and failure for multiple accounts,
  policies and network contexts, including missing mapping, insufficient depth,
  invalid first/final edge and changed provider conditions after provisioning.
  Tests must verify exactly one tuple and `[0, index]`, with no fallback.

The [preliminary schema and execution proposal](#preliminary-schema-and-execution-proposal)
makes concrete choices available for joint discussion. C1's architectural
extension review is concluded with independent policy flows and reusable
resources; C2 is now tracked separately under Issue #45. Concrete C2–C6
choices remain unaccepted.
No production schema validator or executor has been implemented or qualified by this
document. M06 applies the assigned cases to
the first complete branch; M07 extends them to all policies and receiving.

The provider/runtime decision, these proposed signatures/data/failure choices,
feasibility evidence and assigned acceptance obligations must converge before
the M05 freeze. M06 then
implements the first complete branch; M07 adds remaining policies and the
receiving implementation. M08/M09 still own assembled verification and its
independent challenge. No applicable CP is closed by this proposal alone.
