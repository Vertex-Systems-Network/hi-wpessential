# Project status & handoff — Hi WPEssential (as of 21 Oct 2026)

Start the next session from this file. Plugin-side work (kit elements, Site Tools Kit, importer) is tracked in the WPEssential plugin repository; see [CORE-INTEGRATION.md](CORE-INTEGRATION.md). Owner: Vertex Systems Network (Pvt.) Ltd. Version stays **1.0.0**.

## Standing rules (owner decisions)
- Replies to the owner in simple Roman Urdu; do pending work without being asked twice.
- Research first: [docs/research/](research/README.md) process + elements / sections / niche banks. Design only from banks, compare with top themes (Avada, Betheme, Enfold, The7, WoodMart, Shopkeeper, Newspaper, ThemeForest best-sellers), redo if ours is not better.
- [Design rules](demos/design-rules.md): no minimalist demos (Minimal is its own niche); premium art-directed look; **Google map on every Contact/Locations page**.
- Every demo needs in Figma: **light + dark pages, all 8 breakpoints (1920/1440/1024/768/560/480/360/320), UI kit (light + dark)**; in WordPress: **Gutenberg + Elementor**.
- All forms: WPForms Lite. WPEssential.org is the live site (not a demo).
- No tokens/passwords in chat. Merge PRs after CI is green (owner gave permission). New work on a fresh branch from `origin/main`; `gh pr create` is blocked — use `gh api repos/Vertex-Systems-Network/hi-wpessential/pulls`.

## Figma (file key `5AqHWOUl6bcITWWj4PPoAF`)
| Page | State |
| --- | --- |
| Image Library `85:14423` | demo images (bp-*, sf-*, cp-*, team-1..4, map-google) |
| Base Demo `76:2` / Dark `102:16560` / UI Kit `51:2` | Northwind Advisory pages 1440 + 360; old theme-marketing frames removed; aligned in sections |
| Maison `195:2` / Dark `195:3` / UI Kit `195:4` | all breakpoints incl. Size guide, Responsibility, Appointment |
| Pulse `209:2` / Dark `209:3` / UI Kit `209:4` | all breakpoints incl. About, Gallery, Call for speakers, Code of conduct |
| WPEssential.org `232:2` / Dark `232:3` / UI Kit `232:4` | grouped by breakpoint; old Contact removed (Dark Contact v2 still missing) |
| Business Pro `356:2` + Dark + UI Kit | 10 pages × 8 breakpoints, premium style (Corvane group) |
| Saffron `356:3` + Dark + UI Kit | 8 pages × 8 breakpoints (restaurant) |
| Campus `356:4` + Dark + UI Kit | 10 pages × 8 breakpoints (LMS) |

Builder script for premium Figma pages: helper library used in session (hero overlay, glass stats, bento, pill buttons, shadows); breakpoints and dark versions are generated from 1440 by conversion scripts — spot-check frames before shipping.

## WordPress / code (all merged to `main`)
- Theme + Core plugin + importer, demo builder in `tools/demo-builder/` (`python3 build.py [demo]`).
- Demos built for Gutenberg + Elementor: **Base (Northwind Advisory)**, **Maison** (+ size guide, responsibility, appointment), **Pulse** (+ about, gallery, CFP, code of conduct), **WPEssential.org**.
- Google map on Contact: Base, Maison, Pulse, WPEssential.org.
- WPEssential.org office: House 3213/4 8M, Fried Abad, Baron Bohar Gate, Multan · +92 305 1983930 (`OFFICE` in `essential.py`); 18 sample client names (`CLIENTS`).
- PRs merged this period: #18–#26.

## Still dummy (owner to confirm later)
WPEssential.org numbers (1,200+ sites, 4.9/5, 12 yrs), team names/photos, care plan prices ($49/$129/$299), service prices, recent work + review, social links.

## Next steps (in order)
1. Score Business Pro, Saffron, Campus against Avada/Betheme niche demos with the [scorecard](research/README.md); redesign weak parts.
2. Build Business Pro, Saffron, Campus in WordPress (Gutenberg + Elementor, WPForms, WooCommerce/LMS where needed, map on contact).
3. Upgrade existing demos (Base/Northwind, Maison, Pulse, WPEssential.org) to the premium design rules.
4. Northwind dark + all breakpoints review; WPEssential.org Contact v2 dark.
5. Keep growing the banks; plan next niches from the [niche bank](research/niche-bank.md) (Minimal portfolio, News, Electronics store, Clinic, Real estate, Hotel, Nonprofit).
6. Replace dummy WPEssential.org data when the owner sends it.
