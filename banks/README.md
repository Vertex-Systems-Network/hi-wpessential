# Elements bank and options bank

The research base for the **WPEssential kit**: our own elements (one design, a Gutenberg block and an
Elementor widget each, with our 8 breakpoints built in) that every demo is built from. Every kit
element must offer at least what the richest element of its type offers here, and do it better.

All files are generated from the plugins' own source code, so they are complete and can be
refreshed when a plugin updates.

| File | What it holds |
| --- | --- |
| `elements/<source>/<name>.json` | One element: title, categories, keywords and every option. Elementor widgets list their sections (Content / Style / Advanced) with each control's name, label, type, options, whether it is responsive and whether it writes CSS. Gutenberg blocks list their attributes (type, default, allowed values) and block supports. |
| `options/index.json` | Every option kind and the elements that have it, e.g. `group:Group_Control_Typography`, `control:SLIDER`, `feature:responsive`, `supports:spacing`. |
| [CATALOG.md](CATALOG.md) / `catalog.json` | Elements grouped by type (Accordion, Tabs, Pricing table…), with the richest version per builder. |
| [OPTIONS.md](OPTIONS.md) | Every option kind and how many elements offer it. |
| `summary.json` | Elements per source. |

## Sources (October 2026)

| Source | Builder | Elements |
| --- | --- | --- |
| Elementor | Elementor | 36 |
| Essential Addons Lite | Elementor | 64 |
| ElementsKit Lite | Elementor | 43 |
| Premium Addons | Elementor | 41 |
| Happy Addons | Elementor | 73 |
| Ultimate Addons (Header Footer Elementor) | Elementor | 15 |
| Gutenberg core | Gutenberg | 115 |
| Kadence Blocks | Gutenberg | 62 |
| Spectra | Gutenberg | 50 |
| GenerateBlocks | Gutenberg | 9 |
| **Total** | | **507** |

**Not in the bank yet** (no free source to read): Elementor Pro (the GPL copy on GitHub was not
reachable from the build session), JetElements, Ultimate Addons for Elementor Pro, Spectra Pro,
Kadence Pro. Add them by unzipping the plugin into the `--src` folder and adding its folder to
`ELEMENTOR` or `BLOCKS` in `tools/banks/extract.py`.

## Refresh

```sh
# unzip each plugin from wordpress.org into one folder, e.g. src/elementor, src/kadence-blocks …
python3 tools/banks/extract.py --src <that folder> --wp <a WordPress install>   # core blocks come from wp-includes/blocks
python3 tools/banks/catalog.py
```

## Kit specs

`python3 tools/banks/kit_spec.py` writes one spec per element type to [kit/](kit/README.md): the benchmark
elements (two richest per builder), every option they offer, and the baseline every kit element adds
(8 breakpoints, light/dark, hover presets, motion, accessibility, SEO, performance, Gutenberg + Elementor).
45 specs; 7 niche elements stay under "Other" (Weather, World Clock, How To, Retina, EasyJobs, Spectra AI, Unsupported).

## Next

1. Review the specs with the owner and pick the first batch (highest priority: Posts grid, Image, Button, Menu, Heading, Text, Forms, Icon list, Accordion, Testimonial, Info box, Counter, Gallery).
2. Design the kit in Figma (UI Kit, light and dark, every state) from the specs.
3. Build each element as a Gutenberg block and an Elementor widget in Core, then rebuild the demos from the kit.
