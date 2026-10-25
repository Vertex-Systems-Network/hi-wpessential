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

| Image Carousel | Media-library image slides, keyboard-accessible horizontal scroll, arrows and dots, optional autoplay with pause on hover/focus and reduced-motion respect |
| Modal / Popup | Native dialog for explicit click-to-open overlays, Escape/backdrop/close behavior, focus restoration, sanitized rich text and no-JS native disclosure fallback |
| Pricing Table | Responsive single-plan card with features, included/excluded states, featured badge, price labels and safe call-to-action URLs |

| Map | Validated map coordinates, server-rendered OpenStreetMap location link, optional consent-first iframe, responsive map frame, no third-party requests before click |
| WooCommerce Products | Published product cards from real WooCommerce queries with category/featured/stock filters, prices, sale badges, responsive grid, safe product links, missing-plugin fallback |
| Social Icons | Repeatable platform links with hardcoded SVG presets, HTTPS-only profile URLs, accessible labels, optional text and responsive shapes |

| Social Feed | Curated social posts or dynamically queried published WordPress posts, media-library images, safe links, responsive columns and no-JS visible cards with optional progressive load more. No third-party API sync. |
| Call to Action | Responsive banner, optional attachment image, safe rich message, semantic heading, validated primary/secondary button links and Elementor/Gutenberg style controls |
| Calendar / Events | Strict timezone-aware manual events, sorted upcoming list or accessible month calendar, all-day/timed entries, location and safe detail links |
| Search | Native GET search for published site content, optional post/page/WooCommerce product scope, inline or no-JS expandable form with accessible controls |

| Comments | Approved public WordPress comments, author avatar/date/count, safe comment links and optional native WordPress comment submission form, no custom comment endpoint |
| Timeline / Steps | Semantic milestones with images, dates, safe rich descriptions/links, vertical or horizontally scrollable layouts |
| Rating | Accessible display-only rating, 5/10-point scales, fractional rating symbols and numeric score with screen-reader value |
| Alert / Notice | Accessible success/info/warning/error notices with sanitized rich HTML, progressive dismissal and optional session-only persistence |
| Business Hours | Seven-day schedule, validated HH:MM clock format, closed days, optional highlight of current weekday in site's timezone |
| Image Comparison | Before/after images with alt text, native keyboard-accessible range control and visible static split without JS |

| Price List / Restaurant Menu | Repeatable menu and service prices, old prices, badges and images; responsive list/cards, safe optional links, semantic list, no JS required. |
| Navigation / Mega Menu | Native WordPress menus, focus/click/keyboard submenu toggles, two-column dropdowns and **published Gutenberg synced-pattern or Elementor saved-template mega panels** mapped by menu item ID. Mobile submenus open independently and retain no-JS fallback; unpublished/non-template content is rejected. |
| Site / Archive Widgets | Context-aware archive title/description, public post meta, breadcrumbs, author and pagination. |
| WPForms Lite Form | Embed existing published WPForms forms with their validation and antispam; no duplicate form handler. |
| Standalone Icon | Accessible icons or optional links, styles, shape and responsive controls. |
| Chart | Accessible numeric bar, column and donut visualizations with screen-reader table and no dependency. |
| Flip / Hover Box | Native keyboard/touch disclosure with front/back copy and CSS reduced-motion flip. |

## RTL-aware logical inline spacing

Every Kit widget/block now offers responsive **Margin inline start/end** and **Padding inline start/end** in its Advanced panel. Unlike physical left/right spacing, inline start/end automatically swap with the inherited document direction; no separate Arabic/Urdu configuration is necessary. Negative values are supported for margins, while padding controls start at 0. The fields are opt-in and preserve existing saved layouts. Mirroring decorative directional icons remains a separate roadmap task.

## New shared advanced presentation controls

All Kit widgets and blocks now support opt-in **keyboard focus outline** (style, theme-palette colour, responsive width and offset), **glass surfaces** (light/dark palette-aware translucency with backdrop blur and opacity), and **Hide when printing**. Existing widgets do not change unless a control is enabled. Glass uses a solid surface fallback where colour mixing is unsupported. Focus outline choices apply to the element root and its interactive children and take priority over ordinary widget link/field outlines.

## What every element offers

- 9 breakpoints
- units on every size
- custom CSS
- transform and entrance animation
- ARIA options
- custom attributes
- hiding per breakpoint

**Turning elements off:** WPEssential → Elements switches each element on or off; all are on by default. An element that is off is not loaded in either builder.

### Advanced Navigation / Mega panels

In Appearance → Menus, find the WordPress menu item's ID. Add a row in the Navigation widget's **Saved mega panels by menu item ID** repeater, set the menu item ID, choose **Gutenberg synced pattern (wp_block)** or **Elementor saved template**, then enter the published template post ID. Parent links still work, ordinary child menu links stay available, and the template appears alongside them. Only published template posts are rendered. Use the desktop trigger and mega width controls to customize interaction and layout; a separate toggle controls each mobile submenu. JavaScript progressively enhances the links (keyboard Escape closes, aria-expanded updates), with the original submenus readable without JavaScript. Nested templates that recursively embed the current mega menu are suppressed.

Full drag-and-drop menu builder, dynamic per-item badges/media, and complete benchmark parity remain separate phases.
