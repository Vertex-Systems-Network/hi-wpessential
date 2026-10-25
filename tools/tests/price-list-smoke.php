<?php
/**
 * Price List integration smoke test for disposable WordPress + Elementor.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_assert = static function ( $condition, $label ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	echo 'PASS: ' . $label . "\n";
};
$wpe_block = static function ( array $attributes ) {
	return do_blocks( '<!-- wp:wpessential/price-list ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};

$wpe_assert( isset( Kit::defs()['price-list'] ), 'Price List definition discovered' );
$wpe_assert( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/price-list' ), 'Gutenberg registration' );
$wpe_assert( wp_style_is( 'wpessential-kit-price-list', 'registered' ), 'Style registered on demand' );
$wpe_widget = \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-price-list' );
$wpe_assert( is_object( $wpe_widget ) && 'wpe-price-list' === $wpe_widget->get_name(), 'Elementor widget registered' );

$wpe_html = $wpe_block(
	array(
		'title' => '<script>alert(1)</script>Weekly menu',
		'currency' => '€',
		'layout' => 'cards',
		'items' => array(
			array(
				'title' => 'Garden salad',
				'description' => '<img src=x onerror=alert(1)>Seasonal greens',
				'price' => '12',
				'oldPrice' => '15',
				'badge' => '<script>Hot</script>',
				'link' => 'https://example.org/menu',
				'newTab' => true,
				'image' => array( 'url' => 'https://example.org/salad.webp' ),
				'imageAlt' => 'Garden salad photograph',
			),
			array(
				'title' => 'Bad URL',
				'price' => '<svg onload=alert(1)>20',
				'link' => 'javascript:alert(1)',
				'image' => array( 'url' => 'javascript:alert(1)' ),
			),
			array( 'title' => '', 'description' => '', 'price' => '' ),
		),
	)
);
$wpe_assert( false !== strpos( $wpe_html, 'is-cards' ), 'Card layout rendered' );
$wpe_assert( 2 === preg_match_all( '/class="wpe-price-list__item"/', $wpe_html ), 'Empty records skipped, valid records kept' );
$wpe_assert( false !== strpos( $wpe_html, '€12' ) && false !== strpos( $wpe_html, '€15' ), 'Old and current prices rendered' );
$wpe_assert( false !== strpos( $wpe_html, 'alt="Garden salad photograph"' ), 'Image alt text retained' );
$wpe_assert( false !== strpos( $wpe_html, 'href="https://example.org/menu" target="_blank" rel="noopener noreferrer"' ), 'Safe new-tab link' );
$wpe_assert( false === strpos( $wpe_html, '<script>' ) && false === strpos( $wpe_html, 'javascript:' ) && false === strpos( $wpe_html, 'onerror=' ) && false === strpos( $wpe_html, '<svg ' ), 'Escapes malicious content and URLs' );
$wpe_assert( 1 === preg_match_all( '/class="wpe-price-list__image"/', $wpe_html ), 'Only HTTPS image source accepted' );

$wpe_empty = $wpe_block( array( 'items' => array(), 'emptyText' => 'Back soon' ) );
$wpe_assert( false !== strpos( $wpe_empty, 'Back soon' ) && false === strpos( $wpe_empty, 'wpe-price-list__item' ), 'Saved empty repeater does not restore default items' );
$wpe_hidden = $wpe_block( array( 'showImages' => false, 'showDescriptions' => false, 'showBadges' => false ) );
$wpe_assert( false === strpos( $wpe_hidden, 'wpe-price-list__description' ) && false === strpos( $wpe_hidden, 'wpe-price-list__badge' ), 'Display switches respected' );

$wpe_mapped = Kit::from_elementor(
	Kit::defs()['price-list'],
	array(
		'columns' => array( 'size' => 3, 'unit' => '' ),
		'gap' => array( 'size' => 28, 'unit' => 'px' ),
		'items' => array( array( 'title' => 'Hand-made pastry', 'price' => '7', 'newTab' => '' ) ),
	)
);
$wpe_elementor = Kit::render( 'price-list', $wpe_mapped );
$wpe_assert( false !== strpos( $wpe_elementor, 'Hand-made pastry' ), 'Elementor repeater mapped to shared renderer' );
$wpe_assert( false !== strpos( $wpe_elementor, '--wpek-pl-gap:28px' ), 'Elementor responsive spacing mapping' );
$wpe_assert( false !== strpos( $wpe_elementor, '--wpek-pl-columns:3' ), 'Elementor responsive column mapping' );

echo "Price List WordPress + Elementor smoke tests passed.\n";
