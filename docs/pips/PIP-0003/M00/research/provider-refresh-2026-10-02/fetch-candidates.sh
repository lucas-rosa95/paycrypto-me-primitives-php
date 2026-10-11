#!/usr/bin/env bash
set -euo pipefail

# Download pinned external implementations for isolated research, never into vendor/.
candidate_root=${1:?Usage: bash fetch-candidates.sh /absolute/scratch/directory}
mkdir -p "$candidate_root"
while read -r repo revision name; do
    curl -fsSL --max-time 120 "https://api.github.com/repos/$repo/tarball/$revision" \
        -o "$candidate_root/$name.tar.gz"
    mkdir -p "$candidate_root/$name"
    tar -xzf "$candidate_root/$name.tar.gz" -C "$candidate_root/$name" --strip-components=1
done <<'SOURCES'
tuupola/base58 a2fac671f14890c8fa62ac081eeeb90f42839637 tuupola
paragonie/phpecc 1a49380410b8ce826bc7fd3de6324053e049205c paragonie
simplito/elliptic-php be321666781be2be2c89c79c43ffcac834bc8868 elliptic
stephen-hill/base58php 3030c00c0a1e1b78520f3ace6fbf813dacddfab5 stephenhill
simplito/bn-php 83446756a81720eacc2ffb87ff97958431451fd6 bn
simplito/bigint-wrapper-php cf21ec76d33f103add487b3eadbd9f5033a25930 bigint
SOURCES
