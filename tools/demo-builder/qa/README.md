# Demo QA helpers

These are used by the `/build-demo` skill and the playbook checklist. They need Node and Playwright
(Chromium in `/opt/pw-browsers`) and a local WordPress that has the theme and Core active.

| Script | What it does |
| --- | --- |
| `php import.php <wp-path> <demo> <gutenberg\|elementor>` | Runs the real importer for a demo and prints the log. Any `error` line fails the check. |
| `node shot.cjs <url> <out.png> <width>` | Takes a full-page screenshot after scrolling, so motion reveals run. It also prints `scrollWidth`, which must equal the width. |
| `WP_URL=… node validate-blocks.cjs <post ids…>` | Opens each page in the block editor and prints the invalid blocks, which must be 0. It logs in as admin/admin123 by default. |
| `node hover.cjs <url> "<selector>" <out-prefix>` | Takes a before/after hover screenshot of an element and prints its computed transform. |
