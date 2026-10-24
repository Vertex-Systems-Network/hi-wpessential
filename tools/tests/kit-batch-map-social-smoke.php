<?php
/**
 * Map, social links and dependency-safe product grid smoke tests.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $pass, $description ) {
	if ( ! $pass ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};
$wpe_block = static function ( $slug, array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $slug . ' ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};
foreach ( array( 'map', 'social', 'products' ) as $wpe_slug ) {
	$wpe_check( isset( Kit::defs()[ $wpe_slug ] ), $wpe_slug . ' definition registered' );
	$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_slug ), $wpe_slug . ' Gutenberg registered' );
	$wpe_check( wp_style_is( 'wpessential-kit-' . $wpe_slug, 'registered' ), $wpe_slug . ' stylesheet registered' );
	$wpe_widget = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_slug ) : null;
	$wpe_check( is_object( $wpe_widget ) && 'wpe-' . $wpe_slug === $wpe_widget->get_name(), $wpe_slug . ' Elementor registered' );
}
$wpe_check( wp_script_is( 'wpessential-kit-map', 'registered' ), 'Map script registered' );

$wpe_map = $wpe_block( 'map', array( 'latitude' => '0', 'longitude' => '0', 'title' => '<script>unsafe</script>Center', 'address' => '<img src=x onerror=alert(1)>City', 'embed' => true ) );
$wpe_check( false !== strpos( $wpe_map, 'data-wpe-map="1"' ), 'Equator and prime meridian accepted' );
$wpe_check( false !== strpos( $wpe_map, '0.000000,0.000000' ), 'Validated coordinates rendered' );
$wpe_check( false !== strpos( $wpe_map, 'data-map-src="https://www.openstreetmap.org/export/embed.html?' ), 'Map embeds use constrained OpenStreetMap host' );
$wpe_check( false !== strpos( $wpe_map, 'noopener noreferrer' ), 'External map link is secure' );
$wpe_check( false === strpos( $wpe_map, '<iframe' ), 'No iframe or third-party request before consent' );
$wpe_check( false === strpos( $wpe_map, '<script>' ) && false === strpos( $wpe_map, '<img src=x' ), 'Map title/address HTML escaped' );
$wpe_bad_lat = $wpe_block( 'map', array( 'latitude' => '91' ) );
$wpe_bad_long = $wpe_block( 'map', array( 'longitude' => '-181' ) );
$wpe_bad_js = $wpe_block( 'map', array( 'latitude' => 'javascript:alert(1)' ) );
$wpe_check( false === strpos( $wpe_bad_lat, 'data-wpe-map' ) && false === strpos( $wpe_bad_long, 'data-wpe-map' ) && false === strpos( $wpe_bad_js, 'data-wpe-map' ), 'Invalid and injected coordinates rejected' );
$wpe_link_only = $wpe_block( 'map', array( 'embed' => false ) );
$wpe_check( false === strpos( $wpe_link_only, 'data-map-src' ) && false !== strpos( $wpe_link_only, 'openstreetmap.org' ), 'Link-only map does not request embedded data' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['map'], array( 'embed' => '', 'height' => array( 'size' => 470, 'unit' => 'px' ) ) );
$wpe_check( false === $wpe_mapped['embed'], 'Map Elementor consent toggle maps off' );
$wpe_check( false !== strpos( Kit::render( 'map', $wpe_mapped ), '--wpek-map-height:470px' ), 'Map Elementor responsive height mapped' );

$wpe_social = $wpe_block( 'social', array(
	'items' => array(
		array( 'platform' => 'instagram', 'label' => 'Photos', 'url' => 'https://instagram.com/example' ),
		array( 'platform' => 'github', 'label' => '', 'url' => 'https://github.com/example' ),
		array( 'platform' => 'facebook', 'url' => 'javascript:alert(1)' ),
		array( 'platform' => 'linkedin', 'url' => 'http://example.com/insecure' ),
		array( 'platform' => '<svg onload=alert(2)>', 'url' => 'https://example.com' ),
	),
	'newTab' => true,
	'showLabels' => true,
	'heading' => '" onclick="alert(1)',
) );
$wpe_check( 2 === preg_match_all( '/class="wpe-social__link"/', $wpe_social ), 'Only whitelisted social icons with HTTPS links emitted' );
$wpe_check( false !== strpos( $wpe_social, 'aria-label="Photos"' ), 'Custom accessible social name preserved' );
$wpe_check( false !== strpos( $wpe_social, 'aria-label="GitHub"' ), 'Missing social name defaults to official platform' );
$wpe_check( false !== strpos( $wpe_social, 'noopener noreferrer' ), 'Social new-tab links guarded' );
$wpe_check( false === strpos( $wpe_social, 'javascript:' ) && false === strpos( $wpe_social, 'http://example.com' ) && false === strpos( $wpe_social, ' onclick="' ), 'Social URL injection and attribute injection rejected' );
$wpe_check( 2 === preg_match_all( '/<svg viewBox=/', $wpe_social ), 'Controlled local SVG icons rendered' );
$wpe_empty = $wpe_block( 'social', array( 'items' => array( array( 'url' => 'javascript:alert(1)' ) ) ) );
$wpe_check( false === strpos( $wpe_empty, '<nav' ), 'No social navigation emitted when no valid URLs' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['social'], array( 'showLabels' => 'yes', 'shape' => 'pill', 'size' => array( 'size' => 56, 'unit' => 'px' ), 'items' => array( array( 'platform' => 'youtube', 'url' => 'https://youtube.com/example', 'label' => 'Channel' ) ) ) );
$wpe_check( true === $wpe_mapped['showLabels'], 'Social Elementor switcher normalized' );
$wpe_result = Kit::render( 'social', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_result, 'Channel' ) && false !== strpos( $wpe_result, '--wpek-social-size:56px' ), 'Social Elementor shared renderer and responsive CSS' );

// This phase runs before installing WooCommerce.
$wpe_check( ! function_exists( 'wc_get_products' ), 'WooCommerce absent during dependency safety test' );
$wpe_missing = $wpe_block( 'products' );
$wpe_check( false === strpos( $wpe_missing, '<section' ) && false === strpos( $wpe_missing, 'Fatal error' ), 'Products widget fails safely when WooCommerce missing' );
echo "Map, Social and no-WooCommerce fallback tests passed.\n";
