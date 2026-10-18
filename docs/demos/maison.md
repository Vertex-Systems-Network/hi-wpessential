# Maison — Fashion store demo (Pro)

**Job of the site:** sell a curated womenswear / unisex capsule collection; build brand trust like a magazine.
**Audience:** 25–45, design-aware shoppers who buy fewer, better pieces.

## Research (Oct 2026)

| Reference | Does well | We take | We avoid |
| --- | --- | --- | --- |
| Aesop | Text-forward hero, long editorial intros, still-life photography | Editorial copy blocks between product rows | Product-light home that hides prices |
| Bottega Veneta | Restraint, huge imagery, no clutter | Full-bleed campaign hero, 0 px radius | Hidden navigation |
| Dior | Heritage storytelling | "Atelier" story section with process photos | Heavy video autoplay |
| Everlane | Materials, factory, price transparency on PDP | "Materials & care" + "Made in" accordions | Overlong PDP |
| Gymshark | Distraction-free grid, high-impact visuals | Clean 4-col grid, hover second image | Sporty tone |
| Nike | Filters and category organisation | Sticky filter bar, size/colour swatch filters | Dense mega menu |
| Jimmy Choo / Tiffany | Luxury motion, product discovery | Slow mask reveals (editorial motion preset) | Slow-loading 3D |
| 2026 trend roundups | Dark luxury palettes, lifestyle photography, micro-interactions | Espresso dark scheme, lifestyle-first imagery | AR/3D (not needed) |

Sources: wisepops.com/blog/ecommerce-website-design-examples, designveloper.com/blog/luxurious-websites, redefineweb.com/blog/top-ecommerce-web-design-trends-2026-dtc-conversions, kgwebdesigner.com (2026 analysis), insidea.com fashion website ideas.

## Sitemap
Home · Shop (category grid + filters) · Category · Product (simple, variable with size/colour, out of stock) · Lookbook · Atelier (about) · Journal (blog list + article) · Stores / Contact · FAQ / Shipping & Returns · Cart · Checkout · Order received · My Account (dashboard, orders, order detail, addresses, account details, login/register, lost password) · Wishlist · Search results · Empty cart · 404 · Quick view, mini-cart drawer, size guide modal, newsletter popup.

## Direction
- Palette light: ivory #F6F1EA surface, paper #FFFFFF, espresso #2B211B text, taupe #8A7B6E muted, clay #B5643C accent, sand border #E4DACD. Dark: espresso #17110D surface, #221A15 alt, ivory text #F3ECE2, clay #D07A50.
- Type: Cormorant Garamond (display, light italic for accents) + Manrope (UI).
- Radius 0, hairline borders, generous whitespace; uppercase letter-spaced nav.
- Header: utility bar (free shipping), centred logo, split nav left/right, icons right.
- Hero: full-bleed editorial campaign photo with oversized serif headline bottom-left.
- Cards: borderless, 3:4 images, second image on hover, swatches under title.
- Motion: `editorial`.
- Plugins: WooCommerce (required).

## Differentiation
vs Base: no gradients, no rounded cards, serif display, warm palette, centred logo header, photographic hero instead of mockup.

## Hover & motion (research)

Maison uses the `editorial` motion preset and `settings.custom.hover` in `styles/maison.json`.

| Element | Behaviour | Why (source) |
| --- | --- | --- |
| Product card | No lift. The second gallery image (back view) crossfades in over 0.9 s while the image slowly zooms to 1.04. | Lingerie and fashion stores show the back view on hover to explain fit ([commerce-ui.com](https://commerce-ui.com/insights/motion-in-ecommerce-part-1-microinteractions)). Luxury listings keep the product steady and add information without hiding it. |
| Buttons | Fill inverts to an outline, with no lift or glow. | Restrained luxury tone. |
| Links / nav | An underline draws in from the left. | Editorial, magazine feel. |
| Category / lookbook images | Slow zoom inside a fixed frame. | Calm, premium motion ([framer.com](https://www.framer.com/marketplace/components/hover-product-card/)). |
| Scroll | Headings rise, images reveal with a curtain (`editorial`). | Slow mask reveals, as in the luxury references above. |

## Gap audit (Oct 2026)
Compared with top Envato fashion demos (Kalles, Elessi, WoodMart Fashion) and stores such as Everlane, COS and Arket, Maison was missing three pages they all ship:
- **Size guide** — clothing and shoe tables (EU/UK/US) plus how to measure; linked from the footer and PDP help.
- **Responsibility** — impact numbers, six commitments and an honest "where we fall short" block (Everlane / Arket pattern).
- **Book an appointment** — styling, made-to-measure and repairs, with a WPForms request form (luxury-store pattern).
