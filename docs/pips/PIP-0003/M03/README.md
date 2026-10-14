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

This is only a Q06/Q07 boundary subset. M03 still needs a full invalid-point
corpus, unchanged CKDpub consumer and exact-path Q04/Q05 fixtures, independent
libsecp256k1 evidence, Q08 HMAC/metadata checks, deliberate provider/environment
fault injection and Q12 failure containment, as well as maintenance and
dependency-path review. The probe maps exceptions from known malformed point
fixtures to `invalid_point`; it does not establish a general distinction
between malformed input and unexpected provider faults. No ECC provider is
selected for production.

The broader M02 trial lock also caused a `cjpgdk/bech32` nullable-parameter
deprecation to be printed during PHP 8.4 autoloading. That incidental codec
finding belongs to M04; the focused ECC lock avoids this unrelated diagnostic.

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
