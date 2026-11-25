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
		add_theme_support( 'wc-product-gallery-lightbox' );
		// Editorial demos show the gallery as an image grid instead of a slider (option wpessential_pdp_gallery = grid).
		if ( 'grid' !== get_option( 'wpessential_pdp_gallery', '' ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-slider' );
		}
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
		return '<span class="onsale">' . esc_html( sprintf( __( '-%d%%', 'hi-wpessential' ), $percent ) ) . '</span>';
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
			echo '<span class="wpe-badge-oos">' . esc_html__( 'Out of stock', 'hi-wpessential' ) . '</span>';
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
		$message = esc_html__( 'You’ve unlocked free shipping on this order!', 'hi-wpessential' );
	} else {
		/* translators: %s: amount left for free shipping. */
		$message = sprintf( esc_html__( 'Add %s more to unlock free shipping.', 'hi-wpessential' ), wp_kses_post( wc_price( $threshold - $subtotal ) ) );
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

// Second gallery image fades in when a product card is hovered (assets/css/hover.css).
add_action(
	'woocommerce_before_shop_loop_item_title',
	static function () {
		global $product;
		if ( ! $product || ! apply_filters( 'wpessential_loop_hover_image', true ) ) {
			return;
		}
		$gallery = $product->get_gallery_image_ids();
		if ( $gallery ) {
			echo wp_get_attachment_image(
				(int) $gallery[0],
				'woocommerce_thumbnail',
				false,
				array(
					'class'       => 'wpe-hover-img',
					'alt'         => '',
					'aria-hidden' => 'true',
					'loading'     => 'lazy',
				)
			);
		}
	},
	11
);

/**
 * Shop title and intro a demo can set (options wpessential_shop_title / wpessential_shop_lead).
 */
add_filter(
	'woocommerce_page_title',
	static function ( $title ) {
		$custom = (string) get_option( 'wpessential_shop_title', '' );
		return ( is_shop() && '' !== $custom ) ? $custom : $title;
	}
);
add_action(
	'woocommerce_archive_description',
	static function () {
		$lead = (string) get_option( 'wpessential_shop_lead', '' );
		if ( is_shop() && '' !== $lead ) {
			echo '<p class="wpe-shop-lead">' . esc_html( $lead ) . '</p>';
		}
	},
	5
);

/**
 * Filter sidebar on product archives: categories, sizes (pa_size) and price ranges.
 * Uses WooCommerce's own query args (product_cat, filter_size, min_price/max_price), so no plugin is needed.
 * Turn it off with add_filter( 'wpessential_shop_filters', '__return_false' ).
 */
function wpessential_shop_filters_enabled() {
	return ( is_shop() || is_product_taxonomy() ) && apply_filters( 'wpessential_shop_filters', true );
}

/**
 * Link that keeps the current filters and changes one.
 *
 * @param array $args Query args to set; null removes.
 * @return string
 */
function wpessential_shop_filter_url( array $args ) {
	$base = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
	$keep = array();
	foreach ( array( 'filter_size', 'min_price', 'max_price', 'orderby' ) as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter state.
		if ( isset( $_GET[ $key ] ) ) {
			$keep[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
	$merged = array_filter( array_merge( $keep, $args ), static fn( $v ) => null !== $v && '' !== $v );
	return add_query_arg( $merged, is_wp_error( $base ) ? home_url( '/' ) : $base );
}

add_action(
	'woocommerce_before_shop_loop',
	static function () {
		if ( ! wpessential_shop_filters_enabled() ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filter state.
		$size_now = isset( $_GET['filter_size'] ) ? sanitize_title( wp_unslash( $_GET['filter_size'] ) ) : '';
		$min_now  = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : null;
		// phpcs:enable
		$current = is_product_category() ? get_queried_object_id() : 0;
		echo '<div class="wpe-shop-layout"><aside class="wpe-shop-filters" aria-label="' . esc_attr__( 'Filter products', 'hi-wpessential' ) . '">';

		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( $cats && ! is_wp_error( $cats ) ) {
			echo '<h2 class="wpe-shop-filters__title">' . esc_html__( 'Category', 'hi-wpessential' ) . '</h2><ul>';
			echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '"' . ( $current ? '' : ' aria-current="true"' ) . '>' . esc_html__( 'All', 'hi-wpessential' ) . '</a></li>';
			foreach ( $cats as $cat ) {
				printf(
					'<li><a href="%1$s"%2$s>%3$s <span>%4$d</span></a></li>',
					esc_url( get_term_link( $cat ) ),
					$current === $cat->term_id ? ' aria-current="true"' : '',
					esc_html( $cat->name ),
					(int) $cat->count
				);
			}
			echo '</ul>';
		}

		$sizes = taxonomy_exists( 'pa_size' ) ? get_terms(
			array(
				'taxonomy'   => 'pa_size',
				'hide_empty' => true,
			)
		) : array();
		if ( $sizes && ! is_wp_error( $sizes ) ) {
			echo '<h2 class="wpe-shop-filters__title">' . esc_html__( 'Size', 'hi-wpessential' ) . '</h2><ul class="wpe-shop-filters__chips">';
			foreach ( $sizes as $size ) {
				$on = $size_now === $size->slug;
				printf(
					'<li><a href="%1$s"%2$s>%3$s</a></li>',
					esc_url( wpessential_shop_filter_url( array( 'filter_size' => $on ? null : $size->slug ) ) ),
					$on ? ' aria-current="true"' : '',
					esc_html( $size->name )
				);
			}
			echo '</ul>';
		}

		$ranges = (array) apply_filters( 'wpessential_shop_price_ranges', array( array( 0, 100 ), array( 100, 250 ), array( 250, 500 ), array( 500, 0 ) ) );
		echo '<h2 class="wpe-shop-filters__title">' . esc_html__( 'Price', 'hi-wpessential' ) . '</h2><ul>';
		foreach ( $ranges as $range ) {
			list( $min, $max ) = array_map( 'absint', $range );
			$on                = null !== $min_now && $min_now === $min;
			$label             = $max ? wp_strip_all_tags( wc_price( $min ) . ' – ' . wc_price( $max ) ) : wp_strip_all_tags( wc_price( $min ) ) . '+';
			printf(
				'<li><a href="%1$s"%2$s>%3$s</a></li>',
				esc_url(
					wpessential_shop_filter_url(
						$on ? array(
							'min_price' => null,
							'max_price' => null,
						) : array(
							'min_price' => $min,
							'max_price' => $max ? $max : null,
						)
					)
				),
				$on ? ' aria-current="true"' : '',
				esc_html( html_entity_decode( $label ) )
			);
		}
		echo '</ul>';
		if ( $size_now || null !== $min_now ) {
			echo '<p><a class="wpe-shop-filters__clear" href="' . esc_url( remove_query_arg( array( 'filter_size', 'min_price', 'max_price' ) ) ) . '">' . esc_html__( 'Clear filters', 'hi-wpessential' ) . '</a></p>';
		}
		echo '</aside><div class="wpe-shop-main">';
	},
	5
);
add_action(
	'woocommerce_after_shop_loop',
	static function () {
		if ( wpessential_shop_filters_enabled() ) {
			echo '</div></div>';
		}
	},
	50
);

// The block cart does not fire woocommerce_before_cart, so the free-shipping bar goes in front of the block.
add_filter(
	'render_block_woocommerce/cart',
	static function ( $html ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
			return $html;
		}
		ob_start();
		wpessential_free_shipping_notice();
		return ob_get_clean() . $html;
	}
);

// Cart page title a demo can set (option wpessential_cart_title), e.g. "Your bag".
add_filter(
	'the_title',
	static function ( $title, $id = 0 ) {
		$custom = (string) get_option( 'wpessential_cart_title', '' );
		if ( '' !== $custom && $id && function_exists( 'wc_get_page_id' ) && (int) wc_get_page_id( 'cart' ) === (int) $id ) {
			return $custom;
		}
		return $title;
	},
	10,
	2
);

/**
 * Delivery, returns and care promises under the add-to-cart button (option wpessential_pdp_perks, one per line).
 */
add_action(
	'woocommerce_single_product_summary',
	static function () {
		$perks = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'wpessential_pdp_perks', '' ) ) ) );
		if ( ! $perks ) {
			return;
		}
		echo '<ul class="wpe-pdp-perks">';
		foreach ( $perks as $perk ) {
			echo '<li>' . esc_html( $perk ) . '</li>';
		}
		echo '</ul>';
	},
	35
);

