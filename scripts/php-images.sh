#!/usr/bin/env bash
# Shared runtime matrix for CI, setup and release. Update image pins here.
PHP_VERSIONS=(8.3 8.4)
PHP_IMAGES=(
    'php:8.3.36-cli-bookworm@sha256:cdb77e306d6bb99db40e513fbe036ecc6c53b575cbcb7e581387845fef26d755'
    'php:8.4.27-cli-bookworm@sha256:496b5e02fe9fe44fcfb715023b931048111dc4e0bc719789d83c906be9f04b05'
)
DEV_PHP_VERSION=8.3

php_image() {
    local index
    for index in "${!PHP_VERSIONS[@]}"; do
        if [[ "${PHP_VERSIONS[index]}" == "$1" ]]; then
            printf '%s\n' "${PHP_IMAGES[index]}"
            return 0
        fi
    done
    echo "Unsupported development PHP version: $1" >&2
    return 1
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
    set -euo pipefail
    [[ "${1:-}" == --matrix ]] || exit 1
    printf '{"include":['
    separator=''
    for index in "${!PHP_VERSIONS[@]}"; do
        printf '%s{"php":"%s","image":"%s"}' "$separator" "${PHP_VERSIONS[index]}" "${PHP_IMAGES[index]}"
        separator=,
    done
    printf ']}\n'
fi
