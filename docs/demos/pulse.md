# Pulse — Conference / events demo (Pro)

**Job of the site:** sell tickets for a 2-day tech & design conference and present speakers and agenda.
**Audience:** designers, developers, founders; mobile-first visitors from social links.

## Research (Oct 2026)

| Reference | Does well | We take | We avoid |
| --- | --- | --- | --- |
| Web Summit | Bold imagery, clear speaker/partner sections | Big speaker grid with company logos | Overloaded home |
| Google I/O | Minimal hero, filterable session schedule | Day tabs + track filter on agenda | Dev-only tone |
| TED | White space, bold type, one accent colour | Single electric accent on dark | — |
| III Points | Ticket columns expand to show inclusions | Ticket cards with expandable "What's included" | Festival-only styling |
| Awwwards Conference | Ticket-tier comparison table | Comparison table under ticket cards | — |
| Dreamforce / INBOUND | Video-led hero, animation | Kinetic type hero + countdown (dynamic motion preset) | Heavy autoplay video |
| LAUNCH Music Conference | Lineup + schedule + tickets in one flow | Sticky "Get tickets" CTA bar | — |

Sources: mycodelesswebsite.com/conference-websites, bizzabo.com/blog/beautiful-event-websites-design, weblium.com/blog/best-conference-websites, smartwebagency.co.uk 10 best event websites 2026, webnus.net event website examples.

## Sitemap
Home · Speakers · Speaker profile · Agenda (day tabs, tracks) · Session detail · Tickets (tiers + comparison) · Venue & travel · Sponsors · Become a sponsor · Blog list + article · FAQ · Contact · The Events Calendar screens (events list, month view, single event, organiser, venue) · Event Tickets / WooCommerce (ticket selector, cart, checkout with attendee info, order received with QR ticket, My tickets, My Account) · 404 · Search · Countdown bar, ticket drawer, speaker modal.

## Direction
- Palette (dark first): night #0A0A12 surface, #12121E alt, white text, #9A9AB0 muted, electric lime #C6F432 primary (ink text on it), magenta #FF3D8B accent, border #23233A. Light: #F7F7FB surface, ink #0A0A12 text, lime kept for buttons with ink text.
- Type: Space Grotesk (display) + DM Sans (UI).
- Radius: 24 px cards, full pills for buttons/tags; bordered tiles, giant numerals.
- Header: sticky floating pill nav, "Get tickets" lime CTA, countdown strip above.
- Hero: kinetic headline + speaker collage + date/location chips + countdown.
- Motion: `dynamic` (GSAP marquee of sponsors, counters, parallax collage).
- Plugins: The Events Calendar (required), Event Tickets (required), WooCommerce (required for paid tickets).

## Differentiation
vs Base: dark-first, neon lime instead of indigo, pill/tile language, sans-only display, countdown and marquee motion. vs Maison: opposite in every axis (cool/neon vs warm/neutral, rounded vs square, sans vs serif, dynamic vs editorial).

## Figma status

- **Pulse Demo:**
  - 25 pages at each of the 8 breakpoints (1920–320).
  - 6 overlays: speaker modal, ticket drawer, search, add to calendar, mobile menu, agenda filter sheet.
  - Overflow audit: 0 issues.
- **Pulse Demo Dark:** every section cloned with the Dark variable mode.
- **Pulse Demo UI Kit:** Light and Dark boards with tokens, type, buttons, pills, fields, speaker / session / ticket cards, navigation, QR and icons.

### Fixes after review

- **Mobile and tablet headers:** an empty nav frame was making the pill nav too tall; it is now collapsed.
- **Agenda and session speaker names:** they now wrap instead of being cut off.
- **Rows that clipped on phones** (hero meta, list rows, single event): set to wrap.
- **Month view at 768:** event pills use smaller text and padding, so no word breaks in the middle.
- **Desktop checkout (1920, 1440, 1024):** the sidebar now holds event details, what happens next, the refund guarantee and help, instead of an empty column.
- **Re-audit:** 0 clipped nodes, 0 overflowing text, no large empty gaps.

## Hover & motion (research)

Pulse uses the `dynamic` motion preset and `settings.custom.hover` in `styles/pulse.json`.

| Element | Behaviour | Why (source) |
| --- | --- | --- |
| Speaker cards | Grey at rest, full colour on hover, card lifts 6 px. | A common conference pattern for browsing many speakers ([lovable.dev](https://lovable.dev/templates/websites/events/summit-tech-conference-event-template)). |
| Ticket / pass cards | Lift with a 2 px lime ring and a soft shadow. | Ticket cards scale or lift on hover so the choice is clear ([huggingface.co example](https://huggingface.co/spaces/linsa11/techfusion-summit-2024/blob/main/style.css)). |
| Buttons | Lift 3 px with a lime glow, fast at 0.26 s. | Energetic, high-contrast brand. |
| Images / blog cards | Zoom to 1.06 inside the card. | Event and blog cards use an image zoom with a reveal ([framer.com](https://www.framer.com/community/marketplace/components/cms-event-card/)). |
| Scroll | Counters count up, the sponsor marquee scrolls, the hero has parallax and sections reveal (`dynamic` + GSAP). | Kinetic event sites. Motion respects reduced-motion. |

## Gap audit (Oct 2026)
Compared with top Envato event demos (Exhibz, Eventchamp, Meetup) and conferences such as Config, Smashing Conf and Web Summit, Pulse added:
- **About** — story, numbers, edition history and values.
- **Gallery** — last year's photos in a bento grid.
- **Call for speakers** — process, topics and a WPForms proposal form.
- **Code of conduct** — required by most real conferences and linked from About and the footer.
