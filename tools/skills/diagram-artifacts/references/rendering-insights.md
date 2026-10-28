# Format, rendering and size decisions

## Complementary representations

The M01/PIP-0003 diagrams established a useful division of responsibilities.
MMD exposes the editable graph and labels; native draw.io sources preserve
editable shapes and layout when that workflow is chosen. SVG retains text and
scalable vector elements for detailed inspection. PNG gives visual tools a
directly consumable representation of shape, placement, grouping, hierarchy
and relationships.

In the user's experiment, SVG helped an agent understand elements/text while
PNG helped it understand arrangement and architectural structure. This is local
workflow evidence, not a guarantee about every model or viewer. Send both where
that complementary understanding matters. Reading SVG XML alone does not imply
the agent has perceived the rendered diagram, and seeing raster pixels does not
replace reading the source contracts.

## Why reduce PNG resolution?

The first finalized raster exports used 2000 px width to maximize text legibility.
Once SVG and MMD were retained for detailed text, that large raster duplicated
their role unnecessarily. The width was reduced to 1280 px, preserving aspect
ratio and the graph. This deliberately accepts less legibility for small text
in the PNG in exchange for compact visual structure. It does not remove labels
or reduce the detailed information in the other formats.

At 64% of the former width, the raster has approximately 41% of the former pixel
count. Compressed bytes depend on content, so that ratio is not a file-size
promise. Actual observations before quantization were:

| Diagram | 2000 px export | 1280 px export | After pngquant at 1280 px |
|---|---|---|---|
| Public-address flow | 2000 × 1813; 399.3 KiB | 1280 × 1161; 223.5 KiB | 68.7 KiB |
| Ownership/delegation | 2000 × 1927; 414.4 KiB | 1280 × 1234; 232.2 KiB | 66.2 KiB |

Reducing width saved about 44% of PNG bytes; palette quantization then saved
another 69–72% of the smaller files. Total reduction relative to 2000 px was
about 83–84%. Current SVGs were about 53–61 KiB and scale independently of raster
resolution. These measurements apply to these two diagrams, not every export.

Choose width based on identifiable nodes and intact relationships. Text needed
to distinguish nodes, thin/dashed edges and arrowheads must remain useful. Raise
resolution or split a visually overloaded diagram when that condition fails.
PNG-only delivery or an accessibility/print requirement for readable small text
may need a different resolution; the 1280 px choice assumes complementary files.

## SVG compatibility failure and correction

The MCP initially returned SVGs with 36 and 34 `foreignObject` labels and zero
native SVG `text` elements. They parsed as valid XML and displayed in a browser,
but the user's standalone viewer showed boxes without text. The browser-generated
PNG worked, masking the SVG problem. The SVG canvas was also transparent despite
the tool's `backgroundColor: white` argument.

The correction was to disable HTML labels in the source, regenerate via MCP and
finalize the returned vector document with:

- Native SVG text and no `foreignObject`.
- `xml:space="preserve"` on the root; otherwise a second renderer removed leading
  spaces across Mermaid's adjacent word-level tspans, joining words together.
- Numeric dimensions derived from the viewBox and an explicit white rectangle
  behind content, so a dark viewer background cannot change canvas contrast.
- Short enough group headings to avoid overlap with their nodes.

Rasterization through librsvg then verified the actual exported SVG independently
of the MCP's browser renderer. Structural checks plus visual inspection are needed;
no single check establishes compatibility in every viewer.

## Quantization and environment

pngquant reduces the color palette and is lossy. The `90-100` quality range is a
conservative starting point; its score is not an objective text/readability
guarantee. `--nofs` avoids dithering noise in flat fills; `--strip` removes optional
metadata. `--skip-if-larger` avoids growing an input. Exit 99 rejects insufficient
quality; exit 98 rejects larger output. Keep the fresh unquantized raster when
either happens and disclose that optimization was skipped.

Do not quantize an already quantized export repeatedly. Always regenerate from
the finalized SVG. `pngquant` on this user's host does not imply availability in
other containers/machines; use documented binaries/tool versions and fail clearly
when missing. Tool installation and modification require the applicable task's
authorization; this skill does not automatically install tools.

## Original artifacts and references

- [M01 diagram sources, exports and provenance](../../../../docs/pips/PIP-0003/M01/diagrams/README.md).
- [Originating capability work, retrospective Issue #3](https://github.com/paycrypto-me/paycrypto-me-primitives-php/issues/3).
- [Mermaid HTML-label configuration](https://mermaid.js.org/config/schema-docs/config.html#htmllabels).
- [pngquant options and quality rejection](https://pngquant.org/).

The measurements above document the Mermaid workflow used in PIP-0003; they
are not measurements of draw.io exports. The source/SVG/PNG division of roles
also applies to native draw.io diagrams, subject to their own export checks.
This skill does not alter capability contracts, choose production crypto/codec
providers or create a universal architectural mandate for resolution, color
palette or diagram count.
