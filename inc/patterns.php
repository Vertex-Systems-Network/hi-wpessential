<?php
/**
 * Pattern categories. Pattern files in /patterns are registered by core.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		$categories = array(
			'wpessential'          => __( 'WPEssential', 'hi-wpessential' ),
			'wpessential-hero'     => __( 'WPEssential — Heroes', 'hi-wpessential' ),
			'wpessential-sections' => __( 'WPEssential — Sections', 'hi-wpessential' ),
			'wpessential-headers'  => __( 'WPEssential — Headers', 'hi-wpessential' ),
			'wpessential-footers'  => __( 'WPEssential — Footers', 'hi-wpessential' ),
			'wpessential-pages'    => __( 'WPEssential — Full pages', 'hi-wpessential' ),
			'wpessential-shop'     => __( 'WPEssential — Shop', 'hi-wpessential' ),
		);
		foreach ( $categories as $slug => $label ) {
			register_block_pattern_category( $slug, array( 'label' => $label ) );
		}
	}
);

// Shop patterns use WooCommerce shortcodes, so they only exist when WooCommerce is active.
add_action(
	'init',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$patterns = array(
			'shop-categories' => __( 'Shop — Category grid', 'hi-wpessential' ),
			'shop-products'   => __( 'Shop — Featured, new and sale products', 'hi-wpessential' ),
		);
		foreach ( $patterns as $slug => $title ) {
			ob_start();
			require WPESSENTIAL_DIR . '/inc/patterns-shop/' . $slug . '.php';
			register_block_pattern(
				'wpessential/' . $slug,
				array(
					'title'         => $title,
					'categories'    => array( 'wpessential-shop' ),
					'viewportWidth' => 1440,
					'content'       => ob_get_clean(),
				)
			);
		}
	}
);
