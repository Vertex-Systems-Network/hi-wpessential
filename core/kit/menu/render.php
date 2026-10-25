<?php
/**
 * Native WordPress menu, shared by both builders.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Rendered in Kit::template().

$wpe_name = trim( (string) $a['menu'] );
$wpe_menu = '' !== $wpe_name ? wp_get_nav_menu_object( $wpe_name ) : false;
if ( ! $wpe_menu && '' === $wpe_name ) {
	$wpe_locations = get_nav_menu_locations();
	$wpe_menu      = ! empty( $wpe_locations['primary'] ) ? wp_get_nav_menu_object( (int) $wpe_locations['primary'] ) : false;
}
if ( ! $wpe_menu ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p id="' . esc_attr( $id ) . '" class="wpe-menu__empty">' . esc_html__( 'Select a WordPress menu or assign the primary menu.', 'hi-wpessential' ) . '</p>';
	}
	return;
}
$wpe_label   = trim( (string) $a['label'] );
$wpe_label   = '' !== $wpe_label ? $wpe_label : __( 'Main navigation', 'hi-wpessential' );
$wpe_depth   = min( 3, max( 1, absint( $a['depth'] ) ) );
$wpe_break   = in_array( (string) $a['breakpoint'], array( '1024', '768', '560' ), true ) ? (string) $a['breakpoint'] : '768';
$wpe_args    = array(
	'menu'        => (int) $wpe_menu->term_id,
	'echo'        => false,
	'fallback_cb' => false,
	'container'   => false,
	'depth'       => $wpe_depth,
);
$wpe_desktop = wp_nav_menu( $wpe_args + array( 'menu_class' => 'wpe-menu__items' ) );
$wpe_mobile  = wp_nav_menu( $wpe_args + array( 'menu_class' => 'wpe-menu__mobile-items' ) );
if ( ! $wpe_desktop ) {
	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-menu is-break-<?php echo esc_attr( $wpe_break ); ?><?php echo ! empty( $a['mega'] ) ? ' is-mega' : ''; ?>">
	<nav class="wpe-menu__desktop" aria-label="<?php echo esc_attr( $wpe_label ); ?>">
		<?php echo $wpe_desktop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress core nav-menu markup and escaping. ?>
	</nav>
	<details class="wpe-menu__mobile">
		<summary class="wpe-menu__toggle"><?php echo esc_html( '' !== trim( (string) $a['mobileLabel'] ) ? $a['mobileLabel'] : __( 'Menu', 'hi-wpessential' ) ); ?><span aria-hidden="true">☰</span></summary>
		<nav aria-label="<?php echo esc_attr( $wpe_label ); ?>">
			<?php echo $wpe_mobile; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress core nav-menu markup. ?>
		</nav>
	</details>
</div>
