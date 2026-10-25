<?php
/**
 * Shared Kit motion triggers, stagger, reduced-motion and sticky smoke tests.
 *
 * @package WPEssential\Core
 */
use WPEssential\Core\Kit;
$wpe_assert = static function ( $ok, $label ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	echo 'PASS: ' . $label . "\n";
};
$wpe_fields = Kit::attributes( Kit::defs()['price-list'] );
foreach ( array( 'animTrigger', 'animReduced', 'staggerChildren', 'staggerStep', 'stickyEnabled', 'stickyOffset', 'stickyFrom' ) as $wpe_key ) {
	$wpe_assert( isset( $wpe_fields[ $wpe_key ] ), 'Shared motion control ' . $wpe_key );
}
$wpe_html = Kit::render(
	'price-list',
	array(
		'animation'       => 'fade-up',
		'animTrigger'     => 'click',
		'animReduced'     => 'fade',
		'staggerChildren' => true,
		'staggerStep'     => 125,
		'stickyEnabled'   => true,
		'stickyOffset'    => array( 'base' => 24, '768' => 12 ),
		'stickyFrom'      => 'tablet',
	)
);
foreach ( array( 'wpek-motion-trigger-click', 'wpek-motion-reduced-fade', 'wpek-stagger', 'wpek-sticky', 'wpek-sticky-from-tablet', '--wpek-stagger-step:125ms', '--wpek-sticky-offset:24px' ) as $wpe_marker ) {
	$wpe_assert( false !== strpos( $wpe_html, $wpe_marker ), 'Motion output marker ' . $wpe_marker );
}
$wpe_assert( wp_script_is( 'wpessential-kit-motion', 'enqueued' ), 'On-demand motion script enqueued' );
$wpe_plain = Kit::render( 'price-list', array() );
$wpe_assert( false === strpos( $wpe_plain, 'wpek-motion-trigger-' ) && false === strpos( $wpe_plain, 'wpek-sticky' ), 'Default Kit element unchanged' );
$wpe_el = Kit::from_elementor(
	Kit::defs()['price-list'],
	array(
		'animation' => 'fade-up',
		'animTrigger' => 'load',
		'staggerChildren' => 'yes',
		'staggerStep' => array( 'size' => 250, 'unit' => 'ms' ),
		'stickyEnabled' => 'yes',
		'stickyOffset' => array( 'size' => 38, 'unit' => 'px' ),
		'stickyFrom' => 'desktop',
	)
);
$wpe_mapped = Kit::render( 'price-list', $wpe_el );
$wpe_assert( false !== strpos( $wpe_mapped, 'wpek-motion-trigger-load' ), 'Elementor load trigger mapped' );
$wpe_assert( false !== strpos( $wpe_mapped, 'wpek-stagger' ) && false !== strpos( $wpe_mapped, '--wpek-stagger-step:250ms' ), 'Elementor stagger mapping' );
$wpe_assert( false !== strpos( $wpe_mapped, 'wpek-sticky-from-desktop' ) && false !== strpos( $wpe_mapped, '--wpek-sticky-offset:38px' ), 'Elementor sticky mapping' );
echo "Shared Kit advanced motion smoke checks passed.\n";
