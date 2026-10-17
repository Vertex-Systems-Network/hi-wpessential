<?php
/**
 * Title: Header — Default
 * Slug: wpessential/header-default
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

<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"center"}} /-->

<!-- wp:pattern {"slug":"wpessential/header-actions"} /--></div>
<!-- /wp:group --></header>
<!-- /wp:group -->
