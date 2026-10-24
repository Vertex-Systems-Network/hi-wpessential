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

| Gallery | Reorderable images, media-library responsive image output, alt and caption per image, grid or masonry columns, responsive layout, custom links and keyboard-enabled native lightbox with non-JavaScript fallback |

| Container | Responsive flex or CSS grid with gap, wrapping, width, alignment and style controls. Gutenberg: real draggable/reorderable InnerBlocks, including nested containers. Elementor: WYSIWYG content wrapper; for draggable nested Elementor widgets use Elementor's native Container. |

| Video & Audio | HTML5 video/audio files with native controls, loop/mute/preload, optional poster and WebVTT captions; YouTube/Vimeo validated embeds only load after explicit Play; non-JS source links remain available; responsive player sizing and shared Gutenberg/Elementor renderer |

| Progress bar | Multiple progress values with individual max goals, horizontal bars or circular rings, percentages/values/fractions, optional descriptions, screen-reader progress semantics, viewport animation and reduced-motion fallback |

| Code / HTML / Embed | Safe rich HTML (scripts stripped), escaped source-code samples with accessible Copy action, trusted WordPress provider oEmbed and fallback external link; shared Gutenberg and Elementor implementation |

| Data Table | Safe CSV/TSV data import, proper quoted-field parsing, accessible table headings and captions, client-side search and numeric-aware sorting, horizontal scroll or stacked mobile rows and responsive styling |

| Interactive Tooltip | Focus/hover/touch tooltip, native show-more disclosure, two-panel switcher with keyboard/RTL arrow navigation; safe rich content, reduced-motion, and readable no-JavaScript fallbacks |

| Countdown | WordPress site-timezone target date, 4-unit ticking timer, expired state, accessible completion message, and usable date fallback without JavaScript. |
| SVG/Icon Animation | Safe built-in SVG preset paths, decorative/meaningful semantics, viewport-triggered draw/pulse/spin and reduced-motion support. |
| Team Member | Profile image via WordPress media, name/role/bio, profile/email and validated social links, horizontal/stacked responsive cards. |
| Site Logo / Title | Theme custom logo, site title/tagline, branding overrides, linked home destination and responsive image sizing. |
| Divider / Spacer | Semantic horizontal rule, labelled or decorative separator, and non-semantic responsive vertical spacer. |

## What every element offers

- 9 breakpoints
- units on every size
- custom CSS
- transform and entrance animation
- ARIA options
- custom attributes
- hiding per breakpoint

**Turning elements off:** WPEssential → Elements switches each element on or off; all are on by default. An element that is off is not loaded in either builder.
