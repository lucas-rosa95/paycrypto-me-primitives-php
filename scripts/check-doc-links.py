#!/usr/bin/env python3
"""Check paths in current repository Markdown links without network access."""

from __future__ import annotations

import re
import subprocess
from pathlib import Path
from urllib.parse import unquote, urlsplit


ROOT = Path(__file__).resolve().parent.parent
INLINE = re.compile(r"!?\[[^\]\n]+\]\(\s*(<[^>]+>|[^\s)]+)")
REFERENCE = re.compile(r"^ {0,3}\[[^\]]+\]:\s*(<[^>]+>|\S+)")
FENCE = re.compile(r"^ {0,3}(`{3,}|~{3,})")
OWN_MAIN = re.compile(
    r"^/paycrypto-me/paycrypto-me-primitives-php/(?:blob|tree)/main/(.+)$"
)


def markdown_files() -> list[Path]:
    result = subprocess.run(
        ["git", "ls-files", "-z", "--cached", "--others", "--exclude-standard", "--", "*.md"],
        cwd=ROOT,
        capture_output=True,
        check=True,
    )
    return sorted({ROOT / path.decode() for path in result.stdout.split(b"\0") if path})


def destinations(document: Path):
    fence_char = None
    fence_length = 0
    for number, line in enumerate(document.read_text(encoding="utf-8").splitlines(), 1):
        fence = FENCE.match(line)
        if fence:
            marker = fence.group(1)
            if fence_char is None:
                fence_char, fence_length = marker[0], len(marker)
            elif marker[0] == fence_char and len(marker) >= fence_length:
                fence_char = None
            continue
        if fence_char is not None:
            continue
        for match in INLINE.finditer(line):
            yield number, match.group(1)
        reference = REFERENCE.match(line)
        if reference:
            yield number, reference.group(1)


def local_target(document: Path, destination: str) -> Path | None:
    destination = destination.strip("<>")
    if not destination or destination.startswith("#"):
        return None
    url = urlsplit(destination)
    if url.scheme or url.netloc:
        if url.scheme != "https" or url.netloc != "github.com":
            return None
        match = OWN_MAIN.match(url.path)
        return ROOT / unquote(match.group(1)) if match else None
    path = unquote(url.path)
    if not path:
        return None
    return ROOT / path.lstrip("/") if path.startswith("/") else document.parent / path


def main() -> int:
    files = markdown_files()
    checked = 0
    missing = []
    for document in files:
        for number, destination in destinations(document):
            target = local_target(document, destination)
            if target is None:
                continue
            checked += 1
            if not target.exists():
                missing.append((document.relative_to(ROOT), number, destination))
    for path, number, destination in missing:
        print(f"{path}:{number}: missing Markdown target: {destination}")
    print(f"Checked {checked} local/current-repository targets in {len(files)} Markdown files.")
    return 1 if missing else 0


if __name__ == "__main__":
    raise SystemExit(main())
