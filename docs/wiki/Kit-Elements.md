# Kit elements

WPEssential elements are defined once and work as both a Gutenberg block and an Elementor widget, with the same options. Each element is a folder `core/kit/<name>/` with three files:

- `element.json`
- `render.php`
- `style.css`

| Element | Highlights |
| --- | --- |
| Heading | Eyebrow, accent words (gradient, outline, highlight), fluid size, text shadow / stroke / blend |
| Text | Drop cap (default / stacked / framed), columns, line clamp with read more, gradient text |
| Button | Icon library and SVG upload, variants, sizes, hover effects, every state |
| Image | Responsive srcset, dark-mode image, lightbox, aspect ratio / fit per breakpoint, CSS filters |
| Icon list | Repeater items, icons or numbers, inline / grid, dividers, icon shapes |
| Info box | Icon, image or number; top / left / right per breakpoint; badge; button; link on box, title or button; stacked / framed icons; box and media hover effects; equal height |

## What every element offers

- 9 breakpoints
- units on every size
- custom CSS
- transform and entrance animation
- ARIA options
- custom attributes
- hiding per breakpoint

**Turning elements off:** WPEssential → Elements switches each element on or off; all are on by default. An element that is off is not loaded in either builder.
