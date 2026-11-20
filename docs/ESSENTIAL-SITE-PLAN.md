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

## Phase 1 — New elements (built before the pages that need them)
Rule: every element is **general-purpose** for a multi-niche theme. No element is tied to WPEssential.org;
the site's look comes only from settings, content and the `essential` style. Each one works in Gutenberg and
Elementor, light and dark, every breakpoint, and on any niche (shop, agency, events, restaurant, education…).

| Element (general) | Free/Pro | Also covers (WPEssential.org use) |
|---|---|---|
| Login / Register / Lost & reset password form | Free | 16, 29–32 |
| Content switcher (toggle any two/three content sets: pricing periods, plans, before/after) | Free | 01, 02 |
| Breadcrumbs (posts, pages, products, archives, custom post types) | Free | inner pages |
| Post meta, post navigation, author box, related posts, table of contents | Free | 09, 14, 15 |
| Steps / process (numbered, horizontal or vertical) | Free | 01, 06 |
| Logo / brand strip (static or scrolling) | Free | 01, 12 |
| Timeline (dated entries with tags; changelog, roadmap, history, agenda) | Pro (extends Timeline) | 10, 11 |
| Comparison table (columns × features with ticks, badges, CTAs) | Pro | 02, 51 |
| WooCommerce set: product title, gallery, price, add to cart, meta, tabs, related, archive loop, mini-cart, cart, checkout, my account, order received, notices | Pro | 17, 18, 22–28, 34–35, 44 |
| Account dashboard (any user area: tabs from WooCommerce endpoints, custom panels, modals) | Pro | 18, 38–41, 44 |
| Dynamic listing / data views from Query Builder (cards, tables, statuses) | Pro | 45–48 (quotes, invoices, projects as CPT/CCT data) |

Most-requested elements in the market that are still missing (Elementor Pro / Essential Addons / JetElements parity):
Off-canvas panel, Hotspots, Lottie animation, Animated (rotating) headline, Marquee / ticker, Notification bar,
Reading progress + back-to-top, Social share, Advanced data table filters, Dual-button, Cookie notice, Sticky video,
Feed grid (extends Social Feed), Mega-menu templates (Menu already supports).

## Phase 2 — Pages, in this order
1. 01 Home · 02 Pricing · 03 Plugins · 04 Themes & demos · 05 Demo detail · 06 Services · 07 Request a quote
2. 08 Docs · 09 Doc article · 10 Changelog · 11 Roadmap · 12 About · 13 Contact · 14 Blog · 15 Blog post
3. 20 Legal · 21 404 · 16 Login (+29–32)
4. 17 Checkout · 18 My Account (+22–28, 34–35, 38–41, 44) — after the WooCommerce and Account elements
5. 19 Support ticket · 45–51

## Phase 3 — Ship
Elementor package from the same element data, `build.py --zip`, import test on a clean site, screenshots.

Status is tracked in the session task list; this file changes only when scope changes.
