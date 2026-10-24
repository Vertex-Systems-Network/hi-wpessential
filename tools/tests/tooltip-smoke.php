<?php
/**
 * Integration smoke tests for the Interactive Kit element.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $description ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};
$wpe_block = static function ( array $attrs ) {
	return do_blocks( '<!-- wp:wpessential/tooltip ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit class loads' );
$wpe_check( isset( Kit::defs()['tooltip'] ), 'Interactive definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/tooltip' ), 'Gutenberg Interactive block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-tooltip', 'registered' ), 'Interactive CSS registered' );
$wpe_check( wp_script_is( 'wpessential-kit-tooltip', 'registered' ), 'Interactive JavaScript registered' );

$wpe_default = $wpe_block( array() );
$wpe_check( false !== strpos( $wpe_default, 'data-wpe-interactive="tooltip"' ), 'Default tooltip mode renders' );
$wpe_check( false !== strpos( $wpe_default, 'role="tooltip"' ), 'Semantic tooltip description available' );
$wpe_check( false !== strpos( $wpe_default, 'aria-describedby="wpe-tooltip-' ), 'Accessible trigger associates explanation' );
$wpe_check( false !== strpos( $wpe_default, 'Every plan includes' ), 'Tooltip text renders without requiring JavaScript' );
$wpe_check( false === strpos( $wpe_default, 'onclick=' ), 'Tooltip uses no inline JavaScript' );

$wpe_tooltip = $wpe_block(
	array(
		'trigger'  => 'Why choose this?',
		'tipText'  => '<img src=x onerror=alert(1)> Helpful description',
		'position' => 'end',
	)
);
$wpe_check( false !== strpos( $wpe_tooltip, 'is-position-end' ), 'Tooltip placement setting renders' );
$wpe_check( false !== strpos( $wpe_tooltip, 'Helpful description' ), 'Tooltip description remains readable' );
$wpe_check( false === strpos( $wpe_tooltip, '<img src=x' ), 'Tooltip HTML injection is escaped' );
$wpe_check( false === strpos( $wpe_tooltip, ' onerror="' ), 'Tooltip does not expose executable event handlers' );
$wpe_check( false !== strpos( $wpe_tooltip, 'Why choose this?' ), 'Custom trigger label renders' );

$wpe_disclosure = $wpe_block(
	array(
		'mode'            => 'showmore',
		'summary'         => '<p><strong>Read the introduction.</strong></p>',
		'expandedContent' => '<p>Details <em>included</em>.</p><script>alert(2)</script><img src="x" onload="alert(3)">',
		'moreLabel'       => 'Details',
		'lessLabel'       => 'Hide details',
		'expanded'        => true,
	)
);
$wpe_check( false !== strpos( $wpe_disclosure, '<details class="wpe-tooltip__details" open>' ), 'Disclosure starts expanded when requested' );
$wpe_check( false !== strpos( $wpe_disclosure, '<summary class="wpe-tooltip__summary">' ), 'Native details/summary used without JavaScript' );
$wpe_check( false !== strpos( $wpe_disclosure, '<strong>Read the introduction.</strong>' ), 'Safe intro rich text preserved' );
$wpe_check( false !== strpos( $wpe_disclosure, '<em>included</em>' ), 'Safe expanded rich text preserved' );
$wpe_check( false !== strpos( $wpe_disclosure, 'Hide details' ), 'Collapse text rendered' );
$wpe_check( false === strpos( $wpe_disclosure, '<script>' ), 'Rich text scripts rejected' );
$wpe_check( false === strpos( $wpe_disclosure, ' onload=' ), 'Rich text event handler stripped' );

$wpe_closed = $wpe_block( array( 'mode' => 'showmore', 'expanded' => false ) );
$wpe_check( false !== strpos( $wpe_closed, '<details class="wpe-tooltip__details">' ), 'Disclosure starts closed by default' );

$wpe_switcher = $wpe_block(
	array(
		'mode'          => 'switcher',
		'firstTitle'    => 'Monthly',
		'secondTitle'   => 'Yearly',
		'firstContent'  => '<p>Pay month by month</p>',
		'secondContent' => '<p>Pay yearly <strong>and save</strong></p>',
		'defaultTab'    => 'second',
	)
);
$wpe_check( false !== strpos( $wpe_switcher, 'data-wpe-interactive="switcher"' ), 'Switcher mode renders' );
$wpe_check( false !== strpos( $wpe_switcher, 'data-initial="second"' ), 'Selected initial panel provided to enhancement' );
$wpe_check( 2 === preg_match_all( '/data-switch-to=/', $wpe_switcher ), 'Switcher has two tab controls' );
$wpe_check( 2 === preg_match_all( '/data-switch-panel=/', $wpe_switcher ), 'Both switcher panels render in server HTML' );
$wpe_check( false !== strpos( $wpe_switcher, 'Pay month by month' ), 'First switcher panel visible in no-JS markup' );
$wpe_check( false !== strpos( $wpe_switcher, 'Pay yearly' ), 'Second switcher panel visible in no-JS markup' );
$wpe_check( false === strpos( $wpe_switcher, 'aria-selected="' ), 'Tab semantics applied only after JavaScript is active' );
$wpe_check( false === strpos( $wpe_switcher, ' hidden>' ), 'No-JavaScript fallback retains both panels' );
$wpe_check( false !== strpos( $wpe_switcher, 'data-switch-label="Content options"' ), 'Tab group has translated accessible label' );

$wpe_bad = $wpe_block(
	array(
		'mode'          => 'switcher',
		'firstTitle'    => '" onclick="alert(3)',
		'firstContent'  => '<script>alert(4)</script><p>Safe copy</p>',
		'secondContent' => '<iframe src="javascript:alert(5)"></iframe>',
	)
);
$wpe_check( false === strpos( $wpe_bad, ' onclick="' ), 'Switcher tab title cannot inject executable attributes' );
$wpe_check( false === strpos( $wpe_bad, '<script>' ) && false === strpos( $wpe_bad, '<iframe' ), 'Switcher HTML sanitizes scripts and iframes' );
$wpe_check( false !== strpos( $wpe_bad, 'Safe copy' ), 'Safe switcher text preserved' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-tooltip' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-tooltip' === $wpe_widget->get_name(), 'Elementor Interactive widget registered' );
$wpe_check( in_array( 'wpessential-kit-tooltip', $wpe_widget->get_script_depends(), true ), 'Elementor declares Interactive JavaScript dependency' );

$wpe_mapped = Kit::from_elementor(
	Kit::defs()['tooltip'],
	array(
		'mode'       => 'switcher',
		'firstTitle' => 'Basic',
		'firstContent' => '<p>Elementor first</p>',
		'secondContent' => '<p>Elementor second</p>',
		'expanded' => 'yes',
		'maxWidth' => array( 'size' => 650, 'unit' => 'px' ),
		'maxWidth_mobile' => array( 'size' => 300, 'unit' => 'px' ),
	)
);
$wpe_check( true === $wpe_mapped['expanded'], 'Elementor switcher maps to boolean' );
$wpe_check( 650 === $wpe_mapped['maxWidth']['base'] && 300 === $wpe_mapped['maxWidth']['480'], 'Responsive Elementor max width maps correctly' );
$wpe_elementor = Kit::render( 'tooltip', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_elementor, 'Elementor second' ), 'Elementor shares PHP renderer' );
$wpe_check( false !== strpos( $wpe_elementor, '--wpek-interactive-max:650px' ), 'Elementor sizing outputs responsive Kit CSS' );

echo "Tooltip, disclosure and switcher WordPress/Elementor tests passed.\n";
