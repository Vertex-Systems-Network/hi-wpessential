<?php
/**
 * Real Elementor + WPForms Lite browser QA fixtures on disposable WordPress.
 *
 * @package WPEssential\Core
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Disposable test fixture.
if ( ! shortcode_exists( 'wpforms' ) || ! class_exists( '\Elementor\Plugin' ) ) {
	throw new RuntimeException( 'Both WPForms Lite and Elementor must be installed.' );
}
$wpe_menu = wp_create_nav_menu( 'Kit QA Browser Menu' );
if ( is_wp_error( $wpe_menu ) ) {
	throw new RuntimeException( 'Unable to create menu.' );
}
$wpe_parent = wp_update_nav_menu_item( $wpe_menu, 0, array(
	'menu-item-title' => 'Services',
	'menu-item-url' => home_url( '/services/' ),
	'menu-item-type' => 'custom',
	'menu-item-status' => 'publish',
) );
wp_update_nav_menu_item( $wpe_menu, 0, array(
	'menu-item-title' => 'Consulting',
	'menu-item-url' => home_url( '/consulting/' ),
	'menu-item-type' => 'custom',
	'menu-item-parent-id' => $wpe_parent,
	'menu-item-status' => 'publish',
) );
// Published synced pattern provides a realistic, block-authored mega-menu panel.
$wpe_mega_pattern = wp_insert_post(
	array(
		'post_type' => 'wp_block',
		'post_status' => 'publish',
		'post_title' => 'Kit QA Mega Content',
		'post_content' => '<!-- wp:paragraph --><p>Kit QA Saved Mega Content</p><!-- /wp:paragraph -->',
	)
);
$wpe_form_id = wp_insert_post( array( 'post_type' => 'wpforms', 'post_status' => 'publish', 'post_title' => 'Kit QA Contact' ), true );
if ( is_wp_error( $wpe_form_id ) ) {
	throw new RuntimeException( 'Unable to insert WPForms fixture.' );
}
$wpe_form = array(
	'id' => (string) $wpe_form_id,
	'field_id' => '3',
	'fields' => array(
		'1' => array( 'id' => '1', 'type' => 'text', 'label' => 'Full name', 'required' => '1' ),
		'2' => array( 'id' => '2', 'type' => 'email', 'label' => 'Email address', 'required' => '1' ),
		'3' => array( 'id' => '3', 'type' => 'textarea', 'label' => 'Message', 'required' => '1' ),
	),
	'settings' => array(
		'form_title' => 'Kit QA Contact',
		'form_desc' => 'Disposable test contact form',
		'submit_text' => 'Send request',
		'submit_text_processing' => 'Sending...',
		'notification_enable' => '0',
		'confirmations' => array( '1' => array( 'type' => 'message', 'message' => 'Test request received.' ) ),
	),
	'meta' => array(),
);
wp_update_post( array( 'ID' => $wpe_form_id, 'post_content' => wp_slash( wp_json_encode( $wpe_form ) ) ) );
$wpe_defs = array(
	'price-list' => array( 'title' => 'Kit QA Prices', 'animation' => 'fade-up', 'animTrigger' => 'click', 'staggerChildren' => true, 'staggerStep' => 125, 'animReduced' => 'fade', 'logicalMarginStart' => array( 'base' => 14 ), 'paddingToken' => 'sm', 'radiusToken' => 'md', 'items' => array( array( 'title' => 'Spring menu', 'price' => '19', 'badge' => 'Popular' ), array( 'title' => 'Summer menu', 'price' => '24' ) ) ),
	'menu' => array(
		'menu' => (string) $wpe_menu,
		'mega' => true,
		'label' => 'QA Navigation',
		'desktopTrigger' => 'click',
		'templateItems' => array(
			array( 'menuItemId' => (string) $wpe_parent, 'templateId' => (string) $wpe_mega_pattern, 'source' => 'wp_block' ),
		),
	),
	'forms' => array( 'animation' => 'fade', 'animTrigger' => 'scroll', 'formId' => (string) $wpe_form_id, 'heading' => 'Kit QA Real Form', 'showTitle' => false, 'containerQuerySource' => true ),
	'icon' => array( 'icon' => array( 'library' => 'wpessential', 'value' => 'bolt' ), 'label' => 'QA Icon', 'url' => 'https://example.org', 'focusRingStyle' => 'solid', 'focusRingColor' => 'primary', 'focusRingWidth' => array( 'base' => 3 ), 'focusRingOffset' => array( 'base' => 2 ), 'logicalPaddingEnd' => array( 'base' => 9 ), 'containerQueryHideBelow' => 320 ),
	'chart' => array( 'title' => 'Kit QA Chart', 'animation' => 'zoom-in', 'animTrigger' => 'load', 'fluidPaddingMin' => 8, 'fluidPaddingMax' => 32, 'fluidGapMin' => 4, 'fluidGapMax' => 16, 'type' => 'donut', 'glass' => true, 'glassBlur' => array( 'base' => 14 ), 'glassOpacity' => array( 'base' => 75 ), 'hidePrint' => true, 'items' => array( array( 'label' => 'A', 'value' => '60', 'color' => 'primary' ), array( 'label' => 'B', 'value' => '40', 'color' => 'secondary' ) ) ),
	'flip' => array( 'animation' => 'fade-up', 'animTrigger' => 'hover', 'frontTitle' => 'Kit QA Flip Front', 'backTitle' => 'Kit QA Flip Back', 'frontContent' => '<p>Open this card</p>' ),
	'site-archive-widgets' => array( 'stickyEnabled' => true, 'stickyOffset' => array( 'base' => 24 ), 'stickyFrom' => 'tablet', 'mode' => 'title', 'fallback' => 'Kit QA Archive', 'conditionDevice' => 'desktop' ),
);
$wpe_blocks = '';
$wpe_widgets = array();
foreach ( $wpe_defs as $wpe_name => $wpe_atts ) {
	$wpe_blocks .= '<!-- wp:wpessential/' . $wpe_name . ' ' . wp_json_encode( $wpe_atts ) . " /-->\n";
	// Elementor stores switchers as "yes" and responsive sliders as { size, unit }.
	// Gutenberg instead stores booleans and breakpoint-keyed values.
	$wpe_elementor_atts = $wpe_atts;
	foreach ( \WPEssential\Core\Kit::attributes( \WPEssential\Core\Kit::defs()[ $wpe_name ] ) as $wpe_key => $wpe_opt ) {
		if ( ! array_key_exists( $wpe_key, $wpe_elementor_atts ) ) {
			continue;
		}
		if ( 'toggle' === $wpe_opt['type'] ) {
			$wpe_elementor_atts[ $wpe_key ] = $wpe_elementor_atts[ $wpe_key ] ? 'yes' : '';
		} elseif ( 'range' === $wpe_opt['type'] && is_array( $wpe_elementor_atts[ $wpe_key ] ) ) {
			$wpe_elementor_atts[ $wpe_key ] = array(
				'size' => $wpe_elementor_atts[ $wpe_key ]['base'] ?? '',
				'unit' => $wpe_opt['unit'] ?? 'px',
			);
		}
	}
	$wpe_widgets[] = array( 'id' => substr( md5( $wpe_name ), 0, 7 ), 'elType' => 'widget', 'widgetType' => 'wpe-' . $wpe_name, 'settings' => $wpe_elementor_atts, 'elements' => array() );
}
$wpe_gutenberg = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Kit Gutenberg QA', 'post_content' => $wpe_blocks ), true );
$wpe_elementor = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Kit Elementor QA' ), true );
if ( is_wp_error( $wpe_gutenberg ) || is_wp_error( $wpe_elementor ) ) {
	throw new RuntimeException( 'Unable to create editor fixtures.' );
}
$wpe_layout = array( array(
	'id' => 'dd00ca1', 'elType' => 'section', 'settings' => array( 'structure' => '10' ),
	'elements' => array( array( 'id' => 'd0c0001', 'elType' => 'column', 'settings' => array( '_column_size' => 100 ), 'elements' => $wpe_widgets ) ),
) );
update_post_meta( $wpe_elementor, '_elementor_edit_mode', 'builder' );
update_post_meta( $wpe_elementor, '_elementor_template_type', 'wp-page' );
update_post_meta( $wpe_elementor, '_elementor_data', wp_slash( wp_json_encode( $wpe_layout ) ) );
update_post_meta( $wpe_elementor, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' );
$wpe_real_form = do_shortcode( '[wpforms id="' . absint( $wpe_form_id ) . '"]' );
if ( false === strpos( $wpe_real_form, '<form' ) ) {
	throw new RuntimeException( 'Real WPForms Lite form did not render.' );
}
echo wp_json_encode( array(
	'gutenberg' => $wpe_gutenberg, 'elementor' => $wpe_elementor,
	'form' => $wpe_form_id, 'elements' => array_keys( $wpe_defs ),
) );
