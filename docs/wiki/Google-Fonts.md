# Google Fonts

All 1,771 Google Fonts families can be used. The list ships with the theme in `assets/data/google-fonts.json`, and `python3 tools/fonts/google.py <metadata.json>` rebuilds it from `https://fonts.google.com/metadata/fonts`.

## Where you pick them

- **Site Settings → Typography:** body, heading and code font.
- **Site Settings → Typography → More fonts for the editors:** extra Google families that then appear in every block's typography panel, in the kit elements and in Elementor.
- **Site Settings → Global typography:** the family of each typography set.

## How they are served

| Setting | What happens |
| --- | --- |
| **From this site** (default) | On save, the chosen fonts are downloaded to `wp-content/uploads/wpessential-fonts/<font>/` (latin and latin-ext files) and served from the site. Visitors never contact Google, which is better for privacy and GDPR. |
| **From Google servers** | The fonts load from `fonts.googleapis.com`. |

If a download fails, that font falls back to Google's servers. The downloaded files are recorded in the `wpessential_fonts_local` option.

## Theme fonts

Inter, Plus Jakarta Sans, DM Sans, Manrope, Space Grotesk, Bricolage Grotesque and Cormorant Garamond ship with the theme in `assets/fonts/`.
