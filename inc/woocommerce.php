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
	$free    = 0.0 === (float) $product->get_price();
	$pages   = array_slice( $gallery, 1, 6 );
	$pricing = get_page_by_path( 'pricing' );
	$log     = get_page_by_path( 'changelog' );
	$plans   = $pricing ? (string) get_permalink( $pricing ) : '';
	$updated = $product->get_date_modified();
	$host    = $live ? (string) wp_parse_url( $live, PHP_URL_HOST ) . (string) wp_parse_url( $live, PHP_URL_PATH ) : '';
	$checks  = array(
		__( 'Gutenberg and Elementor versions', 'hi-wpessential' ),
		__( 'Real photography, hovers and dark mode', 'hi-wpessential' ),
		__( 'One-click import with undo', 'hi-wpessential' ),
		__( '1 year of updates and support', 'hi-wpessential' ),
	);
	$plugins = array(
		array( 'W', 'WooCommerce', __( 'Recommended', 'hi-wpessential' ) ),
		array( 'E', 'Elementor', __( 'Elementor version', 'hi-wpessential' ) ),
		array( 'C', 'WPEssential', __( 'Included', 'hi-wpessential' ) ),
	);
	ob_start();
	?>
	<div class="wpe-demo">
		<nav class="wpe-demo__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'hi-wpessential' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'hi-wpessential' ); ?></a> /
			<a href="<?php echo esc_url( (string) get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Demos', 'hi-wpessential' ); ?></a> /
			<span aria-current="page"><?php echo esc_html( $product->get_name() ); ?></span>
		</nav>
		<p class="wpe-demo__badges"><span class="<?php echo $free ? 'is-free' : 'is-pro'; ?>"><?php echo $free ? esc_html__( 'Free', 'hi-wpessential' ) : esc_html__( 'Pro', 'hi-wpessential' ); ?></span></p>
		<h1 class="wpe-demo__title"><?php echo esc_html( preg_replace( '/\s+demo$/i', '', $product->get_name() ) ); ?></h1>
		<p class="wpe-demo__meta"><?php echo esc_html( implode( '  ·  ', array_filter( array( $cats, $gallery ? sprintf( /* translators: %d: pages. */ _n( '%d page', '%d pages', count( $gallery ), 'hi-wpessential' ), count( $gallery ) ) : '', sprintf( /* translators: %s: date. */ __( 'Updated %s', 'hi-wpessential' ), $updated ? $updated->date_i18n( 'M Y' ) : '' ) ) ) ) ); ?></p>
		<div class="wpe-demo__grid">
			<div class="wpe-demo__main">
				<div class="wpe-demo__preview">
					<div class="wpe-demo__bar" aria-hidden="true"><span></span><span></span><span></span><?php echo $host ? '<em>' . esc_html( $host ) . '</em>' : ''; ?></div>
					<?php echo wp_get_attachment_image( $shot, 'large', false, array( 'class' => 'wpe-demo__shot' ) ); ?>
					<p class="wpe-demo__note"><?php esc_html_e( 'Live demo running in your browser — click, hover and browse every page before you import.', 'hi-wpessential' ); ?></p>
				</div>
				<nav class="wpe-demo__tabs" aria-label="<?php esc_attr_e( 'Sections', 'hi-wpessential' ); ?>"><a class="is-on" href="#overview"><?php esc_html_e( 'Overview', 'hi-wpessential' ); ?></a><?php echo $pages ? '<a href="#pages">' . esc_html__( 'Pages', 'hi-wpessential' ) . '</a>' : ''; ?><a href="<?php echo esc_url( $log ? (string) get_permalink( $log ) : '#overview' ); ?>"><?php esc_html_e( 'Changelog', 'hi-wpessential' ); ?></a></nav>
				<section id="overview" class="wpe-demo__overview">
					<?php if ( $product->get_short_description() ) : ?>
						<h2><?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ) ); ?></h2>
					<?php endif; ?>
					<?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?>
				</section>
				<?php if ( $pages ) : ?>
					<section id="pages" class="wpe-demo__pages">
						<p class="wpe-demo__label"><?php esc_html_e( 'Pages included', 'hi-wpessential' ); ?></p>
						<ul>
							<?php foreach ( $pages as $img ) : ?>
								<li><?php echo wp_get_attachment_image( $img, 'medium_large' ); ?><span><?php echo esc_html( (string) get_the_title( $img ) ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>
			<aside class="wpe-demo__buy">
				<?php echo do_blocks( '<!-- wp:wpessential/woo-product-price /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks. ?>
				<?php if ( ! $free ) : ?>
					<p class="wpe-demo__incl"><?php esc_html_e( 'Included in every paid plan', 'hi-wpessential' ); ?></p>
					<?php if ( $plans ) : ?>
						<a class="wpe-demo__cta" href="<?php echo esc_url( $plans ); ?>"><?php esc_html_e( 'Get Pro', 'hi-wpessential' ); ?> <span aria-hidden="true">↗</span></a>
					<?php endif; ?>
				<?php endif; ?>
				<?php echo do_blocks( '<!-- wp:wpessential/woo-add-to-cart {"showQuantity":false,"fullWidth":true} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks. ?>
				<?php if ( $live ) : ?>
					<a class="wpe-demo__live" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open live preview', 'hi-wpessential' ); ?></a>
				<?php endif; ?>
				<p class="wpe-demo__label"><?php esc_html_e( 'What you get', 'hi-wpessential' ); ?></p>
				<ul class="wpe-demo__checks">
					<?php foreach ( $checks as $check ) : ?>
						<li><?php echo esc_html( $check ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="wpe-demo__label"><?php esc_html_e( 'Plugins (installed for you)', 'hi-wpessential' ); ?></p>
				<ul class="wpe-demo__plugins">
					<?php foreach ( $plugins as $plugin ) : ?>
						<li><b><?php echo esc_html( $plugin[0] ); ?></b><span><?php echo esc_html( $plugin[1] ); ?></span><em><?php echo esc_html( $plugin[2] ); ?></em></li>
					<?php endforeach; ?>
				</ul>
			</aside>
		</div>
		<?php
		$others = wc_get_products(
			array(
				'category' => array( 'demos' ),
				'exclude'  => array( $product->get_id() ),
				'limit'    => 3,
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
			)
		);
		if ( $others ) :
			?>
			<section class="wpe-demo__related">
				<h2><?php esc_html_e( 'You might also like', 'hi-wpessential' ); ?></h2>
				<div class="wpe-demo__cards">
					<?php foreach ( $others as $other ) : ?>
						<?php $other_free = 0.0 === (float) $other->get_price(); ?>
						<article class="wpe-demo-card">
							<a class="wpe-demo-card__shot" href="<?php echo esc_url( (string) $other->get_permalink() ); ?>">
								<?php echo wp_get_attachment_image( (int) $other->get_image_id(), 'large' ); ?>
								<span class="<?php echo $other_free ? 'is-free' : 'is-pro'; ?>"><?php echo $other_free ? esc_html__( 'Free', 'hi-wpessential' ) : esc_html__( 'Pro', 'hi-wpessential' ); ?></span>
							</a>
							<div class="wpe-demo-card__body">
								<h3><a href="<?php echo esc_url( (string) $other->get_permalink() ); ?>"><?php echo esc_html( preg_replace( '/\s+demo$/i', '', $other->get_name() ) ); ?></a> <b><?php echo $other_free ? esc_html__( 'Free', 'hi-wpessential' ) : esc_html__( 'Pro', 'hi-wpessential' ); ?></b></h3>
								<p><?php echo esc_html( wp_strip_all_tags( $other->get_short_description() ) ); ?></p>
								<p class="wpe-demo-card__builders"><span>G</span><span>E</span></p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
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

/** True when the signed-out My Account page shows the Create account form (Figma 29). */
function wpessential_woo_is_register() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View switch only.
	return isset( $_GET['register'] ) && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
}

/**
 * Signed-out My Account (Figma 16): a dark intro panel beside the sign-in form, with Sign in /
 * Create account tabs. ?register shows the single register card (Figma 29).
 */
function wpessential_woo_login_open() {
	$register = wpessential_woo_is_register();
	$account  = (string) wc_get_page_permalink( 'myaccount' );
	?>
	<div class="wpe-auth<?php echo $register ? ' is-register' : ''; ?>">
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
			<?php if ( $register ) : ?>
				<h1 class="wpe-auth__heading"><?php esc_html_e( 'Create your account', 'hi-wpessential' ); ?></h1>
				<p class="wpe-auth__lead"><?php esc_html_e( 'Use it to buy Pro, download files and manage licences.', 'hi-wpessential' ); ?></p>
			<?php elseif ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
				<nav class="wpe-auth__tabs" aria-label="<?php esc_attr_e( 'Account', 'hi-wpessential' ); ?>"><a class="is-on" href="<?php echo esc_url( $account ); ?>" aria-current="page"><?php esc_html_e( 'Sign in', 'hi-wpessential' ); ?></a><a href="<?php echo esc_url( add_query_arg( 'register', '1', $account ) ); ?>"><?php esc_html_e( 'Create account', 'hi-wpessential' ); ?></a></nav>
			<?php endif; ?>
	<?php
}
add_action( 'woocommerce_before_customer_login_form', 'wpessential_woo_login_open' );

/** Closes the wrapper opened by wpessential_woo_login_open(), with the switch line under the form. */
function wpessential_woo_login_close() {
	$account = (string) wc_get_page_permalink( 'myaccount' );
	if ( wpessential_woo_is_register() ) {
		echo '<p class="wpe-auth__switch"><a href="' . esc_url( $account ) . '">' . esc_html__( 'Already have an account? Sign in', 'hi-wpessential' ) . '</a></p>';
	} elseif ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
		echo '<p class="wpe-auth__switch"><a href="' . esc_url( add_query_arg( 'register', '1', $account ) ) . '">' . esc_html__( 'New here? Create an account — it is free and takes 30 seconds.', 'hi-wpessential' ) . '</a></p>';
	}
	echo '</div></div>';
}
add_action( 'woocommerce_after_customer_login_form', 'wpessential_woo_login_close' );

/**
 * Figma 16 / 29 wording on the sign-in and register forms.
 *
 * @param string $text   Translated text.
 * @param string $source Original text.
 * @param string $domain Text domain.
 * @return string
 */
function wpessential_woo_auth_text( $text, $source, $domain ) {
	if ( 'woocommerce' !== $domain || ! is_account_page() || is_user_logged_in() ) {
		return $text;
	}
	$map = array(
		'Log in'                    => __( 'Sign in', 'hi-wpessential' ),
		'Register'                  => __( 'Create account', 'hi-wpessential' ),
		'Username or email address' => __( 'Email', 'hi-wpessential' ),
		'Email address'             => __( 'Email', 'hi-wpessential' ),
		'Lost your password?'       => __( 'Forgot password?', 'hi-wpessential' ),
	);
	return isset( $map[ $source ] ) ? $map[ $source ] : $text;
}
add_filter( 'gettext', 'wpessential_woo_auth_text', 20, 3 );

/** Register form (Figma 29): full name first, terms checkbox last. */
function wpessential_woo_register_name() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Refills the field; WooCommerce verifies the nonce.
	$name = isset( $_POST['wpe_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpe_full_name'] ) ) : '';
	echo '<p class="woocommerce-form-row form-row form-row-wide"><label for="wpe_full_name">' . esc_html__( 'Full name', 'hi-wpessential' ) . '</label>'
		. '<input type="text" class="input-text" name="wpe_full_name" id="wpe_full_name" autocomplete="name" placeholder="' . esc_attr__( 'Your name', 'hi-wpessential' ) . '" value="' . esc_attr( $name ) . '"></p>';
}
add_action( 'woocommerce_register_form_start', 'wpessential_woo_register_name' );

