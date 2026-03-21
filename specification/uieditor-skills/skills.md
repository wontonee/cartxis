# UI Editor — Feature Specification

**Package:** `packages/Cartxis/UIEditor`
**Version:** 1.0.0
**Status:** In Development
**Date:** 2026-03-02

---

## Overview

The UI Editor is an Elementor-style visual drag-and-drop page builder for Cartxis Commerce. It enables store administrators to visually design CMS pages and the theme homepage using a blocks-based layout system without writing any HTML or CSS. The editor supports responsive preview modes (Desktop, Tablet, Mobile) and a Draft/Publish workflow.

---

## Goals

- Provide a visual WYSIWYG editor for CMS Pages and the Theme Homepage.
- Support a **Sections → Columns → Blocks** nesting model.
- Render editor output seamlessly inside all active themes via a shared renderer.
- Preserve full backward compatibility — pages without a visual layout continue to render raw HTML.
- Allow third-party extensions to register custom block types via the hook system.

---

## Architecture

### Package Location
```
packages/Cartxis/UIEditor/
├── composer.json
└── src/
    ├── Providers/
    │   └── UIEditorServiceProvider.php
    ├── Models/
    │   └── PageLayout.php
    ├── Services/
    │   ├── LayoutService.php
    │   └── BlockRegistry.php
    ├── Repositories/
    │   └── LayoutRepository.php
    ├── Http/
    │   └── Controllers/
    │       └── Admin/
    │           ├── EditorController.php
    │           ├── HomepageEditorController.php
    │           └── ProductsSearchController.php
    ├── Database/
    │   └── Migrations/
    │       └── 2026_03_02_000000_create_uieditor_page_layouts_table.php
    └── Routes/
        └── admin.php
```

### Frontend Location
```
resources/js/
├── stores/
│   └── uiEditorStore.ts
├── pages/
│   └── Admin/
│       └── UIEditor/
│           └── Editor.vue           ← Full-screen editor page
└── components/
    └── UIEditor/
        ├── EditorCanvas.vue
        ├── SectionWrapper.vue
        ├── ColumnWrapper.vue
        ├── BlockWrapper.vue
        ├── BlockPalette.vue
        ├── PropertiesPanel.vue
        ├── PreviewSwitcher.vue
        ├── PublishControls.vue
        ├── DeviceFrame.vue
        ├── blocks/
        │   ├── HeroBlock.vue
        │   ├── TextBlock.vue
        │   ├── HeadingBlock.vue
        │   ├── ButtonBlock.vue
        │   ├── ImageBlock.vue
        │   ├── GalleryBlock.vue
        │   ├── VideoBlock.vue
        │   ├── ProductsGridBlock.vue
        │   ├── TestimonialsBlock.vue
        │   ├── CardBlock.vue
        │   ├── TableBlock.vue
        │   ├── SpacerBlock.vue
        │   └── DividerBlock.vue
        └── panels/
            ├── HeroPanel.vue
            ├── TextPanel.vue
            ├── HeadingPanel.vue
            ├── ButtonPanel.vue
            ├── ImagePanel.vue
            ├── GalleryPanel.vue
            ├── VideoPanel.vue
            ├── ProductsGridPanel.vue
            ├── TestimonialsPanel.vue
            ├── CardPanel.vue
            ├── TablePanel.vue
            └── SpacerPanel.vue
```

---

## Database Schema

### Table: `uieditor_page_layouts`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `page_type` | enum('cms_page','homepage') | Context of the layout |
| `page_id` | bigint nullable FK → pages.id | Set for cms_page type; null for homepage |
| `layout_data` | longText (JSON) | Full Sections→Columns→Blocks tree |
| `status` | enum('draft','published') | Default: draft |
| `published_at` | timestamp nullable | When last published |
| `created_by` | bigint nullable FK → users.id | Admin user |
| `updated_by` | bigint nullable FK → users.id | Admin user |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | timestamp nullable | Soft delete |

**Indexes:** `page_type`, `page_id`, `status`, `(page_type, page_id)` unique partial (excluding homepage)

---

