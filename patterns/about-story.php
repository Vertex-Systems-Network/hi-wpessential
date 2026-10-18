<?php
/**
 * Title: About — story with stats
 * Slug: wpessential/about-story
 * Categories: wpessential-sections
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section"><!-- wp:columns {"verticalAlignment":"center","className":"wpe-gap-lg"} -->
<div class="wp-block-columns are-vertically-aligned-center wpe-gap-lg"><!-- wp:column {"verticalAlignment":"center","width":"46%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:46%"><!-- wp:image {"sizeSlug":"full","className":"bx-rounded"} -->
<figure class="wp-block-image size-full bx-rounded"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/post-1.webp' ) ); ?>" alt="<?php esc_attr_e( 'A designer building a site at her desk', 'hi-wpessential' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Our story', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Small team, big standards', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text-muted"} -->
<p class="has-text-muted-color has-text-color"><?php esc_html_e( 'We started as an agency rebuilding the same sites over and over. Hi WPEssential replaces that repetition with demos you can trust: accessible markup, real performance budgets and no lock-in. Your content stays yours, whichever builder you choose.', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bx-inline-stats"} -->
<p class="bx-inline-stats"><span><b>12 yrs</b><?php esc_html_e( 'Building for WordPress', 'hi-wpessential' ); ?></span><span><b>18k+</b><?php esc_html_e( 'Active installs', 'hi-wpessential' ); ?></span><span><b>24h</b><?php esc_html_e( 'Support response', 'hi-wpessential' ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
