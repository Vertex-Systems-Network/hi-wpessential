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
<section class="wp-block-group wpe-section wpe-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Error 404', 'wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'This page took a day off', 'wpessential' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-wpe-lead"} -->
<p class="is-style-wpe-lead"><?php esc_html_e( 'The link may be broken or the page may have moved. Try a search or head back home.', 'wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'wpessential' ); ?>","showLabel":false,"buttonText":"<?php echo esc_attr__( 'Search', 'wpessential' ); ?>"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
