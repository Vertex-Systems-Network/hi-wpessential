=== Hi WPEssential ===
Contributors: wpessential
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight multi-niche block theme, native for Gutenberg and Elementor.

== Description ==
Hi WPEssential is a fast, multi-niche block theme that is native for both Gutenberg and Elementor. It brings a theme.json design system with a style variation per niche, light and dark schemes, hover effects, scroll animations and full-page patterns.

= Two builders, one design =
Every layout is available for Gutenberg (core blocks) and styled for Elementor (native widgets and Kit globals). Choose your builder; the look stays the same.

= Features =
* theme.json design system with a style variation per niche and bundled local fonts.
* Site Settings in the Customizer: colours (light and dark), typography, buttons, forms, corners, header, links, shadows, hover effects and scroll animations.
* Light / dark toggle for visitors that follows the device setting and remembers the choice.
* Hover effects (zoom, lift, glow, underline, image swap) and animation presets (subtle, editorial, playful, dynamic), all off for visitors who prefer reduced motion.
* Responsive at 1920, 1440, 1280, 1024, 768, 560, 480, 360 and 320 px, with an off-canvas menu on smaller screens.
* WooCommerce styling for the shop, product, cart, checkout and account pages.
* Per-page design settings: hide the title or featured image, content width, spacing and background.
* Full-page patterns (About, Services, Pricing, Contact, FAQ) and shop sections.
* Accessible: WCAG 2.2 AA contrast, skip link, visible focus states and reduced-motion support.
* RTL support and a wpml-config.xml for WPML and Polylang.

= Optional: the WPEssential plugin =
The free WPEssential plugin (on WordPress.org) adds elements for Gutenberg and Elementor, one-click demos, SEO fallbacks, a language switcher and a custom code manager. The theme works fully without it.

== Installation ==
1. In your admin, go to Appearance > Themes > Add New, search for Hi WPEssential and click Install, then Activate.
2. Optional: install the free WPEssential plugin from the notice on the dashboard, or from Plugins > Add New.

== Copyright ==
Hi WPEssential, (C) 2026 WPEssential Team. Distributed under the terms of the GNU GPL.

== Frequently Asked Questions ==

= Do I need the WPEssential plugin? =
No. The theme works on its own. The free WPEssential plugin adds its elements, one-click demo import and other site tools.

= Which screen sizes are supported? =
The layout steps at 1920, 1440, 1280, 1024, 768, 560, 480, 360 and 320 pixels. Each step sets the page gutter, section spacing, container width and heading sizes. From 1024 down the header switches to a compact bar with an off-canvas menu.

= How do I build an About, Services, Pricing, Contact or FAQ page? =
Create a page, choose the "Full width" template, then pick the matching layout from the "Hi WPEssential — Full pages" patterns. With WooCommerce active, the "Hi WPEssential — Shop" patterns add category and product rows.

= Can visitors switch between light and dark? =
Yes. The header has a light/dark toggle. The first visit follows the device setting, and the choice is stored in the visitor's browser only (no cookie, nothing sent to a server). Turn it off with add_filter( 'wpessential_scheme_toggle', '__return_false' ).

= Does the theme collect any data? =
No. The theme does not track visitors or send data anywhere. When "Google Fonts are served from this site" is chosen in Site Settings, the selected fonts are downloaded once from Google to your uploads folder when you save.

= Which languages are included? =
The theme ships translated into 20 languages. Translations from translate.wordpress.org, when available, take priority.

== Changelog ==

= 1.0.0 =
* Initial release.

== Resources ==

* Inter, (C) 2016 The Inter Project Authors, SIL Open Font License 1.1, https://github.com/rsms/inter
* Plus Jakarta Sans, (C) 2020 Tokotype, SIL Open Font License 1.1, https://github.com/tokotype/PlusJakartaSans
* Cormorant Garamond, (C) Christian Thalmann, SIL Open Font License 1.1, https://github.com/CatharsisFonts/Cormorant
* Manrope, (C) Mikhail Sharanda, SIL Open Font License 1.1, https://github.com/sharanda/manrope
* Space Grotesk, (C) Florian Karsten, SIL Open Font License 1.1, https://github.com/floriankarsten/space-grotesk
* DM Sans, (C) Colophon Foundry / Google, SIL Open Font License 1.1, https://github.com/googlefonts/dm-fonts
* Bricolage Grotesque, (C) 2022 The Bricolage Grotesque Project Authors, SIL Open Font License 1.1, https://github.com/ateliertriay/bricolage
* Pickr 1.9.1 (colour picker in Site Settings), (C) 2018-2021 Simon Reinisch, MIT License, https://github.com/Simonwep/pickr (source in assets/vendor/pickr/src)
* Sun and moon icons in the light/dark toggle: Font Awesome Free 6, (C) Fonticons, Inc., CC BY 4.0, https://fontawesome.com/license/free
* Images in assets/images (hero, post-1 to post-3, demo-business, team-1 to team-4) were generated with AI for this theme by the WPEssential Team and are released under GPLv2 or later.