## Layout Data Structure (JSON)

The `layout_data` column stores the entire visual layout as a tree. Example:

```json
{
  "version": "1.0",
  "sections": [
    {
      "id": "sec_abc123",
      "type": "section",
      "settings": {
        "background_color": "#ffffff",
        "background_image": null,
        "padding_top": 60,
        "padding_bottom": 60,
        "full_width": false
      },
      "columns": [
        {
          "id": "col_def456",
          "width": 6,
          "settings": {
            "padding": 16,
            "align": "left"
          },
          "blocks": [
            {
              "id": "blk_ghi789",
              "type": "heading",
              "settings": {
                "level": "h2",
                "text": "Welcome to our store",
                "align": "left",
                "color": "#111827"
              }
            },
            {
              "id": "blk_jkl012",
              "type": "text",
              "settings": {
                "content": "<p>Discover our latest products.</p>"
              }
            }
          ]
        },
        {
          "id": "col_mno345",
          "width": 6,
          "settings": {},
          "blocks": [
            {
              "id": "blk_pqr678",
              "type": "image",
              "settings": {
                "image": "/storage/hero.jpg",
                "alt": "Hero image",
                "border_radius": 8
              }
            }
          ]
        }
      ]
    }
  ]
}
```

**Column `width`** uses a 12-column grid. A 2-column layout has `width: 6` each. A 3-column has `width: 4` each etc.

---

## Block Types

### Layout Blocks
| Type | Description | Key Settings |
|------|-------------|-------------|
| `section` | Top-level section wrapper (handled by SectionWrapper) | background_color, background_image, padding, full_width |
| `spacer` | Vertical white space | height (px) |
| `divider` | Horizontal rule | style, color, width |

### Content Blocks
| Type | Description | Key Settings |
|------|-------------|-------------|
| `heading` | H1–H6 text heading | level, text, align, color, font_size |
| `text` | Rich HTML via TipTap editor | content (HTML string) |
| `image` | Single image with optional link | image, alt, caption, link, border_radius, width |
| `video` | YouTube/Vimeo embed | url, autoplay, controls, ratio (16:9, 4:3, 1:1) |
| `button` | CTA button | label, url, variant (primary/secondary/outline), size, align |
| `table` | Data table | headers[], rows[][], striped, bordered |
| `card` | Flexible card component | image, title, body, footer, badge, link |

### Media Blocks
| Type | Description | Key Settings |
|------|-------------|-------------|
| `gallery` | Image grid or carousel | images[], layout (grid/masonry/carousel), columns, gap |

### Commerce Blocks
| Type | Description | Key Settings |
|------|-------------|-------------|
| `hero` | Full-width hero banner | image, video_url, overlay_color, overlay_opacity, headline, subheading, cta_text, cta_url, height, text_align |
| `products_grid` | Product listing grid | product_ids[], columns (2-6), show_price, show_cart, limit |
| `testimonials` | Customer testimonial cards | items[] (author, avatar, text, rating) |

---

## Block Palette Categories

```
Layout
├── Section (column layout switcher: 1 / 2 / 3 / 4 / 2-1 / 1-2)
├── Spacer
└── Divider

Content
├── Heading
├── Text (Rich HTML)
├── Image
├── Video
├── Button
├── Table
└── Card

Media
└── Gallery

Commerce
├── Hero / Banner
├── Products Grid
└── Testimonials
```

---

## Admin Routes

