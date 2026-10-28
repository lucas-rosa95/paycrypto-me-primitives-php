---
name: diagram-artifacts
description: "Create, edit and validate architecture diagrams, flowcharts and other structured documentation visuals using Mermaid or draw.io MCP tools. Deliver editable sources, portable SVG and optimized PNG; choose the tool that fits the visual and preserve existing sources."
---

# Diagram Artifacts

Use visual explanations where relationships, order, boundaries or alternatives
are easier to understand visually. Keep simple facts as text or tables. Choose
the diagram type for the question: flow for execution, sequence for interactions,
state for transitions, architecture for boundaries and dependencies, or timeline
for chronology. This skill covers structured diagrams; use appropriate plotting
or image tools for scientific figures, photographs or illustrations.

## Choose the source and tool

Inspect the available MCP tool schemas before calling them. The local `mermaid`
and `drawio` servers complement each other; neither is mandatory for every task.

| Need | Preferred approach |
|---|---|
| Existing diagram | Edit its authoritative source and retain its format unless conversion has a concrete benefit. |
| Text-maintained architecture, flowchart, sequence, state, class or ER view | Mermaid source and local `mermaid` MCP rendering/validation. |
| Human visual editing, meaningful positioning, swimlanes, layers or specialized shapes | draw.io source and `drawio` MCP tools. |
| Standard diagram that needs draw.io editing | Import Mermaid through `open_drawio_mermaid`; handcraft XML only when needed. |
| Diagram driven by existing tabular data | Consider `open_drawio_csv`; preserve the data and generation settings. |

Prefer the local `mermaid` MCP for Mermaid rendering and syntax validation, as
requested by this project. Do not migrate or duplicate satisfactory diagrams
merely because a second renderer is available. Read only the relevant procedure:

- [Mermaid rendering and export](references/mermaid.md).
- [draw.io editing, layout and export](references/drawio.md).
- [Rendering compatibility and measured size trade-offs](references/rendering-insights.md)
  when troubleshooting SVGs or choosing raster quality.

Tool availability may change between sessions. Distinguish an unavailable tool
from an uninstalled server; disclose the actual fallback and any missing export.
Do not install tools or change MCP configuration as a side effect of this skill.

## Preserve meaning and decision status

Draw the documented requirement, contract or execution stage. Label unresolved
alternatives as preliminary; drawing a connection must not decide an open
architecture question. A box need not imply a PHP class, interface or separately
configurable capability. Explain arrow meanings when mixing execution flow,
data movement and resource references; shared resources need not share state.
Use English labels and captions in this repository.

Use shapes, labels and grouping as well as color. Keep crossings, repeated prose
and decorative icons to a minimum. Split an overloaded overview into linked
detail views rather than hiding its important conditions or making text unreadable.
Place diagrams in their owning document; preserve issued historical evidence.

## Keep editable and viewable artifacts

For repository documentation, deliver adjacent artifacts with one basename:

- Editable source: `.mmd` for Mermaid or `.drawio` for native draw.io editing.
- `.svg`: portable scalable shapes and inspectable text for detailed reading.
- `.png`: a view for visual inspection of grouping, placement and relationships,
  including viewers that cannot display SVG directly.

Identify one authoritative source for each view. If a Mermaid import is edited
as native draw.io shapes, save that `.drawio` as the new authority; the original
Mermaid becomes import provenance, not an automatically synchronized second source.
Regenerate both exports after source edits. A browser/editor link or screenshot
does not replace a saved editable source and final exports.

For a native-text SVG from either renderer, use
[scripts/export_artifacts.py](scripts/export_artifacts.py) to finalize the SVG
and create a fresh raster with optional palette reduction. The helper requires
Python 3, `rsvg-convert` and `pngquant`. These are documentation tools, not
Composer or consumer dependencies. Inspect availability and use explicit binary
paths when needed:

```bash
python3 tools/skills/diagram-artifacts/scripts/export_artifacts.py \
  /tmp/example-raw.svg --output-dir docs/path/diagrams --stem example
```

The helper rejects HTML `foreignObject` labels or missing native SVG text,
preserves spacing, sets dimensions and adds a white canvas. Obtain a compatible
export from the chosen renderer; do not silently strip labels to pass the check.
Its default is 1280 px width, then `pngquant --quality 90-100 --speed 1 --nofs
--strip --skip-if-larger`. Quantization is lossy. A quality/size rejection retains
the fresh raster and is reported; other tool failures stop export. A different
background or export requirement needs a suitable disclosed export path.

1280 px is a starting point, not a universal constraint. Increase width or split
the view if required labels, thin edges or arrowheads become unclear. Keep the
source and SVG for detail; do not repeatedly quantize an existing PNG.

## Verify and hand off

Inspect the actual final PNG, preferably rasterized from the final SVG through an
independent renderer. Check visible labels, spacing, contrast, arrows, crossings,
containers, clipping and correspondence with the source and agreed contracts.
Parsing XML or successfully opening an editor does not prove visual correctness.

Link the source and exports from the owning artifact. Record the renderer/export
path, relevant tool versions, width and compression settings; report byte-size
changes when optimizing. Disclose any unproduced or unverified format. Visual
validation does not establish protocol correctness. Follow repository link and
status maintenance gates where affected; this skill alone does not authorize
commit, push or publication.
