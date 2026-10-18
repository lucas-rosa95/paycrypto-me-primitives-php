#!/usr/bin/env python3
"""Check the bounded Bech32 observations in an M00 provider-probe JSON file."""

import argparse
import hashlib
import json
import re
import sys
from pathlib import Path


M00 = Path(__file__).resolve().parent.parent
SOURCE = M00 / "research/provider-refresh-2026-10-02/bip-0173.mediawiki"
SOURCE_SHA256 = "b19eab06b264bbd15ad7f7d640fa34de8f4a8809fba8a6ecc3e165545b27a543"


def count_vectors(source: str, section_pattern: str, vector_pattern: str) -> int:
    section = re.search(section_pattern, source, re.DOTALL)
    if section is None:
        raise ValueError(f"BIP173 section not found: {section_pattern}")
    return len(re.findall(vector_pattern, section.group(1), re.MULTILINE))


def is_recorded_exception(result: object) -> bool:
    return (
        isinstance(result, dict)
        and result.get("outcome") == "threw"
        and isinstance(result.get("class"), str)
        and bool(result["class"])
        and isinstance(result.get("message"), str)
        and bool(result["message"])
    )


def check_outcomes(checks: dict, name: str, count: int, expected: object) -> None:
    outcomes = checks.get(name)
    if not isinstance(outcomes, list) or len(outcomes) != count:
        raise ValueError(f"{name}: expected {count} observations")
    for index, result in enumerate(outcomes):
        if expected == "threw":
            matches = is_recorded_exception(result)
        elif expected == "returned_true":
            matches = (
                isinstance(result, dict)
                and result.get("outcome") == "returned"
                and result.get("value") is True
            )
        else:
            matches = result is True
        if not matches:
            raise ValueError(f"{name}[{index}]: expected {expected}, observed {result!r}")


def verify(output: Path) -> None:
    source_bytes = SOURCE.read_bytes()
    if hashlib.sha256(source_bytes).hexdigest() != SOURCE_SHA256:
        raise ValueError("pinned BIP173 source hash changed")
    source = source_bytes.decode("utf-8")
    counts = {
        "bech32_valid_generic": count_vectors(
            source,
            r"The following strings are valid Bech32:(.*?)The following string are not valid Bech32",
            r"^\* <tt>[^<]+</tt>",
        ),
        "bech32_invalid_generic": count_vectors(
            source,
            r"The following string are not valid Bech32.*?:\n(.*?)The following list gives valid segwit",
            r"^\* (?:0x[0-9A-F]+ \+ )?<tt>[^<]+</tt>(?: \+ 0x[0-9A-F]+)?",
        ),
        "segwit_v0_vectors": count_vectors(
            source,
            r"The following list gives valid segwit(.*?)The following list gives invalid segwit",
            r"<tt>[^<]+</tt>: <tt>00(?:14|20)[a-f0-9]+</tt>",
        ),
        "segwit_invalid_vectors": count_vectors(
            source,
            r"The following list gives invalid segwit(.*?)===Checksum design===",
            r"^\* <tt>[^<]+</tt>",
        ),
    }
    if counts != {
        "bech32_valid_generic": 7,
        "bech32_invalid_generic": 12,
        "segwit_v0_vectors": 3,
        "segwit_invalid_vectors": 10,
    }:
        raise ValueError(f"unexpected BIP173 vector inventory: {counts}")

    report = json.loads(output.read_text())
    checks = report.get("checks")
    if not isinstance(checks, dict):
        raise ValueError("provider probe has no checks object")
    check_outcomes(checks, "bech32_valid_generic", 7, "returned_true")
    check_outcomes(checks, "bech32_invalid_generic", 12, "threw")
    check_outcomes(checks, "segwit_v0_vectors", 3, True)
    check_outcomes(checks, "segwit_invalid_vectors", 10, "threw")
    if not is_recorded_exception(checks.get("segwit_v0_bad_program")):
        raise ValueError("segwit_v0_bad_program: expected a recorded rejection")
    print(f"{output}: selected BIP173 acceptance/rejection observations match {counts}")


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("output", type=Path, help="M00 provider-probe JSON to check")
    args = parser.parse_args()
    try:
        verify(args.output)
    except (OSError, ValueError, json.JSONDecodeError) as error:
        print(f"Bech32 evidence check failed: {error}", file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
