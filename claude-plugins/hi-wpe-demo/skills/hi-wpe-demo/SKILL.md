---
name: hi-wpe-demo
description: Design a complete WPEssential demo in Figma from a Figma project/space/file link plus the user's design instructions — niche research and R&D, light and dark schemes, 8 breakpoints, UI kit with hover/focus/active states, plugin screens. Use when the user gives a Figma link and asks for a demo design.
argument-hint: <figma-link> <design instructions>
---

# Hi WPE Demo — design a demo in Figma

The user gives a Figma link (project, team space or file) and design instructions. Claude does the
research and the whole design in Figma with the Figma MCP tools (`use_figma`, `generate_image`,
`get_screenshot`, `get_metadata`). No other plugin is needed.

**Read `docs/DEMO-PLAYBOOK.md` first** (§1 rules, §2 Figma, §1.12 hovers/motion, §1.13 SEO). If the
repo is not open, follow the rules summarised below.

## Inputs

- **Figma link.**
  - File link (`figma.com/design/<key>/…`): design inside that file.
  - Project or team space link: create a new design file there with `create_new_file` named `<Name> Demo`.
- **Instructions:** niche, brand, pages, mood, colours, references. Fill gaps with sensible defaults and keep going; ask only if the link cannot be opened.

Load the `figma-use` and `figma-generate-design` skills before the first `use_figma` call.

## 1. Research and R&D

- WebSearch at least 8 market leaders in the niche. Record for each: sitemap, hero pattern, palette, type, hover and motion behaviour, conversion elements.
- Derive the direction: palette (light + dark, every theme slug), Google Fonts pairing, radius, header, hero, cards, motion preset and hover tokens (zoom, lift, shadow, duration, ease, button lift/glow, underline, image rest).
- Make it different from every existing demo (playbook §7).
- Check WCAG 2.2 AA for every text/background pair in both schemes.
- Write the brief as a `Brief` page in the Figma file (and `docs/demos/<id>.md` when the repo is open): research table with sources, direction, hover & motion table, sitemap, plugins.

## 2. Foundations

- Variables collection `<Name>` with **Light** and **Dark** modes for every colour slug; number variables for spacing and radius; text styles for the type scale; effect styles for shadows.
- Bind every fill, text colour, gap and radius to these variables so the Dark page is a mode switch.

## 3. UI kit — page `<Name> Demo UI Kit`

Components with variants for **default, hover, focus, active, disabled**: buttons (all styles), links, nav item, cards (post, product, team/speaker, feature), inputs, select, checkbox, badges, pagination, header (default/shop/transparent), footer, mobile menu. Hover variants follow the research tokens.

## 4. Pages — page `<Name> Demo`

- Every page from the sitemap plus the plugin screens the niche needs (shop, product, cart, checkout, account; events; courses …) and states (empty cart, 404, search results, form success/error).
- Each page at **1920, 1440, 1024, 768, 560, 480, 360, 320** in auto layout, built from the UI kit instances.
- Real copy for the niche, never lorem ipsum or "Product name". One H1 per page, headings in order (SEO).
- Images: `generate_image` at 1536 × 1024, real content, heads never cropped; collect them in section `<Name> Demo · Images`.
- Prototype links: nav, buttons and cards link between frames; hover variants use "While hovering" interactions.

## 5. Dark — page `<Name> Demo Dark`

Clone the 1440 and 390 frames and set the variable mode to Dark. Fix any contrast failure in the variables, not per layer.

## 6. Audit

Run `use_figma` audits and fix to zero: text overflow or clipping, empty auto-layout gaps, unbound colours, missing hover variants, frames wider than their breakpoint. Screenshot every 1440 and 390 frame and look at them.

## Report

Reply with the file link, the pages built, the research summary (leaders, hover/motion choices), contrast results, and the next step: run `/hi-wpe <figma-link>` to turn the design into importable Gutenberg + Elementor demo packages.
