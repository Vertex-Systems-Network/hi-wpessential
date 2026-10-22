# Site Settings

**WPEssential → Site Settings** (in the main admin menu) opens the Customizer at the WPEssential Site Settings panel. Without the Core plugin the link is under Appearance. It is like Elementor's Site Settings, with more options.

## How the values are used

- Every value becomes part of theme.json (the user layer, above the demo's style variation). The front end, the block editor and Elementor (its kit colours and fonts are synced on save) all use the same value.
- An empty value keeps the demo's default.
- Each value can be reset with one click.
- WordPress sections that never change stay as they are: Site Identity, Homepage Settings, Menus, Widgets and Additional CSS.
- The classic Colors, Header Image and Background Image sections are replaced by ours.
- Everything is in one panel: Site Identity and Homepage Settings come first, the WooCommerce sections (Store Notice, Product Catalog, Product Images, Checkout) and Additional CSS come last.
- Menus stays its own panel next to ours, because WordPress needs that for its menu screens.
- The widgets panel and the empty WooCommerce panel are removed, and the block-theme notice is hidden.

## Sections

| Section | Options |
| --- | --- |
| Global colours | 19 palette colours |
| Dark mode | Dark palette; visitor switch on/off; follow the device or not. Brand colours follow the light ones unless set. |
| Typography | Google Fonts delivery; body / heading / code font; more fonts for the editors; body size (responsive) and line height; heading weight, spacing, line height and case; phone and desktop sizes for Display and H1–H4; H5, H6, small text; paragraph spacing; heading and selection colours |
| Global typography | A typography set for body, H1–H6, buttons, menu and small text, each with a size per device |
| Site background | Background, and a separate background for dark mode |
| Buttons | Radius, padding (responsive dimensions), size (responsive), weight, case, letter spacing, hover lift; Normal / Hover colours, border and shadow |
| Form fields | Radius, height (responsive), padding (responsive), border, background, focus ring |
| Corners & images | Radius scale sm–xl, image hover zoom, rounded content images |
| Layout | Breakpoints you edit (any of the 9 theme breakpoints), content and wide width, section spacing (responsive), side space (responsive), space between blocks, header height (responsive), sticky header, back-to-top |
| Gradients | Brand gradient (or follow Primary → Secondary), night gradient, image fade |
| Links | Colour, hover colour, underline, thickness, distance, weight |
| Shadows | Colour and strength for every shadow, or a box shadow per size |
| Header & footer | Header background, text, line, glass blur, scroll shadow, menu size / weight / gap (responsive); footer background, text, headings and links |
| Accessibility | Focus ring colour and width, visible skip link, smallest text size |
| Hover & motion | Hover speed, easing, card lift, turn off animations |

The controls are described in [Controls](Controls).
