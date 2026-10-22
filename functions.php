<?php
/**
 * WPEssential functions and definitions.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

define( 'WPESSENTIAL_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
// Cache-busting for CSS/JS: the version stays 1.0.0 until the wordpress.org release, so the
// file time of style.css (new on every theme update) is added to asset URLs.
define( 'WPESSENTIAL_ASSET_VERSION', WPESSENTIAL_VERSION . '.' . (int) filemtime( get_template_directory() . '/style.css' ) );
define( 'WPESSENTIAL_DIR', get_template_directory() );
define( 'WPESSENTIAL_URI', get_template_directory_uri() );

require WPESSENTIAL_DIR . '/inc/setup.php';
require WPESSENTIAL_DIR . '/inc/assets.php';
require WPESSENTIAL_DIR . '/inc/block-styles.php';
require WPESSENTIAL_DIR . '/inc/patterns.php';
require WPESSENTIAL_DIR . '/inc/layouts.php';
require WPESSENTIAL_DIR . '/inc/core-bridge.php';
require WPESSENTIAL_DIR . '/inc/scheme.php';
require WPESSENTIAL_DIR . '/inc/motion.php';
require WPESSENTIAL_DIR . '/inc/options.php';
require WPESSENTIAL_DIR . '/inc/customizer.php';
require WPESSENTIAL_DIR . '/inc/seo.php';
require WPESSENTIAL_DIR . '/inc/multilingual.php';

// Demo import, plugin manager and license. Self-contained in core/ so it can ship as a
// separate plugin later; the standalone plugin wins when both are present.
if ( ! defined( 'WPESSENTIAL_CORE_VERSION' ) && apply_filters( 'wpessential_load_bundled_core', true ) ) {
	require WPESSENTIAL_DIR . '/core/wpessential-core.php';
}

// Elementor may load before or after the theme.
if ( did_action( 'elementor/loaded' ) ) {
	require WPESSENTIAL_DIR . '/inc/elementor.php';
} else {
	add_action(
		'elementor/loaded',
		static function () {
			require WPESSENTIAL_DIR . '/inc/elementor.php';
		}
	);
}

// WooCommerce is loaded before themes, so a class check is reliable here.
if ( class_exists( 'WooCommerce' ) ) {
	require WPESSENTIAL_DIR . '/inc/woocommerce.php';
}
