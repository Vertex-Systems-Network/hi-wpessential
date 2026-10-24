<?php
/**
 * Integration smoke tests for carousel, modal and pricing table.
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
$wpe_block = static function ( $name, array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $name . ' ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};

foreach ( array( 'image-carousel', 'modal', 'pricing-table' ) as $wpe_name ) {
	$wpe_check( isset( Kit::defs()[ $wpe_name ] ), $wpe_name . ': definition loaded' );
	$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_name ), $wpe_name . ': Gutenberg block registered' );
	$wpe_check( wp_style_is( 'wpessential-kit-' . $wpe_name, 'registered' ), $wpe_name . ': style registered' );
	$wpe_widget = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_name ) : null;
	$wpe_check( is_object( $wpe_widget ) && 'wpe-' . $wpe_name === $wpe_widget->get_name(), $wpe_name . ': Elementor widget registered' );
}

$wpe_prices = $wpe_block( 'pricing-table', array(
	'plan' => '<img src=x onerror=alert(1)>Gold',
	'price' => '<script>9</script>79',
	'currency' => '$',
	'features' => array(
		array( 'text' => 'Included feature', 'included' => true ),
		array( 'text' => '<script>Unsafe</script>Excluded feature', 'included' => false ),
		array( 'text' => '', 'included' => false ),
	),
	'buttonText' => 'Select plan',
	'buttonUrl' => 'https://example.org/buy',
	'newTab' => true,
) );
$wpe_check( false !== strpos( $wpe_prices, '>Gold</h3>' ) || false !== strpos( $wpe_prices, 'Gold</h3>' ), 'Pricing: title visible safely' );
$wpe_check( false === strpos( $wpe_prices, '<img src=x' ) && false === strpos( $wpe_prices, '<script>' ), 'Pricing: malicious HTML escaped' );
$wpe_check( false !== strpos( $wpe_prices, 'is-excluded' ), 'Pricing: excluded features have a distinct class' );
$wpe_check( false !== strpos( $wpe_prices, '(not included)' ), 'Pricing: excluded features readable by screen readers' );
$wpe_check( false !== strpos( $wpe_prices, 'href="https://example.org/buy"' ), 'Pricing: valid CTA shown' );
$wpe_check( false !== strpos( $wpe_prices, 'rel="noopener noreferrer"' ), 'Pricing: new tab protected' );
$wpe_check( 2 === preg_match_all( '/<li class="is-/', $wpe_prices ), 'Pricing: empty feature rows skipped' );
$wpe_no_link = $wpe_block( 'pricing-table', array( 'buttonUrl' => 'javascript:alert(2)' ) );
$wpe_check( false === strpos( $wpe_no_link, 'wpe-pricing-table__button' ) && false === strpos( $wpe_no_link, 'javascript:' ), 'Pricing: unsafe CTA rejected' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['pricing-table'], array(
	'plan' => 'Agency',
	'featured' => 'yes',
	'features' => array( array( 'text' => 'Unlimited', 'included' => 'yes' ), array( 'text' => 'Support', 'included' => '' ) ),
	'maxWidth' => array( 'size' => 410, 'unit' => 'px' ),
) );
$wpe_check( true === $wpe_mapped['featured'], 'Pricing: Elementor featured switcher mapped' );
$wpe_check( true === $wpe_mapped['features'][0]['included'] && false === $wpe_mapped['features'][1]['included'], 'Pricing: Elementor repeater included toggle mapped' );
$wpe_check( false !== strpos( Kit::render( 'pricing-table', $wpe_mapped ), '--wpek-price-max:410px' ), 'Pricing: Elementor responsive max width generated' );

$wpe_modal = $wpe_block( 'modal', array(
	'title' => '<svg onload=alert(1)>Example',
	'content' => '<p><strong>Secure text</strong></p><script>alert(2)</script><iframe src="javascript:alert(2)"></iframe>',
	'trigger' => 'Open info',
	'closeLabel' => 'Dismiss',
	'footerText' => 'Additional info',
	'closeBackdrop' => false,
) );
$wpe_check( false !== strpos( $wpe_modal, '<details class="wpe-modal__fallback">' ), 'Modal: native no-JS disclosure fallback available' );
$wpe_check( false !== strpos( $wpe_modal, '<dialog class="wpe-modal__dialog"' ), 'Modal: native accessible dialog included' );
$wpe_check( false !== strpos( $wpe_modal, 'aria-labelledby=' ), 'Modal: dialog heading associated with accessible label' );
$wpe_check( false !== strpos( $wpe_modal, 'data-close-backdrop="0"' ), 'Modal: backdrop dismissal can be disabled' );
$wpe_check( false !== strpos( $wpe_modal, 'Secure text' ), 'Modal: permitted rich text remains' );
$wpe_check( false !== strpos( $wpe_modal, 'wpe-modal__close-bottom' ), 'Modal: keyboard-operable close button provided' );
$wpe_check( false === strpos( $wpe_modal, '<script>' ) && false === strpos( $wpe_modal, '<iframe' ) && false === strpos( $wpe_modal, '<svg onload=' ), 'Modal: scripts and injected HTML removed' );
$wpe_check( wp_script_is( 'wpessential-kit-modal', 'registered' ), 'Modal: JS registered' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['modal'], array(
	'title' => 'Elementor modal', 'content' => '<p>Made in Elementor</p>', 'closeBackdrop' => '', 'width' => array( 'size' => 590, 'unit' => 'px' ),
) );
$wpe_check( false === $wpe_mapped['closeBackdrop'], 'Modal: Elementor dismissal switcher maps to false' );
$wpe_check( false !== strpos( Kit::render( 'modal', $wpe_mapped ), '--wpek-modal-width:590px' ), 'Modal: responsive width generated from Elementor settings' );

$wpe_empty = $wpe_block( 'image-carousel' );
$wpe_check( false === strpos( $wpe_empty, 'data-wpe-carousel' ), 'Carousel: no slides = no broken carousel output' );
$wpe_slides = array(
	array( 'image' => array( 'url' => 'https://example.org/a.jpg' ), 'alt' => '<script>unsafe</script>First', 'caption' => 'First image', 'link' => 'https://example.org/product' ),
	array( 'image' => array( 'url' => 'javascript:alert(5)' ), 'caption' => 'Invalid image' ),
	array( 'image' => array( 'url' => 'https://example.org/b.jpg' ), 'caption' => '<img src=x onerror=alert(6)>Caption', 'link' => 'javascript:alert(7)' ),
);
$wpe_carousel = $wpe_block( 'image-carousel', array( 'items' => $wpe_slides, 'loop' => false, 'autoplay' => false, 'arrows' => true, 'dots' => true ) );
$wpe_check( 2 === preg_match_all( '/class="wpe-image-carousel__card"/', $wpe_carousel ), 'Carousel: 2 valid images rendered, invalid URL skipped' );
$wpe_check( false !== strpos( $wpe_carousel, 'href="https://example.org/product"' ), 'Carousel: valid slide link retained' );
$wpe_check( false === strpos( $wpe_carousel, 'javascript:' ) && false === strpos( $wpe_carousel, '<img src=x' ) && false === strpos( $wpe_carousel, '<script>' ), 'Carousel: unsafe images and captions escaped' );
$wpe_check( false !== strpos( $wpe_carousel, 'data-autoplay="0"' ) && false !== strpos( $wpe_carousel, 'data-loop="0"' ), 'Carousel: autoplay and loop flags correctly rendered' );
$wpe_check( false !== strpos( $wpe_carousel, 'class="wpe-image-carousel__viewport" tabindex="0"' ), 'Carousel: scrollable region focusable without JS' );
$wpe_check( 2 === preg_match_all( '/data-carousel-dot=/', $wpe_carousel ), 'Carousel: navigation has one dot per valid slide' );
$wpe_check( false !== strpos( $wpe_carousel, 'hidden>' ), 'Carousel: interactive controls hidden until JS ready' );
$wpe_check( wp_script_is( 'wpessential-kit-image-carousel', 'registered' ), 'Carousel: JS registered' );
$wpe_hide = $wpe_block( 'image-carousel', array( 'items' => $wpe_slides, 'showCaptions' => false, 'dots' => false, 'arrows' => false ) );
$wpe_check( false === strpos( $wpe_hide, 'wpe-image-carousel__controls' ), 'Carousel: disabled controls omitted' );
$wpe_check( false === strpos( $wpe_hide, 'wpe-image-carousel__caption' ), 'Carousel: captions toggle hides captions' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['image-carousel'], array(
	'items' => array( array( 'image' => array( 'url' => 'https://example.org/c.jpg' ), 'caption' => 'Elementor slide' ) ),
	'showCaptions' => 'yes',
	'slidesDesktop' => array( 'size' => 2, 'unit' => '' ),
	'gap' => array( 'size' => 18, 'unit' => 'px' ),
) );
$wpe_el = Kit::render( 'image-carousel', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_el, 'Elementor slide' ), 'Carousel: Elementor renders shared PHP template' );
$wpe_check( false !== strpos( $wpe_el, '--wpek-carousel-visible:2' ), 'Carousel: Elementor responsive items-per-view CSS emitted' );

echo "Three Kit widgets Gutenberg and Elementor integration tests passed.\n";
