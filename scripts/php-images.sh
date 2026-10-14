#!/usr/bin/env bash
# Shared runtime matrix for CI, setup and release. Update image pins here.
PHP_VERSIONS=(8.3 8.4)
PHP_IMAGES=(
    'php:8.3-cli-bookworm@sha256:4687aec76c4a895b68b91bcd5e48f1ba7a3dea120f6de50bba7e1a93af5372dd'
    'php:8.4-cli-bookworm@sha256:836ac6c672d1372a47c8fd61b625015bd07760f493fb2eaab2087636498a2b4b'
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