/** Terms checkbox on the register form. */
function wpessential_woo_register_terms() {
	$terms   = get_page_by_path( 'terms' );
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	echo '<p class="form-row wpe-auth__agree"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox"><input type="checkbox" class="woocommerce-form__input-checkbox" name="wpe_agree" value="1" required> <span>'
		. sprintf(
			/* translators: 1: terms link, 2: privacy link. */
			esc_html__( 'I agree to the %1$s and %2$s', 'hi-wpessential' ),
			$terms ? '<a href="' . esc_url( (string) get_permalink( $terms ) ) . '">' . esc_html__( 'Terms', 'hi-wpessential' ) . '</a>' : esc_html__( 'Terms', 'hi-wpessential' ),
			$privacy ? '<a href="' . esc_url( (string) get_permalink( $privacy ) ) . '">' . esc_html__( 'Privacy policy', 'hi-wpessential' ) . '</a>' : esc_html__( 'Privacy policy', 'hi-wpessential' )
		)
		. '</span></label></p>';
}
add_action( 'woocommerce_register_form', 'wpessential_woo_register_terms', 30 );

/**
 * Requires the terms checkbox.
 *
 * @param WP_Error $errors Registration errors.
 * @return WP_Error
 */
function wpessential_woo_register_validate( $errors ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the register nonce first.
	if ( empty( $_POST['wpe_agree'] ) ) {
		$errors->add( 'wpe_agree', __( 'Please accept the Terms and Privacy policy.', 'hi-wpessential' ) );
	}
	return $errors;
}
add_filter( 'woocommerce_registration_errors', 'wpessential_woo_register_validate' );

