<?php
/**
 * Title: 404 content
 * Slug: wpessential/content-404
 * Categories: wpessential-pages
 * Inserter: no
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","className":"wpe-section wpe-center","layout":{"type":"constrained","contentSize":"640px"}} -->
<section class="wp-block-group wpe-section wpe-center"><!-- wp:paragraph {"className":"wpe-404-num"} -->
<p class="wpe-404-num" aria-hidden="true">404</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'This page took a day off', 'hi-wpessential' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-wpe-lead"} -->
<p class="is-style-wpe-lead"><?php esc_html_e( 'The link may be broken or the page may have moved. Try a search or head back home.', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'hi-wpessential' ); ?>","showLabel":false,"buttonText":"<?php echo esc_attr__( 'Search', 'hi-wpessential' ); ?>"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'hi-wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
