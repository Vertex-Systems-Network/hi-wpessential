<?php
/**
 * Title: Hero — Split with image
 * Slug: wpessential/hero-split
 * Categories: wpessential-hero, banner
 * Keywords: hero, banner, intro, image
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section"><!-- wp:columns {"verticalAlignment":"center","className":"wpe-gap-lg"} -->
<div class="wp-block-columns are-vertically-aligned-center wpe-gap-lg"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Multi-niche WordPress theme', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'Build any website in minutes', 'hi-wpessential' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text-muted","fontSize":"body-l"} -->
<p class="has-text-muted-color has-text-color has-body-l-font-size"><?php esc_html_e( 'Import a ready-made demo for Gutenberg or Elementor with one click, then make it yours.', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-wpe-large"} -->
<div class="wp-block-button is-style-wpe-large"><a class="wp-block-button__link wp-element-button" href="/demos/"><?php esc_html_e( 'Get started', 'hi-wpessential' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-wpe-secondary"} -->
<div class="wp-block-button is-style-wpe-secondary"><a class="wp-block-button__link wp-element-button" href="/about/"><?php esc_html_e( 'Learn more', 'hi-wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"is-style-wpe-shadow"} -->
<figure class="wp-block-image size-full is-style-wpe-shadow"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero.webp' ) ); ?>" alt="<?php esc_attr_e( 'A Hi WPEssential site on a laptop and phone', 'hi-wpessential' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
