# Theme and the WPEssential plugin

The theme's former `core/` module (elements kit, demo importer, licence, account, AI connect) moved
into the WPEssential plugins on 2026-11-15:

- **WPEssential (Free)** — kit engine and basic elements, one-click demo importer, licence and account.
- **WPEssential Pro** — the advanced kit elements.

The theme keeps design only (templates, parts, patterns, styles, `theme.json`, Site Settings in the
Customizer, page settings) and exposes what the plugin reads in `inc/core-bridge.php`. It registers its
offline Base demo (`bundled-demos/base`) with the plugin's importer through the
`wpessential_core_demo_dirs` filter, and shows an admin notice recommending the plugin when it is missing.