// WooCommerce turns the slider and zoom on for every block theme, so switch them off here for grid galleries.
add_filter( 'woocommerce_single_product_flexslider_enabled', static fn( $on ) => 'grid' === get_option( 'wpessential_pdp_gallery', '' ) ? false : $on );
add_filter( 'woocommerce_single_product_zoom_enabled', static fn( $on ) => 'grid' === get_option( 'wpessential_pdp_gallery', '' ) ? false : $on );

/**
 * Shop banner and round category links above the grid (Base demo and the default theme).
 * Demos with their own shop look turn it off with option wpessential_shop_banner = off.
 */
add_action(
	'woocommerce_before_shop_loop',
	static function () {
		if ( ! is_shop() || 'off' === get_option( 'wpessential_shop_banner', '' ) ) {
			return;
		}
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 6,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( ! $cats || is_wp_error( $cats ) ) {
			return;
		}
		echo '<ul class="wpe-shop-cats">';
		foreach ( $cats as $cat ) {
			$thumb = (int) get_term_meta( $cat->term_id, 'thumbnail_id', true );
			if ( ! $thumb ) {
				$ids = get_posts(
					array(
						'post_type'   => 'product',
						'numberposts' => 5,
						'fields'      => 'ids',
						'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Five IDs, shop page only.
							array(
								'taxonomy' => 'product_cat',
								'terms'    => $cat->term_id,
							),
						),
					)
				); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				foreach ( $ids as $pid ) {
					$thumb = (int) get_post_thumbnail_id( $pid );
					if ( $thumb ) {
						break;
					}
				}
			}
			printf(
				'<li><a href="%1$s">%2$s<span>%3$s</span></a></li>',
				esc_url( get_term_link( $cat ) ),
				$thumb ? wp_get_attachment_image( $thumb, 'woocommerce_thumbnail', false, array( 'alt' => '' ) ) : '<i aria-hidden="true">' . esc_html( mb_substr( $cat->name, 0, 1 ) ) . '</i>',
				esc_html( $cat->name )
			);
		}
		echo '</ul>';
	},
	1
);

