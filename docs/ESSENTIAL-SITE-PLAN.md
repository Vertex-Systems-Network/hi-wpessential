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
Needed by the Figma pages:
| Element | Free/Pro | Pages |
|---|---|---|
| Login / Register / Lost & reset password form | Free | 16, 29–32 |
| Content switcher (Monthly / Yearly / Lifetime) | Free | 01, 02 |
| Breadcrumbs | Free | 05, 09, 15, 20 + all inner pages |
| Post meta, post navigation, author box, related posts, table of contents | Free | 09, 14, 15 |
| Steps / process | Free | 01, 06 |
| Logo / brand strip | Free | 01, 12 |
| Changelog & roadmap timeline (extends Pro Timeline) | Pro | 10, 11 |
| Comparison table | Pro | 02, 51 |
| WooCommerce: product title, gallery, price, add to cart, meta, tabs, related, archive loop, mini-cart, cart, checkout, my account, order received, notices | Pro | 17, 18, 22–28, 34–35, 44 |
| Account: licences/sites manager, downloads, subscriptions (modals 38–41) | Pro | 18, 38–41 |
| Service portal: quote, invoice, project status | Pro | 45–48 |

Most-requested elements in the market that are still missing (Elementor Pro / Essential Addons / JetElements parity):
Off-canvas panel, Hotspots, Lottie animation, Animated (rotating) headline, Marquee / logo ticker,
Notification bar, Reading progress + back-to-top, Social share, Advanced data table filters, Dual-button,
Cookie notice, Sticky video, Instagram/feed grid (extends Social Feed), Mega-menu templates (Menu already supports).

## Phase 2 — Pages, in this order
1. 01 Home · 02 Pricing · 03 Plugins · 04 Themes & demos · 05 Demo detail · 06 Services · 07 Request a quote
2. 08 Docs · 09 Doc article · 10 Changelog · 11 Roadmap · 12 About · 13 Contact · 14 Blog · 15 Blog post
3. 20 Legal · 21 404 · 16 Login (+29–32)
4. 17 Checkout · 18 My Account (+22–28, 34–35, 38–41, 44) — after the WooCommerce and Account elements
5. 19 Support ticket · 45–51

## Phase 3 — Ship
Elementor package from the same element data, `build.py --zip`, import test on a clean site, screenshots.

Status is tracked in the session task list; this file changes only when scope changes.
