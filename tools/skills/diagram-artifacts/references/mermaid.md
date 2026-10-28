# Mermaid workflow

Use this procedure when the authoritative source is Mermaid. Prefer the local
`mermaid` MCP server for rendering and syntax validation. Inspect its actual tool
schema; the observed `generate_mermaid_diagram` accepts `mermaid`, `theme`,
`backgroundColor` and `outputType`. Start with `theme: default`,
`backgroundColor: white`, `outputType: svg`, unless the document needs another
appearance. Save the returned SVG content, not an editor screenshot.

For portable flowcharts, include this configuration in the `.mmd` source:

```yaml
---
config:
  htmlLabels: false
  flowchart:
    htmlLabels: false
---
```

Append the actual diagram after that header. Native SVG labels avoid the
observed failure where valid XML containing `foreignObject` text displayed as
empty boxes in standalone viewers. A successful browser PNG alone cannot
validate the SVG's labels.

Run [the shared exporter](../scripts/export_artifacts.py) on the raw SVG, then
inspect the final PNG and compare the labels/connections with the `.mmd`.
The exporter sets explicit dimensions and an opaque white canvas and preserves
word spacing. Check the relevant rendering/tool versions for reproducibility;
the server and its Mermaid version may differ from other renderers.

If the current catalog does not expose the configured renderer, report that
distinction. An available trusted MCP client against the configured server or a
manual editor export can be a disclosed fallback. Do not claim local MCP
rendering after using another renderer, or change MCP configuration implicitly.

For native draw.io editing of a Mermaid structure, follow
[the draw.io workflow](drawio.md). Conversion is optional and does not establish
round-trip equivalence or keep subsequent manual edits synchronized with `.mmd`.
