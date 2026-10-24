# Kit elements

WPEssential elements are defined once and work as both a Gutenberg block and an Elementor widget, with the same options. Each element is a folder `core/kit/<name>/` with three files:

- `element.json`
- `render.php`
- `style.css`

An optional `script.js` is loaded only on pages that use the element, in both builders.

| Element | Highlights |
| --- | --- |
| Heading | Eyebrow, accent words (gradient, outline, highlight), fluid size, text shadow / stroke / blend |
| Text | Drop cap (default / stacked / framed), columns, line clamp with read more, gradient text |
| Button | Icon library and SVG upload, variants, sizes, hover effects, every state |
| Image | Responsive srcset, dark-mode image, lightbox, aspect ratio / fit per breakpoint, CSS filters |
| Icon list | Repeater items, icons or numbers, inline / grid, dividers, icon shapes |
| Info box | Icon, image or number; top / left / right per breakpoint; badge; button; link on box, title or button; stacked / framed icons; box and media hover effects; equal height |
| Accordion | Native details/summary (no JavaScript), one or many open, FAQ schema, numbers, own icon per item, open icon, style presets, open-item colours |
| Tabs | ARIA tabs with arrow keys / Home / End, top / bottom / left / right per breakpoint, icons and badges, underline / pill / box indicators, deep links (#name), autoplay that pauses on hover, scroll / stack / accordion on small screens, all panels shown without JavaScript |
| Posts grid | Published posts and public custom post types; taxonomy term filtering, configurable cards, responsive columns, featured images, pagination and empty state. Shared Gutenberg/Elementor renderer with automated WordPress smoke tests. |

| Testimonial | Accessible quote and rating, avatar, linked author name, role / company, responsive alignment, styles and optional hover effects. Gutenberg + Elementor share the same renderer. |

| Counter | Numeric milestones and statistics; custom start/end values, decimals, separators, prefix/suffix, optional icon, responsive styling, viewport count-up animation with reduced-motion fallback and accessible static final value |

## What every element offers

- 9 breakpoints
- units on every size
- custom CSS
- transform and entrance animation
- ARIA options
- custom attributes
- hiding per breakpoint

**Turning elements off:** WPEssential → Elements switches each element on or off; all are on by default. An element that is off is not loaded in either builder.
