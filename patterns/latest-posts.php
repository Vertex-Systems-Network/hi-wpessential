<?php
/**
 * Title: Latest posts — 3 cards
 * Slug: wpessential/latest-posts
 * Categories: wpessential-sections, query
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","align":"full","backgroundColor":"surface-alt","className":"wpe-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section has-surface-alt-background-color has-background"><!-- wp:group {"className":"wpe-center","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group wpe-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'From the blog', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Guides for faster, better WordPress sites', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","sticky":"exclude","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"280px"}} -->
<!-- wp:pattern {"slug":"wpessential/post-card"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->
</section>
<!-- /wp:group -->
