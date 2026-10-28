#!/usr/bin/env python3
"""Finalize a native-text diagram SVG and rasterize/quantize its PNG counterpart."""

import argparse
import math
import os
from pathlib import Path
import shutil
import struct
import subprocess
import sys
import tempfile
import xml.etree.ElementTree as ET

SVG_NS = "http://www.w3.org/2000/svg"
XML_NS = "http://www.w3.org/XML/1998/namespace"
ET.register_namespace("", SVG_NS)
ET.register_namespace("xlink", "http://www.w3.org/1999/xlink")


def executable(value):
    found = shutil.which(value)
    if found:
        return found
    local = Path.home() / ".local" / "bin" / value
    if local.is_file() and os.access(local, os.X_OK):
        return str(local)
    raise ValueError(f"Required tool unavailable: {value}; install it or supply its path.")


def finalized_svg(source):
    root = ET.fromstring(source.read_bytes())
    if root.tag != f"{{{SVG_NS}}}svg":
        raise ValueError("Input must be an SVG document.")
    if root.findall(f".//{{{SVG_NS}}}foreignObject"):
        raise ValueError("HTML labels found. Export native SVG text; for Mermaid use htmlLabels: false.")
    if not any("".join(node.itertext()).strip() for node in root.findall(f".//{{{SVG_NS}}}text")):
        raise ValueError("No native SVG text found; check the diagram export.")
    values = root.get("viewBox", "").replace(",", " ").split()
    if len(values) != 4:
        raise ValueError("SVG must provide a four-number viewBox.")
    x, y, width, height = map(float, values)
    if not all(math.isfinite(v) for v in (x, y, width, height)) or min(width, height) <= 0:
        raise ValueError("SVG viewBox dimensions must be finite and positive.")
    root.set("width", str(math.ceil(width)))
    root.set("height", str(math.ceil(height)))
    root.set(f"{{{XML_NS}}}space", "preserve")
    # Mark the helper's canvas so re-finalizing an SVG does not stack rectangles.
    for child in list(root):
        if child.get("data-diagram-canvas") == "white":
            root.remove(child)
    root.insert(0, ET.Element(f"{{{SVG_NS}}}rect", {
        "x": str(x), "y": str(y), "width": str(width), "height": str(height),
        "fill": "#ffffff", "data-diagram-canvas": "white",
    }))
    return ET.tostring(root, encoding="utf-8") + b"\n"


def png_dimensions(path):
    header = path.read_bytes()[:24]
    if len(header) != 24 or header[:8] != b"\x89PNG\r\n\x1a\n" or header[12:16] != b"IHDR":
        raise ValueError(f"Invalid PNG output: {path.name}")
    return struct.unpack(">II", header[16:24])


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("svg", type=Path, help="Native-text SVG exported by the diagram renderer")
    parser.add_argument("--output-dir", type=Path, help="Default: input SVG directory")
    parser.add_argument("--stem", help="Default: input SVG basename without extension")
    parser.add_argument("--width", type=int, default=1280)
    parser.add_argument("--rsvg-convert", default="rsvg-convert")
    parser.add_argument("--pngquant", default="pngquant")
    args = parser.parse_args()
    try:
        if args.width <= 0:
            raise ValueError("Raster width must be positive.")
        stem = args.stem or args.svg.stem
        if not stem or stem in (".", "..") or Path(stem).name != stem or "/" in stem or "\\" in stem:
            raise ValueError("Output stem must be a basename, not a path.")
        rsvg = executable(args.rsvg_convert)
        pngquant = executable(args.pngquant)
        svg_data = finalized_svg(args.svg)
        dest = args.output_dir or args.svg.parent
        dest.mkdir(parents=True, exist_ok=True)
        # Stage both outputs before touching existing exports.
        with tempfile.TemporaryDirectory(prefix=".diagram-export-", dir=dest) as temp:
            stage = Path(temp)
            svg = stage / "diagram.svg"
            raster = stage / "raster.png"
            compressed = stage / "compressed.png"
            svg.write_bytes(svg_data)
            subprocess.run([rsvg, "--width", str(args.width), "--output", str(raster), str(svg)], check=True)
            dimensions = png_dimensions(raster)
            if dimensions[0] != args.width:
                raise ValueError("Rasterizer did not produce the requested width.")
            before = raster.stat().st_size
            result = subprocess.run([
                pngquant, "--quality", "90-100", "--speed", "1", "--nofs", "--strip",
                "--skip-if-larger", "--output", str(compressed), "--", str(raster),
            ])
            chosen = raster
            if result.returncode == 0:
                if png_dimensions(compressed) != dimensions:
                    raise ValueError("Quantization changed PNG dimensions.")
                if compressed.stat().st_size < before:
                    chosen = compressed
                else:
                    print("Optimization did not shrink the PNG; retaining fresh raster.", file=sys.stderr)
            elif result.returncode in (98, 99):
                reason = "would increase size" if result.returncode == 98 else "did not meet quality floor"
                print(f"Optimization skipped: {reason}; retaining fresh raster.", file=sys.stderr)
            else:
                raise ValueError(f"pngquant failed with exit code {result.returncode}.")
            after = chosen.stat().st_size
            os.replace(svg, dest / f"{stem}.svg")
            os.replace(chosen, dest / f"{stem}.png")
        print(f"{stem}: {dimensions[0]} x {dimensions[1]} px; "
              f"{before / 1024:.1f} -> {after / 1024:.1f} KiB "
              f"({100 * (1 - after / before):.1f}% smaller)")
        return 0
    except (OSError, ValueError, ET.ParseError, subprocess.CalledProcessError) as error:
        print(f"Export failed: {error}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
