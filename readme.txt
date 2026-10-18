=== Hi WPEssential ===
Contributors: wpessential
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight multi-niche block theme, native for Gutenberg and Elementor.

== Description ==
Hi WPEssential is a fast, multi-niche block theme that is native for both Gutenberg and Elementor. Pick a demo, import it in one click with the builder you prefer, and get a complete, production-ready site: real pages, real content, real images, a working shop, light and dark schemes, hover effects and SEO, all set up for you.

= Two builders, one design =
Every demo ships in a Gutenberg version (core blocks only) and an Elementor version (native widgets and Kit globals, with no HTML widgets or custom CSS). Choose your builder; the look stays the same.

= Features =
* One-click demo import with the bundled Hi WPEssential Core plugin: required plugins, pages, posts, products, menus, images, header, footer and style. Remove a demo again with one click.
* Live demos in the browser (WordPress Playground): click, hover and browse every page before you import.
* theme.json v2 design system with a style variation per demo and local fonts.
* Light / dark toggle for visitors that follows the device setting and remembers the choice.
* Hover effects tuned to each niche (zoom, lift, glow, underline, image swap) and animation presets (subtle, editorial, playful, dynamic).
* Responsive at 1920, 1440, 1280, 1024, 768, 560, 480, 360 and 320 px, with an off-canvas menu on smaller screens.
* WooCommerce styling for the shop, product, cart, checkout and account pages, with badges, notices and empty states.
* Built-in SEO: meta description, Open Graph, Twitter cards and JSON-LD. It steps aside for SEO plugins.
* WPML and Polylang compatible, with a language switcher and full right-to-left (RTL) support.
* Accessible: WCAG 2.2 AA contrast, skip link, visible focus states and reduced-motion support.
* Full-page patterns (About, Services, Pricing, Contact, FAQ) and shop sections.
* Theme Options: CSS / JavaScript snippet manager with device scopes and safe mode.
* Pro demos and license activation from your WPEssential account.

= Demos =
* Base (free): business, agency, shop and blog. 7 pages, 3 articles, 8 products.
* Maison (pro): editorial fashion store with lookbook, atelier and journal.
* Pulse (pro): events and conferences with speakers, agenda, tickets, venue and sponsors.

== Installation ==
1. In your admin, go to Appearance > Themes > Add New > Upload Theme.
2. Upload hi-wpessential.zip and click Activate.
3. Open Hi WPEssential > Demo Library, preview a demo, choose Gutenberg or Elementor, and click Import.

== Copyright ==
Hi WPEssential, (C) 2026 WPEssential Team. Distributed under the terms of the GNU GPL.

== Frequently Asked Questions ==

= Can I switch from Gutenberg to Elementor later? =
Yes. Remove the demo in the Demo Library and import the same demo with the other builder.

= Will importing a demo overwrite my content? =
No. The importer only adds content and keeps a record, so Remove demo deletes what it added and restores your previous style, header and footer.

= Which screen sizes are supported? =
The layout steps at 1920, 1440, 1280, 1024, 768, 560, 480, 360 and 320 pixels. Each step sets the page gutter, section spacing, container width and heading sizes. From 1024 down the header switches to a compact bar with an off-canvas menu.

= Do I need the Hi WPEssential Core plugin? =
No. The theme works on its own. Core adds demo import, the header/footer builder, mega menus and Pro license activation.

= How do I build an About, Services, Pricing, Contact or FAQ page? =
Create a page, choose the "Full width" template, then pick the matching layout from the "Hi WPEssential — Full pages" patterns. With WooCommerce active, the "Hi WPEssential — Shop" patterns add category and product rows.

= Can visitors switch between light and dark? =
Yes. The header has a light/dark toggle. The first visit follows the device setting, and the choice is remembered in the browser. The site's own colours stay the default; the other scheme comes from the theme (the "Dark" style, or the base light palette when the site uses a dark style). Turn it off with add_filter( 'wpessential_scheme_toggle', '__return_false' ).

