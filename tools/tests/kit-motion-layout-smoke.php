<?php
/**
 * Shared parallax and named sticky stop regressions.
 *
 * @package WPEssential\Core
 */
use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $label ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	echo 'PASS: ' . $label . "\n";
};
$wpe_def = Kit::defs()['price-list'];
$wpe_fields = Kit::attributes( $wpe_def );
foreach ( array( 'parallaxEnabled', 'parallaxAxis', 'parallaxLayer1', 'parallaxLayer2', 'parallaxLayer3', 'parallaxLayer4', 'stickyStopId' ) as $wpe_key ) {
	$wpe_check( isset( $wpe_fields[ $wpe_key ] ), 'Common control ' . $wpe_key );
}
$wpe_check( ! empty( $wpe_fields['parallaxLayer1']['responsive'] ), 'Parallax values are responsive' );
$wpe_output = Kit::render(
	'price-list',
	array(
		'parallaxEnabled' => true,
		'parallaxAxis'    => 'x',
		'parallaxLayer1'  => array( 'base' => 80, '560' => 0 ),
		'parallaxLayer2'  => array( 'base' => -45 ),
		'stickyEnabled'   => true,
		'stickyStopId'    => 'kit-stop-target',
	)
);
foreach ( array( 'wpek-parallax', 'wpek-parallax-x', 'wpek-sticky-stop', 'data-wpek-sticky-stop="kit-stop-target"', '--wpek-parallax-layer-1:80px', '--wpek-parallax-layer-2:-45px', '--wpek-parallax-layer-1:0px' ) as $wpe_marker ) {
	$wpe_check( false !== strpos( $wpe_output, $wpe_marker ), 'Rendered ' . $wpe_marker );
}
$wpe_check( wp_script_is( 'wpessential-kit-motion-layout', 'enqueued' ), 'Motion layout script enqueued' );
$wpe_invalid = Kit::render(
	'price-list',
	array(
		'stickyEnabled'   => true,
		'stickyStopId'    => '#evil" onmouseover="hi()',
		'parallaxEnabled' => true,
		'parallaxLayer1'  => array( 'base' => 9999 ),
		'parallaxLayer2'  => array( 'base' => 'calc(100px)' ),
	)
);
$wpe_check( false === strpos( $wpe_invalid, 'data-wpek-sticky-stop' ), 'Invalid target cannot become an attribute' );
$wpe_check( false === strpos( $wpe_invalid, '--wpek-parallax-layer-1:' ), 'Unbounded layer value rejected' );
$wpe_check( false === strpos( $wpe_invalid, '--wpek-parallax-layer-2:' ), 'Injected CSS layer value rejected' );
$wpe_default = Kit::render( 'price-list', array() );
$wpe_check( false === strpos( $wpe_default, 'wpek-parallax' ) && false === strpos( $wpe_default, 'wpek-sticky-stop' ), 'Existing default widgets unchanged' );
$wpe_mapped = Kit::from_elementor(
	$wpe_def,
	array(
		'parallaxEnabled' => 'yes',
		'parallaxAxis' => 'x',
		'parallaxLayer1' => array( 'size' => 70, 'unit' => 'px' ),
		'parallaxLayer1_mobile_extra' => array( 'size' => 0, 'unit' => 'px' ),
		'stickyEnabled' => 'yes',
		'stickyStopId' => 'kit-stop-target',
	)
);
$wpe_widget = Kit::render( 'price-list', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_widget, '--wpek-parallax-layer-1:70px' ), 'Elementor maps base travel' );
$wpe_check( false !== strpos( $wpe_widget, '--wpek-parallax-layer-1:0px' ), 'Elementor maps per-breakpoint off' );
$wpe_check( false !== strpos( $wpe_widget, 'data-wpek-sticky-stop="kit-stop-target"' ), 'Elementor maps named stop' );
echo "Kit parallax and sticky stop smoke passed.\n";
