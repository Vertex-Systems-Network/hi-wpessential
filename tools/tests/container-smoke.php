<?php
/**
 * Disposable WordPress runtime smoke test for Container, nested Gutenberg blocks
 * and an Elementor rich-content widget.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $condition, $description ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};

$wpe_check( class_exists( Kit::class ), 'Kit class loads' );
$wpe_check( isset( Kit::defs()['container'] ), 'Container definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/container' ), 'Gutenberg Container registered' );
$wpe_check( wp_style_is( 'wpessential-kit-container', 'registered' ), 'Container CSS registered' );

$wpe_block = static function ( $attributes, $inner = '' ) {
	return do_blocks( '<!-- wp:wpessential/container ' . wp_json_encode( (object) $attributes ) . ' -->' . $inner . '<!-- /wp:wpessential/container -->' );
};
$wpe_paragraph = '<!-- wp:paragraph --><p>First nested paragraph</p><!-- /wp:paragraph -->';
$wpe_heading = '<!-- wp:heading --><h2 class="wp-block-heading">Second nested heading</h2><!-- /wp:heading -->';
$wpe_html = $wpe_block(
	array(
		'layout'     => 'grid',
		'columns'    => array( 'base' => 3, '480' => 1 ),
		'gap'        => array( 'base' => 18 ),
		'rowGap'     => array( 'base' => 12 ),
		'contentWidth' => 'boxed',
		'tag'        => 'section',
		'ariaLabel'  => 'Project highlights',
	),
	$wpe_paragraph . $wpe_heading
);
$wpe_check( false !== strpos( $wpe_html, '<section id="wpe-container-' ), 'Container uses allowed semantic HTML tag' );
$wpe_check( false !== strpos( $wpe_html, 'aria-label="Project highlights"' ), 'Section accessible label renders' );
$wpe_check( false !== strpos( $wpe_html, 'is-grid' ), 'Grid layout class emitted' );
$wpe_check( false !== strpos( $wpe_html, 'First nested paragraph' ), 'First child Gutenberg block preserved' );
$wpe_check( false !== strpos( $wpe_html, 'Second nested heading' ), 'Second child Gutenberg block preserved' );
$wpe_check( false !== strpos( $wpe_html, '<div class="wpe-container__inner">' ), 'Inner wrapper surrounds child blocks' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-cols:3' ), 'Desktop columns CSS generated' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-cols:1' ), 'Mobile columns CSS generated' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-gap:18px' ), 'Column gap CSS generated' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-row-gap:12px' ), 'Row gap CSS generated' );

$wpe_nested = $wpe_block( array( 'layout' => 'flex' ), '<!-- wp:wpessential/container {"layout":"grid","columns":{"base":2}} -->' . $wpe_paragraph . '<!-- /wp:wpessential/container -->' );
$wpe_check( 2 === preg_match_all( '/class="wpe-container is-/', $wpe_nested, $matches ), 'Containers can nest inside other containers' );
$wpe_check( false !== strpos( $wpe_nested, 'First nested paragraph' ), 'Nested container preserves grandchild blocks' );

$wpe_safe = $wpe_block(
	array(
		'tag'       => 'script',
		'ariaLabel' => 'Label" onmouseover="alert(1)',
		'content'   => '<p><strong>Rich text</strong></p><script>alert(5)</script><img src="x" onerror="alert(2)">',
		'overflow'  => 'not-supported',
	)
);
$wpe_check( false !== strpos( $wpe_safe, '<div id="wpe-container-' ), 'Invalid semantic tags fall back to div' );
$wpe_check( false !== strpos( $wpe_safe, 'role="group"' ), 'Named div receives meaningful group role' );
$wpe_check( false === strpos( $wpe_safe, '<script>' ), 'Rich content removes script tags' );
$wpe_check( false === strpos( $wpe_safe, 'onerror=' ), 'Rich content removes event handlers' );
$wpe_check( false === strpos( $wpe_safe, 'onmouseover=' ), 'Accessible label escapes injected attributes' );
$wpe_check( false !== strpos( $wpe_safe, 'is-overflow-visible' ), 'Overflow falls back to allowed style' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-container' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-container' === $wpe_widget->get_name(), 'Elementor Container widget registered' );
$wpe_mapped = Kit::from_elementor(
	Kit::defs()['container'],
	array(
		'content'         => '<p><em>Elementor rich content</em></p>',
		'layout'          => 'flex',
		'direction'       => 'row',
		'direction_mobile'=> 'column',
		'gap'             => array( 'size' => 24, 'unit' => 'px' ),
		'columns'         => array( 'size' => 3, 'unit' => '' ),
		'contentWidth'    => 'full',
		'equalChildren'   => 'yes',
	)
);
$wpe_check( 'row' === $wpe_mapped['direction']['base'] && 'column' === $wpe_mapped['direction']['480'], 'Elementor responsive direction maps to breakpoints' );
$wpe_check( true === $wpe_mapped['equalChildren'], 'Elementor switcher maps to boolean' );
$wpe_check( 24 === $wpe_mapped['gap']['base'], 'Elementor gap slider maps to number' );
$wpe_elementor = Kit::render( 'container', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_elementor, '<em>Elementor rich content</em>' ), 'Elementor content renders in shared template' );
$wpe_check( false !== strpos( $wpe_elementor, 'is-content-full' ), 'Elementor full-width class is emitted' );
$wpe_check( false !== strpos( $wpe_elementor, '--wpek-direction:column' ), 'Elementor mobile direction emits CSS' );

// Server-rendered nested child HTML must always win over the Elementor content field.
$wpe_override = Kit::render( 'container', array( 'content' => '<p>Fallback only</p>' ), '<p>Actual Gutenberg child</p>' );
$wpe_check( false !== strpos( $wpe_override, 'Actual Gutenberg child' ), 'InnerBlocks markup takes precedence over widget fallback' );
$wpe_check( false === strpos( $wpe_override, 'Fallback only' ), 'Nested block render does not duplicate widget fallback text' );

echo "Container WordPress and Elementor integration smoke tests passed.\n";
