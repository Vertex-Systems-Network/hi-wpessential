<?php
/**
 * CSS container queries, fluid spacing and theme tokens shared Kit regression.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $condition, $label ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	echo 'PASS: ' . $label . "\n";
};

$wpe_fields = Kit::attributes( Kit::defs()['icon'] );
foreach ( array( 'containerQuerySource', 'containerQueryHideBelow', 'containerQueryHideAbove', 'fluidPaddingMin', 'fluidPaddingMax', 'fluidGapMin', 'fluidGapMax', 'paddingToken', 'radiusToken' ) as $wpe_key ) {
	$wpe_check( isset( $wpe_fields[ $wpe_key ] ), 'Common field ' . $wpe_key );
}
$wpe_markup = Kit::render(
	'icon',
	array(
		'containerQuerySource'    => true,
		'containerQueryHideBelow' => 320,
		'containerQueryHideAbove' => 960,
		'fluidPaddingMin'         => 8,
		'fluidPaddingMax'         => 32,
		'fluidGapMin'             => 4,
		'fluidGapMax'             => 16,
		'paddingToken'            => 'sm',
		'radiusToken'             => 'md',
	)
);
$wpe_check( false !== strpos( $wpe_markup, 'wpek-query-container' ), 'Source container class emitted' );
$wpe_check( false !== strpos( $wpe_markup, '@container (max-width:320px)' ), 'Query hide below generated' );
$wpe_check( false !== strpos( $wpe_markup, '@container (min-width:960px)' ), 'Query hide above generated' );
$wpe_check( false !== strpos( $wpe_markup, 'padding:clamp(8px,' ), 'Fluid padding with clamp generated' );
$wpe_check( false !== strpos( $wpe_markup, 'gap:clamp(4px,' ), 'Fluid gap with clamp generated' );
$wpe_check( false !== strpos( $wpe_markup, 'padding:var(--wp--preset--spacing--20,8px)!important' ), 'Actual theme padding token emitted' );
$wpe_check( false !== strpos( $wpe_markup, 'border-radius:var(--wp--custom--radius--md,10px)!important' ), 'Actual theme radius token emitted' );
$wpe_invalid = Kit::render(
	'icon',
	array(
		'containerQueryHideBelow' => '9999};body{opacity:0',
		'containerQueryHideAbove' => -1,
		'fluidPaddingMin'         => 50,
		'fluidPaddingMax'         => 4,
		'fluidGapMin'             => 'calc(1px + 999px)',
		'fluidGapMax'             => 20,
	)
);
$wpe_check( false === strpos( $wpe_invalid, '@container (' ), 'Invalid/unbounded query values rejected' );
$wpe_check( false === strpos( $wpe_invalid, 'padding:clamp(' ) && false === strpos( $wpe_invalid, 'gap:clamp(' ), 'Invalid/misaligned fluid values rejected' );
$wpe_default = Kit::render( 'icon', array() );
$wpe_check( false === strpos( $wpe_default, '@container (' ) && false === strpos( $wpe_default, 'wpek-query-container' ), 'Default saved widgets remain unchanged' );

$wpe_elem = Kit::from_elementor(
	Kit::defs()['icon'],
	array(
		'containerQuerySource'    => 'yes',
		'containerQueryHideBelow' => array( 'size' => 320, 'unit' => 'px' ),
		'fluidPaddingMin'         => array( 'size' => 8, 'unit' => 'px' ),
		'fluidPaddingMax'         => array( 'size' => 32, 'unit' => 'px' ),
		'paddingToken'            => 'lg',
		'radiusToken'             => 'sm',
	)
);
$wpe_elem_markup = Kit::render( 'icon', $wpe_elem );
$wpe_check( false !== strpos( $wpe_elem_markup, 'wpek-query-container' ), 'Elementor container switcher mapped' );
$wpe_check( false !== strpos( $wpe_elem_markup, '@container (max-width:320px)' ), 'Elementor query range mapped' );
$wpe_check( false !== strpos( $wpe_elem_markup, 'padding:clamp(8px,' ), 'Elementor fluid values mapped' );
$wpe_check( false !== strpos( $wpe_elem_markup, 'spacing--50,24px)' ), 'Elementor theme token mapped' );
echo "Kit container/fluid/theme token smoke checks passed.\n";