= Does it work with WPML and Polylang? =
Yes. The theme ships a wpml-config.xml (read by both plugins), adds a language switcher to the header menu automatically (also available as the [wpe_language_switcher] shortcode and a pattern), puts imported demo content into your default language, and supports right-to-left languages such as Arabic and Urdu.

= Which languages are included? =
The theme and its dashboard ship translated into 20 languages: Arabic, Bengali, Chinese (Simplified), Dutch, French, German, Hebrew, Hindi, Indonesian, Italian, Japanese, Korean, Persian, Polish, Portuguese (Brazil), Russian, Spanish, Turkish, Urdu and Vietnamese. Set your language under Settings > General. Translations from translate.wordpress.org, when available, take priority. To add a language, translate languages/hi-wpessential.pot with Poedit or Loco Translate.

= Why don't I see Elementor's banners in the dashboard? =
With this theme active, Elementor's upgrade banners, rating requests, promotional notices, the dashboard overview widget and the notifications feed are hidden so wp-admin stays clean. Error notices and database update notices still show. To bring them back, add add_filter( 'wpessential_elementor_quiet_admin', '__return_false' ); to a snippet or child theme.

= Does it handle SEO? =
Yes. Without an SEO plugin the theme prints a meta description, Open Graph and Twitter tags, and JSON-LD (Organization, WebSite, BreadcrumbList, Article). It steps aside automatically when Yoast SEO, Rank Math, SEOPress, All in One SEO or The SEO Framework is active.

== Changelog ==

= 1.0.0 =
* Initial release.
* Hi WPEssential dashboard: Demo Library with live preview, one-click import wizard (Gutenberg / Elementor), plugin manager, license activation and System Status.
* Importer runs in short resumable steps with info, warning and error logging, automatic retry, crash-safe skipping and one-click removal.
* Theme Options (Appearance): named CSS / JavaScript snippets with device scopes, head/footer placement, safe mode; animation presets (subtle, editorial, playful, dynamic with GSAP).
* Dashboard sign-in / sign-up connected to the WPEssential account; licenses, plans, upgrades and auto-renewal from the import wizard and License tab.
* Base Demo bundled: 7 pages, 3 articles, 8 WooCommerce products, menus and store settings.
* Maison (fashion) and Pulse (events) demos, each for Gutenberg and Elementor.
* Every demo has a native Elementor version (widgets and Kit globals).
* Live demos through WordPress Playground.
* Hover effects and dark palettes for each demo.
* Built-in SEO: meta description, Open Graph, Twitter cards and JSON-LD.
* WPML and Polylang compatibility, language switcher and RTL support.

== Resources ==

* Inter, (C) 2016 The Inter Project Authors, SIL Open Font License 1.1, https://github.com/rsms/inter
* Plus Jakarta Sans, (C) 2020 Tokotype, SIL Open Font License 1.1, https://github.com/tokotype/PlusJakartaSans
* Cormorant Garamond, (C) Christian Thalmann, SIL Open Font License 1.1, https://github.com/CatharsisFonts/Cormorant
* Manrope, (C) Mikhail Sharanda, SIL Open Font License 1.1, https://github.com/sharanda/manrope
* Space Grotesk, (C) Florian Karsten, SIL Open Font License 1.1, https://github.com/floriankarsten/space-grotesk
* DM Sans, (C) Colophon Foundry / Google, SIL Open Font License 1.1, https://github.com/googlefonts/dm-fonts
* Sun and moon icons in the light/dark toggle: Font Awesome Free 6, (C) Fonticons, Inc., CC BY 4.0, https://fontawesome.com/license/free
* Images in assets/images (hero, post-1 to post-3, demo-business, team-1 to team-4) were generated with AI for this theme by the WPEssential Team and are released under GPLv2 or later.
* GSAP 3.12.5 and ScrollTrigger, (C) 2024 GreenSock, Standard "no charge" license, https://gsap.com/standard-license — loaded only when the Dynamic animation preset or GSAP option is on.
