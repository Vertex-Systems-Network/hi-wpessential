# Kit options plan

What every WPEssential kit element offers on top of the element's own options. Built once in the
framework (`core/src/Kit.php`, `core/src/Kit/Groups.php`, `core/kit/common.css`), so every element
gets it. Status: ✅ built · ⏳ next · ⬜ later.

## 1. What Elementor gives every widget (our bar)

- **Advanced tab:** margin, padding, width (full/inline/custom), align self, order, position + offsets,
  z-index, CSS ID, CSS classes; motion effects (entrance animation, duration, delay; Pro: scrolling
  effects, mouse track, 3D tilt, sticky); transform (rotate, offset, scale, skew, flip; normal/hover;
  transition); background (classic, gradient, video, slideshow; normal/hover); border (type, width,
  colour, radius, box shadow; normal/hover); mask; responsive hide; Pro: attributes, custom CSS,
  display conditions.
- **Style groups:** typography, text shadow, text stroke, box shadow, border, background, CSS filters,
  image size, dimensions.
- **Site-wide:** Kit global colours and fonts, up to 7 breakpoints; Pro: dynamic tags, global widgets.

In Elementor our widgets keep Elementor's own Advanced tab and use its native group controls; in
Gutenberg the same options come from our "Layout & effects" panel and group panels.

## 2. What we add (not in Elementor)

| # | Option | Status |
| --- | --- | --- |
| 1 | 9 steps of breakpoints (1920+ … 320); Elementor has at most 7 devices | ✅ |
| 2 | Container queries (respond to the parent's width) | ✅ Parent inline-size container, child visibility thresholds, and allowlisted layout/style rules by parent size |
| 3 | Fluid typography (size at 360 and at 1440, scales between) | ✅ Gutenberg (Elementor: type `clamp()` in the size field) |
| 4 | Fluid spacing | ✅ Interpolated padding and gap (360–1440px viewport) |
| 5 | Aspect ratio, object-fit, object-position per breakpoint | ✅ Image |
| 6 | Logical spacing and mirrored icons for RTL | ✅ Responsive inline start/end margin + padding · directional icons mirror in RTL |
| 7 | Spacing and radius from theme tokens (sm/md/lg) | ✅ Padding and border-radius token presets |
| 8 | Dark-mode value for colours, background, border and shadow | ✅ |
| 9 | Dark-mode image | ✅ background image and Image element |
| 10 | Contrast warning in the editor (WCAG AA) | ✅ Advisory warning from rendered preview colours in Gutenberg and Elementor editors |
| 11 | Focus and pressed (active) states besides hover | ✅ colours |
| 12 | Transition duration, easing and delay | ✅ |
| 13 | Hover presets from the demo's hover tokens (lift, zoom, glow, shadow, dim others, tilt) | ✅ |
| 14 | Magnetic, tilt-follow, ripple, cursor and spotlight hovers | ✅ Five opt-in pointer effects; reduced-motion aware; script only when selected |
| 15 | Separate focus-ring style | ✅ |
| 16 | Scroll-driven entrance animations without JavaScript | ✅ (12 presets) |
| 17 | Staggered animation of children | ✅ Staggered first 12 direct children, configurable delay |
| 18 | Parallax per layer, on/off per breakpoint | ✅ Four responsive direct-child layers with 0-per-breakpoint disable, X/Y axes, reduced-motion safe |
| 19 | Sticky with offset and "stop at" per breakpoint | ✅ Sticky with responsive offset, parent boundary and named same-page stop element |
| 20 | Reduced-motion alternative (not only off) | ✅ Choose no movement or gentle fade for reduced motion |
| 21 | Animation trigger: scroll, hover, click, load | ✅ Scroll, hover/focus, click and load triggers with progressive JS |
| 22 | Gradient text and text clip | ✅ Heading accent and Text |
| 23 | Gradient border, animated gradient border | ✅ Theme-aware static and motion-safe animated conic gradient border |
| 24 | Glass (backdrop blur) | ✅ |
| 25 | Blend modes | ✅ Allowlisted CSS blend modes with opt-in classes |
| 26 | Gradient mask (fade edges) | ✅ Opt-in horizontal/vertical and individual edge gradient masks |
| 27 | Clip-path shapes (wave, slant, blob) | ✅ Allowlisted slant, wave and blob polygon clip shapes |
| 28 | Noise / grain texture | ✅ Embedded SVG turbulence noise, responsive opacity and print-safe overlay |
| 29 | Elevation tokens that follow the state | ✅ Theme-based rest, hover and focus state shadow tokens |
| 30 | Text balance/pretty wrap and line clamp | ✅ headings and Text (balance/pretty, line clamp with read more) |
| 31 | Semantic HTML tag on every element | ✅ Shared tag selector on every element; structural roots retag, interactive/media roots keep their native tag inside a semantic wrapper |
| 32 | Schema output (FAQPage, Product, Review, Event, HowTo, Breadcrumb) | ✅ Opt-in JSON-LD: FAQPage, Product, Review, Event, HowTo and BreadcrumbList |
| 33 | ARIA label, role, aria-hidden, aria-live | ✅ |
| 34 | LCP control for images (priority, lazy/eager) | ✅ Image |
| 35 | Decorative image (empty alt, hidden from screen readers) | ✅ Image |
| 36 | Show by login, role, WooCommerce cart/purchase (free; Elementor has this only in Pro) | ✅ Login and role rules; WooCommerce cart and purchased-product conditions (Woo installed) |
| 37 | Show by date and time | ✅ Timezone-aware scheduled start and end windows |
| 38 | Show by device, URL parameter, referrer | ✅ CSS viewport devices, server URL-path/query and referrer-host conditions |
| 39 | Hide when printing | ✅ |
| 40 | Style presets (save and apply an element style) | ✅ Browser-local named presets save Kit attributes and each editor's native style controls |
| 41 | Copy/paste a style between Gutenberg and Elementor | ✅ Shared clipboard maps matching typography, spacing, border, background, colour, shadow and text-effect groups; native Elementor control values remain available for same-builder fidelity |
| 42 | Analytics data attributes for click events | ✅ Optional event and label data attributes on every element root |
| 43 | content-visibility: auto; CSS/JS only where used | ✅ Opt-in content-visibility with configurable intrinsic size |
| 44 | Every text field translatable (WPML/Polylang) | ✅ Field-specific mappings for all Kit blocks and translatable repeater content |

## 3. Parity rule

Before an element is built, its Elementor widget in `banks/elements/elementor/` is checked option by
option: every Elementor option is in the kit element, then our extras on top. Shared groups for this:
typography, spacing, border, shadow, background, colours, text effects (text shadow normal/hover,
text stroke, blend mode) and CSS filters (blur, brightness, contrast, saturation, hue, greyscale and
opacity, normal and hover).

Order: daily-use first (1–4, 8–9, 11–13, 31–34), then motion and effects, then display rules and workflow.
