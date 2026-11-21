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
		wp_enqueue_style( 'wpessential-demos', WPESSENTIAL_URI . '/assets/css/demos.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );
		// Tabs and filters for any demo; tiny, deferred, loaded only where a page uses them.
		wp_register_script(
			'wpessential-components',
			WPESSENTIAL_URI . '/assets/js/components.js',
			array(),
			WPESSENTIAL_ASSET_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

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

		// Variation swatches on variable product pages.
		if ( function_exists( 'is_product' ) && is_product() ) {
			wp_enqueue_script(
				'wpessential-swatches',
				WPESSENTIAL_URI . '/assets/js/swatches.js',
				array( 'wc-add-to-cart-variation' ),
				WPESSENTIAL_ASSET_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}

		// Live countdown, only on pages that carry one (Pulse demo home).
		if ( is_singular() && false !== strpos( (string) get_post_field( 'post_content', get_queried_object_id() ), 'wpe-px-count' ) ) {
			wp_enqueue_script(
				'wpessential-countdown',
				WPESSENTIAL_URI . '/assets/js/countdown.js',
				array(),
				WPESSENTIAL_ASSET_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
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

/*
 * Load the components script only where a block uses tabs or a filter, and show signed-in
 * visitors "My account" instead of "Sign in" (any paragraph with the class wpe-signin).
 */
add_filter(
	'render_block',
	static function ( $content, $block ) {
		if ( false !== strpos( $content, 'data-wpe-tabs' ) || false !== strpos( $content, 'data-wpe-filter' ) ) {
			wp_enqueue_script( 'wpessential-components' );
		}
		if ( 'core/paragraph' === ( $block['blockName'] ?? '' ) && false !== strpos( (string) ( $block['attrs']['className'] ?? '' ), 'wpe-signin' ) && is_user_logged_in() ) {
			$content = preg_replace( '#>\s*Sign in\s*<#', '>' . esc_html__( 'My account', 'hi-wpessential' ) . '<', $content );
		}
		return $content;
	},
	10,
	2
);

/*
 * A demo's own CSS lives in its style variation (Global Styles → Custom CSS). WordPress prints it
 * with the global styles, before the theme's stylesheets, so the theme would win every tie. Print
 * it once, right after theme.css (where demo styles always sat), so breakpoints and dark mode still follow it.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		global $wp_styles;
		if ( ! function_exists( 'wp_get_global_stylesheet' ) || ! wp_style_is( 'wpessential', 'enqueued' ) ) {
			return;
		}
		$css = trim( (string) wp_get_global_stylesheet( array( 'custom-css' ) ) );
		if ( '' === $css || empty( $wp_styles->registered['global-styles'] ) ) {
			return;
		}
		$after = (array) ( $wp_styles->registered['global-styles']->extra['after'] ?? array() );
		$found = false;
		foreach ( $after as $i => $chunk ) {
			if ( false !== strpos( (string) $chunk, $css ) ) {
				$after[ $i ] = str_replace( $css, '', (string) $chunk );
				$found       = true;
			}
		}
		if ( $found ) {
			$wp_styles->registered['global-styles']->extra['after'] = $after;
			wp_add_inline_style( 'wpessential', $css );
		}
	},
	100
);