/**
 * When WPEssential Pro's WooCommerce elements are active, the shop templates render with them
 * instead of WooCommerce's classic PHP templates. Without Pro, WooCommerce's own output is kept.
 *
 * @param string $html  Rendered block HTML.
 * @param array  $block Parsed block.
 * @return string
 */
function wpessential_woo_kit_template( $html, $block ) {
	if ( 'woocommerce/legacy-template' !== ( $block['blockName'] ?? '' ) || ! WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/woo-product-gallery' ) ) {
		return $html;
	}
	$template = (string) ( $block['attrs']['template'] ?? '' );
	if ( 'single-product' === $template && is_product() && has_term( 'demos', 'product_cat' ) ) {
		return wpessential_woo_demo_detail();
	}
	if ( 'single-product' === $template && is_product() ) {
		$markup = '<!-- wp:columns {"className":"wpe-product-top"} --><div class="wp-block-columns wpe-product-top">'
			. '<!-- wp:column --><div class="wp-block-column"><!-- wp:wpessential/woo-product-gallery /--></div><!-- /wp:column -->'
			. '<!-- wp:column --><div class="wp-block-column">'
			. '<!-- wp:wpessential/woo-product-title {"link":false} /--><!-- wp:wpessential/woo-product-rating /--><!-- wp:wpessential/woo-product-price /-->'
			. '<!-- wp:wpessential/woo-product-excerpt /--><!-- wp:wpessential/woo-add-to-cart /--><!-- wp:wpessential/woo-product-meta /-->'
			. '</div><!-- /wp:column --></div><!-- /wp:columns -->'
			. '<!-- wp:wpessential/woo-product-tabs /--><!-- wp:wpessential/woo-related-products /-->';
		return do_blocks( $markup );
	}
	if ( in_array( $template, array( 'archive-product', 'taxonomy-product_cat', 'taxonomy-product_tag', 'product-search-results' ), true ) ) {
		return do_blocks( '<!-- wp:wpessential/woo-product-archive /-->' );
	}
	return $html;
}
add_filter( 'render_block', 'wpessential_woo_kit_template', 10, 2 );

