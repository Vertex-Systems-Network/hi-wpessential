<?php
/**
 * Disposable WordPress smoke tests for Code, HTML and oEmbed.
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
$wpe_block = static function ( array $attributes ) {
	return do_blocks( '<!-- wp:wpessential/code ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit loaded' );
$wpe_check( isset( Kit::defs()['code'] ), 'Code definition registered' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/code' ), 'Gutenberg Code block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-code', 'registered' ), 'Code stylesheet registered' );
$wpe_check( wp_script_is( 'wpessential-kit-code', 'registered' ), 'Copy JS registered' );

$wpe_default = $wpe_block( array() );
$wpe_check( false !== strpos( $wpe_default, 'rich content' ), 'HTML mode renders default rich text' );
$wpe_check( false !== strpos( $wpe_default, '<strong>' ), 'Safe HTML formatting preserved' );

$wpe_html = $wpe_block( array(
	'mode' => 'html',
	'html' => '<p>Allowed <em>markup</em></p><script>alert(1)</script><img src="https://example.org/image.png" onerror="alert(2)">',
) );
$wpe_check( false !== strpos( $wpe_html, '<em>markup</em>' ), 'Allowed rich HTML persists' );
$wpe_check( false === strpos( $wpe_html, '<script>' ), 'Scripts are removed from rich HTML' );
$wpe_check( false === strpos( $wpe_html, ' onerror=' ), 'Unsafe HTML event handlers stripped' );

$wpe_code = $wpe_block( array(
	'mode' => 'code',
	'code' => '<script>alert("safe");</script>' . "\n" . '<div onclick="bad()">Example</div>',
	'language' => 'javascript',
	'copyLabel' => 'Copy snippet',
	'showCopy' => true,
	'wrapCode' => true,
) );
$wpe_check( false !== strpos( $wpe_code, 'wpe-code__source language-javascript' ), 'Language class supported' );
$wpe_check( false !== strpos( $wpe_code, '&lt;script&gt;' ), 'Code appears escaped as text' );
$wpe_check( false === strpos( $wpe_code, '<script>' ), 'Code is never executed in HTML' );
$wpe_check( false !== strpos( $wpe_code, 'Copy snippet' ), 'Copy label displayed' );
$wpe_check( false !== strpos( $wpe_code, 'a-wrap-yes' ), 'Code wrap setting reflected in CSS class' );
$wpe_check( false !== strpos( $wpe_code, 'role="status"' ), 'Accessible clipboard status exists' );

$wpe_no_copy = $wpe_block( array(
	'mode' => 'code', 'code' => 'abc', 'showCopy' => false, 'showLanguage' => false,
) );
$wpe_check( false === strpos( $wpe_no_copy, 'wpe-code__copy"' ), 'Copy toggle hides button' );
$wpe_check( false === strpos( $wpe_no_copy, 'wpe-code__toolbar"' ), 'Empty toolbar omitted' );
$wpe_check( false !== strpos( $wpe_no_copy, '>abc</code>' ), 'Code still readable without controls' );

$wpe_invalid = $wpe_block( array( 'mode' => 'embed', 'embedUrl' => 'https://example.net/watch?v=123', 'embedTitle' => '<script>Test</script>' ) );
$wpe_check( false === strpos( $wpe_invalid, '<iframe' ), 'Untrusted oEmbed provider cannot create iframe' );
$wpe_check( false !== strpos( $wpe_invalid, 'example.net' ), 'Fallback link still available for untrusted hosts' );
$wpe_check( false === strpos( $wpe_invalid, '<script>' ), 'Embed fallback label escaped' );
$wpe_unsafe = $wpe_block( array( 'mode' => 'embed', 'embedUrl' => 'javascript:alert(3)' ) );
$wpe_check( false === strpos( $wpe_unsafe, '<iframe' ) && false === strpos( $wpe_unsafe, 'javascript:' ), 'Invalid embed protocol rejected' );

$wpe_provider = static function ( $result, $url ) {
	if ( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' === $url ) {
		return '<iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ" title="Test video" width="560" height="315" onload="alert(2)"></iframe><script>alert(1)</script>';
	}
	return $result;
};
add_filter( 'pre_oembed_result', $wpe_provider, 10, 2 );
try {
	$wpe_embed = $wpe_block( array( 'mode' => 'embed', 'embedUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' ) );
	$wpe_check( false !== strpos( $wpe_embed, 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ), 'Approved WordPress oEmbed renders iframe' );
	$wpe_check( false === strpos( $wpe_embed, 'onload=' ), 'Sanitized oEmbed strips event handlers' );
	$wpe_check( false === strpos( $wpe_embed, '<script>' ), 'Sanitized oEmbed strips scripts' );
} finally {
	remove_filter( 'pre_oembed_result', $wpe_provider, 10 );
}

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-code' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-code' === $wpe_widget->get_name(), 'Elementor Code widget registered' );
$wpe_check( in_array( 'wpessential-kit-code', $wpe_widget->get_script_depends(), true ), 'Elementor loads copy script' );

$wpe_mapped = Kit::from_elementor( Kit::defs()['code'], array(
	'mode' => 'code',
	'code' => 'Elementor code',
	'language' => 'text',
	'showCopy' => 'yes',
	'maxWidth' => array( 'size' => 720, 'unit' => 'px' ),
) );
$wpe_check( true === $wpe_mapped['showCopy'], 'Elementor switcher maps correctly' );
$wpe_check( 720 === $wpe_mapped['maxWidth']['base'], 'Elementor responsive width slider maps correctly' );
$wpe_result = Kit::render( 'code', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_result, 'Elementor code' ), 'Elementor renderer shares Code template' );
$wpe_check( false !== strpos( $wpe_result, '--wpek-content-width:720px' ), 'Responsive width CSS generated' );
echo "Code / HTML / Embed WordPress and Elementor smoke tests passed.\n";
