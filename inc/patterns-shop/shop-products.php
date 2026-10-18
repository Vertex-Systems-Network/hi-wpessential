<?php
/**
 * Product row: featured, new arrivals and on-sale (WooCommerce only).
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section wpe-section--md","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section wpe-section--md"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Featured', 'wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"h2"} -->
<h2 class="wp-block-heading has-h-2-font-size"><?php esc_html_e( 'Customer favourites', 'wpessential' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-wpe-outline"} -->
<div class="wp-block-button is-style-wpe-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'View all products', 'wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[products limit="4" columns="4" visibility="featured"]
<!-- /wp:shortcode -->

<!-- wp:heading {"fontSize":"h3"} -->
<h2 class="wp-block-heading has-h-3-font-size"><?php esc_html_e( 'New arrivals', 'wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[products limit="4" columns="4" orderby="date" order="DESC"]
<!-- /wp:shortcode -->

<!-- wp:heading {"fontSize":"h3"} -->
<h2 class="wp-block-heading has-h-3-font-size"><?php esc_html_e( 'On sale now', 'wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[sale_products limit="4" columns="4"]
<!-- /wp:shortcode --></section>
<!-- /wp:group -->
