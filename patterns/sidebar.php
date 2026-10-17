<?php
/**
 * Title: Blog sidebar
 * Slug: wpessential/sidebar
 * Categories: wpessential-sections
 * Inserter: no
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"className":"wpe-sidebar","layout":{"type":"default"}} -->
<div class="wp-block-group wpe-sidebar"><!-- wp:group {"className":"is-style-wpe-card-flat","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-wpe-card-flat"><!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'wpessential' ); ?>","showLabel":false,"buttonText":"<?php echo esc_attr__( 'Search', 'wpessential' ); ?>"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-wpe-card-flat","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-wpe-card-flat"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php esc_html_e( 'Categories', 'wpessential' ); ?></h4>
<!-- /wp:heading -->

<!-- wp:categories {"showPostCounts":true} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-wpe-card-flat","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-wpe-card-flat"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php esc_html_e( 'Recent posts', 'wpessential' ); ?></h4>
<!-- /wp:heading -->

<!-- wp:latest-posts {"postsToShow":4,"displayPostDate":true} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-wpe-card-flat","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-wpe-card-flat"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php esc_html_e( 'Tags', 'wpessential' ); ?></h4>
<!-- /wp:heading -->

<!-- wp:tag-cloud {"className":"is-style-outline"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
