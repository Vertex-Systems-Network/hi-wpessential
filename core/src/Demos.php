<?php
/**
 * Demo catalogue: bundled demos plus the remote manifest.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Reads demos and resolves their packages to a local folder.
 */
class Demos {

	const CACHE = 'wpessential_core_manifest';

	/**
	 * Manifest URL (filterable by the theme or a site).
	 *
	 * @return string
	 */
	public static function manifest_url() {
		$default = function_exists( 'wpessential_demo_manifest_url' ) ? wpessential_demo_manifest_url() : 'https://api.wpessential.org/v1/demos.json';
		return (string) apply_filters( 'wpessential_core_manifest_url', $default );
	}

	/**
	 * Every demo, bundled first. Adds a "notice" when the remote library is unreachable.
	 *
	 * @param bool $refresh Skip the cache.
	 * @return array{demos: array, notices: array}
	 */
	public static function all( $refresh = false ) {
		$notices = array();
		$demos   = self::bundled();

		$remote = $refresh ? false : get_transient( self::CACHE );
		if ( false === $remote ) {
			$response = wp_safe_remote_get( self::manifest_url(), array( 'timeout' => 8 ) );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			if ( is_wp_error( $response ) || 200 !== (int) $code || empty( $body['demos'] ) ) {
				$remote = array();
				set_transient( self::CACHE, $remote, 15 * MINUTE_IN_SECONDS );
				$notices[] = array(
					'level'   => 'info',
					'message' => __( 'The online demo library could not be reached, so only the demos bundled with the theme are listed. They import fully offline.', 'wpessential' ),
				);
			} else {
				$remote = array_map(
					static function ( $demo ) {
						$demo['source'] = 'remote';
						return $demo;
					},
					$body['demos']
				);
				set_transient( self::CACHE, $remote, 12 * HOUR_IN_SECONDS );
			}
		}

		$ids = wp_list_pluck( $demos, 'id' );
		foreach ( (array) $remote as $demo ) {
			if ( isset( $demo['id'] ) && ! in_array( $demo['id'], $ids, true ) ) {
				$demos[] = $demo;
			}
		}

		$imported = Importer::imported();
		foreach ( $demos as &$demo ) {
			$demo['imported'] = isset( $imported['demo'] ) && $imported['demo'] === $demo['id'];
			$demo['locked']   = 'pro' === ( $demo['type'] ?? 'free' ) && ! License::is_active();
		}
		unset( $demo );

		return array(
			'demos'   => apply_filters( 'wpessential_core_demos', $demos ),
			'notices' => $notices,
		);
	}

	/**
	 * Demos shipped in core/demos/{id}/demo.json.
	 *
	 * @return array
	 */
	public static function bundled() {
		$demos = array();
		foreach ( (array) glob( WPESSENTIAL_CORE_DIR . '/demos/*/demo.json' ) as $file ) {
			$demo = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
			if ( ! is_array( $demo ) || empty( $demo['id'] ) ) {
				continue;
			}
			$demo['source']    = 'bundled';
			$demo['thumbnail'] = self::resolve_asset( $demo['thumbnail'] ?? '' );
			$demo['preview']   = array_map( array( __CLASS__, 'resolve_asset' ), (array) ( $demo['preview'] ?? array() ) );
			$demos[]           = $demo;
		}
		return $demos;
	}

	/**
	 * Turn "theme:assets/x.webp" or "core:demos/x.webp" into a URL.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function resolve_asset( $value ) {
		if ( 0 === strpos( (string) $value, 'theme:' ) ) {
			return get_theme_file_uri( substr( $value, 6 ) );
		}
		if ( 0 === strpos( (string) $value, 'core:' ) ) {
			return wpessential_core_url( substr( $value, 5 ) );
		}
		return (string) $value;
	}

	/**
	 * Find one demo.
	 *
	 * @param string $id Demo id.
	 * @return array|null
	 */
	public static function get( $id ) {
		foreach ( self::all()['demos'] as $demo ) {
			if ( $demo['id'] === $id ) {
				return $demo;
			}
		}
		return null;
	}

	/**
	 * Local folder holding content.json for a demo + builder. Downloads remote packages.
	 *
	 * @param array  $demo    Demo.
	 * @param string $builder Builder key.
	 * @return string|\WP_Error
	 */
	public static function package_dir( $demo, $builder ) {
		$package = $demo['builders'][ $builder ]['package'] ?? '';
		if ( 'bundled' === $demo['source'] ) {
			$dir = WPESSENTIAL_CORE_DIR . '/demos/' . $demo['id'] . '/' . ( $package ? $package : $builder );
			return is_readable( $dir . '/content.json' ) ? $dir : new \WP_Error( 'wpe_package_missing', __( 'The bundled demo package is incomplete. Re-upload the theme and try again.', 'wpessential' ) );
		}

		if ( 'licensed' === $package ) {
			$package = License::package_url( $demo['id'], $builder );
			if ( is_wp_error( $package ) ) {
				return $package;
			}
		}
		if ( ! wp_http_validate_url( $package ) ) {
			return new \WP_Error( 'wpe_package_url', __( 'This demo has no download address in the library.', 'wpessential' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpessential-demos/' . sanitize_key( $demo['id'] . '-' . $builder );
		if ( is_readable( $dir . '/content.json' ) ) {
			return $dir;
		}

		$tmp = download_url( $package, 120 );
		if ( is_wp_error( $tmp ) ) {
			/* translators: %s: error message. */
			return new \WP_Error( 'wpe_download', sprintf( __( 'Downloading the demo package failed: %s', 'wpessential' ), $tmp->get_error_message() ) );
		}
		if ( ! WP_Filesystem() ) {
			wp_delete_file( $tmp );
			return new \WP_Error( 'wpe_fs', __( 'WordPress cannot write to the uploads folder. Check its permissions.', 'wpessential' ) );
		}
		$result = unzip_file( $tmp, $dir );
		wp_delete_file( $tmp );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return is_readable( $dir . '/content.json' ) ? $dir : new \WP_Error( 'wpe_package_invalid', __( 'The downloaded package does not contain content.json.', 'wpessential' ) );
	}
}
