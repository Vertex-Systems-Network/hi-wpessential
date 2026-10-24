<?php
/**
 * Runtime smoke tests for the Testimonial kit element.
 *
 * Usage: wp eval-file tools/tests/testimonial-smoke.php --path=/path/to/wordpress
 * Run on a temporary WordPress site.
 */

use WPEssential\Core\Kit;

$check = static function ( $condition, $description ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo "PASS: " . $description . "\n";
};

$check( class_exists( Kit::class ), 'Kit class loads' );
$check( isset( Kit::defs()['testimonial'] ), 'Testimonial definition loads' );
$check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/testimonial' ), 'Gutenberg Testimonial registered' );
$check( wp_style_is( 'wpessential-kit-testimonial', 'registered' ), 'Testimonial CSS registered' );

$block = static function ( $attrs ) {
	return do_blocks( '<!-- wp:wpessential/testimonial ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};
$default = $block( array() );
$check( strpos( $default, '<figure' ) !== false, 'Default renders a figure' );
$check( strpos( $default, '<blockquote' ) !== false, 'Default renders an accessible quote' );
$check( strpos( $default, '<figcaption' ) !== false, 'Default renders a customer caption' );
$check( strpos( $default, 'Alex Morgan' ) !== false, 'Default customer name rendered' );
$check( strpos( $default, 'Rating: 5 out of 5' ) !== false, 'Star rating has screen-reader label' );
$check( strpos( $default, 'aria-hidden="true"' ) !== false, 'Decorative rating hidden from assistive tech' );

$custom = $block( array(
	'quote' => '<p>Fast service.</p><script>alert(1)</script>',
	'name' => '<script>bad</script>Casey & Co',
	'role' => 'Engineer',
	'company' => 'Design Studio',
	'showRating' => false,
	'quoteMark' => false,
	'profileUrl' => 'https://example.org/profile',
	'newTab' => true,
	'avatar' => array( 'url' => 'javascript:alert(1)' ),
) );
$check( strpos( $custom, 'Fast service.' ) !== false, 'Custom quote renders' );
$check( strpos( $custom, '<script>' ) === false, 'Quote and name scripts are escaped or stripped' );
$check( strpos( $custom, '<span class="wpe-testimonial__stars"' ) === false, 'Rating toggle hides stars' );
$check( strpos( $custom, 'wpe-testimonial__mark' ) === false, 'Quote-mark toggle hides decoration' );
$check( strpos( $custom, 'javascript:' ) === false, 'Unsafe avatar protocol is rejected' );
$check( strpos( $custom, 'rel="noopener noreferrer"' ) !== false, 'External profile uses safe rel' );
$check( strpos( $custom, 'Engineer, Design Studio' ) !== false, 'Role and company render correctly' );

$no_author = $block( array( 'quote' => '<p>Only quote</p>', 'name' => '', 'role' => '', 'company' => '', 'showRating' => false ) );
$check( strpos( $no_author, 'wpe-testimonial__author' ) === false, 'No empty figcaption renders' );

$clamped = $block( array( 'rating' => 100, 'showRating' => true ) );
$check( strpos( $clamped, 'Rating: 5 out of 5' ) !== false, 'Rating clamps to 5' );

$remote_avatar = $block( array( 'avatar' => array( 'url' => 'https://example.org/avatar.jpg' ) ) );
$check( strpos( $remote_avatar, 'src="https://example.org/avatar.jpg"' ) !== false, 'HTTPS avatar URL renders' );
$check( strpos( $remote_avatar, 'alt=""' ) !== false, 'Avatar is decorative with visible name' );

$widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-testimonial' ) : null;
$check( is_object( $widget ) && 'wpe-testimonial' === $widget->get_name(), 'Elementor Testimonial widget registered' );

$settings = array(
	'quote' => '<p>Elementor says hello.</p>',
	'name' => 'Sam',
	'rating' => array( 'size' => 4, 'unit' => '' ),
	'avatar' => array( 'url' => 'https://example.org/sam.jpg', 'id' => 0 ),
	'showRating' => 'yes',
	'align' => 'center',
	'align_mobile' => 'end',
);
$mapped = Kit::from_elementor( Kit::defs()['testimonial'], $settings );
$check( 4 === $mapped['rating'], 'Elementor rating range maps to number' );
$check( true === $mapped['showRating'], 'Elementor toggle maps to boolean' );
$check( 'center' === $mapped['align']['base'] && 'end' === $mapped['align']['480'], 'Elementor responsive alignment maps to breakpoints' );
$check( 'https://example.org/sam.jpg' === $mapped['avatar']['url'], 'Elementor image URL maps correctly' );

$elementor_html = Kit::render( 'testimonial', $mapped );
$check( strpos( $elementor_html, 'Elementor says hello.' ) !== false, 'Elementor settings render via shared PHP template' );
$check( strpos( $elementor_html, 'Rating: 4 out of 5' ) !== false, 'Elementor rating renders correctly' );
$check( strpos( $elementor_html, '--wpek-author-justify:center' ) !== false, 'Responsive alignment generates CSS mapping' );

echo "Testimonial WordPress integration smoke tests passed.\n";
