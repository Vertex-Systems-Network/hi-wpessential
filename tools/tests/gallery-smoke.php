<?php
/**
 * WordPress + Elementor integration checks for the Gallery kit element.
 *
 * Run on a disposable CI installation with WP-CLI.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $passed, $reason ) {
	if ( ! $passed ) {
		throw new RuntimeException( 'FAIL: ' . $reason );
	}
	echo 'PASS: ' . $reason . "\n";
};
$wpe_count = static function ( $html, $pattern ) {
	return preg_match_all( $pattern, $html, $match );
};
$wpe_block = static function ( $attrs ) {
	return do_blocks( '<!-- wp:wpessential/gallery ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit class is available' );
$wpe_check( isset( Kit::defs()['gallery'] ), 'Gallery definition loaded' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/gallery' ), 'Gutenberg gallery block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-gallery', 'registered' ), 'Gallery stylesheet registered' );
$wpe_check( wp_script_is( 'wpessential-kit-gallery', 'registered' ), 'Gallery lightbox script registered' );

$wpe_items = array(
	array( 'image' => array( 'url' => 'https://example.org/first.jpg' ), 'alt' => 'Coastal home', 'caption' => 'Exterior view' ),
	array( 'image' => array( 'url' => 'https://example.org/second.jpg' ), 'alt' => 'Bright interior', 'caption' => 'Interior view' ),
	array( 'image' => array( 'url' => 'https://example.org/third.jpg' ), 'alt' => 'Garden', 'caption' => 'Garden view', 'link' => 'https://example.org/project' ),
);
$wpe_html = $wpe_block( array( 'items' => $wpe_items ) );
$wpe_check( 3 === $wpe_count( $wpe_html, '/<figure class="wpe-gallery__item">/' ), 'Gutenberg renders three images' );
$wpe_check( 2 === $wpe_count( $wpe_html, '/data-wpe-gallery-open="1"/' ), 'Only non-custom-link images open the lightbox' );
$wpe_check( 1 === $wpe_count( $wpe_html, '/<dialog class="wpe-gallery__dialog"/' ), 'One lightbox dialog per gallery' );
$wpe_check( false !== strpos( $wpe_html, 'href="https://example.org/project"' ), 'Custom image links remain normal hyperlinks' );
$wpe_check( false !== strpos( $wpe_html, 'alt="Coastal home"' ), 'Alt text renders' );
$wpe_check( false !== strpos( $wpe_html, '<figcaption class="wpe-gallery__caption">Exterior view</figcaption>' ), 'Visible caption renders' );
$wpe_check( false !== strpos( $wpe_html, 'aria-label="Next image"' ), 'Lightbox has accessible Next control' );
$wpe_check( false !== strpos( $wpe_html, 'aria-label="Close gallery"' ), 'Lightbox has accessible Close control' );
$wpe_check( false !== strpos( $wpe_html, 'href="https://example.org/first.jpg"' ), 'Non-JS fallback links directly to image' );

$wpe_masonry = $wpe_block( array( 'items' => $wpe_items, 'layout' => 'masonry', 'showCaptions' => false, 'lightbox' => false ) );
$wpe_check( false !== strpos( $wpe_masonry, 'is-masonry' ), 'Masonry variant renders' );
$wpe_check( 0 === $wpe_count( $wpe_masonry, '/<dialog /' ), 'Lightbox toggle disables dialog' );
$wpe_check( 0 === $wpe_count( $wpe_masonry, '/<figcaption /' ), 'Caption toggle hides captions' );
$wpe_check( false === strpos( $wpe_masonry, 'data-wpe-gallery-open' ), 'Disabled lightbox removes gallery openers' );

$wpe_empty = $wpe_block( array( 'items' => array() ) );
$wpe_check( false === strpos( $wpe_empty, 'data-wpe-gallery="1"' ), 'Empty gallery renders no front-end wrapper' );

$wpe_malicious = $wpe_block(
	array(
		'items' => array(
			array(
				'image'   => array( 'url' => 'javascript:alert(1)' ),
				'caption' => 'Invalid URL',
			),
			array(
				'image'   => array( 'url' => 'https://example.org/legit.jpg' ),
				'caption' => '<img src=x onerror=alert(2)>',
				'alt'     => '" onload="alert(3)',
				'link'    => 'javascript:alert(4)',
			),
		),
	)
);
$wpe_check( 1 === $wpe_count( $wpe_malicious, '/<figure class="wpe-gallery__item">/' ), 'Unsafe image protocols rejected' );
$wpe_check( false === strpos( $wpe_malicious, '<img src=x onerror' ), 'Unsafe caption HTML escaped' );
$wpe_check( false === strpos( $wpe_malicious, 'alt="" onload=' ), 'Unsafe alt attributes escaped' );
$wpe_check( false === strpos( $wpe_malicious, 'javascript:' ), 'Unsafe custom links rejected' );

$wpe_no_valid = $wpe_block( array( 'items' => array( array( 'image' => array( 'url' => 'javascript:alert(1)' ) ) ) ) );
$wpe_check( false === strpos( $wpe_no_valid, '<figure' ), 'Gallery drops invalid-only items' );

$wpe_attachment = null;
try {
	$wpe_uploaded = wp_upload_bits( 'wpe-gallery-smoke.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jMwAAAABJRU5ErkJggg==' ) );
	$wpe_check( empty( $wpe_uploaded['error'] ), 'Test image uploaded to disposable WordPress' );
	$wpe_attachment = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'Smoke gallery image',
			'post_status'    => 'inherit',
		),
		$wpe_uploaded['file']
	);
	$wpe_check( $wpe_attachment && ! is_wp_error( $wpe_attachment ), 'Media attachment created' );
	update_post_meta( $wpe_attachment, '_wp_attachment_image_alt', 'Media alt text' );
	$wpe_media = $wpe_block( array( 'items' => array( array( 'image' => array( 'id' => $wpe_attachment ), 'caption' => 'Media caption' ) ) ) );
	$wpe_check( false !== strpos( $wpe_media, 'alt="Media alt text"' ), 'Image inherits media-library alt when custom alt omitted' );
	$wpe_check( false !== strpos( $wpe_media, 'wp-image-' . $wpe_attachment ), 'Media library uses WordPress responsive-image markup' );
} finally {
	if ( $wpe_attachment && ! is_wp_error( $wpe_attachment ) ) {
		wp_delete_attachment( $wpe_attachment, true );
	}
}

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-gallery' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-gallery' === $wpe_widget->get_name(), 'Elementor Gallery widget registered' );
$wpe_check( in_array( 'wpessential-kit-gallery', $wpe_widget->get_script_depends(), true ), 'Elementor declares lightbox script dependency' );

$wpe_mapped = Kit::from_elementor(
	Kit::defs()['gallery'],
	array(
		'items' => array(
			array( 'image' => array( 'id' => 0, 'url' => 'https://example.org/elementor.jpg' ), 'alt' => 'Elementor gallery image', 'caption' => 'Elementor caption', 'link' => '' ),
		),
		'columns'          => array( 'size' => 3, 'unit' => '' ),
		'columns_mobile'   => array( 'size' => 1, 'unit' => '' ),
		'showCaptions'     => 'yes',
		'lightbox'         => 'yes',
	)
);
$wpe_check( 'https://example.org/elementor.jpg' === $wpe_mapped['items'][0]['image']['url'], 'Elementor image repeater maps to gallery image' );
$wpe_check( 3 === $wpe_mapped['columns']['base'] && 1 === $wpe_mapped['columns']['480'], 'Elementor responsive columns map correctly' );
$wpe_check( true === $wpe_mapped['lightbox'], 'Elementor lightbox toggle maps to boolean' );
$wpe_html = Kit::render( 'gallery', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_html, 'Elementor caption' ), 'Elementor content uses the same Gallery render function' );
$wpe_check( false !== strpos( $wpe_html, '--wpek-cols:3' ), 'Responsive column CSS emits' );

echo "Gallery WordPress and Elementor smoke tests passed.\n";
