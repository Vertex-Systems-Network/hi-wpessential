<?php
/**
 * Shared visual FX: gradient borders, animated borders, blend modes and masks.
 *
 * @package WPEssential\Core
 */
use WPEssential\Core\Kit;

$wpe_assert = static function ( $ok, $message ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
};

$wpe_def = Kit::defs()['price-list'];
$wpe_fields = Kit::attributes( $wpe_def );
foreach ( array( 'effectGradientBorder', 'effectBorderFrom', 'effectBorderTo', 'effectBorderWidth', 'effectBorderAngle', 'effectBorderSpin', 'effectBorderSpeed', 'effectBlend', 'effectMask' ) as $wpe_key ) {
	$wpe_assert( isset( $wpe_fields[ $wpe_key ] ), 'Shared visual FX option ' . $wpe_key );
}
$wpe_attrs = array(
	'effectGradientBorder' => true,
	'effectBorderFrom'     => 'primary',
	'effectBorderTo'       => 'secondary',
	'effectBorderWidth'    => array( 'base' => 3, '560' => 6 ),
	'effectBorderAngle'    => 45,
	'effectBorderSpin'     => true,
	'effectBorderSpeed'    => 5000,
	'effectBlend'          => 'multiply',
	'effectMask'           => 'bottom',
);
$wpe_html = Kit::render( 'price-list', $wpe_attrs );
foreach ( array( 'wpek-gradient-border', 'wpek-gradient-border-animated', 'wpek-blend-multiply', 'wpek-mask-bottom', '--wpek-gradient-width:3px', '--wpek-gradient-width:6px', '--wpek-gradient-angle:45deg', '--wpek-gradient-speed:5000ms', '--wpek-gradient-a:var(--wp--preset--color--primary)', '--wpek-gradient-b:var(--wp--preset--color--secondary)' ) as $wpe_fragment ) {
	$wpe_assert( false !== strpos( $wpe_html, $wpe_fragment ), 'Valid visual effect output ' . $wpe_fragment );
}
$wpe_bad = Kit::render(
	'price-list',
	array(
		'effectGradientBorder' => true,
		'effectBlend'          => 'url(javascript:alert(1))',
		'effectMask'           => 'evil',
		'effectBorderWidth'    => array( 'base' => -1 ),
		'effectBorderAngle'    => 9999,
		'effectBorderSpeed'    => '1000ms}body{display:none',
	)
);
$wpe_assert( false === strpos( $wpe_bad, 'wpek-blend-' ) && false === strpos( $wpe_bad, 'wpek-mask-' ), 'Unknown blend and mask values never become CSS classes' );
$wpe_assert( false === strpos( $wpe_bad, '--wpek-gradient-width:' ) && false === strpos( $wpe_bad, '--wpek-gradient-angle:' ) && false === strpos( $wpe_bad, '--wpek-gradient-speed:' ), 'Invalid numeric gradient CSS values are suppressed' );
$wpe_default = Kit::render( 'price-list', array() );
$wpe_assert( false === strpos( $wpe_default, 'wpek-gradient-border' ) && false === strpos( $wpe_default, 'wpek-blend-' ) && false === strpos( $wpe_default, 'wpek-mask-' ), 'Default elements remain unchanged' );

$wpe_elementor = Kit::from_elementor(
	$wpe_def,
	array(
		'effectGradientBorder' => 'yes',
		'effectBorderWidth'    => array( 'size' => 4, 'unit' => 'px' ),
		'effectBorderAngle'    => array( 'size' => 90, 'unit' => 'deg' ),
		'effectBorderSpin'     => 'yes',
		'effectBorderSpeed'    => array( 'size' => 6000, 'unit' => 'ms' ),
		'effectBlend'          => 'screen',
		'effectMask'           => 'left',
	)
);
$wpe_widget = Kit::render( 'price-list', $wpe_elementor );
foreach ( array( 'wpek-gradient-border-animated', 'wpek-blend-screen', 'wpek-mask-left', '--wpek-gradient-width:4px', '--wpek-gradient-angle:90deg', '--wpek-gradient-speed:6000ms' ) as $wpe_fragment ) {
	$wpe_assert( false !== strpos( $wpe_widget, $wpe_fragment ), 'Elementor effect mapping ' . $wpe_fragment );
}
echo "Kit Visual Effects tests passed.\n";
