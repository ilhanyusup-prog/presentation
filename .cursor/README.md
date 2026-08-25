# Figma integration

`mcp.json` points this project at Figma's remote MCP server, so an agent working
in this repo can read a Figma file — frames, components, variables, layout data —
and write back to the canvas. For a deck that is rebuilt from a design reference,
that means slide geometry and colours can be pulled from the source of truth
instead of measured off a flattened page.

## Finish the setup

The config only names the server; it carries no credentials. Authenticate once
per machine:

1. Open the command palette and search for **Cursor Settings**.
2. Select **Tools & MCP**.
3. Find `figma` and click **Connect** to complete the Figma OAuth flow.

## Getting Figma's Agent Skills too

Figma publishes a [marketplace plugin](https://cursor.com/marketplace/figma)
that bundles this same server URL with about a dozen Agent Skills —
`figma-design-to-code`, `figma-generate-design`, `figma-code-connect` and
others. Several are written as mandatory prerequisites for their matching MCP
tool, so the tools behave better with the skills loaded. Install it from the
editor:

```text
/add-plugin figma
```

Plugin installs are scoped to a user or a project through your Cursor account,
not to a Git repository, so they cannot be committed here. That is why this
directory holds only the MCP server config — the part that *is* repo-scoped.
Installing the plugin on top is harmless: it supplies the same endpoint.

If you want these tools available to Cloud Agents rather than just the editor,
add the server under **Dashboard → Integrations & MCP** as well.

## Desktop server fallback

Figma also ships a local server inside the desktop app. It exists for specific
organization and enterprise cases and exposes a narrower set of tools, so prefer
the remote server above. Should you need it, open a Design file in the desktop
app, switch to Dev Mode (Shift+D), enable the MCP server from the inspect panel,
and swap `mcp.json` for:

```json
{
  "mcpServers": {
    "figma-desktop": {
      "url": "http://127.0.0.1:3845/mcp"
    }
  }
}
```

It only answers while the desktop app is open — a refused connection almost
always means Figma is closed or the toggle is off.
