# Issue #17 — independent M00 probe reproduction

Issue: [#17](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/17), under [#14](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/14). Scope: historical M00 feasibility evidence only. This check does not qualify a production provider or change the M00 review verdict.

## Inputs and recovered environment

The unchanged entries in both issued `SHA256SUMS` manifests verified with `sha256sum -c`, excluding each later-edited README as their instructions require. The six newly downloaded candidate archives all matched `ARCHIVE-SHA256SUMS` byte for byte. The [fetch script](research/provider-refresh-2026-10-02/fetch-candidates.sh) pins:

| Source | Commit |
|---|---|
| `tuupola/base58` | `a2fac671f14890c8fa62ac081eeeb90f42839637` |
| `paragonie/phpecc` | `1a49380410b8ce826bc7fd3de6324053e049205c` |
| `simplito/elliptic-php` | `be321666781be2be2c89c79c43ffcac834bc8868` |
| `stephen-hill/base58php` | `3030c00c0a1e1b78520f3ace6fbf813dacddfab5` |
| `simplito/bn-php` | `83446756a81720eacc2ffb87ff97958431451fd6` |
| `simplito/bigint-wrapper-php` | `cf21ec76d33f103add487b3eadbd9f5033a25930` |

The WooCommerce reference checkout was recovered at the provider pack's recorded `777559de23002d84ad243be50de243df9690bbf1`. Its `src/trunk/composer.lock` has SHA-256 `405b94cee7ddf97924254437cecb90a27d58634310f3da10d12cf9033170d5aa`. All six packages in the [recorded installed-package list](research/provider-refresh-2026-10-02/installed-reference-packages.json) have the same version and source commit in that lock: `bitwasp/bech32` v0.0.1 `e1ea58c`, `bitwasp/bitcoin` v1.1.0 `527b1ee`, `bitwasp/buffertools` v0.5.7 `133746d`, `genkgo/php-asn1` v2.9.0 `dc53534`, `paragonie/ecc` v2.5.0 `d25bd2a`, and `paragonie/sodium_compat` v2.5.0 `4714da6`. The complete installed graph was regenerated from that lock with Composer 2.10.3, including development packages, using `--no-scripts --ignore-platform-reqs --prefer-dist`. The lock revision and all 463 files in the four-package BitWasp manifest match the preserved output. The older BitWasp investigation's checkout commit `2c36c4d7197c4464b011ea63a2f768bb42b6c02d` was unavailable from the current remote; the original installed vendor directory and generated Composer autoloader were also unavailable.

Runs used isolated, read-only Podman containers with no network. PHP 8.3.35, 64-bit, GMP 8.3.35 used local image `sha256:571bc51823ea9100dd8a6430e9426390a5fbb69cf2dffa7b2eae5397228407a6`. PHP 8.1.34, 64-bit, without GMP used `sha256:15b11efaa694f4b629edb433c9755d93ca5658e7fa2f4489a12f6859c5e43a53`. These are different local images from those originally recorded, although their relevant PHP/extension identities match. No native `secp256k1` extension was loaded.

## Commands

Run from the repository root. The Composer 2.10.3 install used the recorded lock, with the reference plugin and its dependency directory confined to scratch storage:

```bash
repo_root=$PWD
provider_root="$repo_root/docs/pips/PIP-0003/M00/research/provider-refresh-2026-10-02"
bitwasp_root="$repo_root/docs/pips/PIP-0003/M00/reviews/bitwasp-public-derivation"
git clone --filter=blob:none https://github.com/paycrypto-me/paycrypto-me-for-woocommerce.git /tmp/issue17-reference
git -C /tmp/issue17-reference checkout --detach 777559de23002d84ad243be50de243df9690bbf1
bash "$provider_root/fetch-candidates.sh" /tmp/issue17-candidates
(cd /tmp/issue17-candidates && sha256sum -c "$provider_root/ARCHIVE-SHA256SUMS")
podman run --rm --network host --userns keep-id -v /tmp/issue17-reference/src/trunk:/app -w /app docker.io/library/composer:latest install --no-interaction --no-scripts --ignore-platform-reqs --prefer-dist
for runtime in 83 81; do
  if [ "$runtime" = 83 ]; then image=sha256:571bc51823ea9100dd8a6430e9426390a5fbb69cf2dffa7b2eae5397228407a6; else image=sha256:15b11efaa694f4b629edb433c9755d93ca5658e7fa2f4489a12f6859c5e43a53; fi
  podman run --rm --network none --read-only \
    -v /tmp/issue17-candidates:/candidates:ro \
    -v /tmp/issue17-reference/src/trunk:/reference:ro \
    -v "$provider_root:/evidence:ro" -v "$bitwasp_root:/old-evidence:ro" \
    --entrypoint php "$image" /evidence/probe.php > "/tmp/issue17-provider-php$runtime.json"
  cmp "$provider_root/probe-php$runtime.json" "/tmp/issue17-provider-php$runtime.json"
done
podman run --rm --network none --read-only \
  -v /tmp/issue17-reference/src/trunk:/reference:ro -v "$bitwasp_root:/evidence:ro" \
  --entrypoint php sha256:571bc51823ea9100dd8a6430e9426390a5fbb69cf2dffa7b2eae5397228407a6 \
  /evidence/inspect.php > /tmp/issue17-bitwasp-php83.json
```

The image IDs identify this local Podman store; another host may need equivalent PHP images. The provider output comparisons use `cmp`; the BitWasp output requires a field comparison because regenerated Composer files differ.

## Expected versus observed

| Pack | Status and comparison |
|---|---|
| Provider, PHP 8.3.35 with GMP | **Reproduced byte for byte.** Output SHA-256 `3b6482951e75fb2dba2f8dd066978e793c47740d295fa415360a8b46645f6904` equals the issued `probe-php83.json`. This includes 17 XPUB byte-split round trips, four StephenHill raw cases, three hash answers, three unsigned integer cases, Bech32/SegWit observations, ECC zero/order/infinity/wrong-length outcomes, and zero diagnostics. |
| Provider, PHP 8.1.34 without GMP | **Reproduced byte for byte.** Output SHA-256 `cbadbdd33574cea2ca9a23c9543b542fa212df84ed39d4608120c15c49fb0e5a` equals `probe-php81.json`. The original explicit ECC and StephenHill GMP skips remain; there are zero diagnostics. |
| BitWasp, PHP 8.3.35 with GMP | **Semantically reproduced; JSON bytes differ.** All output fields match `observations.json` except five entries in `pre_fault_loaded_vendor_files`. The 463-file four-package manifest, 11 package records, lock and fixture hashes, six public derivation edges, 17 public round trips, 30 address regressions, six fixture exclusions, invalid-key and fault observations, and ten diagnostics match exactly. |

For BitWasp, the newly generated `vendor/autoload.php`, `vendor/composer/autoload_real.php`, `autoload_static.php`, and `installed.php` have different hashes. The original loaded-file inventory also included `vendor/composer/platform_check.php`, absent after this `--ignore-platform-reqs` installation: 134 original loaded files versus 133 now. These differences are tied to regenerated Composer metadata, not to any of the four locked provider package files or behavioral fixture outputs. The new BitWasp JSON SHA-256 is `a507b99aa9eef6dd783af7cf7d46bb925d13f8666b5f9b7ec47469e4e759452b`.

This independently confirms the recorded runtime observations within the recovered sources and matching runtime configurations. It cannot attest to the exact original BitWasp checkout, generated vendor tree, or historical container image identity. The raw provider behaviors, including accepting or rejecting boundary inputs, remain research observations; they do not establish Primitives admission semantics or production qualification. No issued JSON, manifest, or README was changed.
