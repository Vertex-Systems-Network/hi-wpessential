<?php
/**
 * Title: Blog hero
 * Slug: wpessential/blog-hero
 * Categories: wpessential-pages
 * Inserter: no
 *
 * @package WPEssential
 */

$wpessential_over  = (string) get_option( 'wpessential_blog_over', '' );
$wpessential_title = (string) get_option( 'wpessential_blog_title', '' );
$wpessential_lead  = (string) get_option( 'wpessential_blog_lead', '' );
?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section wpe-section--sm wpe-page-hero","backgroundColor":"surface-alt","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section wpe-section--sm wpe-page-hero has-surface-alt-background-color has-background"><?php echo '' !== $wpessential_over ? '<!-- wp:paragraph {"className":"is-style-wpe-overline"} --><p class="is-style-wpe-overline">' . esc_html( $wpessential_over ) . '</p><!-- /wp:paragraph -->' : ''; ?><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php echo esc_html( '' !== $wpessential_title ? $wpessential_title : __( 'Blog', 'hi-wpessential' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-wpe-lead"} -->
<p class="is-style-wpe-lead"><?php echo esc_html( '' !== $wpessential_lead ? $wpessential_lead : __( 'Guides, tutorials and product news from the Hi WPEssential team.', 'hi-wpessential' ) ); ?></p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->
