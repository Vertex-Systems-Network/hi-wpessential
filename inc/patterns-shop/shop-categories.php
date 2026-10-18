<?php
/**
 * Shop categories grid (WooCommerce only).
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section wpe-section--md","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section wpe-section--md"><!-- wp:group {"className":"wpe-center","layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group wpe-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Shop by category', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"h2"} -->
<h2 class="wp-block-heading has-h-2-font-size"><?php esc_html_e( 'Find what you love', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[product_categories number="6" columns="6" hide_empty="1" parent="0"]
<!-- /wp:shortcode --></section>
<!-- /wp:group -->
