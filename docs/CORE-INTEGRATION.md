# Theme and the WPEssential plugin

The theme's former `core/` module (elements kit, demo importer, licence, account, AI connect) moved
into the WPEssential plugins on 2026-11-15:

- **WPEssential (Free)** — kit engine and basic elements, one-click demo importer, licence and account.
- **WPEssential Pro** — the advanced kit elements.

The theme keeps design only (templates, parts, patterns, styles, `theme.json`, Site Settings in the
Customizer, page settings) and exposes what the plugin reads in `inc/core-bridge.php`. It registers its
offline Base demo (`bundled-demos/base`) with the plugin's importer through the
`wpessential_core_demo_dirs` filter, and shows an admin notice recommending the plugin when it is missing.

Since then:
- **2026-11-26 (ADR-0239):** the Site Kit moved from Pro to **WPEssential Free** as **Site Tools Kit**. It is the single place for global defaults: colours, fonts, units, breakpoints and custom code. The theme's Customizer Site Settings are planned to use the same option keys (plugin plan, WP8).
- **2026-11-26:** demo style variations moved out of `styles/` into each demo package (`tools/demo-builder/styles/`). Only `styles/dark.json` (the theme's own dark scheme) remains. On import, the plugin applies the style and copies its palette and fonts into Site Tools Kit.
- **2026-11-27:** the kit elements documentation moved to the plugin (`wpessential/docs/KIT-ELEMENTS.md`).

Plugin-side plans and rules: `wpessential/docs/IMPLEMENTATION/BLOCK-EDITOR-PARITY-AND-SITE-TOOLS-KIT-V1.md`.
