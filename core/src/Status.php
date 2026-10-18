<?php
/**
 * Server checks for the System Status tab and the importer preflight.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * System status.
 */
class Status {

	/**
	 * Every check: label, value, state (ok|warn|fail), hint, blocking.
	 *
	 * @return array
	 */
	public static function checks() {
		global $wp_version;
		$memory  = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );
		$time    = (int) ini_get( 'max_execution_time' );
		$upload  = wp_max_upload_size();
		$uploads = wp_upload_dir();
		$checks  = array(
			array(
				'label' => __( 'WordPress version', 'hi-wpessential' ),
				'value' => $wp_version,
				'state' => version_compare( $wp_version, '6.5', '>=' ) ? 'ok' : 'fail',
				'hint'  => __( 'Update WordPress to 6.5 or newer.', 'hi-wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'PHP version', 'hi-wpessential' ),
				'value' => PHP_VERSION,
				'state' => version_compare( PHP_VERSION, '7.4', '>=' ) ? ( version_compare( PHP_VERSION, '8.1', '>=' ) ? 'ok' : 'warn' ) : 'fail',
				'hint'  => __( 'PHP 8.1 or newer is faster and still supported. Ask your host to upgrade.', 'hi-wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'Memory limit', 'hi-wpessential' ),
				'value' => $memory < 0 ? __( 'Unlimited', 'hi-wpessential' ) : size_format( $memory ),
				'state' => $memory < 0 || $memory >= 256 * MB_IN_BYTES ? 'ok' : ( $memory >= 128 * MB_IN_BYTES ? 'warn' : 'fail' ),
				'hint'  => __( 'Set memory_limit to 256M in php.ini, or define WP_MEMORY_LIMIT in wp-config.php.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Max execution time', 'hi-wpessential' ),
				'value' => $time ? $time . 's' : __( 'Unlimited', 'hi-wpessential' ),
				'state' => ( 0 === $time || $time >= 30 ) ? 'ok' : 'warn',
				'hint'  => __( 'The importer works in short batches, so 30 seconds is enough.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Max upload size', 'hi-wpessential' ),
				'value' => size_format( $upload ),
				'state' => $upload >= 8 * MB_IN_BYTES ? 'ok' : 'warn',
				'hint'  => __( 'Raise upload_max_filesize and post_max_size to at least 8M.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Uploads folder', 'hi-wpessential' ),
				'value' => empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] ) ? __( 'Writable', 'hi-wpessential' ) : __( 'Not writable', 'hi-wpessential' ),
				'state' => empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] ) ? 'ok' : 'fail',
				'hint'  => __( 'Give the web server write access to wp-content/uploads.', 'hi-wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'Plugins folder', 'hi-wpessential' ),
				'value' => wp_is_writable( WP_PLUGIN_DIR ) ? __( 'Writable', 'hi-wpessential' ) : __( 'Not writable', 'hi-wpessential' ),
				'state' => wp_is_writable( WP_PLUGIN_DIR ) ? 'ok' : 'warn',
				'hint'  => __( 'Without write access, required plugins must be installed by hand.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'ZIP support', 'hi-wpessential' ),
				'value' => class_exists( 'ZipArchive' ) ? 'ZipArchive' : 'PclZip',
				'state' => class_exists( 'ZipArchive' ) ? 'ok' : 'warn',
				'hint'  => __( 'Enable the PHP zip extension for faster package extraction.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Image editor', 'hi-wpessential' ),
				'value' => wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? __( 'WebP supported', 'hi-wpessential' ) : __( 'No WebP support', 'hi-wpessential' ),
				'state' => wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? 'ok' : 'warn',
				'hint'  => __( 'Images still import, but WordPress cannot create smaller sizes. Ask your host to enable Imagick or GD with WebP.', 'hi-wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Permalinks', 'hi-wpessential' ),
				'value' => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Plain', 'hi-wpessential' ),
				'state' => get_option( 'permalink_structure' ) ? 'ok' : 'warn',
				'hint'  => __( 'Choose "Post name" under Settings → Permalinks for clean shop and page addresses.', 'hi-wpessential' ),
				'block' => false,
			),
		);

		return array_map(
			static function ( $check ) {
				return array(
					'label'    => $check['label'],
					'value'    => (string) $check['value'],
					'state'    => $check['state'],
					'hint'     => 'ok' === $check['state'] ? '' : $check['hint'],
					'blocking' => $check['block'],
				);
			},
			$checks
		);
	}

	/**
	 * Environment summary.
	 *
	 * @return array
	 */
	public static function environment() {
		$theme = wp_get_theme();
		return array(
			array( __( 'Theme', 'hi-wpessential' ), $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ),
			array( __( 'Core module', 'hi-wpessential' ), WPESSENTIAL_CORE_VERSION . ' (' . ( 0 === strpos( wp_normalize_path( WPESSENTIAL_CORE_DIR ), wp_normalize_path( WP_PLUGIN_DIR ) ) ? __( 'plugin', 'hi-wpessential' ) : __( 'built into theme', 'hi-wpessential' ) ) . ')' ),
			array( __( 'Site address', 'hi-wpessential' ), home_url() ),
			array( __( 'Multisite', 'hi-wpessential' ), is_multisite() ? __( 'Yes', 'hi-wpessential' ) : __( 'No', 'hi-wpessential' ) ),
			array( __( 'Server', 'hi-wpessential' ), isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '' ),
			array( __( 'Database', 'hi-wpessential' ), $GLOBALS['wpdb']->db_server_info() ),
			array( __( 'Active plugins', 'hi-wpessential' ), (string) count( (array) get_option( 'active_plugins', array() ) ) ),
		);
	}
}
