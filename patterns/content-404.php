<?php
/**
 * Title: 404 content
 * Slug: wpessential/content-404
 * Categories: wpessential-pages
 * Inserter: no
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"section","className":"wpe-section wpe-center","layout":{"type":"constrained","contentSize":"640px"}} -->
<section class="wp-block-group wpe-section wpe-center"><!-- wp:paragraph {"className":"wpe-404-num"} -->
<p class="wpe-404-num" aria-hidden="true">404</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size"><?php echo esc_html( '' !== (string) get_option( 'wpessential_404_title', '' ) ? (string) get_option( 'wpessential_404_title' ) : __( 'This page took a day off', 'hi-wpessential' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-wpe-lead"} -->
<p class="is-style-wpe-lead"><?php echo esc_html( '' !== (string) get_option( 'wpessential_404_lead', '' ) ? (string) get_option( 'wpessential_404_lead' ) : __( 'The link may be broken or the page may have moved. Try a search or head back home.', 'hi-wpessential' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'hi-wpessential' ); ?>","showLabel":false,"buttonText":"<?php echo esc_attr__( 'Search', 'hi-wpessential' ); ?>"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<?php
// Demo links ([label, page slug] rows in wpessential_404_links); "Back to home" when none are set.
$wpessential_links = (array) get_option( 'wpessential_404_links', array() );
if ( ! $wpessential_links ) {
	$wpessential_links = array( array( __( 'Back to home', 'hi-wpessential' ), '' ) );
}
foreach ( $wpessential_links as $wpessential_i => $wpessential_link ) {
	$wpessential_page = '' !== (string) ( $wpessential_link[1] ?? '' ) ? get_page_by_path( (string) $wpessential_link[1] ) : null;
	$wpessential_url  = $wpessential_page ? (string) get_permalink( $wpessential_page ) : home_url( '/' );
	echo '<!-- wp:button' . ( $wpessential_i ? ' {"className":"is-style-wpe-outline"}' : '' ) . ' --><div class="wp-block-button' . ( $wpessential_i ? ' is-style-wpe-outline' : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $wpessential_url ) . '">' . esc_html( (string) ( $wpessential_link[0] ?? '' ) ) . '</a></div><!-- /wp:button -->';
}
?>
</div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
