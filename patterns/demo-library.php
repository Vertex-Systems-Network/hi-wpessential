<?php
/**
 * Title: Demo library — start from a site that works
 * Slug: wpessential/demo-library
 * Categories: wpessential-sections
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

$wpessential_demos = array(
	array( 'demo-business', __( 'Business', 'hi-wpessential' ), __( 'Free', 'hi-wpessential' ), 'is-free' ),
	array( 'demo-maison', __( 'Maison — Fashion shop', 'hi-wpessential' ), __( 'Pro', 'hi-wpessential' ), 'is-pro' ),
	array( 'demo-pulse', __( 'Pulse — Conference', 'hi-wpessential' ), __( 'Pro', 'hi-wpessential' ), 'is-pro' ),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'Demo library', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"h2"} -->
<h2 class="wp-block-heading has-h-2-font-size"><?php esc_html_e( 'Start from a site that already works', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-wpe-outline"} -->
<div class="wp-block-button is-style-wpe-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'View all demos →', 'hi-wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"wpe-bx-demos","layout":{"type":"grid","columnCount":3}} -->
<div class="wp-block-group wpe-bx-demos">
<?php
foreach ( $wpessential_demos as $wpessential_demo ) :
	?>
	<!-- wp:group {"className":"wpe-bx-demo","layout":{"type":"default"}} -->
<div class="wp-block-group wpe-bx-demo"><!-- wp:image {"sizeSlug":"full","className":"wpe-bx-demo__img"} -->
<figure class="wp-block-image size-full wpe-bx-demo__img"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $wpessential_demo[0] . '.webp' ) ); ?>" alt="<?php echo esc_attr( $wpessential_demo[1] ); ?>"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"wpe-bx-demo__meta"} -->
<p class="wpe-bx-demo__meta"><strong><?php echo esc_html( $wpessential_demo[1] ); ?></strong> <span class="wpe-bx-badge <?php echo esc_attr( $wpessential_demo[3] ); ?>"><?php echo esc_html( $wpessential_demo[2] ); ?></span></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
