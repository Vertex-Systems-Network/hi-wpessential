<?php
/**
 * Disposable WordPress runtime smoke tests for Progress Bar.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $description ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo "PASS: " . $description . "\n";
};
$wpe_block = static function ( $attrs ) {
	return do_blocks( '<!-- wp:wpessential/progress-bar ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit class loads' );
$wpe_check( isset( Kit::defs()['progress-bar'] ), 'Progress Bar definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/progress-bar' ), 'Progress Bar Gutenberg block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-progress-bar', 'registered' ), 'Progress Bar stylesheet registered' );
$wpe_check( wp_script_is( 'wpessential-kit-progress-bar', 'registered' ), 'Progress Bar script registered' );

$wpe_default = $wpe_block( array() );
$wpe_check( 3 === preg_match_all( '/role="progressbar"/', $wpe_default ), 'Three default items rendered' );
$wpe_check( false !== strpos( $wpe_default, 'Design' ), 'Default label rendered' );
$wpe_check( false !== strpos( $wpe_default, 'aria-valuenow="90"' ), 'Accessible current value rendered' );
$wpe_check( false !== strpos( $wpe_default, 'aria-valuemax="100"' ), 'Accessible maximum rendered' );
$wpe_check( false !== strpos( $wpe_default, 'aria-valuetext="90% complete"' ), 'Accessible completion percent rendered' );
$wpe_check( false !== strpos( $wpe_default, 'data-target="90"' ), 'Client percent normalized in data attribute' );
$wpe_check( false !== strpos( $wpe_default, '--wpek-target-progress:90%' ), 'Static final progress renders without JavaScript' );
$wpe_check( false !== strpos( $wpe_default, 'aria-labelledby=' ), 'Visible labels bind to progressbars' );

$wpe_circle = $wpe_block(
	array(
		'layout' => 'circle',
		'items' => array(
			array( 'label' => 'Quarter', 'value' => 25, 'max' => 100, 'description' => 'Q1' ),
			array( 'label' => 'Launch', 'value' => 75, 'max' => 120, 'description' => 'Q2' ),
		),
		'showDescription' => true,
		'valueFormat' => 'fraction',
		'columns' => 2,
		'animate' => false,
	)
);
$wpe_check( false !== strpos( $wpe_circle, 'is-circle' ), 'Circular variant selected' );
$wpe_check( 2 === preg_match_all( '/role="progressbar"/', $wpe_circle ), 'Two circle indicators render' );
$wpe_check( false !== strpos( $wpe_circle, 'data-target="62.5"' ), 'Fractional percentage 75/120 normalized' );
$wpe_check( false !== strpos( $wpe_circle, 'aria-valuemax="120"' ), 'Custom maximum exposed accessibly' );
$wpe_check( false !== strpos( $wpe_circle, '75 / 120' ), 'Value fraction displayed' );
$wpe_check( false !== strpos( $wpe_circle, 'Q2' ), 'Item description rendered' );
$wpe_check( false !== strpos( $wpe_circle, 'data-animate="0"' ), 'Animation can be disabled' );
$wpe_check( false !== strpos( $wpe_circle, '--wpek-columns:2' ), 'Layout column control generates CSS' );

$wpe_limits = $wpe_block(
	array(
		'items' => array(
			array( 'label' => 'Over', 'value' => 200, 'max' => 100 ),
			array( 'label' => 'Negative', 'value' => -400, 'max' => 100 ),
			array( 'label' => 'Zero max', 'value' => 45, 'max' => 0 ),
			array( 'label' => 'Invalid', 'value' => '1e999', 'max' => '1e999' ),
		),
	)
);
$wpe_check( false !== strpos( $wpe_limits, 'aria-valuenow="100"' ), 'Over-range values clamp to max' );
$wpe_check( false !== strpos( $wpe_limits, 'data-target="0"' ), 'Negative or invalid values clamp to zero' );
$wpe_check( false !== strpos( $wpe_limits, 'aria-valuemax="1"' ), 'Zero max safely clamps to 1' );
$wpe_check( false === strpos( $wpe_limits, 'INF' ) && false === strpos( $wpe_limits, 'NaN' ), 'Nonfinite values do not leak into output' );

$wpe_hidden = $wpe_block(
	array(
		'items' => array( array( 'label' => 'Hidden label', 'value' => 33, 'max' => 100 ) ),
		'showLabel' => false,
		'showValue' => false,
	)
);
$wpe_check( false !== strpos( $wpe_hidden, 'aria-label="Hidden label"' ), 'Hidden labels still name accessible progressbar' );
$wpe_check( false === strpos( $wpe_hidden, 'wpe-progress__value' ), 'Value toggle hides visible value only' );

$wpe_unsafe = $wpe_block(
	array(
		'items' => array( array(
			'label' => '<img src=x onerror=alert(1)>',
			'description' => '<script>alert(2)</script>',
			'value' => 12,
			'max' => 100,
		) ),
		'showDescription' => true,
	)
);
$wpe_check( false === strpos( $wpe_unsafe, '<img src=x' ), 'Unsafe label HTML is escaped' );
$wpe_check( false === strpos( $wpe_unsafe, '<script>' ), 'Unsafe descriptions are escaped' );
$wpe_check( false === strpos( $wpe_unsafe, ' onerror=' ), 'Injection cannot become an event-handler attribute' );

$wpe_empty = $wpe_block( array( 'items' => array() ) );
$wpe_check( false === strpos( $wpe_empty, 'role="progressbar"' ), 'Empty repeater has no indicators' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-progress-bar' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-progress-bar' === $wpe_widget->get_name(), 'Elementor Progress Bar widget registered' );
$wpe_check( in_array( 'wpessential-kit-progress-bar', $wpe_widget->get_script_depends(), true ), 'Elementor declares progress animation dependency' );

$wpe_settings = Kit::from_elementor( Kit::defs()['progress-bar'], array(
	'items' => array( array(
		'label' => 'Elementor task',
		'value' => array( 'size' => 35, 'unit' => '' ),
		'max' => array( 'size' => 70, 'unit' => '' ),
		'description' => 'From Elementor repeater',
	) ),
	'layout' => 'horizontal',
	'showValue' => 'yes',
	'valueFormat' => 'percent',
	'animate' => 'yes',
	'duration' => array( 'size' => 1300, 'unit' => 'ms' ),
) );
$wpe_check( 35 === $wpe_settings['items'][0]['value'], 'Elementor repeater slider maps to number' );
$wpe_check( true === $wpe_settings['animate'], 'Elementor toggle maps to boolean' );
$wpe_check( 1300 === $wpe_settings['duration'], 'Elementor duration maps to number' );
$wpe_elementor = Kit::render( 'progress-bar', $wpe_settings );
$wpe_check( false !== strpos( $wpe_elementor, 'Elementor task' ), 'Elementor content rendered by shared PHP template' );
$wpe_check( false !== strpos( $wpe_elementor, 'data-target="50"' ), 'Elementor value/max maps to 50 percent' );

echo "Progress Bar WordPress and Elementor smoke tests passed.\n";
