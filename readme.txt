=== WPEssential ===
Contributors: wpessential
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight multi-niche block theme, native for Gutenberg and Elementor.

== Description ==
WPEssential is a fast, theme.json driven block theme. Every demo ships in a Gutenberg and an Elementor version. Install the WPEssential Core plugin for one-click demo import, live demo preview, automatic plugin setup and Pro license activation.

== Copyright ==
WPEssential, (C) 2026 WPEssential Team. Distributed under the terms of the GNU GPL.

== Frequently Asked Questions ==

= Which screen sizes are supported? =
The layout steps at 1920, 1440, 1280, 1024, 768, 560, 480, 360 and 320 pixels. Each step sets the page gutter, section spacing, container width and heading sizes. From 1024 down the header switches to a compact bar with an off-canvas menu.

= Do I need the WPEssential Core plugin? =
No. The theme works on its own. Core adds demo import, the header/footer builder, mega menus and Pro license activation.

= How do I build an About, Services, Pricing, Contact or FAQ page? =
Create a page, choose the "Full width" template, then pick the matching layout from the "WPEssential — Full pages" patterns. With WooCommerce active, the "WPEssential — Shop" patterns add category and product rows.

= Can visitors switch between light and dark? =
Yes. The header has a light/dark toggle. The first visit follows the device setting, and the choice is remembered in the browser. The site's own colours stay the default; the other scheme comes from the theme (the "Dark" style, or the base light palette when the site uses a dark style). Turn it off with add_filter( 'wpessential_scheme_toggle', '__return_false' ).

== Changelog ==

= 1.0.0 =
* Initial release.
* WPEssential dashboard: Demo Library with live preview, one-click import wizard (Gutenberg / Elementor), plugin manager, license activation and System Status.
* Importer runs in short resumable steps with info, warning and error logging, automatic retry, crash-safe skipping and one-click removal.
* Theme Options (Appearance): named CSS / JavaScript snippets with device scopes, head/footer placement, safe mode; animation presets (subtle, editorial, playful, dynamic with GSAP).
* Dashboard sign-in / sign-up connected to the WPEssential account; licenses, plans, upgrades and auto-renewal from the import wizard and License tab.
* Base Demo bundled: 7 pages, 3 articles, 8 WooCommerce products, menus and store settings.

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
