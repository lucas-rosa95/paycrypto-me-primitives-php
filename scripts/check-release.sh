#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

# Mechanical checks complement the required human architectural review.
canonical=docs/canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md
test -s "$canonical"
test -s LICENSE
grep -Fq "./$canonical" README.md
python3 scripts/check-doc-links.py

# Validate the actual Git archive, rather than inferring payload from attributes.
archive_list=$(git archive HEAD | tar -tf -)
for required in composer.json LICENSE README.md "$canonical"; do
    grep -Fxq "$required" <<< "$archive_list"
done
if grep -Eq '^(vendor/|tests/|tools/|scripts/|\.github/|\.phpunit.cache/|\.phpstan.cache/|\.env$|composer.lock$|\.dockerignore$|\.editorconfig$|\.gitignore$|Dockerfile$|docker-compose\.yml$|phpunit\.xml\.dist$|phpstan\.neon\.dist$)' <<< "$archive_list"; then
    echo 'Development files leaked into the release archive.' >&2
    exit 1
fi
echo 'Documentation references and Git distribution archive verified.'
