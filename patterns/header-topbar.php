<?php
/**
 * Title: Top bar
 * Slug: wpessential/header-topbar
 * Categories: wpessential-headers
 *
 * @package WPEssential
 */
?>
<!-- wp:group {"className":"wpe-topbar","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group wpe-topbar"><!-- wp:group {"className":"wpe-topbar__inner","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group wpe-topbar__inner"><!-- wp:paragraph -->
<p><?php esc_html_e( 'Free shipping on orders over $75 · 30-day returns', 'wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"wpe-topbar__links"} -->
<p class="wpe-topbar__links"><a href="/track-order/"><?php esc_html_e( 'Track order', 'wpessential' ); ?></a>   ·   <a href="/faq/"><?php esc_html_e( 'Help', 'wpessential' ); ?></a>   ·   <a href="/contact/"><?php esc_html_e( 'Contact', 'wpessential' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
