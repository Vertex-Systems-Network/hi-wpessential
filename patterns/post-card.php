<?php
/**
 * Title: Post card
 * Slug: wpessential/post-card
 * Inserter: no
 *
 * Used inside every post grid (query loop) so all archives share one card.
 *
 * @package WPEssential
 */
?>
<!-- wp:group {"className":"wpe-card","layout":{"type":"default"}} -->
<div class="wp-block-group wpe-card"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10","sizeSlug":"wpessential-card"} /-->

<!-- wp:group {"className":"wpe-card__body","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group wpe-card__body"><!-- wp:post-terms {"term":"category","className":"is-style-wpe-overline"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"h4"} /-->

<!-- wp:post-excerpt {"excerptLength":18} /-->

<!-- wp:group {"className":"wpe-post-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group wpe-post-meta"><!-- wp:post-author-name /-->

<!-- wp:post-date /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
