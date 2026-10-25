<?php
/** Real WooCommerce conditions with native WC_Cart and WC_Order. @package WPEssential\Core */
use WPEssential\Core\Kit;
$check = static function ( $ok, $msg ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $msg ); } echo 'PASS: ' . $msg . "\n"; };
$check( function_exists( 'WC' ) && class_exists( 'WC_Product_Simple' ), 'Real WooCommerce available' );
$saved_cart = WC()->cart;
$saved_user = get_current_user_id();
$user_id = 0;
$product_id = 0;
$order_id = 0;
try {
	$user_id = wp_create_user( 'kit_customer_' . wp_rand( 10000, 99999 ), wp_generate_password( 30 ), 'kit_customer_' . wp_rand( 10000, 99999 ) . '@example.test' );
	$check( ! is_wp_error( $user_id ), 'Test purchaser exists' );
	wp_set_current_user( $user_id );
	$product = new WC_Product_Simple();
	$product->set_name( 'Kit Gated Product' );
	$product->set_regular_price( '25' );
	$product->set_status( 'publish' );
	$product_id = $product->save();
	$check( $product_id > 0, 'WooCommerce product saved' );
	WC()->cart = new WC_Cart();
	$check( false !== strpos( Kit::render( 'icon', array( 'conditionCart' => 'empty' ) ), 'wpe-icon' ), 'Empty cart allows empty rule' );
	$check( '' === Kit::render( 'icon', array( 'conditionCart' => 'nonempty' ) ), 'Empty cart denies nonempty rule' );
	WC()->cart->set_cart_contents( array( 'kit-demo' => array( 'quantity' => 2 ) ) );
	$check( false !== strpos( Kit::render( 'icon', array( 'conditionCart' => 'nonempty' ) ), 'wpe-icon' ), 'Filled cart permits nonempty rule' );
	$check( '' === Kit::render( 'icon', array( 'conditionCart' => 'empty' ) ), 'Filled cart denies empty rule' );
	$check( '' === Kit::render( 'icon', array( 'conditionPurchasedProduct' => (string) $product_id ) ), 'No purchase cannot unlock content' );
	$order = wc_create_order( array( 'customer_id' => $user_id ) );
	$check( ! is_wp_error( $order ), 'Real order created' );
	$order_id = $order->get_id();
	$order->add_product( $product, 1 );
	$order->set_status( 'completed' );
	$order->save();
	$check( false !== strpos( Kit::render( 'icon', array( 'conditionPurchasedProduct' => (string) $product_id ) ), 'wpe-icon' ), 'Completed purchase unlocks for current customer' );
	wp_set_current_user( 0 );
	$check( '' === Kit::render( 'icon', array( 'conditionPurchasedProduct' => (string) $product_id ) ), 'Guest never gets purchaser content' );
} finally {
	WC()->cart = $saved_cart;
	wp_set_current_user( $saved_user );
	if ( $order_id ) { $order = wc_get_order( $order_id ); if ( $order ) { $order->delete( true ); } }
	if ( $product_id ) { wp_delete_post( $product_id, true ); }
	if ( $user_id && ! is_wp_error( $user_id ) ) { wp_delete_user( $user_id ); }
}
echo "Kit real WooCommerce condition smoke passed.\n";
