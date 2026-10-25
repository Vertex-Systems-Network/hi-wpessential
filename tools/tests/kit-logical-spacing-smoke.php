<?php
/**
 * RTL-aware common Kit logical spacing, Gutenberg and Elementor smoke tests.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
};
$wpe_controls = array(
	'logicalMarginStart' => 'margin-inline-start',
	'logicalMarginEnd' => 'margin-inline-end',
	'logicalPaddingStart' => 'padding-inline-start',
	'logicalPaddingEnd' => 'padding-inline-end',
);
foreach ( $wpe_controls as $wpe_key => $wpe_prop ) {
	$wpe_attr = Kit::attributes( Kit::defs()['icon'] )[ $wpe_key ] ?? null;
	$wpe_assert( is_array( $wpe_attr ) && ! empty( $wpe_attr['responsive'] ) && $wpe_prop === $wpe_attr['css'], 'Shared responsive logical field: ' . $wpe_key );
}
$wpe_html = Kit::render(
	'icon',
	array(
		'logicalMarginStart' => array( 'base' => 14, '768' => 23 ),
		'logicalMarginEnd' => array( 'base' => -5 ),
		'logicalPaddingStart' => array( 'base' => 7 ),
		'logicalPaddingEnd' => array( 'base' => 9 ),
	)
);
foreach ( array( 'margin-inline-start:14px', 'margin-inline-end:-5px', 'padding-inline-start:7px', 'padding-inline-end:9px' ) as $wpe_part ) {
	$wpe_assert( false !== strpos( $wpe_html, $wpe_part ), 'Logical CSS rendered: ' . $wpe_part );
}
$wpe_assert( false !== strpos( $wpe_html, 'margin-inline-start:23px' ) && false !== strpos( $wpe_html, '@media (max-width: 1023px)' ), 'Responsive margin is constrained to tablet width' );
$wpe_default = Kit::render( 'icon', array() );
$wpe_assert( false === strpos( $wpe_default, 'margin-inline-start:' ), 'Default styling unchanged when logical spacing is unset' );
$wpe_mapped = Kit::from_elementor(
	Kit::defs()['icon'],
	array(
		'logicalMarginStart' => array( 'size' => 12, 'unit' => 'px' ),
		'logicalPaddingEnd' => array( 'size' => 2.5, 'unit' => 'rem' ),
	)
);
$wpe_from_widget = Kit::render( 'icon', $wpe_mapped );
$wpe_assert( false !== strpos( $wpe_from_widget, 'margin-inline-start:12px' ), 'Elementor maps logical start margin' );
$wpe_assert( false !== strpos( $wpe_from_widget, 'padding-inline-end:2.5rem' ), 'Elementor preserves responsive logical units' );
echo "Kit logical RTL spacing tests passed.\n";