/**
 * Saves the full name on the new customer.
 *
 * @param int $customer_id New customer ID.
 */
function wpessential_woo_register_save( $customer_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the register nonce first.
	$name = isset( $_POST['wpe_full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpe_full_name'] ) ) : '';
	if ( '' === $name ) {
		return;
	}
	$parts = explode( ' ', $name, 2 );
	wp_update_user(
		array(
			'ID'           => $customer_id,
			'first_name'   => $parts[0],
			'last_name'    => isset( $parts[1] ) ? $parts[1] : '',
			'display_name' => $name,
		)
	);
}
add_action( 'woocommerce_created_customer', 'wpessential_woo_register_save' );

/**
 * Demo cards (Figma 03/04) from the live products in the "demos" category, so a new demo product
 * shows up with its own card, badges and filter counts. facets="1" adds the filter sidebar and bar.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function wpessential_demo_cards( $atts ) {
	$atts  = shortcode_atts(
		array(
			'limit'  => 0,
			'facets' => 0,
			'chips'  => 0,
			'stats'  => 0,
		),
		$atts,
		'wpessential_demo_cards'
	);
	$items = wc_get_products(
		array(
			'category' => array( 'demos' ),
			'status'   => 'publish',
			'limit'    => (int) $atts['limit'] > 0 ? (int) $atts['limit'] : -1,
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
		)
	);
	if ( ! $items ) {
		return '';
	}
	if ( (int) $atts['facets'] || (int) $atts['chips'] ) {
		wp_enqueue_script( 'wpessential-components' ); // Elementor pages skip the render_block check in inc/assets.php.
	}
	$import = get_page_by_path( 'doc-import', OBJECT, array( 'page', 'post', 'wpessential_doc' ) );
	$niches = array();
	$cards  = '';
	foreach ( $items as $item ) {
		$free  = 0.0 === (float) $item->get_price();
		$name  = preg_replace( '/\s+demo$/i', '', $item->get_name() );
		$keys  = array_filter( array_map( 'sanitize_key', explode( ',', (string) $item->get_meta( '_wpe_niches' ) ) ) );
		$text  = (string) $item->get_meta( '_wpe_card_text' );
		$badge = (string) $item->get_meta( '_wpe_badge' );
		$url   = (string) $item->get_permalink();
		foreach ( $keys as $key ) {
			$niches[ $key ] = isset( $niches[ $key ] ) ? $niches[ $key ] + 1 : 1;
		}
		$badges = '<span class="wpe-ess-badge' . ( $free ? ' wpe-ess-badge--orange' : '' ) . '">' . ( $free ? esc_html__( 'FREE', 'hi-wpessential' ) : esc_html__( 'PRO', 'hi-wpessential' ) ) . '</span>';
		if ( '' !== $badge ) {
			$badges .= '<span class="wpe-ess-badge">' . esc_html( $badge ) . '</span>';
		}
		$classes = array( 'wpe-ess-demo', 'wpe-filter-item', 'wpe-plan-' . ( $free ? 'free' : 'pro' ), 'wpe-builder-gutenberg', 'wpe-builder-elementor' );
		foreach ( $keys as $key ) {
			$classes[] = 'wpe-cat-' . $key;
			$classes[] = 'wpe-niche-' . $key;
		}
		$cards .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">'
			. '<div class="wpe-ess-demo__shot">' . wp_get_attachment_image( (int) $item->get_image_id(), 'large', false, array( 'alt' => $name . ' demo' ) )
			. '<span class="wpe-ess-demo__badges">' . $badges . '</span>'
			. '<span class="wpe-ess-demo__over"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Live preview', 'hi-wpessential' ) . ' <span aria-hidden="true">↗</span></a>'
			. ( $import ? '<a class="is-import" href="' . esc_url( (string) get_permalink( $import ) ) . '">' . esc_html__( 'Import', 'hi-wpessential' ) . ' <span aria-hidden="true">↗</span></a>' : '' )
			. '</span></div><div class="wpe-ess-demo__body">'
			. '<div class="wpe-ess-demo__head"><h3 class="wpe-ess-demo__name"><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></h3>'
			. '<p class="wpe-ess-demo__price">' . ( $free ? esc_html__( 'Free', 'hi-wpessential' ) : wp_kses_post( wc_price( (float) $item->get_price(), array( 'decimals' => 0 ) ) ) ) . '</p></div>'
			. '<p class="wpe-ess-demo__text">' . esc_html( '' !== $text ? $text : wp_strip_all_tags( $item->get_short_description() ) ) . '</p>'
			. '<div class="wpe-ess-demo__meta"><span>' . esc_html__( 'Gutenberg & Elementor', 'hi-wpessential' ) . '</span><span class="wpe-ess-gb" title="Gutenberg">G</span><span class="wpe-ess-gb" title="Elementor">E</span></div>'
			. '</div></div>';
	}
	if ( (int) $atts['stats'] ) {
		$stats = array(
			array( count( $items ), __( 'ready demos', 'hi-wpessential' ) ),
			array( 2, __( 'native builders', 'hi-wpessential' ) ),
			array( 20, __( 'languages', 'hi-wpessential' ) ),
			array( 8, __( 'breakpoints', 'hi-wpessential' ) ),
		);
		$out   = '';
		foreach ( $stats as $stat ) {
			$out .= '<li><b>' . (int) $stat[0] . '</b><span>' . esc_html( $stat[1] ) . '</span></li>';
		}
		return '<ul class="wpe-ess-hero__stats">' . $out . '</ul>';
	}
	if ( (int) $atts['chips'] ) {
		$chips = '<a class="wpe-ess-chip is-on" href="#all">' . esc_html__( 'All', 'hi-wpessential' ) . ' <i>' . count( $items ) . '</i></a>';
		foreach ( $niches as $key => $count ) {
			$chips .= '<a class="wpe-ess-chip" href="#' . esc_attr( $key ) . '">' . esc_html( ucwords( str_replace( '-', ' ', $key ) ) ) . ' <i>' . (int) $count . '</i></a>';
		}
		return '<p class="wpe-ess-cats" data-wpe-filter>' . $chips . '</p>';
	}
	$grid = '<div class="wpe-ess-grid wpe-ess-grid--3 wpe-ess-demo-grid">' . $cards . '</div>';
	if ( ! (int) $atts['facets'] ) {
		return $grid;
	}
	$total = count( $items );
	$opt   = function ( $group, $value, $label, $count = null, $on = false, $single = false ) {
		return '<a class="wpe-ess-facet__opt' . ( $single ? ' is-radio' : '' ) . ( $on ? ' is-on' : '' ) . '" href="#' . esc_attr( $value ) . '" data-facet="' . esc_attr( $group ) . '"' . ( $single ? ' data-single="1"' : '' ) . '>'
			. esc_html( $label ) . ( null === $count ? '' : ' <i>' . (int) $count . '</i>' ) . '</a>';
	};
	$niche = '';
	foreach ( $niches as $key => $count ) {
		$niche .= $opt( 'niche', $key, ucwords( str_replace( '-', ' ', $key ) ), $count );
	}
	return '<div class="wpe-ess-facet-layout"><div class="wpe-ess-facets" data-wpe-facets role="group" aria-label="' . esc_attr__( 'Filter demos', 'hi-wpessential' ) . '">'
		. '<p class="wpe-ess-facets__head"><b>' . esc_html__( 'Filters', 'hi-wpessential' ) . '</b> <a href="#" data-facet-reset>' . esc_html__( 'Reset', 'hi-wpessential' ) . '</a></p>'
		. '<p class="wpe-ess-facets__search"><span data-facet-search>' . esc_html__( 'Search demos', 'hi-wpessential' ) . '</span></p>'
		. '<p class="wpe-ess-facets__group"><span class="wpe-ess-facets__legend">' . esc_html__( 'Plan', 'hi-wpessential' ) . '</span>' . $opt( 'plan', '', __( 'All', 'hi-wpessential' ), null, true, true ) . $opt( 'plan', 'free', __( 'Free', 'hi-wpessential' ), null, false, true ) . $opt( 'plan', 'pro', __( 'Pro', 'hi-wpessential' ), null, false, true ) . '</p>'
		. '<p class="wpe-ess-facets__group"><span class="wpe-ess-facets__legend">' . esc_html__( 'Builder', 'hi-wpessential' ) . '</span>' . $opt( 'builder', 'gutenberg', 'Gutenberg', $total ) . $opt( 'builder', 'elementor', 'Elementor', $total ) . '</p>'
		. ( '' !== $niche ? '<p class="wpe-ess-facets__group"><span class="wpe-ess-facets__legend">' . esc_html__( 'Niche', 'hi-wpessential' ) . '</span>' . $niche . '</p>' : '' )
		. '</div><div class="wpe-ess-facet-main"><div class="wpe-ess-facets__bar"><p data-facet-count>'
		/* translators: %d: number of demos. */
		. esc_html( sprintf( _n( '%d demo', '%d demos', $total, 'hi-wpessential' ), $total ) ) . '</p>'
		. '<p class="wpe-ess-facets__sort">' . esc_html__( 'Sort:', 'hi-wpessential' ) . ' <span data-facet-sort>' . esc_html__( 'Newest | Name A–Z | Name Z–A', 'hi-wpessential' ) . '</span></p></div>'
		. $grid . '</div></div>';
}
add_shortcode( 'wpessential_demo_cards', 'wpessential_demo_cards' );
