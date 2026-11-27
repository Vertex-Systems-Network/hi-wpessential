# WPEssential.org (Essential demo) — build plan

Source: Figma `WPEssential.org` → page **Final** (frames 00–51). Rule: every block on every page is a
WPEssential kit element (`wpessential/*`); no core, WooCommerce or third-party blocks in demo content.
Admin screens use our own UI toolkit. One pass per page, checked against the Figma frame before moving on.

## Phase 0 — Foundation (once)
1. `tools/demo-builder/kit.py`: helpers that emit kit blocks (container, heading, text, button, image,
   icon-list, info-box, tabs, accordion, testimonial, search, menu, site-logo, social, counter, table,
   gallery, pricing-table, posts-grid, products, forms, and every new element below).
2. Design tokens from the Figma Tool Kit page → theme style `essential` (colours, type scale, radius,
   spacing, light/dark). Header and footer built once as kit-only template parts.

## Phase 1 — New elements (plugin work, moved)
The elements belong to the WPEssential plugins. Their list and status are now in the plugin repository, `wpessential/docs/KIT-ELEMENTS.md` (as of 2026-11-27, every element in the Phase 1 list is built except a dual button, advanced table filters and mega-menu templates). The rule stays the same: every element is general-purpose and works in Gutenberg and Elementor, in light and dark, at every breakpoint and for every niche.

## Phase 2 — Pages, in this order
1. 01 Home · 02 Pricing · 03 Plugins · 04 Themes & demos · 05 Demo detail · 06 Services · 07 Request a quote
2. 08 Docs · 09 Doc article · 10 Changelog · 11 Roadmap · 12 About · 13 Contact · 14 Blog · 15 Blog post
3. 20 Legal · 21 404 · 16 Login (+29–32)
4. 17 Checkout · 18 My Account (+22–28, 34–35, 38–41, 44) — after the WooCommerce and Account elements
5. 19 Support ticket · 45–51

## Phase 3 — Ship
Elementor package from the same element data, `build.py --zip`, import test on a clean site, screenshots.

Status is tracked in the session task list; this file changes only when scope changes.