/**
 * Demo detail page (Figma 05): breadcrumb and title, a framed live-site preview beside a buy box,
 * the description, then related demos. Built from WPEssential Pro WooCommerce elements.
 *
 * @return string
 */
function wpessential_woo_demo_detail() {
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		return '';
	}
	$gallery = $product->get_gallery_image_ids();
	$shot    = $gallery ? (int) $gallery[0] : (int) $product->get_image_id();
	$cats    = wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), ' · ' ) );
	$live    = (string) $product->get_meta( 'wpe_live_url' );
	ob_start();
	?>
	<div class="wpe-demo">
		<nav class="wpe-demo__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'hi-wpessential' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'hi-wpessential' ); ?></a> /
			<a href="<?php echo esc_url( (string) get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Demos', 'hi-wpessential' ); ?></a> /
			<span aria-current="page"><?php echo esc_html( $product->get_name() ); ?></span>
		</nav>
		<h1 class="wpe-demo__title"><?php echo esc_html( $product->get_name() ); ?></h1>
		<p class="wpe-demo__meta"><?php echo esc_html( $cats ); ?></p>
		<div class="wpe-demo__grid">
			<div class="wpe-demo__preview">
				<div class="wpe-demo__bar" aria-hidden="true"><span></span><span></span><span></span></div>
				<?php echo wp_get_attachment_image( $shot, 'large', false, array( 'class' => 'wpe-demo__shot' ) ); ?>
			</div>
			<aside class="wpe-demo__buy">
				<?php
				echo do_blocks( '<!-- wp:wpessential/woo-product-price /--><!-- wp:wpessential/woo-add-to-cart {"showQuantity":false,"fullWidth":true} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks.
				if ( $live ) :
					?>
					<a class="wpe-demo__live" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open live preview', 'hi-wpessential' ); ?></a>
				<?php endif; ?>
				<div class="wpe-demo__get"><?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?></div>
			</aside>
		</div>
		<?php echo do_blocks( '<!-- wp:wpessential/woo-related-products {"heading":"You might also like","count":3,"columns":3} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks. ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Checkout (Figma 17): when nothing in the cart ships, ask only for name and email. Address,
 * company and phone are dropped for virtual-only carts; physical products keep every field.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function wpessential_woo_virtual_checkout_fields( $fields ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->needs_shipping() ) {
		return $fields;
	}
	$keep               = array( 'billing_first_name', 'billing_last_name', 'billing_email' );
	$fields['billing']  = array_intersect_key( $fields['billing'] ?? array(), array_flip( $keep ) );
	$fields['shipping'] = array();
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'wpessential_woo_virtual_checkout_fields', 20 );
add_filter( 'woocommerce_enable_order_notes_field', static fn( $on ) => ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->needs_shipping() ) ? false : $on );

/**
 * Signed-out My Account (Figma 16): a dark intro panel beside the WooCommerce sign-in form.
 */
function wpessential_woo_login_open() {
	?>
	<div class="wpe-auth">
		<div class="wpe-auth__intro">
			<p class="wpe-auth__over"><?php esc_html_e( 'Welcome back', 'hi-wpessential' ); ?></p>
			<h2 class="wpe-auth__title"><?php esc_html_e( 'Sign in to manage your licences, downloads and subscriptions.', 'hi-wpessential' ); ?></h2>
			<ul class="wpe-auth__list">
				<li><?php esc_html_e( 'Activate sites without copying keys', 'hi-wpessential' ); ?></li>
				<li><?php esc_html_e( 'Renew, upgrade or switch to lifetime', 'hi-wpessential' ); ?></li>
				<li><?php esc_html_e( 'Download themes and demo packages', 'hi-wpessential' ); ?></li>
			</ul>
		</div>
		<div class="wpe-auth__form">
	<?php
}
add_action( 'woocommerce_before_customer_login_form', 'wpessential_woo_login_open' );

/** Closes the wrapper opened by wpessential_woo_login_open(). */
function wpessential_woo_login_close() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_customer_login_form', 'wpessential_woo_login_close' );
