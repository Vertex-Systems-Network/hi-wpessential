---
name: hi-wpe
description: Build a complete WPEssential demo (Figma file, Gutenberg + Elementor packages, images, style variation, hovers, SEO, server zips) from a UI link — a Figma file/frame, a Claude Design / Framer / Webflow / live site URL — following docs/DEMO-PLAYBOOK.md. Use when the user gives a design link and asks for a demo.
argument-hint: <design-url> [demo-id] [free|pro]
---

# Build a demo from a UI link

The user gives a link to a finished UI. Turn it into a WPEssential demo that imports into this theme with
one click, in **both** builders, and obeys every rule in `docs/DEMO-PLAYBOOK.md`.

**Read the playbook first, every time.** It is the record; this skill is only the order of work.

## Inputs

- **Design link (required).**
  - Figma: `figma.com/design/<fileKey>/...?node-id=1-2`.
  - Any other tool: Claude Design, Framer, Webflow, a live site or a prototype URL.
- **Demo id.** Lowercase, e.g. `aurora`. If missing, derive it from the brand.
- **free or pro.**
  - Free demos are open to every plan.
  - Pro demos are gated by plan on the server.
- **Plugins** the niche needs, e.g. WooCommerce, The Events Calendar, LearnDash. If unclear, infer them from the UI (shop pages → WooCommerce).

Pick sensible defaults and keep going. Ask only if the link cannot be opened at all.

## 1. Read the design

- **Figma.**
  - `get_metadata` → list the pages and frames.
  - `get_screenshot` for every page frame.
  - `get_design_context` for the tokens: colours, type, spacing and radius.
  - `get_variable_defs` for the variables.
- **Other tools.** Open the URL with Playwright (`/opt/pw-browsers`, Chromium) at 1440 and 390. Take a full-page screenshot of every page in its navigation, then extract:
  - Colours from computed styles.
  - Font families from `document.fonts`.
  - Spacing and radius.
  - Any hover effects already present: hover each interactive element and diff the computed styles.
- Write down the sitemap, every component, and every state that is visible.

## 2. Research (playbook §1.3, §1.12)

- Study at least 8 market leaders for the niche (WebSearch).
- Write `docs/demos/<id>.md` with these sections:
  - Job of the site, audience, a research table with sources, and the sitemap.
  - Direction: palette light and dark, type, radius, header, hero, cards.
  - Motion preset.
  - A **Hover & motion** table with sources.
  - Plugins.
  - Differentiation against every demo in §7.
- If the UI breaks a rule, keep the design and fix the rule-breaker:
  - Contrast that fails WCAG.
  - Placeholder text or images.
  - Cropped heads.
  - Missing hover states.

  Note each fix in the brief.

## 3. Figma (playbook §2)

