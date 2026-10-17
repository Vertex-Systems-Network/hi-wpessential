# WPEssential Demo Playbook

The record every person and every AI agent follows when creating a WPEssential demo, in Figma and in code.
Read it fully before starting a demo. Update it when a rule changes; never work around it silently.

## 1. Non-negotiables

1. **No placeholders.** No lorem ipsum, grey boxes, "Product name", fake keys, empty images, `example.com`, broken links. Every image is real (Figma AI generated), every text is real copy for the niche.
2. **Every demo is different.** Even when two demos share a niche they must differ in: palette, type pairing, header layout, hero composition, card style, corner radius, motion preset and photography direction. Check §7 before choosing.
3. **Research first.** Before designing, study at least 8 live market leaders for the niche (ThemeForest top sellers, Awwwards, real brands). Write the findings into the demo brief (§3). No research → no design.
4. **Light and dark.** Every demo ships a light and a dark version that share the same tokens.
5. **All breakpoints.** 1920, 1440, 1024, 768, 560, 480, 360, 320 + device mockups, exactly like the Base Demo.
6. **Every plugin screen.** If the demo uses a plugin (WooCommerce, The Events Calendar, AffiliateWP, LearnDash, bbPress, Contact Form…), design *all* of that plugin's front-end screens and states (lists, single, cart, checkout, account, empty, error, success).
7. **Two builders, one design.** Gutenberg uses theme blocks/patterns. Elementor uses **native Elementor widgets and Global Kit settings only** — no custom CSS, no custom code, no HTML widgets for layout.
8. **Never crop heads.** Any image with a person must show the full head (hair included) with breathing room above it — in the generated image, in every Figma frame/crop at every breakpoint, and in theme `object-position`. Prompts for people always say "full head visible, not cropped". Product shots may hide the face only by turning the model away or framing from a distance, never by cutting through the head. Check every image after generation and regenerate if a head is cut.
9. **Sharp at the largest breakpoint (1920 px).** Every image must look crisp in the biggest place it is used at 1920 px, including retina. Generate at Figma AI's maximum (`gpt-image-2.5-sunburst`, 1536 × 1024), then upscale 2× with an AI upscaler to 3072 × 2048 before exporting to the theme as WebP. Never stretch a small image in a layout; regenerate it instead.
10. **One size for every image.** All images are generated at the same size and ratio: 1536 × 1024 (3:2). Compose with the subject centred and generous margins on every side, so the same file crops to 16:9 heroes, 3:4 product cards, 1:1 tiles or 4:5 mobile without losing the subject or the head.
11. **Accessibility.** WCAG 2.2 AA contrast in both schemes, focus states visible, motion respects `prefers-reduced-motion`.

## 2. Figma structure (file `5AqHWOUl6bcITWWj4PPoAF`)

All demos live in the same file as the Base Demo. Pages per demo, in this order:

| Page | Content |
| --- | --- |
| `[Name] Demo` | Light. Sections: Spec, Pages (desktop), plugin screens, overlays, mobile screens, 1920…320 rows, Devices |
| `[Name] Demo Dark` | Same frames in the dark variable mode |
| `[Name] Demo UI Kit` | Foundations (colour, type, spacing, radius, shadow), breakpoint options, components with light/dark variants, icons, motion spec |

Images go to the shared **Image Library** page in a section named after the demo; frame names are `[Demo] / [subject]` and the same names are used as file names in the theme package.

Colours are bound to the **Color** variable collection; each demo adds its own mode set (`[Name] Light`, `[Name] Dark`) rather than raw hex values.

## 3. Demo brief (write before designing)

Add `docs/demos/<id>.md` with:

- Niche, audience, the job of the site (sell / book / sign up / inform).
- Research table: 8+ references, what each does well, what we take, what we avoid.
- Sitemap: every page and plugin screen.
- Design direction: palette (light + dark), fonts, radius, header type, hero type, card type, photo direction, motion preset.
- Plugins with their source (wporg / zip / licensed) and state (required / recommended / conflicts).
- Differentiation check against every existing demo (§7).

## 4. Code structure

```
core/demos/<id>/
  demo.json                 catalogue entry (title, niche, type free|pro, thumbnail, preview, pages, highlights, builders)
  thumbnail.webp            880×605 from the real imported home
  preview/desktop|tablet|mobile.webp   full-page screenshots of the real import
  gutenberg/content.json    pages, posts, media, terms, products, menus, settings (see core/README.md)
  gutenberg/media/*.webp    images exported from the Image Library (same names)
  elementor/content.json    same content, Elementor data in post meta (_elementor_data), kit settings in settings.elementor_kit
styles/<id>.json            theme.json style variation (full palette, fonts, radius) + its dark twin styles/<id>-dark.json
patterns/<id>-*.php         demo-specific patterns (Gutenberg)
```

Pro demos are hosted by the WPEssential Server plugin (`package: "licensed"`), free demos may be bundled.

## 5. Motion presets

Set per demo through `settings.motion` in content.json (written to the `wpessential_motion` option):

| Preset | Feel | Use for |
| --- | --- | --- |
| `subtle` | 400 ms fade-up, small distance | Corporate, SaaS, finance, medical |
| `editorial` | Slow masks, image reveals, line-by-line headings | Fashion, magazine, portfolio, luxury |
| `playful` | Spring scale, stagger, slight rotation | Kids, food, events, creative agency |
| `dynamic` | Parallax, counters, horizontal scroll sections (GSAP ScrollTrigger) | Events, sport, tech launch |
| `none` | No motion | Accessibility-first sites |

Elements opt in with the block class `wpe-anim` plus `wpe-anim--fade-up|fade|zoom|slide-left|slide-right|mask|stagger|parallax|counter`.

## 6. Checklist before a demo is "done"

- [ ] Brief written with research
- [ ] Light, Dark and UI Kit pages complete, every breakpoint, every plugin screen
- [ ] All images in Image Library, AI generated, named `[Demo] / …`
- [ ] No person image has a cropped head (source image and every crop/breakpoint)
- [ ] Every image generated at 1536 × 1024, same size for all, upscaled 2× for the theme
- [ ] No pixelation at 1920 px or on retina crops
- [ ] Zero placeholder text (search the file for lorem, placeholder, xxx, example)
- [ ] Gutenberg package imports with 0 errors on a clean site and on a site with content
- [ ] Elementor package uses only native widgets + Kit; import tested
- [ ] Previews + thumbnail regenerated from the real import
- [ ] No horizontal scroll at any breakpoint, light and dark
- [ ] Differentiation table (§7) updated

## 7. Demo register (keep up to date)

| Demo | Niche | Palette | Fonts | Header | Hero | Cards | Radius | Motion | Plugins |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Base | Business / multi | Indigo #4F46E5 + violet, ink #0B1020 | Plus Jakarta Sans / Inter | Transparent, centred nav, CTA | Dark gradient, centred headline + browser mockup | Soft shadow, image top | 14–20 px | subtle | WooCommerce |
| Maison | Fashion store | Warm ivory #F6F1EA, espresso #2B211B, clay #B5643C | Cormorant Garamond / Manrope | Centred logo, split nav, utility bar | Full-bleed editorial photo, oversized serif | Borderless, tall 3:4 image, hover second image | 0 px | editorial | WooCommerce |
| Pulse | Conference / events | Electric lime #C6F432 on night #0A0A12, magenta accent | Space Grotesk / DM Sans | Sticky pill nav, ticket CTA, countdown bar | Kinetic type + speaker collage + countdown | Bordered tiles, big numbers | 24 px pills | dynamic | The Events Calendar, Event Tickets, WooCommerce |
