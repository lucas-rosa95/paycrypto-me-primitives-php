# draw.io workflow

Use this procedure for native visual editing, meaningful positioning, swimlanes,
layers or specialized diagram shapes. Inspect the exposed `drawio` MCP schemas;
do not assume it renders or saves files just because it opens an editor.

## Existing files

Use `list_pages` before `get_page` to select the relevant page by index, name or
ID. `get_page` returns its uncompressed `mxGraphModel` XML. Use `set_page` with
that model to replace only the intended page; it preserves the other pages and
matches the page's original compression convention. Re-read the modified page
and verify the retained pages. Page mutation does not regenerate SVG/PNG exports.

## New diagrams and imports

- Prefer `open_drawio_mermaid` for standard diagram types that need draw.io
  editing. Call `get_drawio_reference(format: "mermaid")` once before the first
  such call in a conversation. Supported syntax varies by parser/version.
- Use `open_drawio_xml` for precise positioning, custom shapes or native features
  that Mermaid cannot express. Follow its schema's XML rules rather than copying
  arbitrary editor internals. Request `get_drawio_reference(format: "xml")` when
  layers, metadata, tables, grids or other advanced features require it.
- Use `open_drawio_csv` when the source is genuinely tabular and its import
  configuration helps maintain the diagram; do not convert ordinary flowcharts
  into CSV just to use another endpoint.
- Use `search_shapes` only for needed specialized, branded or pictorial stencils.
  Standard flowcharts, UML and ER diagrams need basic shapes, not icon searches.

The server's reference suggests matching the conversation language; repository
documentation still uses **English**, including labels and captions.

For Mermaid flowcharts, request `postLayout: "elk"` when the tool recommends it:
roughly 20 or more nodes, at least three decisions or endpoints, or any back-edge.
For directional XML flows, use `postLayout: "elk"` and the intended direction.
For meaningful hand-positioned architecture or lanes, retain positions; use
`routing: "libavoid"` if connectors would cross shapes. Do not combine routing
with post-layout. Inspect the result: layout engines do not establish semantics.

## Save and export

The currently exposed `open_drawio_*` tools open/return an editor view; they are
not a guaranteed local file-save or SVG/PNG-export API. Treat their results
according to the actual response. Save the resulting `.drawio`, including any
manual or post-layout changes, before claiming a reproducible repository artifact.
Use an available export tool, an already installed draw.io CLI or an editor
export for SVG/PNG; disclose the actual path. If none can complete the task,
state the missing delivery rather than treating an editor URL as the artifact.

Choose one authoritative source after import. Native edits to converted shapes
do not update the original Mermaid. Preserve original input only as clearly
identified provenance when useful, and regenerate exports from the saved source.

SVG labels require particular attention. Native `.drawio` cells can legitimately
use `html=1`; this is distinct from portable SVG text. Export with text settings
that retain visible native SVG labels without `foreignObject` dependence, then
inspect through an independent renderer. Do not fix compatibility by deleting
text, rasterizing away the editable vector labels or hiding export warnings.

Use [the shared exporter](../scripts/export_artifacts.py) only on a compatible
native-text SVG with a valid viewBox and a suitable white background. It does not
convert `.drawio`/XML, run draw.io layout, or repair HTML-based labels. It rejects
incompatible input before replacing existing exports. An alternate export path
must preserve the required labels and be disclosed and visually checked.

Keep the saved source and exports beside their owning document, record relevant
renderer/export settings and versions, and inspect the actual final PNG. Opening
the editor or round-tripping a page is not an export or visual-validation result.
