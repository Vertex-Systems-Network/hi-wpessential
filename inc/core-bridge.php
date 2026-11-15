<?php
/**
 * Contract between the theme and the WPEssential plugin.
 *
 * Elements, demo import, plugins and licensing live in the WPEssential plugin. This file
 * exposes the theme data the plugin reads and recommends the plugin when it is missing.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme capabilities the Core plugin can rely on.
 *
 * @return array
 */
function wpessential_theme_supports() {
	return apply_filters(
		'wpessential_theme_supports',
		array(
			'version'  => WPESSENTIAL_VERSION,
			'builders' => array( 'gutenberg', 'elementor' ),
			'features' => array( 'block-templates', 'layout-html-filter', 'elementor-locations', 'elementor-kit-sync', 'woocommerce', 'style-variations' ),
		)
	);
}

/**
 * Where the demo manifest lives. See docs/demo-manifest.schema.json.
 *
 * @return string
 */
function wpessential_demo_manifest_url() {
	return apply_filters( 'wpessential_demos_manifest_url', 'https://wpessential.org/wp-json/wpessential-server/v1/demos' );
}

/**
 * Recommend the Core plugin. Suggestion only — no automatic install.
 */
add_action(
	'admin_notices',
	static function () {
		if ( defined( 'WPE_VERSION' ) || ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'wpessential_dismiss_core_notice', true ) ) {
			return;
		}
		$url = wp_nonce_url( add_query_arg( 'wpessential-dismiss', '1' ), 'wpessential-dismiss' );
		printf(
			'<div class="notice notice-info"><p>%1$s <a href="%2$s">%3$s</a> | <a href="%4$s">%5$s</a></p></div>',
			esc_html__( 'Hi WPEssential works with the free WPEssential plugin: its elements for Gutenberg and Elementor, one-click demos and site tools.', 'hi-wpessential' ),
			esc_url( 'https://wpessential.org/' ),
			esc_html__( 'Install WPEssential', 'hi-wpessential' ),
			esc_url( $url ),
			esc_html__( 'Dismiss', 'hi-wpessential' )
		);
	}
);

add_action(
	'admin_init',
	static function () {
		if ( isset( $_GET['wpessential-dismiss'] ) && check_admin_referer( 'wpessential-dismiss' ) ) {
			update_user_meta( get_current_user_id(), 'wpessential_dismiss_core_notice', 1 );
		}
	}
);
