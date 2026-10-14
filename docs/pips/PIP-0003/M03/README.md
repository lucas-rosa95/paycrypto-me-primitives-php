# M03 — ECC qualification research in progress

This research branch follows the accepted M02 record merged into `main` by
[PR #9](https://github.com/paycrypto-me/paycrypto-me-primitives-php/pull/9).
It does not add a production provider or claim that M03 has passed. The
[execution plan](../EXECUTION-PLAN.md) remains
the source of M03's complete Q04–Q08/Q12 gate.

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
seven point-admission cases and six tweak cases. They admitted the compressed
generator; rejected short, long, wrong-prefix, off-curve and out-of-field
points; preserved the generator for a zero tweak; returned a point for tweak
one; reported infinity for an order-minus-one tweak; and rejected order,
order-plus-one and wrong-width tweaks. The full result objects match across
both runtimes. These are controlled cases, not independent proof that the
resulting point for tweak one is correct.

The raw Paragonie parser/serializer returned `02` followed by 32 `ff` bytes
as a point, even though its x-coordinate exceeds the secp256k1 field prime.
The probe preserves this raw observation separately from its admitted results.
The shared semantic guard now checks the coordinate bound before either
provider. The observed agreement depends on that guard; it is not evidence
that the raw APIs are interchangeable without validation.

This first probe is only a Q06/Q07 boundary subset. The CKDpub composition
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

For the next M03 gate, Paragonie remains the provisional PHP adapter lead and
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
the same generator and zero/one-tweak points as both PHP adapters, including
the compressed `2G` result. Native parsing rejected the same six malformed
point fixtures, including x-coordinates at or above the field prime. Raw
native tweak-add returned `false` for both the infinity case (`n - 1`) and
invalid order-sized scalars, while a short scalar threw `ValueError`. The
adapter must distinguish those owned failure meanings before or around the
native call; `false` alone does not identify the cause. The two native output
objects match after excluding the PHP-version header. This narrows the
independent Q06/Q07 evidence; it does not verify CKDpub or all M03 cases.

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

The [public-only fixture file](ckdpub-public-fixtures.json) contains BIP32
vectors 1 and 2: `m/0H -> 1`, `m/0H/1/2H -> 2 -> 1000000000`, and `m -> 0`.
The textual xpubs appear in the pinned [BIP32 snapshot](../M00/reviews/bitwasp-public-derivation/bip-0032.mediawiki).
The 78-byte public payloads were taken from BitWasp bitcoin-php v1.1.0's
`tests/Data/bip32testvectors.json`; all seven participating public payloads
were separately Base58Check-encoded with the Tuupola trial codec and matched
their published xpub strings. No private fixture field is copied. The exact
expected child payload checks compressed public key, chain code, depth,
immediate-parent fingerprint and child number at once. The two-step fixture
also checks that the intermediate child is used as the next parent.

The [comparison record](ckdpub-comparison.json) captures six executions:
three unchanged-provider runs on PHP 8.3.35 and the same three on PHP 8.4.26.
All six agreed exactly on the three published vector payloads and the forced
case record. Injected HMAC left halves at group order failed at first,
intermediate and last requested positions, with exactly one HMAC call for each
attempted level and no later request or index advance. Zero preserved the
point while updating chain code and metadata; `G + (n-1)G` failed as infinity;
order-plus-one failed before ECC; and the last public index remained exact.
The probe rejected empty, hardened, negative and wrong-type paths, depth
exhaustion, malformed payload length, wrong point prefix and invalid/out-of-field
points. It checked HMAC key/message order and normalized injected short HMAC,
HMAC exception, ECC exception, malformed ECC output, admission exception and
changed point on admission as provider failures.

This closes the unchanged-consumer comparison for these sourced paths and
forced cases. It does **not** close M03: the point corpus and scalar/domain
extremes remain bounded; long-key/empty/binary hash and conversion Q08 cases,
other missing runtime facilities and broader provider-fault containment still
need qualification. The PHP adapters' parser exceptions are still treated as
malformed points for known bad fixtures; unexpected faults inside those parsers
are not independently distinguishable here. The native false classification
below is bounded to the pinned source and tested API operations. M05 must not
select an adapter based on this subset alone.

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

The [research-only classifier](native-false-classifier.php) goes further: after
`false`, it verifies that the PHP key stayed unchanged, then confirms infinity
through separate native point operations. For a nonzero public BIP32 tweak,
it compares the point `tweak × G` with the negated parent; equality means the
sum is infinity. A `false` for zero, a changed object, a failed confirmation,
or an exception is a provider failure. This calls the binding's
`ec_pubkey_create` with **publicly derived HMAC material** as a point-multiply
facility; no wallet private key, seed, signing or private derivation enters the
research composition. M05 must review whether that API route is acceptable
inside a selected public-only adapter or require an equivalent delegated
confirmation. The classifier is a qualification spike, not a frozen API.

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

The [independent PHP adapter cross-check](native-false-crosscheck-probe.php)
ran the same three infinity sums plus the zero and one finite controls through
both Paragonie and Simplito on PHP 8.3/8.4. Their point and infinity outcomes
agreed with one another and the native results, as recorded in the comparison
file. This checks the mathematics through distinct PHP implementations; it
does not make the two native confirmation calls independent at runtime.

This resolves the observed **multi-meaning `false`** for the pinned, admitted
inputs and tested faults. It does not prove that every possible native defect
can be diagnosed by another operation in the same library. A common-mode ECC
fault could fool both operations; broader independent qualification and M05's
selection gate remain necessary. If the public-only adapter boundary cannot
admit this confirmation route, the native binding must remain an independent
reference or be rejected for production until an equivalent qualified route
exists. No false result is silently treated as a successful child.

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
