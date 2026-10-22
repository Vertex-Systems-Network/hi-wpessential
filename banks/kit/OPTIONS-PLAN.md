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
| 2 | Container queries (respond to the parent's width) | ⏳ |
| 3 | Fluid typography (size at 360 and at 1440, scales between) | ✅ Gutenberg (Elementor: type `clamp()` in the size field) |
| 4 | Fluid spacing | ⏳ |
| 5 | Aspect ratio, object-fit, object-position per breakpoint | ✅ Image |
| 6 | Logical spacing and mirrored icons for RTL | ⏳ |
| 7 | Spacing and radius from theme tokens (sm/md/lg) | ⏳ |
| 8 | Dark-mode value for colours, background, border and shadow | ✅ |
| 9 | Dark-mode image | ✅ background image and Image element |
| 10 | Contrast warning in the editor (WCAG AA) | ⬜ |
| 11 | Focus and pressed (active) states besides hover | ✅ colours |
| 12 | Transition duration, easing and delay | ✅ |
| 13 | Hover presets from the demo's hover tokens (lift, zoom, glow, shadow, dim others, tilt) | ✅ |
| 14 | Magnetic, tilt-follow, ripple, cursor and spotlight hovers | ⬜ |
| 15 | Separate focus-ring style | ⏳ |
| 16 | Scroll-driven entrance animations without JavaScript | ✅ (12 presets) |
| 17 | Staggered animation of children | ⏳ |
| 18 | Parallax per layer, on/off per breakpoint | ⬜ |
| 19 | Sticky with offset and "stop at" per breakpoint | ⏳ |
| 20 | Reduced-motion alternative (not only off) | ⬜ |
| 21 | Animation trigger: scroll, hover, click, load | ⬜ |
| 22 | Gradient text and text clip | ✅ Heading accent and Text |
| 23 | Gradient border, animated gradient border | ⏳ |
| 24 | Glass (backdrop blur) | ⏳ |
| 25 | Blend modes | ⏳ |
| 26 | Gradient mask (fade edges) | ⏳ |
| 27 | Clip-path shapes (wave, slant, blob) | ⬜ |
| 28 | Noise / grain texture | ⬜ |
| 29 | Elevation tokens that follow the state | ⏳ |
| 30 | Text balance/pretty wrap and line clamp | ✅ headings and Text (balance/pretty, line clamp with read more) |
| 31 | Semantic HTML tag on every element | ✅ Heading, Text, Image (figure when captioned) · ⏳ others |
| 32 | Schema output (FAQPage, Product, Review, Event, HowTo, Breadcrumb) | ⏳ with those elements |
| 33 | ARIA label, role, aria-hidden, aria-live | ✅ |
| 34 | LCP control for images (priority, lazy/eager) | ✅ Image |
| 35 | Decorative image (empty alt, hidden from screen readers) | ✅ Image |
| 36 | Show by login, role, WooCommerce cart/purchase (free; Elementor has this only in Pro) | ⬜ |
| 37 | Show by date and time | ⬜ |
| 38 | Show by device, URL parameter, referrer | ⬜ |
| 39 | Hide when printing | ⏳ |
| 40 | Style presets (save and apply an element style) | ⬜ |
| 41 | Copy/paste a style between Gutenberg and Elementor | ⬜ |
| 42 | Analytics data attributes for click events | ⏳ |
| 43 | content-visibility: auto; CSS/JS only where used | ✅ CSS per element · ⏳ content-visibility |
| 44 | Every text field translatable (WPML/Polylang) | ⏳ wpml-config for kit blocks |

Order: daily-use first (1–4, 8–9, 11–13, 31–34), then motion and effects, then display rules and workflow.
