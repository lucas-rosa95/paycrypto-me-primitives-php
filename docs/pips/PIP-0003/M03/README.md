# M03 — ECC qualification research independently reviewed

This research record follows the accepted M02 record merged into `main` by
[PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9).
It does not add a production provider. The M03-specific correction passed
[independent follow-up review](cp-reviewer-2026-10-16-074200.md) at `2aa0194`;
[PR #12](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/12)
merged the bounded research handoff at
[`7ecfce4`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/commit/7ecfce42e16e98819122d2ccc28ed2aa6a4bf959). The
[execution plan](../EXECUTION-PLAN.md) remains the source of milestone scope
and later Q04–Q08/Q12 obligations.
The separate [Issue #31 deep re-review](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/31)
examined `b07e6082b00a5ffba2b1dc384ba0f98d46ebb391` and led to a
[focused correction](#issue-31-corrective-research--2026-10-19) at technical
revision `85fdae149c803c713795eea7a1bb6ed4b9dc118e` in
[PR #37](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/37). Its independent
check and bounded conclusion are pending; the prior review at `2aa0194`
retains only its declared scope.
For the original review handoff and its then-current risks, see the dated
[reviewer notes](REVIEWER-NOTES.md); the follow-up report records the final
bounded M03 review verdict.

## Bounded result on 2026-10-14

The focused [Composer manifest](M03-ECC-COMPOSER.json) and
[lock](M03-ECC-COMPOSER.lock) resolve six packages. Direct candidates are
`paragonie/ecc` 2.6.0 and `simplito/elliptic-php` 1.0.12. The transitive graph
contains `genkgo/php-asn1` 2.9.0, `paragonie/sodium_compat` 2.5.2,
`simplito/bn-php` 1.1.4 and `simplito/bigint-wrapper-php` 1.0.0. Composer
installed this exact lock separately on PHP 8.3.35 and 8.4.26 with GMP;
`composer check-platform-reqs` passed on both. A `composer audit --locked
--no-dev` query reported no advisories or abandoned packages for this exact
lock at this checkpoint. That response is not a security certification.

The [bounded probe](ecc-boundary-probe.php) ran through Composer autoloading on
both runtimes, with [PHP 8.3 output](ecc-probe-php83.json) and
[PHP 8.4 output](ecc-probe-php84.json). Its two research adapters agreed on
13 point-admission cases, nine generator-tweak cases and two additional
cross-point infinity cases. Three malformed public keys are the actual bytes
from [BIP32 test vector 5](../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki),
not locally invented substitutes: wrong prefixes `04` and `01`, and the
off-curve compressed point with x-coordinate `7`. The separate
[xpub provenance probe](bip32-official-xpub-probe.php) checks their published
Base58Check text and 33-byte key fields through two decoders on both runtimes.
The remaining boundary cases are controlled, independently justified by
secp256k1's field/order bounds or compared with pinned libsecp256k1; they are
not presented as published BIP vectors. The full PHP result objects match
across both runtimes and both adapters.

The raw Paragonie parser/serializer returned `02` followed by 32 `ff` bytes
as a point, even though its x-coordinate exceeds the secp256k1 field prime.
The probe preserves this raw observation separately from its admitted results.
The shared semantic guard now checks the coordinate bound before either
provider. The observed agreement depends on that guard; it is not evidence
that the raw APIs are interchangeable without validation.

This probe is still a Q06/Q07 boundary subset. The CKDpub composition
probe below adds public-path evidence; it does not close M03's full Q04–Q08/Q12
gate. The ECC probe maps exceptions from known malformed point fixtures to
`invalid_point`; it does not establish a general distinction between malformed
input and unexpected provider faults. No ECC provider is selected for
production.

The broader M02 trial lock also caused a `cjpgdk/bech32` nullable-parameter
deprecation to be printed during PHP 8.4 autoloading. That incidental codec
finding belongs to M04; the focused ECC lock avoids this unrelated diagnostic.

## BitWasp v1.1.0 and phpecc lineage check

The user-suggested [`phpecc/phpecc`](https://github.com/phpecc/phpecc) lead was
checked against BitWasp bitcoin-php's [v1.1.0 release](https://github.com/Bit-Wasp/bitcoin-php/releases/tag/v1.1.0),
whose commit is `527b1ee7d2cd958b5ce011c7918801ffbfb53f97`. BitWasp describes
this as a security release that replaced `phpecc` / `mdanter/ecc` with
`paragonie/ecc`; its [tagged Composer manifest](https://github.com/Bit-Wasp/bitcoin-php/blob/v1.1.0/composer.json)
requires `paragonie/ecc:^2.1.0`. The `1.0` branch pointed at the same commit
when checked on 2026-10-14; the release tag is the stable reference here.

The `phpecc/phpecc` repository publishes the [`mdanter/ecc` package](https://packagist.org/packages/mdanter/ecc).
Packagist marks that package abandoned and directs users to `paragonie/ecc`.
The [GitHub security advisory](https://github.com/advisories/GHSA-3494-cfwf-56hw)
identifies an unpatched point-addition timing issue in `mdanter/ecc` and states
that Paragonie hard-forked this code and patched its fork in 2.0.1. The M03
trial uses Paragonie 2.6.0. Because these packages share implementation lineage,
the older package cannot supply M03's independent ECC reference. BitWasp's
release remains useful historical integration evidence; no additional M03
provider or production dependency is selected by this check.

## Native libsecp256k1 reference candidate

For M03 qualification, Paragonie remains the provisional PHP adapter lead and
Simplito the comparison adapter. The newer
[`1ma/secp256k1-php` v0.1.2](https://github.com/1ma/secp256k1-php/tree/v0.1.2)
(Composer package `uma/secp256k1-php`, tag commit
`3ee95fa4299c108877e0527e8bd1efc4f57982ba`) is a candidate for the
independently implemented libsecp256k1 reference, and possibly a native
adapter if later qualification justifies it. Its [API stubs](https://github.com/1ma/secp256k1-php/blob/v0.1.2/secp256k1.stub.php)
expose public-key parse, serialize and tweak-add operations. Its
[Composer manifest](https://github.com/1ma/secp256k1-php/blob/v0.1.2/composer.json)
requires PHP 8.2+, and its [CI workflow](https://github.com/1ma/secp256k1-php/blob/v0.1.2/.github/workflows/ci.yml)
includes PHP 8.3 and 8.4. These upstream declarations and passing
[tagged CI run](https://github.com/1ma/secp256k1-php/actions/runs/38165368958)
were feasibility evidence before our local trial.

The tag's libsecp256k1 submodule is pinned to
`6e2c8bc4ecdc6e71dbe7a368f360d8d453ce435d` (v0.8.0). We built this
exact pair in disposable PHP 8.3.35 and 8.4.26 research containers with GMP;
the extension loaded as binding 0.1.2 / libsecp256k1 0.8.0 on both, and its
32 local PHPT tests passed on each runtime with no skips. The
[bounded native probe](native-ecc-reference-probe.php) and its
[8.3](native-probe-php83.json) and [8.4](native-probe-php84.json) outputs show
the same 13 point-admission outcomes and nine scalar-boundary outcomes as both
PHP adapters, including compressed `2G` and `−G`. Native parsing rejected all
three published BIP32 invalid points and the controlled malformed points,
including x-coordinates at or above the field prime. `G + (n−2)G` produced
`−G`; `2G + (n−2)G` and `−G + G` reached infinity. Raw native tweak-add
returned `false` for these infinity sums and invalid order-sized scalars,
while both short and long scalars threw `ValueError`. The adapter must
distinguish those owned failure meanings before or around the native call;
`false` alone does not identify the cause. The two native output objects match
after excluding the PHP-version header. This narrows the independent Q06/Q07
evidence; the raw binding result still needs semantic classification and these
cases do not verify all M03 behavior.

The binding is new and pre-1.0, even though its underlying
[libsecp256k1](https://github.com/bitcoin-core/secp256k1) is separately
maintained. The CKDpub comparison below challenges it against unchanged PHP
consumers for sourced paths and forced invalid cases; the focused native-false
probe below checks another failure boundary. M05 must separately weigh
deployment footprint and maintenance before any production selection. The older
[BitWasp binding](https://github.com/Bit-Wasp/secp256k1-php) remains historical
reference only; its latest tag is `v0.2.2` from 2019.

## Unchanged CKDpub consumer across three ECC adapters

The [research composition](ckdpub-composition-probe.php) consumes the same
public-node fixtures and owns one exact-index CKDpub path loop. Only its
public-point adapter changes: Paragonie, Simplito or the pinned native binding.
PHP runtime `hash_hmac`, `hash`, `pack` and `unpack` provide the delegated
HMAC, HASH160 and fixed-width operations. The research composition does not
decode Base58Check or implement curve arithmetic. It is not a selected public
PHP API, production package or general extended-key admission implementation.

The [public-only fixture file](ckdpub-public-fixtures.json) contains six
public derivation cases from BIP32 vectors 1 and 2: `m/0H -> 1`,
`m/0H/1/2H -> 2` and `2 -> 1000000000`, `m -> 0`,
`m/0/2147483647H -> 1`, and
`m/0/2147483647H/1/2147483646H -> 2`. The textual xpubs appear in the
pinned [BIP32 snapshot](../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki).
The 78-byte public payloads were taken from BitWasp bitcoin-php v1.1.0's
`tests/Data/bip32testvectors.json`; all 12 public payloads in those two source
vectors were separately Base58Check-encoded with the Tuupola trial codec and
matched their published xpub strings. Eleven of those payloads participate in
the six cases. The [official-xpub provenance probe](bip32-official-xpub-probe.php)
checks those eleven against the BIP's own xpub text in the SHA-256-pinned
snapshot, decodes each with both Tuupola Base58 2.2.0 and StephenHill Base58
2.1.0 from the M02 trial lock, verifies its four-byte double-SHA-256 checksum,
and compares the 78-byte payload with the BitWasp-sourced fixture. Its
[PHP 8.3/8.4 record](bip32-official-xpub-comparison.json) passed for all six
cases. This gives the payload oracle an independent normative source; the two
decoders still share runtime hashing/GMP, which M04 qualifies separately. The
broad M02 trial autoload emitted its already-known unrelated `cjpgdk/bech32`
nullable-parameter deprecation on PHP 8.4. No private fixture field is copied.
The exact expected child payload checks compressed public key, chain code, depth,
immediate-parent fingerprint and child number at once. The two-step fixture
also checks that the intermediate child is used as the next parent.

The [comparison record](ckdpub-comparison.json) captures six executions:
three unchanged-provider runs on PHP 8.3.35 and the same three on PHP 8.4.26.
All six agreed exactly on the six published vector payloads and the forced
case record. Injected HMAC left halves at group order failed at first,
intermediate and last requested positions, with exactly one HMAC call for each
attempted level and no later request or index advance. Controlled
`G + (n-1)G` infinity failures now do the same at first, intermediate and last
requested positions, with preceding zero tweaks retaining `G`. Zero preserved
the point while updating chain code and metadata;
order-plus-one failed before ECC; and the last public index remained exact,
including an invalid child there without retry. The probe rejected empty,
hardened, negative, fractional, wrong-type, non-list and overlong paths before
HMAC. A 255-component path from depth zero reached depth 255; synthetic imported
depths 253 and 254 accepted exactly their remaining capacity, while depths 254
and 255 rejected paths beyond it before HMAC. A generic `[0, 0]` derivation
matched two sequential exact-index calls, exercising the approved receiving
suffix without claiming a finished receiving API or account-key mapping.
The probe also rejected malformed payload length, wrong point prefix and
invalid/out-of-field points. It checked HMAC key/message order and normalized
injected short HMAC, HMAC exception, ECC exception, malformed ECC output,
admission exception and changed point on admission as provider failures.

This closes the Q04/Q05 research comparison for these sourced public edges and
path/depth cases. On its own it did not close M03: a concrete receiving API and
provisioned account-key mapping remain later implementation/qualification work;
the point corpus and scalar/domain extremes remain bounded; the remaining
M04 end-to-end Q08 integration and broader Q12 provider-fault containment
still need qualification. The PHP parser-fault challenge below distinguishes
the pinned malformed-point signals from different unexpected exceptions and
malformed parser returns. An internal fault that reproduces an identical
malformed-point signal remains indistinguishable at this boundary. The native
false classification below is bounded to the pinned source and tested API
operations. M05 must not select an adapter based on this subset alone.

## Q08 runtime hash/conversion and Q12 facility challenge

The [focused research probe](hash-conversion-probe.php) ran on PHP 8.3.35 and
8.4.26 with separate [8.3](hash-conversion-php83.json) and
[8.4](hash-conversion-php84.json) outputs. Its SHA-256 `abc` and million-`a`
expected answers come from [NIST's published test data](https://www.nist.gov/itl/ai/ai-standards-and-guidelines-group/nsrl-test-data).
The RIPEMD-160 empty and `abc` answers come from the
[algorithm authors' test table](https://homes.esat.kuleuven.be/~bosselae/ripemd160.html).
HMAC-SHA512 cases 1, 4 and 6 use the exact published key, data and full digest
from [RFC 4231](https://www.rfc-editor.org/rfc/rfc4231.html): case 4 includes
binary key/data and case 6 crosses SHA-512's 128-byte HMAC key block. Both
runtimes returned 32/20/64 binary bytes, matched the hex answers and rejected
swapped HMAC argument order. Empty SHA-256 and empty-key/message HMAC are also
exercised; the latter is recorded as a runtime observation without a claimed
published answer.

For composition, the probe hashes the 33-byte compressed key in the pinned
[BIP32 vector 2](ckdpub-public-fixtures.json) and gets the published child
fingerprint `bd16bee5` from the first four bytes of its 20-byte HASH160. It
also detects hex-text input and reversed hash order. PHP `pack('N')` and
`unpack('N')` preserved four-byte big-endian values at zero, one, `2^31-1`,
`2^31` and `2^32-1` on both tested 64-bit runtimes. The BIP32 vector 1
child number `1000000000` independently anchors bytes `3b9aca00`.
The [PHP pack manual](https://www.php.net/manual/en/function.pack.php) specifies
`N` as an unsigned four-byte big-endian value. This probe qualifies the
tested runtime width only; a representation strategy for narrower integer
runtimes remains an implementation decision, not an assumed baseline.
Research-level guards rejected negative, out-of-range, float, string, boolean
and null values, plus short/trailing byte fields. Direct `unpack('N')` on three
bytes emitted a warning and returned `false` on both runtimes, so exact length
admission must precede that call. These guards are examples for the eventual
boundary; they are not a production implementation.

Both runs normalized an actual unknown PHP hash/HMAC algorithm `ValueError`,
injected short/non-string results and a vendor exception to a stable
`provider_environment_failure` category without echoing the injected detail.
The research public wrapper also drops the raw exception from `getPrevious()`;
the probe records the raw `ValueError` class separately from that wrapper.
An explicit facility preflight likewise failed for an unavailable native
extension and unknown algorithm. Running the same probe with `php -n` proved
the missing-GMP preflight on [8.3](missing-gmp-php83.json) and
[8.4](missing-gmp-php84.json). This is bounded Q12 environment evidence: the
research preflight chooses no replacement provider. It does not prove that
arbitrary future adapter internals contain every provider fault.

Q08's M03 runtime/conversion subset now has sourced known answers and tested
boundaries; M04 still has its own codec and end-to-end composition qualification.
Q12 remains open for broader lower-level provider faults and common-mode
challenge. The bounded PHP parser-error distinction is recorded below.
The earlier [synthetic adapter](ckdpub-comparison.json) returned `null` on an
officially finite vector 2 child; all six unchanged-consumer runs interpreted
that as `invalid_child`. The follow-up [raw-result probe](php-ecc-result-origin-probe.php)
ran against the pinned Paragonie 2.6.0 and Simplito 1.0.12 packages on
[PHP 8.3](php-ecc-result-origin-php83.json) and
[PHP 8.4](php-ecc-result-origin-php84.json). Both raw additions returned point
objects, `isInfinity() === false` and the BIP32-published finite child bytes.
For `G + (n−1)G`, both returned point objects with `isInfinity() === true`.
Pinned [Paragonie point addition](https://github.com/paragonie/phpecc/blob/1a49380410b8ce826bc7fd3de6324053e049205c/src/Primitives/Point.php)
declares a `PointInterface` result; pinned
[Simplito point addition](https://github.com/simplito/elliptic-php/blob/be321666781be2be2c89c79c43ffcac834bc8868/lib/Curve/ShortCurve/Point.php)
returns a point object on the inspected branches. Neither raw API uses `null`
as its infinity signal in these runs.

`null` is a **research adapter outcome**, with the documented meaning
"provider-confirmed infinity." The injected adapter deliberately broke that
contract. CKDpub cannot distinguish a lying adapter from a truthful one using
only that outcome; this is an adapter trust boundary, not an observed provider
bug or a reason to change the consumer's `null → invalid_child` rule. The
selected adapter must map only an actual provider-confirmed infinity to `null`,
normalize other raw returns or exceptions as provider failure, and undergo
lower-boundary fault qualification. Unexpected parser exceptions can still look
like malformed points only if they reproduce the pinned invalid-point signal
exactly; the focused challenge below documents this limit. Native `false` is
challenged separately below. Broader provider trust remains for M05 selection
and later assembled/independent qualification.

## PHP parser faults: bounded Q12 distinction

The original CKDpub PHP research adapters mapped every raw parser exception to
`malformed_point`. This also mislabeled unexpected internal exceptions raised
while parsing an otherwise valid BIP32 public point. The
[updated composition probe](ckdpub-composition-probe.php) now checks the
33-byte compressed point shape first, then maps only the source-pinned parser's
off-curve signal to `malformed_point`. Other parser exceptions, non-point
returns, and malformed serializer outputs become `provider_failure`.
For the Simplito adapter, a successful compressed-point parse followed by
`validate() === false` is also a provider inconsistency: its pinned
`pointFromX` rejects an off-curve compressed input during parsing.

The [pinned Paragonie parser](https://github.com/paragonie/phpecc/blob/1a49380410b8ce826bc7fd3de6324053e049205c/src/Primitives/CurveFp.php)
raises `PointRecoveryException` when compressed x-coordinate recovery fails.
The [pinned Simplito parser](https://github.com/simplito/elliptic-php/blob/be321666781be2be2c89c79c43ffcac834bc8868/lib/Curve/ShortCurve.php)
raises a generic `Exception("Invalid point")` on that path. The latter
classification depends on an exact message and must be requalified for any
provider version change. Both are trusted provider signals; they do not prove
what actually happened inside a faulty parser.

The [four-run record](php-parser-fault-comparison.json) covers both providers
on PHP 8.3 and 8.4. The published off-curve point from BIP32 vector 5 stays
`malformed_point`; an injected unrelated exception, an incorrect parser return,
and an unrelated parser exception become `provider_failure`. Deliberately
injecting the exact invalid-point signal while parsing a published valid point
still gives `malformed_point`. A boundary that sees only that signal cannot
distinguish its true cause without independent evidence. The ordinary CKDpub
comparison still matches the existing six-run result on both PHP runtimes and
both PHP adapters. This is a research-adapter correction, not production
provider qualification or closure of Q12.
The Issue #31 addition injects a real Simplito `KeyPair` with corrupted point
state after a successful parse of a valid published point. Its failed
validation now yields `provider_failure` in the same four-run record.

To reproduce the focused challenge with the M03 Composer lock at `/research`
and this directory mounted at `/probe`, run each command on PHP 8.3 and 8.4:

```sh
php /probe/ckdpub-composition-probe.php paragonie --parser-faults
php /probe/ckdpub-composition-probe.php simplito --parser-faults
```

## Shared-code negative control and ECC lineage

The three ECC paths are not three independent end-to-end BIP32 implementations.
The two PHP adapters call distinct point implementations: Paragonie's
[point operations](https://github.com/paragonie/phpecc/blob/1a49380410b8ce826bc7fd3de6324053e049205c/src/Primitives/Point.php)
retain the `Mdanter\Ecc` lineage, while Simplito's
[point operations](https://github.com/simplito/elliptic-php/blob/be321666781be2be2c89c79c43ffcac834bc8868/lib/Curve/ShortCurve/Point.php)
use its `Elliptic` and `simplito/bn-php` graph. Their locked package graphs do
not depend on each other, although both PHP paths use GMP. The native binding
delegates point arithmetic to source-pinned
[Bitcoin Core libsecp256k1](https://github.com/bitcoin-core/secp256k1/blob/6e2c8bc4ecdc6e71dbe7a368f360d8d453ce435d/src/eckey_impl.h).
The older `mdanter/ecc` package is a known Paragonie ancestor, not another
independent oracle. This is a code/dependency-lineage assessment, not proof
against every shared mathematical defect or runtime failure.

All three adapters nevertheless share the **same research CKDpub consumer**,
PHP HMAC call, public-node parser, scalar/point guards and test harness. Their
agreement alone therefore cannot validate those shared parts. The
[common-mode challenge](common-mode-comparison.json) deliberately exchanges
the HMAC key and message for BIP32 vector 2 root-to-0. Both PHP adapters then
produce the same *wrong* complete child node on PHP 8.3 and 8.4. A second
injection changes only one bit of the derived chain code; both adapters again
agree, and their public point still matches the published point. In both
cases comparison with the **full published BIP32 node**, including chain code,
detects the fault. The expected node is pinned in
[the sourced fixture](ckdpub-public-fixtures.json), independently recovered
from its published xpub by the [xpub provenance probe](bip32-official-xpub-probe.php).
These are synthetic shared-consumer faults, not observed defects in either ECC
package. They show why ECC adapter agreement and point-only checks cannot
replace official complete-node counter-proofs. The native extension was not
rerun for these two injections; it shares this consumer and cannot provide an
independent HMAC/metadata calculation by itself. A common-mode defect in the
native `false` confirmation's own libsecp256k1 calls also remains possible.

With the focused Composer lock at `/research` and M03 mounted at `/probe`,
reproduce the PHP negative controls on both runtimes:

```sh
php /probe/ckdpub-composition-probe.php paragonie --common-mode
php /probe/ckdpub-composition-probe.php simplito --common-mode
```

## Native `false`: source path and bounded resolution

The pinned [PHP binding's tweak-add wrapper](https://github.com/1ma/secp256k1-php/blob/3ee95fa4299c108877e0527e8bd1efc4f57982ba/ext_secp256k1.c#L231-L257)
checks 32-byte width, copies the opaque key into a temporary, calls
libsecp256k1, returns `false` without replacing the PHP object on failure,
and replaces it only on success. The pinned [libsecp256k1 public-tweak API](https://github.com/bitcoin-core/secp256k1/blob/6e2c8bc4ecdc6e71dbe7a368f360d8d453ce435d/include/secp256k1.h#L793-L811)
documents invalid arguments and an invalid sum as failure. Its [implementation](https://github.com/bitcoin-core/secp256k1/blob/6e2c8bc4ecdc6e71dbe7a368f360d8d453ce435d/src/secp256k1.c#L712-L734)
returns zero for scalar overflow, an invalid opaque key, or the [point at
infinity](https://github.com/bitcoin-core/secp256k1/blob/6e2c8bc4ecdc6e71dbe7a368f360d8d453ce435d/src/eckey_impl.h#L62-L75).
Fresh parsing and exact serialization of an admitted compressed point exclude
the invalid-key route in ordinary execution. Exact 32-byte and `< n` checks
exclude the width and overflow routes before tweak-add. The focused probe also
found that direct construction, reflection without a constructor, cloning,
serialization and unserialization do not produce an opaque key object on
either tested PHP runtime. This is bounded evidence for the PHP-facing object
boundary, not a claim about arbitrary native memory corruption. Under the pinned
implementation's contract, infinity is the remaining expected `false`.
The research classifier treats a native parse rejection as invalid point
material, but a successful parse that serializes to different bytes as a
provider failure; these are distinct observable events.

The [research-only classifier](native-false-classifier.php) goes further: after
`false`, it verifies that the PHP key stayed unchanged, then confirms infinity
through separate native point operations. For a nonzero public BIP32 tweak,
it compares the point `tweak × G` with the negated parent through both native
point comparison and compressed serialization; agreement means the sum is
infinity. It also rejects a no-op negation. A `false` for zero, a changed
object, a failed confirmation, or an exception is a provider failure. This
calls the binding's
`ec_pubkey_create` with **publicly derived HMAC material** as a point-multiply
facility; no wallet private key, seed, signing or private derivation enters the
research composition. M05 must review whether that API route is acceptable
inside a selected public-only adapter or require an equivalent delegated
confirmation. The classifier is a qualification spike, not a frozen API.
The [pinned binding stub](https://github.com/1ma/secp256k1-php/blob/3ee95fa4299c108877e0527e8bd1efc4f57982ba/secp256k1.stub.php)
declares `ec_pubkey_negate` as `void`; there is no ignored boolean failure
return at that call site.

The [focused probe](native-false-classification-probe.php) and
[comparison record](native-false-comparison.json) cover both PHP 8.3.35 and
8.4.26 against the same pinned extension. Raw `false` occurred for three
distinct infinity cases (`G + (n−1)G`, `2G + (n−2)G`, `−G + G`) and for
order-sized scalars. Wrong width threw `ValueError`; zero and one succeeded.
The PHP object and its alias remained unchanged after each raw failure, and
the same object succeeded on the next valid tweak. Forced `false` on finite
and zero sums, mutation followed by `false`, non-boolean return and injected
vendor exceptions all became provider failures. A forced `false` where the
separate native equality check confirms infinity remained an invalid child. The
classifier also reports `provider_unavailable` when the extension is absent.
The unchanged CKDpub consumer still matched its prior outputs on both runtimes
after using the classifier.

The same pinned binding and libsecp256k1 commits were rebuilt for a further
confirmation-path challenge on PHP 8.3.35 and 8.4.26. Its expanded
[comparison record](native-false-comparison.json) includes injected
confirmation `false`, exception and non-boolean return for a real infinity:
each becomes `provider_failure`. Injected native comparator false-positive and
false-negative results conflict with serialized equality and become
`provider_failure`. It also deliberately makes a finite tweak
return `false` **and** makes the same-library confirmation lie `true`; the
classifier then reports infinity. An injected `true` without any tweak returns
the unchanged but valid-looking parent point. These last two are controlled
counterexamples to any claim that local serialization, type checks or a second
call into the same library can prove arbitrary provider correctness. They are
not observed behavior of the real binding. The separate `G + G = 2G` control
and two PHP ECC paths expose these *tested* wrong outcomes; sourced BIP32
complete-node checks protect the exercised derivations. None makes the native
confirmation independent at runtime.

The [independent PHP adapter cross-check](native-false-crosscheck-probe.php)
ran the same three infinity sums plus the zero and one finite controls through
both Paragonie and Simplito on PHP 8.3/8.4. Their point and infinity outcomes
agreed with one another and the native results, as recorded in the comparison
file. This checks the mathematics through distinct PHP implementations; it
does not make the two native confirmation calls independent at runtime.

This resolves the observed **multi-meaning `false`** for the pinned, admitted
inputs and tested ordinary faults. A common-mode ECC fault can fool both
operations, as the injected false-positive demonstrates; M05's independent
selection gate remains necessary. If the public-only adapter boundary cannot
admit this confirmation route, the native binding must remain an independent
reference or be rejected for production until an equivalent qualified route
exists. No false result is silently treated as a successful child.

## M03 research gate assessment — 2026-10-15

The M03-specific research checks in the [execution plan](../EXECUTION-PLAN.md)
have the following bounded evidence. The [independent review](cp-reviewer-2026-10-15-204006.md)
returned the earlier revision to execution for R1. The
[follow-up review](cp-reviewer-2026-10-16-074200.md) passed CP-07/CP-10 at
`2aa0194`. This is not a production-provider selection or a completed public
capability.

| M03 check | Evidence and disposition |
|---|---|
| Q04/Q05 exact CKDpub | Six published BIP32 public derivations match their complete nodes across two PHP adapters and native libsecp256k1 on PHP 8.3/8.4. Empty/hardened/wrong-type paths and depth overflow fail before HMAC. Zero, order-sized and confirmed-infinity child cases preserve exact-index atomicity; the two invalid-child kinds were forced at first, intermediate and last positions without retry. Last public index and full depth capacity were exercised. |
| Q06/Q07 ECC boundaries | The 13-point/nine-scalar/two-cross-point corpus agrees across the three ECC implementations; BIP32 vector 5 supplies three published malformed-point bytes. Finite and infinity raw provider results, strict adapter admission, round trips and the PHP parser-fault distinction were checked. The native library is an independent ECC implementation, while the two PHP paths share GMP and the common CKDpub consumer. |
| Q08 runtime subset | SHA-256, RIPEMD-160, HMAC-SHA512, HASH160 and fixed-width conversion matched independently sourced known answers and boundary cases on both runtimes. M04 still owns codec and complete address integration. |
| Q12 controlled failures | Missing facilities, malformed HMAC/ECC results, provider exceptions, PHP parser errors, native multi-meaning `false`, comparator/confirmation faults and shared-consumer negative controls were exercised. Ordinary failures normalize without a fallback or partial child. Exact mimicked provider signals, coordinated same-library faults and plausible but wrong successful outputs remain inherent trust limits; M05 provider selection and later assembled/independent gates must assess them. |

The focused Composer graph and source identities are pinned. Paragonie is a
provisional PHP lead, Simplito is a real replacement exercise, and the native
binding is a separately implemented reference candidate. No backend is
selected. At this 2026-10-15 assessment, the remaining M03 handoff step was
the maintainer's manual merge decision. PR #12 subsequently merged the handoff;
M04/M05 and M09 own the downstream obligations described above.

## Independent review findings and executor response — 2026-10-16

The preserved [independent report](cp-reviewer-2026-10-15-204006.md) reviewed
commit `f3afccb76b8a345eb386c54e0e2a3df419ef768e` and returned M03 to
execution. Its CP-07 and CP-10 failures apply to that reviewed revision;
changing the executor branch does not rewrite the issued verdict.

- **R1, required:** `NativePointTrial::admit()` previously reported
  `malformed_point` when a successful native parse serialized to different
  bytes. It now reserves that category for an actual native parse rejection and
  classifies a changed finite point, short/non-string serialization or
  serializer exception as `provider_failure`. The
  [focused fault record](native-admission-fault-comparison.json) exercises the
  real native parse with controlled serializer faults on PHP 8.3/8.4. The
  published BIP32 vector 5 off-curve point still fails as `malformed_point`
  before the serializer is called. Normal native CKDpub results were rerun on
  both runtimes and still equal the saved [complete-node comparison](ckdpub-comparison.json).
- **R2, downstream:** the CKDpub research helper's internal `TrialFailure` can
  retain a raw throwable in `getPrevious()`. No public address exception is
  exposed by M03. M05/M06 must define and test one public sanitization boundary
  so neither the message, context nor exception chain exposes vendor diagnostics
  or sensitive material. The isolated hash/conversion wrapper's containment
  test does not establish that property for CKDpub.
- **R3, nonblocking:** the HASH160 negative control now deliberately reverses
  the two delegated hashes and checks that the resulting four-byte fingerprint
  (`13ce5fe1`) fails the same published BIP32 fingerprint assertion that the
  correct order passes (`bd16bee5`). The updated [PHP 8.3](hash-conversion-php83.json)
  and [PHP 8.4](hash-conversion-php84.json) records differ from the earlier
  records only by this negative-control result. A sourced full 20-byte HASH160
  answer and address integration remain M04 work.

To repeat R1 after the focused Composer install at `/research` and the pinned
native extension at `/native`, run this mode on each PHP runtime with M03 mounted
at `/probe`, as in the [reproduction](#reproduction) instructions:

```sh
LD_LIBRARY_PATH=/native/build/secp256k1/lib \
    php -d extension=/native/modules/secp256k1.so \
    /probe/ckdpub-composition-probe.php native --native-admission-faults
```

The [independent follow-up review](cp-reviewer-2026-10-16-074200.md) reran the
corrected evidence at `2aa0194`, closed R1 and R3, and passed CP-07/CP-10.
It retained R2 for M05/M06. The issued first review remains unchanged for its
earlier revision.

## Issue #31 corrective research — 2026-10-19

The deep review began at `b07e6082b00a5ffba2b1dc384ba0f98d46ebb391`;
the focused correction is in [PR #37](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/37).
It reproduced the published BIP32, ECC, native and runtime records on PHP
8.3.35 and 8.4.26, then found two research-boundary gaps. At technical
revision `85fdae149c803c713795eea7a1bb6ed4b9dc118e`:

- `SimplitoPointTrial::admit()` maps failed validation **after successful
  parsing** to `provider_failure`. The injected real `KeyPair` with corrupted
  point state reaches that branch; the published BIP32 off-curve point still
  fails during parsing as `malformed_point`. The updated
  [parser record](php-parser-fault-comparison.json) covers both runtimes.
- The shared CKDpub consumer delegates both HASH160 stages through a guarded
  fingerprint helper. Exceptions, non-string and wrong-width results become
  `provider_failure`, with no next index attempted. The focused
  [hash-fault record](hash-fault-comparison.json) covers all three ECC adapters
  on both runtimes, `hash` and `hash_hmac` disabled separately, input-path
  precedence, and a plausible wrong digest rejected by the full published
  node. The runtime probe preflight now checks both functions as well as the
  extension and algorithms. Its ordinary [8.3](hash-conversion-php83.json) and
  [8.4](hash-conversion-php84.json) answers remain unchanged.
- Internal `TrialFailure` objects no longer retain raw provider exceptions in
  `getPrevious()`. Every forced research failure checks its category, message
  and empty exception chain. This addresses the M03 research-chain exposure
  observed in R2. A future **public** exception boundary must still be defined
  and checked against selected providers in M05/M06; no such API exists in M03.

The six complete BIP32 child nodes and all pre-existing CKDpub forced cases
still match [the saved comparison](ckdpub-comparison.json) across three adapters
and both runtimes. Mutants restoring the old Simplito classification, removing
the SHA-256 width guard, or reattaching a vendor diagnostic each fail the new
controls on both runtimes. The native reference still confirms infinity through
the same library; its coordinated-fault limit remains as previously recorded.
These are research corrections pending an independent check of the corrected
revision, not production-provider selection or assembled-capability verification.

With the focused Composer install at `/research`, M03 at `/probe`, and the
pinned native extension loaded for `native`, reproduce the new controls on
each PHP runtime:

```sh
php /probe/ckdpub-composition-probe.php simplito --parser-faults
php /probe/ckdpub-composition-probe.php paragonie --hash-faults
php /probe/ckdpub-composition-probe.php simplito --hash-faults
LD_LIBRARY_PATH=/native/build/secp256k1/lib \
    php -d extension=/native/modules/secp256k1.so \
    /probe/ckdpub-composition-probe.php native --hash-faults
php -d disable_functions=hash \
    /probe/ckdpub-composition-probe.php paragonie --expect-disabled-hash
php -d disable_functions=hash_hmac \
    /probe/ckdpub-composition-probe.php paragonie --expect-disabled-hmac
php -d disable_functions=hash \
    /probe/hash-conversion-probe.php --expect-disabled-hash
php -d disable_functions=hash_hmac \
    /probe/hash-conversion-probe.php --expect-disabled-hmac
```

The result objects match the parser and hash-fault records above. The disabled
function controls require the corresponding `-d` setting; ordinary probes
remain those in the reproduction section below.

## Reproduction

The local runs used research images
`sha256:571bc51823ea9100dd8a6430e9426390a5fbb69cf2dffa7b2eae5397228407a6`
for PHP 8.3 and
`sha256:e885005563f9c9adef4a565ff59e3f014f2ba559bd746fa773b9cd89a8dc64b5`
for PHP 8.4. Each extends this repository's pinned development image with
GMP through [Containerfile.gmp](Containerfile.gmp). Debian package sources
were not snapshot-pinned, so the IDs identify these local runs rather than a
reproducible binary build.

From the repository root, with Podman and network access for image/package
installation, build the base and research images:

```bash
source scripts/php-images.sh
for version in 8.3 8.4; do
    podman build --build-arg "PHP_IMAGE=$(php_image "$version")" \
        -f Dockerfile -t "localhost/primitives-php${version//./}-m03-base" .
    podman build --build-arg "BASE=localhost/primitives-php${version//./}-m03-base" \
        -f docs/pips/PIP-0003/M03/Containerfile.gmp \
        -t "localhost/primitives-php${version//./}-m03-gmp" .
done
```

For each runtime, copy `M03-ECC-COMPOSER.json` and
`M03-ECC-COMPOSER.lock` to `composer.json` and `composer.lock` in a separate
scratch directory. Install with `composer install --no-interaction
--no-plugins --no-scripts --prefer-dist`, then run `composer
check-platform-reqs`. Mount that directory read-only at `/research` and M03
read-only at `/probe`, and run `php /probe/ecc-boundary-probe.php` with
`--network none`. The script exits nonzero if its bounded expectations or the
two adapter outcomes differ. Compare its JSON with the stored runtime output,
excluding only the runtime header if patch versions differ. Research packages
must remain outside the library's production Composer manifest.

For the native reference probe, clone tag `v0.1.2` with its submodule into a
scratch directory, verify the binding and libsecp256k1 commits above, and
copy the checkout separately for each runtime. The following commands were
run inside each copy mounted at `/probe` in the corresponding M03 GMP image,
with this M03 directory mounted read-only at `/research`. Build commands need
network access to install CMake; the final probe runs with `--network none`.
The Debian CMake package was not snapshot-pinned, so these are source-pinned
results rather than bit-for-bit reproducible binary builds.

```sh
apt-get update
apt-get install -y --no-install-recommends cmake
cd /probe
cmake -S secp256k1 -B build/secp256k1 \
    -DCMAKE_INSTALL_PREFIX=/probe/build/secp256k1 \
    -DSECP256K1_ENABLE_MODULE_RECOVERY=ON \
    -DSECP256K1_BUILD_BENCHMARK=OFF \
    -DSECP256K1_BUILD_CTIME_TESTS=OFF \
    -DSECP256K1_BUILD_TESTS=OFF \
    -DSECP256K1_BUILD_EXHAUSTIVE_TESTS=OFF
cmake --build build/secp256k1 -j2
cmake --install build/secp256k1
phpize
PKG_CONFIG_PATH=/probe/build/secp256k1/lib/pkgconfig ./configure --with-secp256k1
make -j2
NO_INTERACTION=1 REPORT_EXIT_STATUS=1 \
    LD_LIBRARY_PATH=/probe/build/secp256k1/lib make test TESTS="-q"
LD_LIBRARY_PATH=/probe/build/secp256k1/lib \
    php -d extension=/probe/modules/secp256k1.so \
    /research/native-ecc-reference-probe.php
```

The probe exits nonzero if its bounded expectations change. Compare the JSON
output with the file for the corresponding PHP runtime. The native extension
and build dependencies are research-only and are not in Primitives' Composer
manifest.

For the CKDpub composition run, mount the same focused PHP Composer install at
`/research` and this M03 directory at `/probe`. For each PHP runtime, run:

```sh
php /probe/ckdpub-composition-probe.php paragonie
php /probe/ckdpub-composition-probe.php simplito
LD_LIBRARY_PATH=/native/build/secp256k1/lib \
    php -d extension=/native/modules/secp256k1.so \
    /probe/ckdpub-composition-probe.php native
```

The native run additionally mounts the matching source-pinned compiled checkout
at `/native`. Each command exits nonzero if a sourced vector or forced semantic
case changes. Excluding only `php` and `provider`, the six output objects must
match one another and the `common_result` in the comparison record. The
matrix entries record the observed patch versions and provider identity.

To counter-check the public fixture payloads directly against the official
BIP32 xpub text, mount the M02 trial Composer install at `/research` and the
whole `PIP-0003` directory at `/pip`, then run on both PHP runtimes:

```sh
php /pip/M03/bip32-official-xpub-probe.php
```

Its successful JSON result must match the corresponding entry in
`bip32-official-xpub-comparison.json`. The broad M02 trial autoload may also
print the unrelated PHP 8.4 `cjpgdk/bech32` deprecation noted above.

To repeat the native false/fault probe in each pinned native container, with
M03 mounted at `/probe` and the compiled extension at `/native`, run:

```sh
LD_LIBRARY_PATH=/native/build/secp256k1/lib \
    php -d extension=/native/modules/secp256k1.so \
    /probe/native-false-classification-probe.php
```

The command exits nonzero if any raw or classified outcome changes. Compare
both outputs with `native-false-comparison.json`, excluding only `php` before
checking equality. To check the missing-extension branch, require
`native-false-classifier.php` in the same PHP image without loading the
extension; a call must raise `provider_unavailable` before native functions
are invoked. With the focused ECC Composer install at `/research`, run
`php /probe/native-false-crosscheck-probe.php` on both runtimes and compare its
`results` field with `php_adapter_crosscheck.agreed_results` in the record.
The false classifier and fault injections are research-only.

To repeat the Q08/Q12 runtime probe, mount M03 read-only at `/probe` in each
M03 GMP image and run both commands:

```sh
php /probe/hash-conversion-probe.php
php -n /probe/hash-conversion-probe.php --expect-missing-gmp
```

The first output matches `hash-conversion-php83.json` or
`hash-conversion-php84.json`; the second matches `missing-gmp-php83.json` or
`missing-gmp-php84.json`. `php -n` intentionally disables the separately
installed GMP extension. Only the PHP patch-version header may differ on a
later image. The missing-native check in the first command assumes the native
extension is not loaded in the GMP image; run the ECC/native probes separately
with the source-pinned extension when testing the native adapter.

To reproduce the PHP ECC raw-result origin check, install the focused M03
Composer lock at `/research`, mount M03 at `/probe`, and run on both runtimes:

```sh
php /probe/php-ecc-result-origin-probe.php
```

The result must match `php-ecc-result-origin-php83.json` or
`php-ecc-result-origin-php84.json` except for the PHP patch version. This
checks two real raw addition paths; it does not certify arbitrary future
provider faults or a production adapter.