- **Create in file `5AqHWOUl6bcITWWj4PPoAF`** (or the user's file):
  - `<Name> Demo`.
  - `<Name> Demo Dark`, a clone with the Dark variable mode.
  - `<Name> Demo UI Kit`, which includes hover, focus and active states for every component.
- Build every page at **1920, 1440, 1024, 768, 560, 480, 360 and 320**.
- Also build every plugin screen and every state. Store a builder library plus page builders in hidden text nodes, the same way Maison and Pulse do.
- Run the overflow, clipping and empty-gap audits and fix the results to zero.

## 4. Images (playbook §1.1, §1.8–1.10)

- Generate every image with Figma AI at **1536 × 1024** using `gpt-image-2.5-sunburst`, as real content (no placeholders).
  - Heads are never cropped; the subject is centred with margins.
  - Add the images to the Image Library section `<Name> Demo · Images`.
- **Export.**
  1. Clone the rects at 1536 × 1024 into a temporary section.
  2. `get_screenshot` each one with `maxDimension: 1536` and `enableBase64Response: true`. The PNG is saved under tool-results.
  3. Convert it to WebP in `demos/<id>/media/<key>.webp`.
  4. Delete the temporary section.

## 5. Style variation

`styles/<id>.json` must contain:

- The palette, with every theme slug filled.
- Local fonts: `npm pack @fontsource-variable/<font>` into `assets/fonts/`, plus a credit line in `readme.txt`.
- Gradients.
- `custom.radius`, `custom.scheme.dark` (the dark palette) and `custom.color.primaryText` (readable accent on light surfaces).
- `custom.hover`, from the research: zoom, lift, productLift, shadow, cardShadow, duration, ease, buttonLift, buttonGlow, underline, imageRest.
- Button and heading styles.

Check the contrast of every text/background pair in both schemes.

## 6. Packages (tools/demo-builder)

Create `tools/demo-builder/<id>.py`, copying the structure of `maison.py` / `pulse.py`:

- **Data:** `MEDIA` (key, title, alt), `TERMS`, products/events/posts and `DEMO` (manifest entry: type, niche, pages, highlights, plugins).
- **Gutenberg pages:** `g_<page>()`, written only with `blocks.py`, so the markup is core-block-valid and uses theme classes (`wpe-section`, `wpe-card`, `is-style-wpe-*`).
- **Elementor pages:** `e_<page>()`, written only with `elementor.py`. This means native widgets and Kit globals, with no custom CSS and no HTML widget.
  - Set `e.FONTS` and `e.HOVER` in `package()` from the brief.
- **Header and footer:** `g_header()` / `g_footer()` passed in `settings.parts`. Cover `header`, `header-shop`, `header-transparent` and `footer`. The footer credits WPEssential.
- **settings:**
  - `front_page`, `blog_page`, `style`, `motion` and `options` (`blogname`, `blogdescription`).
  - `woocommerce` (currency, shipping) when used.
  - `elementor_kit`, through `e.kit(...)`.
- **SEO (playbook §1.13):**
  - One H1 per page and headings in order.
  - Real alt text, readable slugs, and internal links between key pages.
  - A unique title and description for every page.
- **Register and build:**
  1. Append `'<id>'` to `DEMOS` in `build.py`.
  2. Run `python3 tools/demo-builder/build.py <id> --zip`.

## 7. Validate (playbook checklist)

On a local WordPress with the theme, Core and the plugins active, using `tools/demo-builder/qa/` (see its README), do all of the following:

1. **Import:** both builders import with 0 errors.
2. **Block editor:** open every page in the block editor. The result must be **0 invalid blocks** (Playwright: `wp.data.select('core/block-editor').getBlocks()` → `isValid`).
3. **Screenshots:** take them at 1440 and 390 (and spot-check 768). The result must be **no horizontal scroll** and nothing hidden by motion. Read every screenshot.
4. **Hovers:** hover the product card, speaker/team card, button, link and nav item. Each state must match the brief (`tools/demo-builder/qa/hover.cjs`).
5. **Dark scheme:** toggle the dark scheme and check contrast.
6. **SEO gate:** Lighthouse SEO 100 and Accessibility 95 or more, with valid JSON-LD.
7. **Previews:** generate the thumbnail (880 × 605) and the previews (desktop 1240, tablet 820, mobile 390) from the real import into `demos/<id>/`.

## 8. Ship

1. **Server.** Upload `dist/<id>-gutenberg.zip` and `dist/<id>-elementor.zip` to the WPEssential Server: **Demos → <Name>**, with type free or pro, the plugins and the tagline from `DEMO`. The server hands them out by plan.
2. **Live demo.** The live demo works automatically through WordPress Playground once the theme zip is uploaded in **Settings → Live demos**.
3. **Elementor MCP (optional).** If the user's Elementor MCP site is connected, rebuild the home page there with `elementor-build-composition`:
   - Fonts are single family names.
   - Hovers go in `&:hover { … }` inside each style.
   - Afterwards, create or update a global variable to force Elementor to regenerate its CSS.
   - Screenshot the result with hover.
4. **Playbook.** Update §7 (demo register) and the checklist.
5. **Commit and push** on the working branch.

## Report

Give the user:

- What was built: pages, plugins, images.
- How the hovers and motion follow the research.
- Anything from the UI that was changed to meet the rules.
- Validation results with real numbers.
- What still needs their action (server upload, plugin licences).
