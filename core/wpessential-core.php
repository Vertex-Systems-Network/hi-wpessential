<?php
/**
 * Plugin Name:       WPEssential Core
 * Plugin URI:        https://wpessential.org/core/
 * Description:       One-click demo import, plugin manager and license activation for the Hi WPEssential theme.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            WPEssential Team
 * Author URI:        https://wpessential.org/
 * License:           GPL-2.0-or-later
 * Text Domain:       hi-wpessential
 *
 * This folder ships inside the theme and is loaded by functions.php only when the
 * standalone plugin is not active. To extract it, copy core/ to wp-content/plugins/wpessential-core/:
 * this file already carries the plugin header and nothing here depends on theme code.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'WPESSENTIAL_CORE_VERSION' ) ) {
	return;
}

define( 'WPESSENTIAL_CORE_VERSION', '1.0.0' );
define( 'WPESSENTIAL_CORE_FILE', __FILE__ );
define( 'WPESSENTIAL_CORE_DIR', __DIR__ );

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'WPEssential\\Core\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}
		$file = WPESSENTIAL_CORE_DIR . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

/**
 * Public URL of a file inside core/, whether it runs as a plugin or inside the theme.
 *
 * @param string $path Relative path.
 * @return string
 */
function wpessential_core_url( $path = '' ) {
	$real = static function ( $dir ) {
		$resolved = realpath( $dir );
		return wp_normalize_path( $resolved ? $resolved : $dir );
	};
	$dir  = $real( WPESSENTIAL_CORE_DIR );
	if ( 0 === strpos( $dir, $real( WP_PLUGIN_DIR ) ) ) {
		return plugins_url( $path, WPESSENTIAL_CORE_FILE );
	}
	return get_template_directory_uri() . substr( $dir, strlen( $real( get_template_directory() ) ) ) . '/' . ltrim( $path, '/' );
}

WPEssential\Core\Plugin::instance();
