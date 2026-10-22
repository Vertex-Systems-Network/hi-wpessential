# Page settings

Every page and post has its own settings:

- **Gutenberg:** the WPEssential panels in the Page sidebar.
- **Elementor:** the "WPEssential: …" sections in the page settings.

| Panel | Options |
| --- | --- |
| Layout | Hide title, hide featured image, content width (narrow / wide / full / custom), space above and below |
| Header & footer | Hide, or pick any header / footer variation; sticky header on/off |
| Colours & background | Lock light or dark mode, brand colour, background colour / gradient / image, dark-mode background |
| Advanced | Body classes, custom CSS (`selector` = this page) |

The definition is `WPEssential\Core\PageSettings::fields()` in the Core plugin. The values are stored in the `_wpessential_page` post meta, or in Elementor's page settings for Elementor pages.
