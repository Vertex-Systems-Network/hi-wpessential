<?php
/**
 * Title: Footer — Simple
 * Slug: wpessential/footer-simple
 * Categories: wpessential-footers
 * Block Types: core/template-part/footer
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"footer","className":"wpe-footer-simple wpe-section--sm","layout":{"type":"constrained","contentSize":"1200px"}} -->
<footer class="wp-block-group wpe-footer-simple wpe-section--sm"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:site-title /-->

<!-- wp:navigation {"overlayMenu":"never","fontSize":"small"} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'About', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/about/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Blog', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/blog/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Contact', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/contact/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Privacy', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Terms', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/terms/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Refunds', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Cookies', 'hi-wpessential' ); ?>","url":"<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"} /-->
<!-- /wp:navigation -->

<!-- wp:paragraph {"textColor":"text-muted","fontSize":"small"} -->
<p class="has-text-muted-color has-text-color has-small-font-size">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></footer>
<!-- /wp:group -->
