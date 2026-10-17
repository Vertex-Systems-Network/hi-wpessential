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
