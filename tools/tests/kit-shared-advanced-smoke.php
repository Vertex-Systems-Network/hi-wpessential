<?php
/**
 * Common Kit advanced controls (focus ring, glass and print) regression.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $message ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
};
$wpe_attrs = Kit::attributes( Kit::defs()['icon'] );
foreach ( array( 'focusRingStyle', 'focusRingColor', 'focusRingWidth', 'focusRingOffset', 'glass', 'glassBlur', 'glassOpacity', 'hidePrint' ) as $wpe_attr ) {
	$wpe_check( isset( $wpe_attrs[ $wpe_attr ] ), 'All Kit elements share ' . $wpe_attr );
}
$wpe_html = Kit::render(
	'icon',
	array(
		'focusRingStyle'  => 'dashed',
		'focusRingColor'  => 'primary',
		'focusRingWidth'  => array( 'base' => 3, '768' => 4 ),
		'focusRingOffset' => array( 'base' => 2 ),
		'glass'           => true,
		'glassBlur'       => array( 'base' => 14 ),
		'glassOpacity'    => array( 'base' => 75 ),
		'hidePrint'       => true,
	)
);
$wpe_check( false !== strpos( $wpe_html, 'wpek-focus-ring' ), 'Shared opt-in focus styling class present' );
$wpe_check( false !== strpos( $wpe_html, 'wpek-glass' ), 'Shared opt-in glass class present' );
$wpe_check( false !== strpos( $wpe_html, 'wpek-hide-print' ), 'Shared opt-in print visibility class present' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-focus-width:3px' ), 'Focus ring width serializes correctly' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-glass-blur:14px' ), 'Glass blur serializes correctly' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-glass-opacity:75%' ), 'Glass transparency serializes correctly' );
$wpe_check( false !== strpos( $wpe_html, 'max-width: 1023px' ), 'Responsive focus control uses a breakpoint' );
$wpe_default = Kit::render( 'icon', array() );
$wpe_check( false === strpos( $wpe_default, 'wpek-glass' ) && false === strpos( $wpe_default, 'wpek-focus-ring' ) && false === strpos( $wpe_default, 'wpek-hide-print' ), 'Default widgets remain visually unchanged' );
$wpe_elementor = Kit::from_elementor(
	Kit::defs()['icon'],
	array(
		'glass'          => 'yes',
		'hidePrint'      => 'yes',
		'focusRingStyle' => 'solid',
		'focusRingWidth' => array( 'size' => 5, 'unit' => 'px' ),
		'glassBlur'      => array( 'size' => 8, 'unit' => 'px' ),
	)
);
$wpe_output = Kit::render( 'icon', $wpe_elementor );
$wpe_check( false !== strpos( $wpe_output, 'wpek-glass' ) && false !== strpos( $wpe_output, 'wpek-hide-print' ), 'Elementor toggles map to shared presentation classes' );
$wpe_check( false !== strpos( $wpe_output, '--wpek-focus-width:5px' ), 'Elementor focus ring size maps to CSS' );
$wpe_check( false !== strpos( $wpe_output, '--wpek-glass-blur:8px' ), 'Elementor glass blur maps to CSS' );
echo "Shared advanced focus/glass/print Kit regression tests passed.\n";
