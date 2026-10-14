# Release guide

Releases use Git tags as their version source. Do not add a `version` field to
Composer metadata or ship `vendor/` with this library.

1. Review API compatibility, tests and architecture impact. When behavior or
   architectural decisions change, revise the canonical architecture using the
   repository's canonical document governance instructions. The mechanical
   documentation check only verifies the current canonical link and presence;
   it cannot verify semantic consistency.
2. Commit all release changes (including changelog/documentation where needed)
   and review the intended revision on `main`. The script requires a clean tree
   and prints the exact commit. It does not generate a meaningless version bump
   commit: the tag is the version.
3. Preview: `bash scripts/release.sh -v 0.1.0 --git --dry-run`.
4. Validate: `bash scripts/release.sh -v 0.1.0`. This checks the distribution
   archive and docs, then installs development dependencies and runs Composer
   metadata validation, syntax checks, PHPUnit and PHPStan under real PHP 8.3
   and 8.4 in isolated temporary directories. Platform requirements are checked
   against each actual runtime too. All gates must pass; there are no skip flags.
5. Validate and create an annotated tag:
   `bash scripts/release.sh -v 0.1.0 --git`.
6. Push the reviewed commit and tag: `git push --atomic origin main refs/tags/v0.1.0`.
   Alternatively `--push` performs validation, tagging and that push in one run.
7. Register the public repository on Packagist once, enable automatic updates,
   and verify the version and metadata on
   <https://packagist.org/packages/paycrypto-me/primitives>. Push does not prove
   indexing succeeded. If indexing is delayed, inspect the Packagist update
   status before attempting another release. Never move a published tag.

GitHub Actions runs the same Composer quality gates for PHP 8.3 and 8.4 and
checks the distribution archive. Docker network access is required to download
tooling. The supported PHP baseline is 8.3; the matrix provides actual runtime
evidence beyond Composer's platform pin.

`composer.lock` is intentionally ignored for this library. Each validation
resolves development tooling afresh within the declared version constraints;
Docker image pins do not make that dependency resolution immutable. Consumers
resolve the library using their own application's lock file.

Setup, CI and release read the PHP versions and image digest pins from
`scripts/php-images.sh`. Update that file deliberately when adopting new PHP
patches or expanding the matrix. The Dockerfile's fallback PHP image is used
only by direct builds without an explicit build argument; keep it aligned with
the default development version when updating the matrix. Composer's image is
pinned in the Dockerfile in all flows.

The CI container runs with the runner's UID/GID so that the mounted checkout is
writable. Local development defaults to UID/GID 1000; on other hosts pass
`--user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer` to `docker compose run`.
