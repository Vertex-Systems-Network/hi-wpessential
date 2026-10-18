# Essential — Theme & demo marketplace demo (Pro)

**Job of the site:** sell themes, demos, yearly plans and services, and host the docs — the WPEssential.org site as an importable demo.
**Design source:** Figma file `5AqHWOUl6bcITWWj4PPoAF` (pages WPEssential.org, WPEssential.org UI Kit), palette from Vertex Systems Network.

## Pages
Home · Demos · Pricing · Services · Docs · Blog (guides + release notes) · Contact · WooCommerce shop, cart, checkout, My Account.

## How selling works
- **Demos** (`product_cat` Demos): virtual WooCommerce products — Base (free), Maison, Pulse.
- **Services** (`product_cat` Services): virtual WooCommerce products, one per service. Custom development has no price, so it is not purchasable and links to Contact.
- **Plans** (`product_cat` Plans): Starter, Pro, Agency. Each product carries the WPEssential Server plan meta
  (`_wpes_plan`, `_wpes_months`, `_wpes_sites`, `_wpes_white_label`, `_wpes_pro`) and is sold individually.
  With the WPEssential Server plugin active, a completed order creates the license, renewals extend it and upgrades
  credit the unused value — the same subscription flow as the rest of the system (no WooCommerce Subscriptions needed).
  Without the server plugin they are ordinary WooCommerce products.

## Direction
- Palette: indigo #625BA8 primary, teal #5AC8D6 accent, ink #07080C; gradient indigo → blue #6188C6 → teal. Dark scheme included.
- Type: Bricolage Grotesque (headings) + Plus Jakarta Sans (UI), both local.
- Images: real — Base/Maison/Pulse thumbnails and previews, and WPEssential dashboard screenshots. No stock or placeholders.
- Plugins: WooCommerce (required).
