<?php
/**
 * Shared clip shape, SVG grain and theme elevation regression.
 *
 * @package WPEssential\Core
 */
use WPEssential\Core\Kit;

$wpe_assert = static function ( $good, $message ) {
	if ( ! $good ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
};
$wpe_def = Kit::defs()['chart'];
$wpe_controls = Kit::attributes( $wpe_def );
foreach ( array( 'clipShape', 'grainEnabled', 'grainOpacity', 'elevationRest', 'elevationHover', 'elevationFocus' ) as $wpe_key ) {
	$wpe_assert( isset( $wpe_controls[ $wpe_key ] ), 'Available on every Kit element: ' . $wpe_key );
}
$wpe_assert( ! empty( $wpe_controls['grainOpacity']['responsive'] ), 'Noise opacity is responsive' );
$wpe_output = Kit::render( 'chart', array(
	'clipShape' => 'slant',
	'grainEnabled' => true,
	'grainOpacity' => array( 'base' => 9, '560' => 4 ),
	'elevationRest' => 'sm',
	'elevationHover' => 'lg',
	'elevationFocus' => 'md',
) );
foreach ( array( 'wpek-clip-slant', 'wpek-grain', '--wpek-grain-opacity:9%', '--wpek-grain-opacity:4%', 'wpek-elev-rest-sm', 'wpek-elev-hover-lg', 'wpek-elev-focus-md', 'wpek-elevation' ) as $wpe_marker ) {
	$wpe_assert( false !== strpos( $wpe_output, $wpe_marker ), 'CSS and classes emitted: ' . $wpe_marker );
}
$wpe_invalid = Kit::render( 'chart', array(
	'clipShape' => 'url(javascript:alert(1))',
	'grainEnabled' => true,
	'grainOpacity' => array( 'base' => 9999, '560' => 'calc(10px)' ),
	'elevationRest' => 'alert(1)',
	'elevationHover' => 'evil',
	'elevationFocus' => 'not-a-preset',
) );
foreach ( array( 'wpek-clip-', 'wpek-elev-', '--wpek-grain-opacity:' ) as $wpe_bad ) {
	$wpe_assert( false === strpos( $wpe_invalid, $wpe_bad ), 'Unknown/invalid CSS rejected: ' . $wpe_bad );
}
$wpe_plain = Kit::render( 'chart', array() );
foreach ( array( 'wpek-clip-', 'wpek-grain', 'wpek-elevation' ) as $wpe_absent ) {
	$wpe_assert( false === strpos( $wpe_plain, $wpe_absent ), 'Existing defaults unchanged: ' . $wpe_absent );
}
$wpe_widget = Kit::from_elementor(
	$wpe_def,
	array(
		'clipShape' => 'wave',
		'grainEnabled' => 'yes',
		'grainOpacity' => array( 'size' => 12, 'unit' => '%' ),
		'grainOpacity_mobile_extra' => array( 'size' => 5, 'unit' => '%' ),
		'elevationRest' => 'md',
		'elevationHover' => 'lg',
		'elevationFocus' => 'sm',
	)
);
$wpe_rendered = Kit::render( 'chart', $wpe_widget );
foreach ( array( 'wpek-clip-wave', 'wpek-grain', '--wpek-grain-opacity:12%', '--wpek-grain-opacity:5%', 'wpek-elev-rest-md', 'wpek-elev-hover-lg', 'wpek-elev-focus-sm' ) as $wpe_marker ) {
	$wpe_assert( false !== strpos( $wpe_rendered, $wpe_marker ), 'Elementor maps feature: ' . $wpe_marker );
}
echo "Kit shapes, grain and elevation regression passed.\n";
