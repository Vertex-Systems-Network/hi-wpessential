<?php
/**
 * WooCommerce real product CRUD and Kit integration smoke test.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $pass, $description ) {
	if ( ! $pass ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};
$wpe_render = static function ( array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/products ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};
$wpe_check( function_exists( 'wc_get_products' ) && class_exists( 'WooCommerce' ), 'WooCommerce plugin active for integration' );
$wpe_check( isset( Kit::defs()['products'] ), 'Product Kit registered with WooCommerce active' );

$wpe_term = wp_insert_term( 'WPE Smoke Products', 'product_cat', array( 'slug' => 'wpe-smoke-products' ) );
$wpe_check( ! is_wp_error( $wpe_term ), 'WooCommerce product category created' );
$wpe_ids = array();
try {
	$wpe_first = new WC_Product_Simple();
	$wpe_first->set_name( 'WPE Test Product Alpha' );
	$wpe_first->set_status( 'publish' );
	$wpe_first->set_catalog_visibility( 'visible' );
	$wpe_first->set_regular_price( '59.00' );
	$wpe_first->set_sale_price( '39.00' );
	$wpe_first->set_featured( true );
	$wpe_first->set_category_ids( array( (int) $wpe_term['term_id'] ) );
	$wpe_first_id = $wpe_first->save();
	$wpe_ids[]    = $wpe_first_id;
	$wpe_check( $wpe_first_id > 0, 'Featured sale product saved' );

	$wpe_second = new WC_Product_Simple();
	$wpe_second->set_name( 'WPE Test Product Beta' );
	$wpe_second->set_status( 'publish' );
	$wpe_second->set_catalog_visibility( 'visible' );
	$wpe_second->set_regular_price( '20.00' );
	$wpe_second->set_featured( false );
	$wpe_second->set_stock_status( 'outofstock' );
	$wpe_second->set_category_ids( array( (int) $wpe_term['term_id'] ) );
	$wpe_second_id = $wpe_second->save();
	$wpe_ids[]     = $wpe_second_id;
	$wpe_check( $wpe_second_id > 0, 'Out-of-stock nonfeatured product saved' );

	$wpe_grid = $wpe_render( array( 'category' => 'wpe-smoke-products', 'sort' => 'title', 'order' => 'ASC', 'featuredOnly' => false, 'count' => 5, 'heading' => '<script>alert(9)</script>Test products', 'buttonLabel' => '<img src=x onerror=alert(2)>Details' ) );
	$wpe_check( false !== strpos( $wpe_grid, 'WPE Test Product Alpha' ) && false !== strpos( $wpe_grid, 'WPE Test Product Beta' ), 'Category query returns both published products' );
	$wpe_check( 2 === preg_match_all( '/class="wpe-products__item"/', $wpe_grid ), 'Exactly two product cards emitted' );
	$wpe_check( false !== strpos( $wpe_grid, 'class="wpe-products__sale"' ), 'WooCommerce sale status shown' );
	$wpe_check( false !== strpos( $wpe_grid, '39' ) && false !== strpos( $wpe_grid, '59' ), 'WooCommerce price HTML rendered' );
	$wpe_check( false === strpos( $wpe_grid, '<script>' ) && false === strpos( $wpe_grid, '<img src=x' ), 'Section heading and action text are escaped' );
	$wpe_check( false !== strpos( $wpe_grid, 'wpe-products__image-link' ), 'Product images/placeholder shown' );
	$wpe_check( false !== strpos( $wpe_grid, get_permalink( $wpe_first_id ) ), 'Links navigate to real product page' );

	$wpe_featured = $wpe_render( array( 'featuredOnly' => true, 'category' => 'wpe-smoke-products' ) );
	$wpe_check( false !== strpos( $wpe_featured, 'WPE Test Product Alpha' ) && false === strpos( $wpe_featured, 'WPE Test Product Beta' ), 'Featured-only filter excludes nonfeatured products' );

	$wpe_stock = $wpe_render( array( 'inStockOnly' => true, 'category' => 'wpe-smoke-products' ) );
	$wpe_check( false !== strpos( $wpe_stock, 'WPE Test Product Alpha' ) && false === strpos( $wpe_stock, 'WPE Test Product Beta' ), 'In-stock filter excludes out-of-stock products' );

	$wpe_selection = $wpe_render( array( 'ids' => (string) $wpe_first_id, 'showImage' => false, 'showPrice' => false, 'showButton' => false, 'showSale' => false, 'showRating' => false ) );
	$wpe_check( false !== strpos( $wpe_selection, 'WPE Test Product Alpha' ) && false === strpos( $wpe_selection, 'WPE Test Product Beta' ), 'Product IDs constrain selection' );
	$wpe_check( false === strpos( $wpe_selection, 'wpe-products__price' ) && false === strpos( $wpe_selection, 'wpe-products__action' ), 'Display toggles remove price and CTA' );

	$wpe_none = $wpe_render( array( 'category' => 'nonexistent-kit-category', 'emptyMessage' => 'No listed products' ) );
	$wpe_check( false !== strpos( $wpe_none, 'No listed products' ), 'No matches show safe empty state' );

	$wpe_mapped = Kit::from_elementor( Kit::defs()['products'], array( 'heading' => 'Elementor shop', 'category' => 'wpe-smoke-products', 'showPrice' => 'yes', 'columns' => array( 'size' => 2, 'unit' => '' ), 'gap' => array( 'size' => 18, 'unit' => 'px' ) ) );
	$wpe_check( true === $wpe_mapped['showPrice'], 'Elementor Price toggle maps correctly' );
	$wpe_elementor = Kit::render( 'products', $wpe_mapped );
	$wpe_check( false !== strpos( $wpe_elementor, 'Elementor shop' ) && false !== strpos( $wpe_elementor, '--wpek-products-cols:2' ), 'Elementor outputs product listing and responsive columns' );
} finally {
	foreach ( $wpe_ids as $wpe_id ) {
		wp_delete_post( $wpe_id, true );
	}
	wp_delete_term( (int) $wpe_term['term_id'], 'product_cat' );
}
echo "WooCommerce product integration tests passed.\n";
