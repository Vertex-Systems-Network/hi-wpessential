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

## Page plan and user flow (Figma `WPEssential.org` → Final, frame `00 · User flow map`)

### Inventory
| # | Page / state | Job · main action | General elements |
|---|---|---|---|
| 01 | Home | discover → pick a demo / Get Pro | container, heading, text, search, button, info-box, steps, pricing table, content switcher, testimonial, posts grid, forms |
| 02 | Pricing | choose a plan → 22 | content switcher, pricing table, comparison table, accordion |
| 03 | Plugins | download free plugins → wp.org | info-box, button |
| 04 | Themes & demos | filter demos → 05 | filter/listing, card |
| 05 | Demo detail | preview / buy → 22 | gallery, breadcrumbs, button, icon-list |
| 06 | Services | pick a service → 07 | info-box, steps, pricing table |
| 07 | Request a quote | send brief → 45 | forms |
| 08 | Docs | search / open category → 09 | search, info-box |
| 09 | Doc article | read, next article | breadcrumbs, table of contents, post meta, post navigation |
| 10 | Changelog | read releases | timeline |
| 11 | Roadmap | vote/read plans | timeline |
| 12 | About | trust → contact | heading, counter, logo strip, team member |
| 13 | Contact | send message (success/error) | forms, icon-list |
| 14 | Blog | open post → 15 | posts grid, search |
| 15 | Blog post | read → related | post meta, author box, related posts, social share |
| 16 | Login | sign in → 33 · create account → 29 · forgot → 30 | login/register form |
| 17 | Checkout | pay → 27 / 28 | Woo checkout |
| 18 | My Account | dashboard tabs → 34–44 | account dashboard |
| 19 | Support ticket | open ticket (success/error) | forms |
| 20 | Legal | read | text, table of contents |
| 21 | 404 | search / go home | search, button |
| 22–23 | Product (Pro / add-on) | choose billing, add to cart → 24 | Woo product set |
| 24–25 | Cart / empty cart | coupon, remove → 26 / shop | Woo cart |
| 26–28 | Checkout / order received / payment failed | pay; downloads → 36–37; retry → 26 | Woo checkout, order received |
| 29–32 | Register · lost password · email sent · reset | account recovery → 16 | login/register form |
| 33–44 | Account: dashboard, orders, order detail, downloads, licences, subscriptions, invoices, addresses, details | manage purchases | account dashboard, Woo account |
| 38–41 | Modals: manage sites, upgrade, cancel | change licence/subscription | modal, forms |
| 45–48 | Quote view · invoice pay · invoice paid · project status | accept → pay → track | dynamic listing (CPT/CCT), forms |
| 49–51 | Refund policy · licence terms · billing FAQ | read | text, accordion, comparison table |

### Flow map (from → click/action → goes to)
- Header: Sign in → 16 → 33 Dashboard · Get Pro → 02 · Search → results / 21 when empty.
- 02 Pricing: Get Starter / Pro / Agency → 22 (plan preselected).
- 22 Product: choose billing + Add to cart → 24 · add an add-on → 23.
- 24 Cart: remove / coupon → 24 updated or 25 empty · Proceed to checkout → 26.
- 26 Checkout: Pay → 27 Order received or 28 Payment failed (retry → 26).
- 27 Order received: Download / View licence → 36 Downloads · 37 Licences.
- 16 Login: Create account → 29 · Forgot password → 30 → 31 email sent → 32 reset → 16.
- My account tabs (Orders, Downloads, Licences, Subscriptions, Invoices, Addresses, Account details) → 34–44; Orders → View → 35;
  Licences → Manage sites → 38 (modal); Subscriptions → Upgrade 40 / Cancel 41 (modals).
- 06 Services → Request a quote → 07 → 45 Quote view → Accept → 46 Invoice pay (Payoneer / PayPal / Stripe / Bank) → 47 paid → 48 Project status.
- Footer: every Products / Resources / Company / Support link and the newsletter form (success/error).

### Journeys (built and tested in this order)
1. Discover → demo → pricing → product → cart → checkout → order received → downloads/licences.
2. Sign in / register / recover password → account dashboard → orders, licences, subscriptions.
3. Services → quote → invoice → payment → project status.
4. Learn: docs → article, blog → post, changelog, roadmap; support ticket; 404 recovery.
