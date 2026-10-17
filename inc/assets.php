<?php
/**
 * Front-end and editor assets. No jQuery dependency.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'wpessential', WPESSENTIAL_URI . '/assets/css/theme.css', array(), WPESSENTIAL_VERSION );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'wpessential-woocommerce', WPESSENTIAL_URI . '/assets/css/woocommerce.css', array( 'wpessential' ), WPESSENTIAL_VERSION );
	}

	if ( did_action( 'elementor/loaded' ) ) {
		wp_enqueue_style( 'wpessential-elementor', WPESSENTIAL_URI . '/assets/css/elementor.css', array( 'wpessential' ), WPESSENTIAL_VERSION );
	}

	wp_enqueue_script(
		'wpessential',
		WPESSENTIAL_URI . '/assets/js/theme.js',
		array(),
		WPESSENTIAL_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
} );

// Block styles from the toolkit also need to look right inside the editor canvas.
add_action( 'enqueue_block_editor_assets', static function () {
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'wpessential-woocommerce-editor', WPESSENTIAL_URI . '/assets/css/woocommerce.css', array(), WPESSENTIAL_VERSION );
	}
} );
