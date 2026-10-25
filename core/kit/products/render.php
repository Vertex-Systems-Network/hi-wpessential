<?php
/**
 * Published WooCommerce product cards; WooCommerce is an optional dependency.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped Kit render template.

if ( ! function_exists( 'wc_get_products' ) || ! class_exists( 'WooCommerce' ) ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-products__missing">' . esc_html__( 'Activate WooCommerce to display products.', 'hi-wpessential' ) . '</p>';
	}
	return;
}
$wpe_limit    = min( 24, max( 1, (int) $a['count'] ) );
$wpe_orderby  = in_array( $a['sort'], array( 'date', 'title', 'price', 'popularity', 'rating' ), true ) ? $a['sort'] : 'date';
$wpe_category = sanitize_title( trim( (string) $a['category'] ) );
$wpe_ids      = array_values( array_filter( array_unique( array_map( 'absint', array_map( 'trim', explode( ',', (string) $a['ids'] ) ) ) ) ) );
$wpe_args     = array(
	'status'  => 'publish',
	'limit'   => $wpe_limit,
	'orderby' => $wpe_orderby,
	'order'   => 'ASC' === $a['order'] ? 'ASC' : 'DESC',
);
if ( '' !== $wpe_category ) {
	$wpe_args['category'] = array( $wpe_category );
}
if ( $wpe_ids ) {
	$wpe_args['include'] = array_slice( $wpe_ids, 0, 50 );
}
if ( ! empty( $a['featuredOnly'] ) ) {
	$wpe_args['featured'] = true;
}
if ( ! empty( $a['inStockOnly'] ) ) {
	$wpe_args['stock_status'] = 'instock';
}
$wpe_items   = wc_get_products( $wpe_args );
$wpe_schema_products = array();
$wpe_classes = array( 'wpe-products', 'is-' . ( 'minimal' === $a['cardStyle'] ? 'minimal' : 'boxed' ) );
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>">
	<?php if ( '' !== trim( (string) $a['heading'] ) ) : ?>
		<h2 class="wpe-products__heading"><?php echo esc_html( (string) $a['heading'] ); ?></h2>
	<?php endif; ?>
	<?php if ( $wpe_items ) : ?>
		<div class="wpe-products__grid">
			<?php foreach ( $wpe_items as $wpe_product ) : ?>
				<?php
				if ( ! $wpe_product instanceof WC_Product || 'publish' !== get_post_status( $wpe_product->get_id() ) || ! $wpe_product->is_visible() ) {
					continue;
				}
				$wpe_product_url = get_permalink( $wpe_product->get_id() );
				$wpe_name        = $wpe_product->get_name();
				if ( ! empty( $a['schemaProducts'] ) ) {
					$wpe_price = $wpe_product->get_price();
					$wpe_offer = array( '@type' => 'Offer', 'url' => esc_url_raw( $wpe_product_url, array( 'http', 'https' ) ), 'priceCurrency' => get_woocommerce_currency(), 'availability' => $wpe_product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' );
					if ( '' !== (string) $wpe_price && is_numeric( $wpe_price ) ) {
						$wpe_offer['price'] = wc_format_decimal( $wpe_price );
					}
					$wpe_schema_product = array( '@type' => 'Product', 'name' => sanitize_text_field( $wpe_name ), 'offers' => $wpe_offer );
					$wpe_image_url = wp_get_attachment_image_url( $wpe_product->get_image_id(), 'full' );
					if ( $wpe_image_url ) { $wpe_schema_product['image'] = esc_url_raw( $wpe_image_url ); }
					$wpe_sku = $wpe_product->get_sku();
					if ( $wpe_sku ) { $wpe_schema_product['sku'] = sanitize_text_field( $wpe_sku ); }
					$wpe_schema_products[] = $wpe_schema_product;
				}
				?>
				<article class="wpe-products__item">
					<?php if ( $a['showImage'] ) : ?>
						<a class="wpe-products__image-link" href="<?php echo esc_url( $wpe_product_url ); ?>" aria-label="<?php echo esc_attr( $wpe_name ); ?>">
							<?php echo wp_kses_post( $wpe_product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $a['showSale'] && $wpe_product->is_on_sale() ) : ?>
						<span class="wpe-products__sale"><?php esc_html_e( 'Sale', 'hi-wpessential' ); ?></span>
					<?php endif; ?>
					<h3 class="wpe-products__name"><a href="<?php echo esc_url( $wpe_product_url ); ?>"><?php echo esc_html( $wpe_name ); ?></a></h3>
					<?php if ( $a['showPrice'] ) : ?>
						<div class="wpe-products__price"><?php echo wp_kses_post( $wpe_product->get_price_html() ); ?></div>
					<?php endif; ?>
					<?php if ( $a['showRating'] && (float) $wpe_product->get_average_rating() > 0 ) : ?>
						<p class="wpe-products__rating" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Rating out of 5. */ __( 'Rated %s out of 5', 'hi-wpessential' ), $wpe_product->get_average_rating() ) ); ?>">
							<?php echo esc_html( number_format_i18n( (float) $wpe_product->get_average_rating(), 1 ) ); ?> / 5
						</p>
					<?php endif; ?>
					<?php if ( $a['showButton'] ) : ?>
						<a class="wpe-products__action" href="<?php echo esc_url( $wpe_product_url ); ?>"><?php echo esc_html( (string) $a['buttonLabel'] ); ?></a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="wpe-products__empty"><?php echo esc_html( (string) $a['emptyMessage'] ); ?></p>
	<?php endif; ?>
</section>
<?php if ( $wpe_schema_products ) : ?><script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $wpe_schema_products ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* prevents script termination. ?></script><?php endif; ?>
