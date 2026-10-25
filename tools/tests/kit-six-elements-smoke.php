<?php
/**
 * Full Kit batch smoke: six new Gutenberg and Elementor elements.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_ok = static function ( $success, $message ) {
	if ( ! $success ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
};
$wpe_block = static function ( $name, array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $name . ' ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};
foreach ( array( 'menu', 'site-archive-widgets', 'forms', 'icon', 'chart', 'flip' ) as $wpe_name ) {
	$wpe_ok( isset( Kit::defs()[ $wpe_name ] ), $wpe_name . ': definition registered' );
	$wpe_ok( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_name ), $wpe_name . ': Gutenberg registered' );
	$wpe_ok( wp_style_is( 'wpessential-kit-' . $wpe_name, 'registered' ), $wpe_name . ': style registered' );
	$wpe_widget = \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_name );
	$wpe_ok( is_object( $wpe_widget ) && 'wpe-' . $wpe_name === $wpe_widget->get_name(), $wpe_name . ': Elementor widget registered' );
}

// Real native WordPress menu and descendants.
$wpe_menu_id = wp_create_nav_menu( 'WPE Test Navigation' );
$wpe_ok( ! is_wp_error( $wpe_menu_id ), 'Menu: native menu created' );
try {
	$wpe_parent = wp_update_nav_menu_item( $wpe_menu_id, 0, array(
		'menu-item-title' => 'Services',
		'menu-item-url' => home_url( '/services/' ),
		'menu-item-status' => 'publish',
		'menu-item-type' => 'custom',
	) );
	$wpe_child = wp_update_nav_menu_item( $wpe_menu_id, 0, array(
		'menu-item-title' => 'Consulting',
		'menu-item-url' => home_url( '/consulting/' ),
		'menu-item-parent-id' => $wpe_parent,
		'menu-item-status' => 'publish',
		'menu-item-type' => 'custom',
	) );
	$wpe_html = $wpe_block( 'menu', array( 'menu' => (string) $wpe_menu_id, 'mega' => true, 'breakpoint' => '1024' ) );
	$wpe_ok( false !== strpos( $wpe_html, 'Services' ) && false !== strpos( $wpe_html, 'Consulting' ), 'Menu: parent and child rendered' );
	$wpe_ok( false !== strpos( $wpe_html, 'is-mega' ) && false !== strpos( $wpe_html, 'is-break-1024' ), 'Menu: mega variant and responsive breakpoint' );
	$wpe_ok( false !== strpos( $wpe_html, '<summary' ) && false !== strpos( $wpe_html, 'wpe-menu__mobile' ), 'Menu: native mobile disclosure' );
	$wpe_ok( false === strpos( $wpe_block( 'menu', array( 'menu' => 'does-not-exist' ) ), 'wpe-menu__items' ), 'Menu: no unwanted fallback on unknown menu' );
} finally {
	wp_delete_nav_menu( $wpe_menu_id );
}

// WPForms Lite integration. Fake published fixture only; actual plugin retains submission control.
$wpe_ok( '' === trim( $wpe_block( 'forms', array( 'formId' => '0' ) ) ), 'Forms: blank ID does not render a form' );
$wpe_ok( '' === trim( $wpe_block( 'forms', array( 'formId' => '1][bad' ) ) ), 'Forms: shortcode injection rejected' );
register_post_type( 'wpforms', array( 'public' => false ) );
add_shortcode( 'wpforms', static function ( $atts ) {
	return '<form class="wpforms-form" method="post"><input value="' . esc_attr( (string) $atts['id'] ) . '"></form>';
} );
$wpe_form_id = wp_insert_post( array( 'post_type' => 'wpforms', 'post_status' => 'publish', 'post_title' => 'Contact test' ) );
try {
	$wpe_html = $wpe_block( 'forms', array( 'formId' => (string) $wpe_form_id, 'heading' => '<script>bad</script>Get in touch' ) );
	$wpe_ok( false !== strpos( $wpe_html, 'wpforms-form' ) && false !== strpos( $wpe_html, 'value="' . $wpe_form_id . '"' ), 'Forms: existing WPForms shortcode used' );
	$wpe_ok( false === strpos( $wpe_html, '<script>' ), 'Forms: heading HTML escaped' );
} finally {
	wp_delete_post( $wpe_form_id, true );
	remove_shortcode( 'wpforms' );
}

// Labelled icon, safe links, rendered SVG.
$wpe_icon = $wpe_block( 'icon', array( 'label' => 'Contact', 'icon' => array( 'library' => 'wpessential', 'value' => 'bolt' ), 'url' => 'https://example.org/contact', 'newTab' => true ) );
$wpe_ok( false !== strpos( $wpe_icon, '<svg' ) && false !== strpos( $wpe_icon, 'aria-label="Contact"' ), 'Icon: accessible SVG icon' );
$wpe_ok( false !== strpos( $wpe_icon, 'rel="noopener noreferrer"' ), 'Icon: safe new-tab link' );
$wpe_ok( false === strpos( $wpe_block( 'icon', array( 'url' => 'javascript:alert(1)' ) ), 'javascript:' ), 'Icon: JavaScript URL rejected' );
$wpe_ok( false !== strpos( $wpe_block( 'icon', array( 'decorative' => true ) ), 'aria-hidden="true"' ), 'Icon: decorative accessibility mode' );

// Charts must normalize and bound values and present accessible table.
$wpe_chart = $wpe_block( 'chart', array( 'type' => 'donut', 'items' => array(
	array( 'label' => '<script>bad</script>Alpha', 'value' => '30', 'color' => 'primary' ),
	array( 'label' => 'Beta', 'value' => '70', 'color' => 'accent' ),
	array( 'label' => 'Bad', 'value' => '-1' ),
	array( 'label' => 'Wrong', 'value' => 'NaN' ),
) ) );
$wpe_ok( 2 === preg_match_all( '/<circle /', $wpe_chart ), 'Chart: exactly two valid donut segments' );
$wpe_ok( false === strpos( $wpe_chart, '<script>' ) && false !== strpos( $wpe_chart, 'Alpha' ), 'Chart: untrusted title sanitized' );
$wpe_ok( false !== strpos( $wpe_chart, '<table' ), 'Chart: accessible data table' );
$wpe_ok( false === strpos( $wpe_block( 'chart', array( 'type' => 'donut', 'items' => array( array( 'label' => 'Empty', 'value' => '0' ) ) ) ), 'NaN' ), 'Chart: zero data handled safely' );
$wpe_ok( false !== strpos( $wpe_block( 'chart', array( 'type' => 'columns' ) ), 'is-columns' ), 'Chart: column layout renders' );

// Native disclosure; no JS dependency, safe rich content and URLs.
$wpe_flip = $wpe_block( 'flip', array( 'frontTitle' => 'Front', 'backTitle' => 'Back', 'backContent' => '<strong>Safe</strong><script>alert(1)</script>', 'url' => 'javascript:alert(1)' ) );
$wpe_ok( false !== strpos( $wpe_flip, '<details' ) && false !== strpos( $wpe_flip, '<summary' ), 'Flip: keyboard/touch disclosure' );
$wpe_ok( false !== strpos( $wpe_flip, '<strong>Safe</strong>' ) && false === strpos( $wpe_flip, '<script>' ), 'Flip: allowed formatting, blocked scripts' );
$wpe_ok( false === strpos( $wpe_flip, 'javascript:' ), 'Flip: unsafe CTA URL blocked' );

// Archive output sanitization even without active archive in CLI.
$wpe_archive = $wpe_block( 'site-archive-widgets', array( 'mode' => 'title', 'fallback' => '<script>bad</script>Our latest posts', 'prefix' => 'Blog: ' ) );
$wpe_ok( false === strpos( $wpe_archive, '<script>' ), 'Archive: fallback title markup escaped' );
$wpe_crumbs = $wpe_block( 'site-archive-widgets', array( 'mode' => 'breadcrumbs', 'fallback' => 'News' ) );
$wpe_ok( false !== strpos( $wpe_crumbs, 'Breadcrumbs' ), 'Archive: semantic breadcrumb navigation' );

// Elementor control mappings are shared with the Gutenberg renderers.
foreach ( array(
	'icon' => array( 'size' => array( 'size' => 40, 'unit' => 'px' ) ),
	'flip' => array( 'height' => array( 'size' => 300, 'unit' => 'px' ) ),
	'chart' => array( 'gap' => array( 'size' => 19, 'unit' => 'px' ) ),
	'site-archive-widgets' => array( 'maxWidth' => array( 'size' => 860, 'unit' => 'px' ) ),
) as $wpe_name => $wpe_controls ) {
	$wpe_values = Kit::from_elementor( Kit::defs()[ $wpe_name ], $wpe_controls );
	$wpe_html   = Kit::render( $wpe_name, $wpe_values );
	$wpe_ok( is_string( $wpe_html ), $wpe_name . ': Elementor shared renderer' );
}
echo "All six Kit categories WordPress + Elementor smoke checks passed.\n";
