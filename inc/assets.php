<?php
/**
 * Front-end and editor assets. No jQuery dependency.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'wpessential', WPESSENTIAL_URI . '/assets/css/theme.css', array(), WPESSENTIAL_ASSET_VERSION );
		// Marketplace sections (.ess-*), used by the WPEssential.org demo in pages, header and footer.
		wp_enqueue_style( 'wpessential-marketplace', WPESSENTIAL_URI . '/assets/css/marketplace.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );
		wp_enqueue_style( 'wpessential-demos', WPESSENTIAL_URI . '/assets/css/demos.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );

		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_style( 'wpessential-woocommerce', WPESSENTIAL_URI . '/assets/css/woocommerce.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );
		}

		if ( did_action( 'elementor/loaded' ) ) {
			wp_enqueue_style( 'wpessential-elementor', WPESSENTIAL_URI . '/assets/css/elementor.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );
		}

		// Breakpoint tokens load last so every step overrides the base styles.
		$wpessential_deps = array( 'wpessential' );
		if ( wp_style_is( 'wpessential-woocommerce', 'enqueued' ) ) {
			$wpessential_deps[] = 'wpessential-woocommerce';
		}
		// Hover system: values come from each style variation's custom.hover tokens.
		wp_enqueue_style( 'wpessential-hover', WPESSENTIAL_URI . '/assets/css/hover.css', $wpessential_deps, WPESSENTIAL_ASSET_VERSION );
		$wpessential_deps[] = 'wpessential-hover';
		wp_enqueue_style( 'wpessential-responsive', WPESSENTIAL_URI . '/assets/css/responsive.css', $wpessential_deps, WPESSENTIAL_ASSET_VERSION );

		wp_enqueue_style( 'wpessential-dark', WPESSENTIAL_URI . '/assets/css/dark.css', array( 'wpessential-responsive' ), WPESSENTIAL_ASSET_VERSION );

		wp_enqueue_script(
			'wpessential',
			WPESSENTIAL_URI . '/assets/js/theme.js',
			array(),
			WPESSENTIAL_ASSET_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
);

// Block styles from the toolkit also need to look right inside the editor canvas.
add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_style( 'wpessential-woocommerce-editor', WPESSENTIAL_URI . '/assets/css/woocommerce.css', array(), WPESSENTIAL_ASSET_VERSION );
		}
	}
);
