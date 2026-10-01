# Cartxis MCP (Model Context Protocol)

Cartxis ships a **remote MCP server** so AI clients (Cursor, Claude Code, VS Code Copilot, ChatGPT connectors, Grok, etc.) can manage store catalog, orders, customers, and safe settings over HTTP.

## What it is

- **Transport:** Streamable HTTP JSON-RPC 2.0 on `POST /mcp` (optional `GET /mcp` probe)
- **Auth:** Sanctum personal access tokens with the `mcp` ability (`Authorization: Bearer <token>`)
- **Admin UI:** Settings → **MCP**
- **No Node sidecar:** Pure Laravel/PHP

## Enable MCP

1. Sign in as an admin.
2. Open **Settings → MCP**.
3. Toggle **Enable MCP** and save.
4. Create a token (name + optional expiry). **Copy the plaintext token once** — it is not shown again.

If MCP is disabled, `/mcp` returns **HTTP 503**.

## Connect from Cursor

Create or edit `.cursor/mcp.json` (project) or `~/.cursor/mcp.json` (user):

```json
{
  "mcpServers": {
    "cartxis": {
      "url": "https://your-store.test/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_MCP_TOKEN"
      }
    }
  }
}
```

### Local Herd / Valet HTTPS (`.test`)

Node does **not** trust Herd/Valet self-signed certificates by default. You will see:

`UNABLE_TO_VERIFY_LEAF_SIGNATURE` / `unable to verify the first certificate`

Use the **mcp-remote** bridge and point Node at the Herd CA (or skip verification for local only):

```json
{
  "mcpServers": {
    "cartxis": {
      "command": "npx",
      "args": [
        "-y",
        "mcp-remote",
        "https://cartxis.test/mcp",
        "--header",
        "Authorization: Bearer YOUR_MCP_TOKEN"
      ],
      "env": {
        "NODE_EXTRA_CA_CERTS": "/Users/YOU/Library/Application Support/Herd/config/valet/CA/LaravelValetCASelfSigned.pem",
        "NODE_TLS_REJECT_UNAUTHORIZED": "0"
      }
    }
  }
}
```

- Expand `YOU` to your macOS username (or copy the path from Admin → MCP → Connect).
- `NODE_TLS_REJECT_UNAUTHORIZED=0` is **local-only** — never use it in production.
- After changing `mcp.json`, reload MCP servers in Cursor (or restart Cursor).

If your Cursor build prefers a stdio bridge without local TLS issues (production HTTPS with a public CA):

```json
{
  "mcpServers": {
    "cartxis": {
      "command": "npx",
      "args": [
        "-y",
        "mcp-remote",
        "https://your-store.test/mcp",
        "--header",
        "Authorization: Bearer YOUR_MCP_TOKEN"
      ]
    }
  }
}
```

Replace the URL with your `APP_URL` + `/mcp` and paste the token from Admin → MCP.

## Claude Code / Claude Desktop

Use the same `mcp-remote` pattern in Claude Desktop MCP config, or Claude Code MCP settings pointing at your `/mcp` endpoint with the Bearer header.

## VS Code (GitHub Copilot MCP)

```json
{
  "servers": {
    "cartxis": {
      "type": "http",
      "url": "https://your-store.test/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_MCP_TOKEN"
      }
    }
  }
}
```

## ChatGPT / custom GPT

Point custom actions or an MCP-compatible connector at `{APP_URL}/mcp` with:

```http
Authorization: Bearer YOUR_MCP_TOKEN
```

OAuth is **not** supported in this MVP.

## Protocol methods

| Method | Purpose |
|--------|---------|
| `initialize` | Handshake / capabilities |
| `notifications/initialized` | Client ready (no response body) |
| `tools/list` | List registered tools |
| `tools/call` | Invoke a tool |
| `ping` | Liveness |

## Tools (MVP)

### Store ops

| Tool | Description |
|------|-------------|
| `cartxis_store_info` | Store name, URL, locale/currency summary |
| `cartxis_list_products` | Paginated product list (`search`, `page`, `per_page` ≤ 50) |
| `cartxis_get_product` | Product by `id` or `sku` |
| `cartxis_create_product` | Create product (`name`, `sku`, `price`, `status`, …) |
| `cartxis_update_product` | Update product fields by `id` |
| `cartxis_list_orders` | Paginated orders (`status`, `page`, `per_page`) |
| `cartxis_get_order` | Order by `id` or `increment_id` / `order_number` |
| `cartxis_update_order_status` | Status transitions (invalid transitions refused) |
| `cartxis_get_settings` | Safe `general` / `store` settings only |
| `cartxis_update_settings` | Update one whitelisted key |
| `cartxis_list_customers` | Paginated customers (`search`, `page`) |
| `cartxis_search` | Unified search across products, orders, customers |

### Storefront templates (themes)

| Tool | Description |
|------|-------------|
| `cartxis_list_templates` | Installed themes (`discover`, `active_only`) |
| `cartxis_get_template` | Theme details + config by `slug` |
| `cartxis_activate_template` | Activate installed theme by `slug` |

### Cartxis UI / template editor (pages + layouts)

These tools drive the same **UI Editor** used in Admin → Content → Pages (sections → columns → blocks).

| Tool | Description |
|------|-------------|
| `cartxis_list_pages` | CMS pages (`search`, `status`, pagination) |
| `cartxis_get_page` | Page by `id` or `url_key` + layout summary / editor URL |
| `cartxis_create_page` | Create page + empty layout draft |
| `cartxis_update_page` | Update page metadata / SEO |
| `cartxis_get_page_layout` | Read `layout_data` for a page or homepage |
| `cartxis_update_page_layout` | Save layout draft (`layout_data`); optional `publish` |
| `cartxis_publish_page_layout` | Publish current layout draft |
| `cartxis_list_editor_blocks` | Block types + defaults for building `layout_data` |

**Typical edit flow:** `cartxis_create_page` → `cartxis_list_editor_blocks` → `cartxis_update_page_layout` (with sections/blocks) → `cartxis_publish_page_layout`.

Creating a full storefront theme *package* on disk (zip / Template Zone) is not exposed via MCP — use Template Zone in admin for that.

## Security notes

- Only **admin** users can create MCP tokens.
- Tokens must include the `mcp` ability; other Sanctum tokens are rejected.
- Endpoint is rate-limited (`60` requests / minute).
- Tools **never** expose payment gateway secrets, SMTP passwords, or AI provider API keys.
- Settings tools only allow a **whitelist** of general/store keys.
- No arbitrary SQL, shell, or media upload in this MVP.
- Prefer short-lived tokens and revoke unused ones in Admin → MCP.
- Keep MCP disabled on stores that do not need AI integrations.

## Related

- Admin UI: Settings → MCP
- User guide: [USER_GUIDE.md](./USER_GUIDE.md) § MCP
