<?php
/**
 * Title: Split — image + checklist (dark)
 * Slug: wpessential/builders-split
 * Categories: wpessential-sections
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","align":"full","backgroundColor":"ink","className":"wpe-section is-style-wpe-section-dark","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section is-style-wpe-section-dark has-ink-background-color has-background"><!-- wp:columns {"verticalAlignment":"center","className":"wpe-gap-lg"} -->
<div class="wp-block-columns are-vertically-aligned-center wpe-gap-lg"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Your builder, your choice', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Same design. Gutenberg or Elementor.', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"body-l"} -->
<p class="has-body-l-font-size"><?php esc_html_e( 'Colors, fonts and spacing come from one theme.json and sync into Elementor’s global kit automatically.', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-wpe-checklist"} -->
<ul class="wp-block-list is-style-wpe-checklist"><!-- wp:list-item -->
<li><?php esc_html_e( 'Shared design tokens across both builders', 'hi-wpessential' ); ?></li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li><?php esc_html_e( 'Elementor Pro Theme Builder locations supported', 'hi-wpessential' ); ?></li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li><?php esc_html_e( 'Full Site Editing templates and patterns', 'hi-wpessential' ); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/post-1.webp' ) ); ?>" alt="<?php esc_html_e( 'Designer building a site', 'hi-wpessential' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
