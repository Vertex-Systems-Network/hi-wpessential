<?php
/**
 * WooCommerce integration.
 *
 * Markup comes from WooCommerce block templates (see /templates) or, on
 * shortcode-based pages, from WooCommerce's classic templates. This file only
 * adds the small details from the design: category label on cards, sale
 * percentage, out-of-stock badge and the free-shipping progress bar.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 600,
				'single_image_width'    => 900,
				'product_grid'          => array(
					'default_columns' => 4,
					'min_columns'     => 2,
					'max_columns'     => 4,
					'default_rows'    => 3,
				),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}
);

/**
 * Sale badge shows the real discount ("-20%") instead of "Sale!".
 */
add_filter(
	'woocommerce_sale_flash',
	static function ( $html, $post, $product ) {
		$percent = wpessential_product_discount( $product );
		if ( ! $percent ) {
			return $html;
		}
		/* translators: %d: discount percentage. */
		return '<span class="onsale">' . esc_html( sprintf( __( '-%d%%', 'wpessential' ), $percent ) ) . '</span>';
	},
	10,
	3
);

/**
 * Largest discount of a product (variable products: best variation).
 *
 * @param WC_Product $product Product.
 * @return int Whole percent, 0 when not on sale.
 */
function wpessential_product_discount( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return 0;
	}
	$pairs = array();
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_variation_prices()['regular_price'] as $id => $regular ) {
			$pairs[] = array( (float) $regular, (float) $product->get_variation_prices()['sale_price'][ $id ] );
		}
	} else {
		$pairs[] = array( (float) $product->get_regular_price(), (float) $product->get_sale_price() );
	}
	$best = 0;
	foreach ( $pairs as $pair ) {
		if ( $pair[0] > 0 && $pair[1] > 0 && $pair[1] < $pair[0] ) {
			$best = max( $best, (int) round( ( 1 - $pair[1] / $pair[0] ) * 100 ) );
		}
	}
	return $best;
}

/**
 * Classic loop: out-of-stock badge and category label above the title.
 */
add_action(
	'woocommerce_before_shop_loop_item_title',
	static function () {
		global $product;
		if ( $product && ! $product->is_in_stock() ) {
			echo '<span class="wpe-badge-oos">' . esc_html__( 'Out of stock', 'wpessential' ) . '</span>';
		}
	},
	9
);

add_action(
	'woocommerce_shop_loop_item_title',
	static function () {
		global $product;
		if ( ! $product ) {
			return;
		}
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			echo '<span class="wpe-loop-cat">' . esc_html( $terms[0]->name ) . '</span>';
		}
	},
	5
);

/**
 * Four related products in one row, matching the design.
 */
add_filter(
	'woocommerce_output_related_products_args',
	static function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/**
 * Free-shipping progress bar above the classic cart and in the cart block.
 */
function wpessential_free_shipping_notice() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$threshold = (float) apply_filters( 'wpessential_free_shipping_threshold', wpessential_free_shipping_min_amount() );
	if ( $threshold <= 0 ) {
		return;
	}
	$subtotal = (float) WC()->cart->get_displayed_subtotal();
	$percent  = min( 100, round( $subtotal / $threshold * 100 ) );
	if ( $subtotal >= $threshold ) {
		$message = esc_html__( 'You’ve unlocked free shipping on this order!', 'wpessential' );
	} else {
		/* translators: %s: amount left for free shipping. */
		$message = sprintf( esc_html__( 'Add %s more to unlock free shipping.', 'wpessential' ), wp_kses_post( wc_price( $threshold - $subtotal ) ) );
	}
	printf(
		'<div class="wpe-free-shipping">%1$s<div class="wpe-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%2$d"><span style="width:%2$d%%"></span></div></div>',
		wp_kses_post( $message ),
		(int) $percent
	);
}
add_action( 'woocommerce_before_cart', 'wpessential_free_shipping_notice' );

/**
 * Smallest "Free shipping" minimum amount configured in any shipping zone.
 *
 * @return float 0 when no free-shipping minimum is set.
 */
function wpessential_free_shipping_min_amount() {
	$cached = get_transient( 'wpessential_free_shipping_min' );
	if ( false !== $cached ) {
		return (float) $cached;
	}
	$min   = 0;
	$zones = class_exists( 'WC_Shipping_Zones' ) ? WC_Shipping_Zones::get_zones() : array();
	foreach ( $zones as $zone ) {
		foreach ( $zone['shipping_methods'] as $method ) {
			if ( 'free_shipping' === $method->id && 'yes' === $method->enabled && in_array( $method->requires, array( 'min_amount', 'either' ), true ) ) {
				$amount = (float) $method->min_amount;
				$min    = $min ? min( $min, $amount ) : $amount;
			}
		}
	}
	set_transient( 'wpessential_free_shipping_min', $min, DAY_IN_SECONDS );
	return (float) $min;
}
add_action(
	'woocommerce_update_options_shipping',
	static function () {
		delete_transient( 'wpessential_free_shipping_min' );
	}
);

/*
 * WooCommerce hooks the account and mini-cart blocks after the header navigation.
 * Our header actions already place them next to search, so skip the auto-inserted copies.
 */
add_filter(
	'hooked_block_types',
	static function ( $hooked_blocks, $relative_position, $anchor_block ) {
		if ( 'core/navigation' === $anchor_block ) {
			$hooked_blocks = array_values( array_diff( $hooked_blocks, array( 'woocommerce/customer-account', 'woocommerce/mini-cart' ) ) );
		}
		return $hooked_blocks;
	},
	20,
	3
);