All routes are under `middleware ['web', 'auth:admin']` and prefix `/admin`:

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/admin/uieditor/pages/{page}/editor` | `admin.uieditor.pages.editor` | Open editor for CMS page |
| POST | `/admin/uieditor/pages/{page}/save` | `admin.uieditor.pages.save` | Save layout as draft |
| POST | `/admin/uieditor/pages/{page}/publish` | `admin.uieditor.pages.publish` | Publish layout |
| POST | `/admin/uieditor/pages/{page}/unpublish` | `admin.uieditor.pages.unpublish` | Revert to draft |
| GET | `/admin/uieditor/pages/{page}/preview` | `admin.uieditor.pages.preview` | Iframe preview (draft or published) |
| GET | `/admin/uieditor/homepage/editor` | `admin.uieditor.homepage.editor` | Open editor for homepage |
| POST | `/admin/uieditor/homepage/save` | `admin.uieditor.homepage.save` | Save homepage layout as draft |
| POST | `/admin/uieditor/homepage/publish` | `admin.uieditor.homepage.publish` | Publish homepage layout |
| POST | `/admin/uieditor/homepage/unpublish` | `admin.uieditor.homepage.unpublish` | Revert homepage to draft |
| GET | `/admin/uieditor/homepage/preview` | `admin.uieditor.homepage.preview` | Iframe preview |
| GET | `/admin/uieditor/products/search` | `admin.uieditor.products.search` | Search products for block settings |

---

## Editor UI Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  TOP BAR                                                            │
│  [← Back] [Page Title]   [Desktop|Tablet|Mobile]   [Draft●] [Save Draft] [Publish] │
├──────────────┬──────────────────────────────────┬───────────────────┤
│              │                                  │                   │
│  BLOCK       │   CANVAS                         │  PROPERTIES       │
│  PALETTE     │   (drag-drop surface)            │  PANEL            │
│              │                                  │                   │
│  Layout      │  ┌──────────────────────────┐   │  (Shows settings  │
│  ├ Section   │  │  SECTION                 │   │   for selected    │
│  ├ Spacer    │  │  ┌──────┐  ┌──────────┐  │   │   block/section)  │
│  └ Divider   │  │  │ Col1 │  │  Col 2   │  │   │                   │
│              │  │  │[blk] │  │ [block]  │  │   │                   │
│  Content     │  │  │      │  │ [block]  │  │   │                   │
│  ├ Heading   │  │  └──────┘  └──────────┘  │   │                   │
│  ├ Text      │  └──────────────────────────┘   │                   │
│  ├ Image     │                                  │                   │
│  └ ...       │  [+ Add Section]                 │                   │
│              │                                  │                   │
│  Commerce    │                                  │                   │
│  ├ Hero      │                                  │                   │
│  └ Products  │                                  │                   │
└──────────────┴──────────────────────────────────┴───────────────────┘
```

---

## Preview Modes

| Mode | Canvas Width | Device Frame |
|------|-------------|--------------|
| Desktop | Unrestricted (full canvas) | None |
| Tablet | 768px max, centered | Rounded border with orientation hint |
| Mobile | 390px max, centered | Narrow rounded border with notch hint |

When in **Preview mode** (toggle in top bar), the canvas switches from the live drag-drop editor to an `<iframe>` pointing to `/admin/uieditor/pages/{id}/preview`. The iframe width is driven by the selected preview mode. This loads the **real theme CSS** inside the iframe so the visual output matches the actual storefront.

---

## Draft / Publish Workflow

```
[No Layout]  →  [Create/Edit]  →  [Draft]  →  [Published]
                                     ↑               │
                                     └───[Unpublish]─┘
```

### States
- **No Layout**: Page has no UIEditor layout; storefront renders raw HTML from `pages.content`.
- **Draft**: Layout saved but not visible to public; storefront still renders raw HTML (or last published layout).
- **Published**: Layout is live; storefront renders `UIBlockRenderer` from `layout_data`.

### Auto-save
The editor watches for changes and debounces auto-save after **3 seconds of inactivity**. Auto-save always saves to draft. The status badge shows "Saving…" during auto-save and briefly shows "Saved" on success. Auto-save never triggers a publish.

### Published Layout Persistence
When a layout is published and then edited further, the in-progress edits remain as a **new draft** while the last published version stays live. The publish action always writes the current draft state as the new published version.

---

## Storefront Rendering

### CMS Pages (`/{slug}`)
The `PageController` loads `PageLayout::published()->forPage($page)->first()`. If found, `layoutData` is passed to the theme's `CMS/Page.vue` which renders `<UIBlockRenderer :layout="layoutData" />` instead of the raw HTML `v-html`. Falls back to `v-html` if no published layout.

