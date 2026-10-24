<?php
/**
 * WordPress runtime smoke tests for the Counter kit element.
 *
 * Usage: wp eval-file tools/tests/counter-smoke.php --path=/path/to/wordpress
 * Runs only on a temporary CI WordPress installation.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $condition, $description ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo "PASS: " . $description . "\n";
};

$wpe_check( class_exists( Kit::class ), 'WPEssential Kit class loads' );
$wpe_check( isset( Kit::defs()['counter'] ), 'Counter definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/counter' ), 'Gutenberg Counter block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-counter', 'registered' ), 'Counter styles registered' );
$wpe_check( wp_script_is( 'wpessential-kit-counter', 'registered' ), 'Counter animation registered' );

$wpe_block = static function ( array $attrs ) {
	return do_blocks( '<!-- wp:wpessential/counter ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

$wpe_html = $wpe_block( array() );
$wpe_check( false !== strpos( $wpe_html, 'data-wpe-counter="1"' ), 'Default Counter renders' );
$wpe_check( false !== strpos( $wpe_html, '1,250' ), 'Default grouping uses commas' );
$wpe_check( false !== strpos( $wpe_html, 'Projects delivered' ), 'Default label renders' );
$wpe_check( false !== strpos( $wpe_html, 'class="screen-reader-text"' ), 'Finished value is available to screen readers' );
$wpe_check( false !== strpos( $wpe_html, 'data-wpe-animate="1"' ), 'Animation defaults to enabled' );

$wpe_custom = $wpe_block(
	array(
		'start'       => '-50',
		'end'         => '12345.678',
		'decimals'    => 2,
		'thousands'   => 'dot',
		'decimalMark' => 'comma',
		'prefix'      => '€',
		'suffix'      => '+',
		'label'       => 'Customers',
		'animate'     => false,
		'duration'    => 2400,
	)
);
$wpe_check( false !== strpos( $wpe_custom, '12.345,68' ), 'Locale-style separators and rounding work' );
$wpe_check( false !== strpos( $wpe_custom, '€12.345,68+' ), 'Accessible finished value includes prefix/suffix' );
$wpe_check( false !== strpos( $wpe_custom, 'data-wpe-start="-50"' ), 'Negative animation start supported' );
$wpe_check( false !== strpos( $wpe_custom, 'data-wpe-duration="2400"' ), 'Duration emitted as sanitized data' );
$wpe_check( false !== strpos( $wpe_custom, 'data-wpe-animate="0"' ), 'Animation toggle disables client animation' );

$wpe_same = $wpe_block(
	array(
		'end'         => '12345.67',
		'decimals'    => 2,
		'thousands'   => 'comma',
		'decimalMark' => 'comma',
	)
);
$wpe_check( false !== strpos( $wpe_same, '12345,67' ), 'Identical separators do not produce ambiguous numbers' );
$wpe_check( false === strpos( $wpe_same, '12,345,67' ), 'Conflicting thousands separator is suppressed' );

$wpe_safe = $wpe_block(
	array(
		'end'         => '1e999',
		'decimals'    => 999,
		'prefix'      => '<img src=x onerror=alert(1)>',
		'suffix'      => '<script>alert(2)</script>',
		'label'       => '<b>unsafe</b>',
		'description' => '<svg onload=alert(3)>',
	)
);
$wpe_check( false === strpos( $wpe_safe, '<img' ), 'Prefix markup is escaped' );
$wpe_check( false === strpos( $wpe_safe, '<script' ), 'Suffix markup is escaped' );
$wpe_check( false === strpos( $wpe_safe, '<svg' ), 'Description markup is escaped' );
$wpe_check( false !== strpos( $wpe_safe, 'data-wpe-end="0"' ), 'Non-finite end value clamps to zero' );
$wpe_check( false !== strpos( $wpe_safe, 'data-wpe-decimals="4"' ), 'Decimals clamp to maximum of four' );

$wpe_negative = $wpe_block( array( 'end' => '-9876.5', 'decimals' => 1, 'suffix' => '' ) );
$wpe_check( false !== strpos( $wpe_negative, '-9,876.5' ), 'Negative final values format correctly' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-counter' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-counter' === $wpe_widget->get_name(), 'Elementor Counter widget registered' );
$wpe_check( in_array( 'wpessential-kit-counter', $wpe_widget->get_script_depends(), true ), 'Elementor declares Counter animation dependency' );

$wpe_mapped = Kit::from_elementor(
	Kit::defs()['counter'],
	array(
		'start'            => '0',
		'end'              => '2400',
		'decimals'         => array( 'size' => 0, 'unit' => '' ),
		'duration'         => array( 'size' => 1800, 'unit' => 'ms' ),
		'animate'          => 'yes',
		'alignment'        => 'center',
		'alignment_mobile' => 'end',
	)
);
$wpe_check( 0 === $wpe_mapped['decimals'], 'Elementor decimal-count slider maps to number' );
$wpe_check( 1800 === $wpe_mapped['duration'], 'Elementor duration slider maps to number' );
$wpe_check( true === $wpe_mapped['animate'], 'Elementor animate switcher maps to boolean' );
$wpe_check( 'center' === $wpe_mapped['alignment']['base'] && 'end' === $wpe_mapped['alignment']['480'], 'Elementor responsive alignment maps to breakpoint' );
$wpe_markup = Kit::render( 'counter', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_markup, '2,400' ), 'Elementor settings render through shared Counter template' );
$wpe_check( false !== strpos( $wpe_markup, '--wpek-justify:center' ), 'Responsive alignment renders CSS map' );

echo "Counter WordPress and Elementor integration smoke tests passed.\n";
