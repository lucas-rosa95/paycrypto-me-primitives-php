#!/usr/bin/env python3
"""Reproduce bounded M05 graph probes using installed locks and a PHP image.

Dependencies stay outside the repository. See README.md for installation and
runtime identities. This runner never selects or installs a production provider.
"""

import argparse
import hashlib
import json
from pathlib import Path
import subprocess


M05 = Path(__file__).resolve().parent
ROOT = M05.parents[3]
GRAPHS = {
    "paragonie-bitwasp": ("paragonie", "bitwasp"),
    "simplito-cjpgdk": ("simplito", "cjpgdk"),
    "paragonie-cardano": ("paragonie", "cardano"),
}


def read_json(path):
    return json.loads(path.read_text())


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--image", required=True)
    parser.add_argument("--installs", required=True, type=Path)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()
    args.output.mkdir(parents=True, exist_ok=True)
    image = json.loads(subprocess.check_output(["podman", "image", "inspect", args.image]))[0]
    sources = [Path(__file__).resolve(), M05 / "provider-graph-probe.php",
               M05.parent / "M03" / "ckdpub-composition-probe.php",
               M05.parent / "M03" / "native-false-classifier.php",
               M05.parent / "M03" / "ckdpub-public-fixtures.json",
               M05.parent / "M03" / "ckdpub-comparison.json",
               M05.parent / "M03" / "php-parser-fault-comparison.json",
               M05.parent / "M03" / "hash-fault-comparison.json",
               M05.parent / "M03" / "common-mode-comparison.json",
               M05.parent / "M00" / "reviews" / "bitwasp-public-derivation" / "bip-0032.mediawiki",
               M05.parent / "M04" / "codec-composition-probe.php"]
    record = {"image_id": image["Id"],
              "probe_sources_sha256": {str(p.relative_to(ROOT)): hashlib.sha256(p.read_bytes()).hexdigest()
                                       for p in sources}, "graphs": {}}
    addresses = None
    for name, (ecc, segwit) in GRAPHS.items():
        install = (args.installs / name).resolve()
        expected_lock = (M05 / "graphs" / name / "composer.lock").read_bytes()
        if (install / "composer.lock").read_bytes() != expected_lock:
            raise RuntimeError(f"{name}: install directory does not contain the recorded lock")
        base = ["podman", "run", "--rm", "--network", "none", "-v", f"{ROOT}:/repo:ro",
                "-v", f"{install}:/trial:ro", "-w", "/trial", args.image,
                "sh", "-c", 'ln -s /trial /research && exec "$@"', "sh"]
        commands = []

        def run(label, command):
            completed = subprocess.run(base + command, capture_output=True, check=False)
            stem = args.output / f"{name}-{label}"
            stem.with_suffix(".json").write_bytes(completed.stdout)
            stem.with_suffix(".stderr").write_bytes(completed.stderr)
            commands.append({"label": label, "command": command, "exit": completed.returncode,
                             "stdout_sha256": hashlib.sha256(completed.stdout).hexdigest(),
                             "stderr_sha256": hashlib.sha256(completed.stderr).hexdigest()})
            if completed.returncode:
                raise RuntimeError(f"{name}/{label} failed: {completed.stderr.decode()}")
            return json.loads(completed.stdout)

        run("platform", ["composer", "check-platform-reqs", "--no-dev", "--format=json"])
        php = ["php", "-d", "error_reporting=E_ALL", "-d", "display_errors=stderr"]
        graph = run("graph", php + ["/repo/docs/pips/PIP-0003/M05/provider-graph-probe.php", ecc, segwit])
        locked_references = {p["name"]: p["source"]["reference"]
                             for p in read_json(install / "composer.lock")["packages"]}
        if graph["installed_package_references"] != locked_references:
            raise RuntimeError(f"{name}: installed package references differ from the lock")
        if addresses is None:
            addresses = graph["published_and_differential_addresses"]
        if addresses != graph["published_and_differential_addresses"]:
            raise RuntimeError(f"{name}: address disagreement; investigate rather than voting")
        for mode, file, key in [
            ("parser-faults", "php-parser-fault-comparison.json", "runs"),
            ("hash-faults", "hash-fault-comparison.json", "matrix"),
            ("common-mode", "common-mode-comparison.json", "runs"),
        ]:
            result = run(mode, php + ["/repo/docs/pips/PIP-0003/M03/ckdpub-composition-probe.php", ecc, "--" + mode])
            expected = dict(read_json(M05.parent / "M03" / file)[key]["php83"][ecc])
            result.pop("php")
            expected.pop("php")
            if result != expected:
                raise RuntimeError(f"{name}/{mode}: differs from recorded M03 result")
        for function, mode in [("hash", "expect-disabled-hash"), ("hash_hmac", "expect-disabled-hmac")]:
            run(mode, php + ["-d", "disable_functions=" + function,
                            "/repo/docs/pips/PIP-0003/M03/ckdpub-composition-probe.php", ecc, "--" + mode])
        record["graphs"][name] = {
            "php": graph["php"], "integer_bytes": graph["integer_bytes"],
            "lock_sha256": hashlib.sha256(expected_lock).hexdigest(),
            "installed_packages": [{"name": p["name"], "version": p["version"], "source": p["source"]["reference"]}
                                   for p in read_json(install / "composer.lock")["packages"]],
            "commands": commands,
            "graph_result": graph,
        }
    record["cross_graph_address_agreement"] = True
    (args.output / "summary.json").write_text(json.dumps(record, indent=2) + "\n")
    print(f"Passed {sum(len(g['commands']) for g in record['graphs'].values())} commands; all candidate address outputs agree")


if __name__ == "__main__":
    main()
