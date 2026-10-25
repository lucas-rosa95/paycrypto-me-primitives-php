# M05 — Provider, runtime and concrete contract selection

Work: [Issue #41](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/41),
under the approved PIP-0003 and PIP00010 boundaries in the
[Execution Plan](../EXECUTION-PLAN.md). Execution PR:
[PR #44](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/44).

Status, 2026-10-22: **executor assessment in progress; no joint selection or
contract freeze**. This record owns the current candidate comparison and its
reproducible evidence. The Plan owns execution status and the
[M00–M04 resumption checkpoint](../EXECUTION-PLAN.md#m05-resumption-checkpoint).
Earlier approved decisions and independently reviewed research retain their
scopes. This is neither an independent review nor an M06 handoff.

## Proposed direction and remaining decision

The executor proposes Paragonie ECC 2.6.0, Tuupola Base58 2.2.0 and BitWasp
Bech32 0.0.1 for the **required public-address operations**, using PHP's hash,
HMAC and fixed-width conversion facilities. Retain PHP 8.3 as the proposed
consumer minimum while checking consumer constraints; qualify the eventual
implementation on 8.3, 8.4 and 8.5. This proposal has not changed the root
Composer manifest or the configured 8.3/8.4 verification matrix.

The recommendation favors the already exercised ECC and codec boundaries,
explicitly chosen execution paths and absence of observed runtime diagnostics.
It carries real costs: Paragonie's extra mandatory packages, Tuupola's
four-byte-version adaptation, and BitWasp's old release and sparse license
distribution. The current evidence supports discussing these costs; it does
not establish unqualified correctness or production readiness.

Before selection, settle the provider/runtime tradeoff jointly with the user,
record intended consumers' deployed PHP versions and upgrade constraints (or
explicitly accept that they remain unknown), and complete the concrete
contract review and assignment of downstream acceptance obligations. The [PHP contract proposal](PHP-CONTRACTS.md)
now supplies exact entry-point signatures, result fields, duplicate-safe
provisioning, failure reasons and lifecycle/precedence choices. It is an
executor draft, not a freeze. Consumer reach and migration effort cannot currently
be quantified. Keep alternatives available for qualification; install one
chosen implementation per boundary in production, with explicit wiring and
no runtime fallback or backend autodetection.

The [composition direction](PHP-CONTRACTS.md#agreed-composition-direction)
began in the joint design discussion on 2026-10-22: inspectable composition
data, semantic compatibility between connections and immediate abort on failure.
The later C1 clarification gives distinct protocol candidates their own flow
declarations, while compatible resources are shared; equivalent network or
parameter variants within one candidate reuse its declaration. Names such as
`BitcoinDefinition` are assessed by responsibility; the constraint concerns
hidden or duplicated behavior, not a naming ban. The proposal now maps the
three approved policy flows, distinguishes parameter data, composition
declarations and provider wiring, and assigns
schema/abort acceptance cases to M06/M07. The new
[preliminary schema and execution proposal](PHP-CONTRACTS.md#preliminary-schema-and-execution-proposal)
initially advanced C1–C6 for joint discussion: ordered declarations, duplicate-observable
source records, semantic lineage, explicit intent bindings and execution/failure
boundaries. Its [reading guide](PHP-CONTRACTS.md#reading-and-decision-status)
distinguishes accepted constraints, agreed direction and unaccepted proposals.
C1 is jointly closed at the architectural level: declared execution order and
inspectable producer-output-to-consumer-input connections. Each step consumes
only declared inputs/parameters or earlier outputs; setup resolves all admitted intent bindings
and rejects circular dependencies among declarations. This does not constrain
internal capability recursion or bounded iteration. Neutral subcompositions may be
shared when they have cohesive, candidate-independent contracts; ordinary
internal helpers and context-specific components need not become shared steps.
Resource scope follows responsibility and semantics, not a consumer count:
general resources may start shared on their first concrete use, while local
components can be promoted when a compatible new use appears.
Each capability is a complete unit within its enclosing composition, with
documented inputs, outcome and failure; its implementation remains open to
inspection and tests at relevant lower boundaries. This does not promise
rollback or settle the remaining capability/subcomposition boundaries.
The three delivered Bitcoin policies share their ordered route through
derived-key HASH160 and bind the explicitly selected terminal directly to the
declared `encode` step. Other protocol candidates retain their own flow
declarations. The jointly accepted terminal split keeps P2PKH payload/encoder
steps, P2SH-P2WPKH script/HASH160/payload/encoder steps, and only a SegWit-v0
encoder step for P2WPKH. Its derived key hash reaches the encoder's program
input through a checked semantic connection, not a pass-through `program`
execution step; the selected definition supplies the HRP. Remaining
subcomposition boundaries, their contract checks and exact C2 syntax remain
open. The draft's verbose pair-list example is a
preliminary C2 representation, not the agreed concept;
the remaining C2–C6 choices and concrete exception-chain design remain open.
The closed C1 direction requires deterministic, unambiguous stage contracts and
a chain that can be inspected, independently validated and reproduced, with a
stable, complete and atomic caller-facing outcome. Concrete contract checking
and the point-infinity outcome form remain under C3; the executor and auditor
inspection mechanisms remain under C6.
Independent auditors, rather than ordinary consumers, own verification of the
chain; a consumer is not required to collect per-invocation evidence to use its
documented result or failure. The joint C1 audit direction is to inspect
declarations, resolved connections, contracts and the assembled mapping from
steps to capabilities and selected implementations; reproduce pinned code,
environment, inputs and independently anchored outputs; and challenge routing,
order and abort behavior with substitutable test capabilities at existing
contract boundaries through the same validator and executor. Auditors choose
which owned, native or third-party component boundaries merit deeper review;
no fixed risk ranking governs execution. Native-algorithm trust does not
replace review of our invocation, input bytes and output connections.
Provenance and reproducibility help identify the examined artifact, not prove
its protocol behavior. Independent review findings belong in the existing
durable Issue/PR/CI trail when sufficient. Remaining component boundaries need
C4 joint review; their checks remain under C3 and the auditor-facing mechanism
under C6/M06–M07. These are not assembled-chain verification results.
The [exploratory reuse fitness challenges](PHP-CONTRACTS.md#exploratory-reuse-fitness-challenges--outside-pip-0003-delivery)
use synthetic Bitcoin Cash CashAddr and Taproot key-path cases to test these
boundaries before M05 freeze. They add no admitted intent, implementation or
protocol-qualification evidence.
The [terminal-junction comparison](PHP-CONTRACTS.md#synthetic-terminal-junction-comparison--c1-decision-evidence)
now applies one runnable symbolic probe to the named-slot and direct-step
binding candidates. Both pass the same six-intent, rejection and abort cases;
the joint C1 choice is direct binding at this local step because the slot adds
an identifier without an extra demonstrated guarantee. Exact source syntax and
other design choices stay open; this probe does not validate a production loader
or address behavior.
Concrete choices, including admission/body separation and owned-code failure
classification, remain open alongside provider/runtime selection. This is a
reviewable design proposal, not an implementation handoff; it adds no runtime
evidence or implemented capability.

## Current package suitability

[Provider metadata](provider-metadata.json) captures the Packagist and GitHub
queries on 2026-10-21, including exact source references, declared constraints,
licenses, latest stable releases, default-branch commits and open issues.
None of the inspected repositories was archived and no inspected package was
marked abandoned. Neither fact guarantees active support. GitHub issue counts
and a recent commit are observations, not quality or security scores.

| Required boundary | Proposal and evidence | Alternative and disposition |
|---|---|---|
| Point admission and public tweak | **Paragonie ECC 2.6.0**, MIT, released 2026-08-20. The corrected M03 adapter and this run use explicit `GmpMath` and `SecgCurve::generator256k1(null, false)`. Zero, scalar range, infinity, complete-node and parser-fault controls retain their results. | **Simplito elliptic-php 1.0.12**, MIT, released 2024-01-09, remains a real replacement candidate with matching bounded results. Its graph includes `bn-php` and `bigint-wrapper-php`; required GMP is explicit in the trial root. Prefer Paragonie's more recent maintenance evidence provisionally, not because Simplito failed derivation. |
| Base58Check key admission and address encoding | **Tuupola Base58 2.2.0**, MIT, released 2025-12-29. Explicit `PhpEncoder`, Bitcoin alphabet and checked operation; no optional GMP/BCMath selection. Its one-byte checked API is composed with raw decoding while retaining all four version bytes. | **Muvon 0.1**, MIT, released 2021-04-27, remains deprioritized: undeclared BCMath, empty-string invalid decode and additional failure-classification obligations. Its bounded M04 results stand; it was not included in the new minimal graphs. |
| SegWit v0/20-byte address generation | **BitWasp Bech32 0.0.1**, metadata license Unlicense, released 2018-02-05. Published generation outputs and injected encoder faults pass without diagnostics on all four runtimes. Generation does not call its decoder. | **cjpgdk Bech32 0.1.2**, MIT, released 2022-03-23, passes the same outputs but emits three implicit-nullability deprecations on 8.4/8.5. **Cardano PHP Bech32 1.1.0**, Apache-2.0, released 2025-01-07, passes the new bounded generation probe without diagnostics, but adds `ext-sodium`, a broader API and a separate 8-to-5-bit conversion call. Neither is rejected for a decoder-only finding. |
| SHA-256, RIPEMD-160, HMAC-SHA512 and fixed-width conversions | PHP facilities; require 64-bit PHP and `ext-hash`, check actual required algorithms/functions and output widths. M03's normative hash evidence and this run's disabled-function/fault controls remain the basis. | Do not invent duplicate runtime wrappers to manufacture independence. Same-runtime agreement is not a second algorithm implementation. |

The installed BitWasp distribution declares Unlicense in `composer.json` but
has no license file. The maintainer addressed that omission in
[upstream issue #7](https://github.com/Bit-Wasp/bech32/issues/7#issuecomment-481061186).
Record the declaration and distribution limitation accurately; this assessment
does not supply a missing license text. The latest default-branch commit in
the metadata is from 2019. Choosing its narrow generation API accepts that
maintenance exposure and needs downstream regression coverage.

Cardano's class explicitly credits BitWasp. The other two codecs also share
BIP173 implementation lineage. Agreement among them demonstrates adapter
replacement, not independent checksum mathematics. Published BIP outputs are
the independent expected values for the named vectors. Cardano's package also
declares sodium for functionality outside the exercised generic encoding path;
that remains a mandatory Composer requirement even when the path does not
call it. Apache-2.0 and attribution obligations must remain visible in the
dependency record if this alternative is chosen.

The native **uma/secp256k1-php 0.1.2** binding retains its bounded M03 role as
an independent ECC reference. A PHP version constraint does not account for
building and deploying the extension plus its native library. Its ambiguous
`false` and same-library confirmation limits remain documented in M03. No
native rebuild or new native qualification was performed here. StephenHill
offers raw Base58 rather than checked encoding; Furqan Siddiqui 0.1.5's
`php-64bit: ^7.2` excludes the examined PHP 8 graph; allty's historical integer
codec does not meet the binary/checked boundary. Their earlier behavioral
dispositions are not newly executed results.

### Security and complete dependency graphs

[The advisory response](advisories.json) preserves the package-specific
Packagist query. [Composer audit results](composer-audit.json) show zero
matching advisories and abandoned packages in all three exact locks on
2026-10-21. Historical advisories exist for older Paragonie ECC, Simplito and
sodium_compat versions; these tested versions are outside the returned
affected ranges. Absence of a reported advisory is not a security audit or a
guarantee of future suitability. Recheck the selected lock at the handoff.

| Isolated graph | Exact production packages in the trial lock | Required platform |
|---|---|---|
| [paragonie-bitwasp](graphs/paragonie-bitwasp/composer.lock) | `paragonie/ecc 2.6.0`, `genkgo/php-asn1 2.9.0`, `paragonie/sodium_compat 2.5.2`, `tuupola/base58 2.2.0`, `bitwasp/bech32 0.0.1` | The selected graph resolves with PHP 8.1; 64-bit PHP, GMP, hash and ctype are explicit trial requirements. |
| [simplito-cjpgdk](graphs/simplito-cjpgdk/composer.lock) | `simplito/elliptic-php 1.0.12`, `simplito/bn-php 1.1.4`, `simplito/bigint-wrapper-php 1.0.0`, `tuupola/base58 2.2.0`, `cjpgdk/bech32 0.1.2` | Resolves with PHP 8.1; explicit 64-bit PHP, GMP, hash and ctype. |
| [paragonie-cardano](graphs/paragonie-cardano/composer.lock) | `paragonie/ecc 2.6.0`, `genkgo/php-asn1 2.9.0`, `paragonie/sodium_compat 2.5.2`, `tuupola/base58 2.2.0`, `cardano-php/bech32 1.1.0` | Resolves with PHP 8.2; 64-bit PHP, GMP, hash, ctype and sodium. |

These are three representative full installations, not an exhaustive Cartesian
product or a production dependency selection. The `config.platform.php` values
test resolution floors, not executable compatibility promises. PHP 8.1 was not
rerun here. The research's imported readonly classes themselves need PHP 8.2;
the production code has not yet established its own minimum.

The saved results enumerate installed package references and SHA-256 hashes
of loaded vendor files. Paragonie's ASN.1 package is installed although not
executed by the public-point path; sodium_compat autoload files are executed.
The probe does not exercise Paragonie's optimized/OpenSSL factory or every
file in any installed package. Composer presence and actual path execution
are distinct. Research/reference packages, BCMath used by Muvon, and development
tools do not automatically become production requirements.
The trial roots explicitly require ctype because the exercised ECC adapters
use `ctype_xdigit()` to validate vendor serialization. Retaining that call in
production requires the same declared facility; a successful check of vendor
requirements alone would miss this adapter-owned requirement.

## Runtime assessment

The [official PHP support schedule](https://www.php.net/supported-versions.php)
lists security support ending on 2026-12-31 for 8.2, 2027-12-31 for 8.3,
2028-12-31 for 8.4 and 2029-12-31 for 8.5. PHP 8.1 is already
[end of life](https://www.php.net/eol.php). These are observations as of this
assessment, not a commitment to adopt PHP's entire support policy.

The [2026-10-21 PHP security release](https://www.php.net/archive/2026.php#2026-10-21-5)
fixes CVE-2026-109618 and CVE-2026-109619 in 8.2.35, 8.3.36, 8.4.27 and
8.5.12. The original 8.3/8.4 research images predate those fixes. The final M05
graph and language results below therefore use all four patched releases.
The development default and shared setup/CI/release pins now use 8.3.36 and
8.4.27; this patch refresh does not change the configured PHP minor-version
floor or matrix. Both new development images passed root `composer ci` with
fresh isolated installs of the development graph (PHPUnit 10.5.66 and
PHPStan 2.3.1), including the existing one-test/two-assertion setup check. That
check still does not verify an address capability. The selected consumer policy
must require maintained patch releases or vendor-equivalent security fixes;
a Composer `config.platform.php` value alone does not establish that state.

| Candidate minimum | Observed feasibility | Adoption and maintenance tradeoff |
|---|---|---|
| 8.2 | All three trial graphs pass 21 bounded commands each. | Could admit consumers still on 8.2, whose number is unknown. Its short remaining security window is a concrete cost; passing research does not justify expanding production support by itself. |
| **8.3, proposed** | All three graphs pass; retains the configured floor and existing M03/M04 runtime. | Includes 8.3 consumers while providing a longer security window than 8.2. No required operation in this research needs a higher version. Actual migration impact remains unknown. |
| 8.4 | All three graphs pass; cjpgdk emits deprecations. | A higher floor excludes 8.3 consumers without an evidenced capability benefit here, although it increases the minimum runtime's support horizon. Diagnostics remain an obligation even if a lower floor is selected. |
| 8.5 | All three graphs pass; cjpgdk emits the same three diagnostic categories. | Useful additional verification target, with the longest support horizon. No established need to require it of all consumers. Root development tools and CI have not yet been qualified on 8.5. |

The proposed production matrix is 8.3/8.4/8.5, **pending** selection and its
own assembled tests, development-tool checks and synchronized Composer,
images, CI and installation guidance. The current configured matrix remains
8.3/8.4. Changes in future provider versions, transitive resolution or runtime
versions require affected-boundary qualification; passing these pinned graphs
does not authorize arbitrary upgrades.

## Executed evidence and limits

All recorded containers ran on Linux/amd64 with 64-bit PHP. Each runtime passed 21 commands: a real platform check, the graph probe,
M03 parser/hash/common-mode fault probes, and the disabled `hash` and
`hash_hmac` controls for each of three graphs. **84 commands passed.**
All ordinary semantic graph outputs agree across runtimes; the three graphs
agree on all nine SegWit outputs. Exact research-source, lock, image, installed
package and loaded-file identities are in the saved results.

| Runtime | Local image ID | Results |
|---|---|---|
| PHP 8.2.35 | `cef53200c65c4df763df679dfe01418807c60db9b18f28c1d831df8f9bea2020` | [PHP 8.2](results-php82.json) |
| PHP 8.3.36 | `ac241017cf12d530cd7ffe845dbd681168bffcd81a597467260b4864d0c9f700` | [PHP 8.3](results-php83.json) |
| PHP 8.4.27 | `08e98c2f508055b222a30114548b271f27af2c030d93839d9d1dd10bf0fc319b` | [PHP 8.4](results-php84.json) |
| PHP 8.5.12 | `72c1cdc1ac79c39dffd2237f1605f32e4a78d9b7169da3cadaa9e2425dceb252` | [PHP 8.5](results-php85.json) |

The [graph probe](provider-graph-probe.php) reuses the corrected M03 CKDpub
consumer and M04 adapters without edits. Complete-node ordinary/forced results
and parser/hash/common-mode outputs match their recorded M03 counterparts.
Additional codec checks cover a published BIP32 xpub round trip, all six public
versions as synthetic envelope variants, the published BIP49 nested address,
three published BIP173/BIP84 generation outputs and six generated differential
programs. The latter six are not normative vectors. The pinned sources are
retained in M00's [BIP32 investigation](../M00/reviews/bitwasp-public-derivation/README.md)
and [BIP173 snapshot](../M00/research/provider-refresh-2026-10-02/bip-0173.mediawiki);
M04's [probe](../M04/codec-composition-probe.php) and
[handoff](../M04/README.md) identify the BIP49/BIP84 fixtures and source revisions.

The new Cardano adapter delegates bit conversion to its public
`hexToByteArray()` and checksum/text encoding to `encode()`. It owns only
the admitted HRP/program constraints, witness-v0 element and output checks.
Injected conversion exceptions, wrong types, wrong count, non-list arrays,
non-integer words and out-of-range words fail. The same consuming address
checks exercise all three codecs. No standardized codec or cryptographic
algorithm was implemented locally.

Each codec rejects the tested invalid widths/HRPs and contains the tested
empty, wrong-type, uppercase and thrown encoder faults. Tuupola also rejects
the corrupted input checksum and contains a decoder fault on valid text.
An injected plausible wrong address **passes the coarse runtime boundary**
and fails the independently anchored expected-output check. That is a
qualification trust limit, not a claim that every bad output is detected at
runtime. Same-library classifier faults and ECC failures mimicking a pinned
invalid-point signal likewise remain the M03/M04 limits.

The cjpgdk graph emits three deprecations in the ordinary probe on 8.4/8.5;
its autoloaded function also emits a warning in ECC-only fault runs. Preserve
[8.4 diagnostics](cjpgdk-php84-diagnostics.txt) and
[8.5 diagnostics](cjpgdk-php85-diagnostics.txt) separately: PHP reports two
different declaration line positions, despite identical loaded source hashes.
The other graphs emit no PHP diagnostics in these probes. Composer's normal
platform-check stderr is not a PHP diagnostic. Consumers converting
deprecations to exceptions would need separate qualification; warnings have
not been silently suppressed to make this comparison pass.

These minimal-graph probes do not rerun every M04 decoder/admission vector on
every runtime or construct the proposed production API. The initial resumption
reproduction separately covered unchanged M03/M04 on 8.3/8.4. Public exception
classes, diagnostic containment, receiving-source semantics and assembled
CP-07/CP-10/CP-11 remain unverified. Research `category()` strings and legacy
adapter exception chains are not public failure contracts.

## Concrete contract proposal and remaining qualification

The [retained decision map](../EXECUTION-PLAN.md#m05-resumption-checkpoint)
prevents historical draft questions from reopening accepted semantics. The
[concrete proposal](PHP-CONTRACTS.md) supplies choices for the following surfaces;
review them and assign their affected acceptance cases before the M06 handoff:

| Contract surface | Review and qualification obligation |
|---|---|
| Public request/result | Freeze concrete signatures and immutable representations for serialized key, nonempty integer path, validated network and explicit policy; return address plus exact network/policy/path context. Keep generic derivation distinct from receiving account/index. |
| Integer ingress | Reject booleans, numeric strings and integral floats **before coercion**. PHP parameter types plus callee `strict_types` do not suffice when the caller is weakly typed. Use explicit original-value validation at the admission boundary; later callers consume admitted semantic values. Test both caller modes. |
| Definitions | Choose a concrete immutable schema and construction boundary that cannot bypass validation, rejects unknown/duplicate data and preserves order-independent equivalence. Test-only Signet data variation must not become public network support. `testnet` describes encoding parameters and does not authenticate testnet3/testnet4/Signet chain identity. |
| Composition and execution | Preserve the closed C1 execution model and direct intent binding at the delivered Bitcoin `encode` step. Review the remaining C2–C6 choices: pair-list source, role/lineage validation, other component boundaries, operation prelude/body boundary, terminal parameter exposure, explicit provider assembly and executor mechanism. Resolve representation feasibility and unexpected owned-code failure handling before M05 freeze; no production validator or executor has been qualified. |
| Receiving source | Freeze duplicate-safe provisioning, validation timing and missing/malformed/incompatible mapping failures. Select exactly by network/policy/account; provisioning binds ancestry, which serialized metadata cannot prove. Invoke generic `[0, index]` without advancement or fallback; return logical account/branch/index. |
| Lower capabilities | Name only boundaries with real protocol responsibility and replacement evidence. Preserve zero-tweak identity, distinguish confirmed infinity from provider malfunction, and define the meaning of every nullable/boolean/outcome return. No vendor objects or generic buffer/crypto framework in public contracts. |
| Failures and precedence | Choose owned exception classes/categories, safe messages and previous-chain rules; preserve corrected M02 validation stages and exact-index failure without partial success. Verify absent facilities, wrong provider types/widths and thrown faults through the eventual public operation. Do not expose raw vendor messages or exception chains. |

The integer issue was reproduced on PHP 8.2–8.5: a weak caller passed `"7"`, `true`
and `7.0` to an `int` parameter in a strict callee and obtained `7`, `1` and
`7`. This follows PHP's
[caller-controlled strict typing](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict).
It is a concrete admission test to carry forward, not a reason to change the
approved non-hardened integer domain. The separate
[language probes](PHP-CONTRACTS.md#contract-review-and-downstream-acceptance)
also demonstrate readonly-array aliasing and the difference between trace
redaction and erasure. Their four commands are additional to the 84 graph
commands; they do not verify a production contract.

## Reproduction

Dependencies remain outside the repository. The three `graphs/*/composer.json`
files have exact direct pins; their locks retain the complete resolved graphs.
Copy each manifest/lock pair into a separate scratch directory, then install
with `composer install --no-dev --no-plugins --no-scripts`. Do not run `update`
when reproducing these results. Run `composer audit --locked --no-dev
--format=json` separately with network access to refresh current advisories;
new advisory output need not match the dated snapshot.

PHP 8.3/8.4 use new research images based on the patched development
[Dockerfile](../../../../Dockerfile) and [image pins](../../../../scripts/php-images.sh),
then extended with the unchanged [M03 GMP recipe](../M03/Containerfile.gmp).
Their tags are `localhost/primitives-php83-m05-gmp:latest` and
`localhost/primitives-php84-m05-gmp:latest`. The initial resumption reproduction
used the historical M04 images separately; its saved conclusions are unchanged.
For 8.2/8.5, the extra research images were built with the following recipe;
substitute the pinned official PHP base for each build and copy Composer from
the recorded local 8.3 image. The existing image's Composer binary is not a
new provider dependency.

```dockerfile
ARG BASE
FROM ${BASE}
COPY --from=localhost/primitives-php83-m04-codecs:latest /usr/local/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends libgmp-dev git unzip && docker-php-ext-install gmp && rm -rf /var/lib/apt/lists/*
WORKDIR /trial
```

- 8.2 base: `docker.io/library/php@sha256:053cebfd4dcfabd4ce9a8b04dc03fdd9579e3ab495055316509b642eff6f74f2`.
- 8.5 base: `docker.io/library/php@sha256:390c25b2a1c451c21fcc861d6003fe9662dc2e5903d82074fb009311dd463526`.

The Debian package repositories were not snapshot-pinned; rebuilding need not
produce the identical local image ID. The saved IDs identify the actual runs.
Fresh builds must report their own identities and pass platform checks. Sodium
is present in all four tested images, including for the Cardano graph.

After installing the three graphs under `/absolute/scratch/graphs/<graph>`,
run from the repository root, once per runtime image:

```sh
python3 docs/pips/PIP-0003/M05/run-graph-probes.py \
  --image localhost/primitives-php83-m05-gmp:latest \
  --installs /absolute/scratch/graphs \
  --output /absolute/scratch/results-php83
```

The runner verifies the lock bytes and installed source references, mounts the
repository and dependencies read-only, and disables networking. Inside each
disposable container `/research` is a symlink to `/trial` so both historical
probes use the same Composer autoloader. It asserts M03 expected results and
cross-graph address agreement, then saves command status/output hashes and
the semantic graph results. For cross-runtime comparison ignore only `php`
and `loaded_vendor_files_sha256` in each `graph_result`; all other fields must
agree for the same graph. Compare source hashes for files shared across
runtimes separately: sodium_compat loads one extra file on 8.2/8.3. Preserve
stderr and investigate diagnostics instead of dropping it from the assessment.