### Homepage
The homepage controller loads `PageLayout::published()->homepage()->first()` and passes it to `Home/Index.vue`. If present, `UIBlockRenderer` drives the entire page. Falls back to the current `cmsBlocks` slot system if no published layout.

### UIBlockRenderer
`UIBlockRenderer.vue` is the shared rendering component used by:
- All theme `CMS/Page.vue` variants
- All theme `Home/Index.vue` variants
- The iframe preview route
- The editor canvas in non-edit preview mode

It accepts `layout: Section[]` and `editorMode: boolean` (default `false`). In `editorMode = true`, it renders drag handles, empty-state placeholders, and hover actions. In `editorMode = false` (storefront/preview), it renders clean output with no editor chrome.

---

## Hook Extension

Third-party extensions can register custom block types by binding to the `uieditor.register_blocks` hook:

```php
// In extension ServiceProvider::boot()
app(\Cartxis\Core\Services\HookService::class)
    ->addAction('uieditor.register_blocks', function ($registry) {
        $registry->register([
            'type'     => 'countdown_timer',
            'label'    => 'Countdown Timer',
            'icon'     => 'timer',
            'category' => 'interactive',
            'defaults' => [
                'end_date'    => null,
                'show_days'   => true,
                'show_hours'  => true,
                'label'       => 'Offer ends in',
            ],
        ]);
    });
```

The frontend block component and its panel are expected to be shipped as Vue components registered via the Vite glob mechanism (or as Inertia shared data).

---

## Integration Points

| Existing Feature | How UIEditor Uses It |
|-----------------|---------------------|
| `vuedraggable@4` (already installed) | Powers all section/column/block DnD in the editor canvas |
| `TiptapEditor.vue` | Used inside `TextPanel.vue` for rich HTML editing |
| `ImageUploader.vue` + Media Library picker | Used in Hero, Image, Gallery panels for media selection |
| `HookService` | `uieditor.register_blocks` hook for extension block types |
| `pages` table | `page_id` FK; storefront page controller queries layout by page |
| Theme `CMS/Page.vue` | Updated to render `UIBlockRenderer` when layout data is present |
| Theme `Home/Index.vue` | Updated similarly for the homepage layout |
| Admin Sidebar `MenuService` | UIEditor entries added: Content > Visual Editor, Appearance > Homepage Editor |

---

## Non-Goals (v1.0)

- No undo/redo history (v2 roadmap)
- No revision history / version snapshots (v2 roadmap)
- No scheduled publish (date-based; v2 roadmap)
- No multi-language layout variants (awaiting i18n package)
- No global block library (saved reusable blocks; v2 roadmap)
- No product page or category page visual editor (v2 scope)
- No visitor-facing inline editing

---

## Acceptance Criteria

1. `php artisan migrate` creates `uieditor_page_layouts` table without errors.
2. Admin → Content → Pages → Edit any page shows an "Open Visual Editor" button.
3. Clicking the button opens the full-screen editor with the 3-panel layout (Palette / Canvas / Properties).
4. Top bar shows the responsive preview switcher and Draft/Publish controls.
5. Dragging a block from the palette onto the canvas inserts it and marks the editor as dirty.
6. Switching to Tablet preview constains canvas to 768px with a device frame; Mobile to 390px.
7. Save Draft saves without publishing; storefront is unaffected.
8. Publish makes the layout live; the storefront page renders via `UIBlockRenderer`.
9. Unpublish (with confirmation) reverts the storefront to raw HTML.
10. Auto-save fires after 3 seconds of inactivity; saves silently without publishing.
11. All block types (Hero, Text, Heading, Button, Image, Gallery, Video, Products Grid, Testimonials, Card, Table, Spacer, Divider) can be added, configured, and rendered.
12. Iframe preview at `/admin/uieditor/pages/{id}/preview` renders the current draft in a bare theme view.
13. Homepage editor at `Admin → Appearance → Homepage Editor` functions identically to the page editor.
14. All existing CMS page tests pass after the changes (`php artisan test`).
15. A page without a `UIEditor` layout still renders its raw HTML content correctly (backward compat).
