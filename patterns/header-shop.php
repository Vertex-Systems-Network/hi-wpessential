<?php
/**
 * Title: Header — Shop (search + account + cart)
 * Slug: wpessential/header-shop
 * Categories: wpessential-headers
 * Block Types: core/template-part/header
 *
 * @package WPEssential
 */

?>
<!-- wp:pattern {"slug":"wpessential/header-topbar"} /-->

<!-- wp:group {"tagName":"header","className":"wpe-header","layout":{"type":"constrained","contentSize":"1200px"}} -->
<header class="wp-block-group wpe-header"><!-- wp:group {"className":"wpe-header__inner","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group wpe-header__inner"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:site-logo {"width":40,"shouldSyncIcon":true} /-->

<!-- wp:site-title /--></div>
<!-- /wp:group -->

<!-- wp:search {"label":"<?php esc_attr_e( 'Search products', 'wpessential' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search products, brands and more…', 'wpessential' ); ?>","width":420,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wpessential' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"product"},"className":"wpe-header-search"} /-->

<!-- wp:pattern {"slug":"wpessential/header-actions"} /--></div>
<!-- /wp:group -->

<!-- wp:navigation {"overlayMenu":"mobile","className":"wpe-hide-mobile","layout":{"type":"flex","justifyContent":"left"}} /--></header>
<!-- /wp:group -->
