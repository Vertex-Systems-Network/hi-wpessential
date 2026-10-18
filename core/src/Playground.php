<?php
/**
 * Live previews for bundled demos in WordPress Playground, without a demo server.
 *
 * Playground (WordPress running in the visitor's browser) loads a blueprint from this site:
 * it installs this theme from a zip this site serves, installs the demo's wordpress.org
 * plugins and runs the same importer customers use. Every page, link and hover in the
 * preview is the real demo. Remote (server) demos keep the server's own Playground links.
 *
 * Disable with add_filter( 'wpessential_core_live_preview', '__return_false' ).
 *
 * @package WPEssential
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Playground.
 */
class Playground {

	const PLAYGROUND = 'https://playground.wordpress.net/';

	/**
	 * Whether live previews can work here: on, ZipArchive available and the site reachable
	 * from the internet (Playground fetches the blueprint and theme from this site).
	 *
	 * @return bool
	 */
	public static function enabled() {
		$host   = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$public = $host && ! preg_match( '/^(localhost|127\.|10\.|192\.168\.|\[?::1)|\.(local|test|localhost|invalid)$/i', $host );
		return (bool) apply_filters( 'wpessential_core_live_preview', $public && class_exists( '\ZipArchive' ) );
	}

	/**
	 * REST routes (public: Playground runs on another origin, without cookies).
	 */
	public static function routes() {
		register_rest_route(
			Rest::NS,
			'/playground/blueprint',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'blueprint' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			Rest::NS,
			'/playground/theme',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'theme' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Link that opens a bundled demo in Playground, or '' when it cannot run there.
	 *
	 * @param array  $demo    Bundled demo.
	 * @param string $builder Builder.
	 * @return string
	 */
	public static function open_url( $demo, $builder ) {
		if ( ! self::enabled() || ! self::in_theme( $demo ) || ! empty( $demo['locked'] ) || 'pro' === ( $demo['type'] ?? 'free' ) ) {
			return '';
		}
		$blueprint = add_query_arg(
			array(
				'demo'    => $demo['id'],
				'builder' => $builder,
				'v'       => self::version(),
			),
			rest_url( Rest::NS . '/playground/blueprint' )
		);
		return self::PLAYGROUND . '?blueprint-url=' . rawurlencode( $blueprint );
	}

	/**
	 * The demo's files ship inside the theme zip Playground installs.
	 *
	 * @param array $demo Demo.
	 * @return bool
	 */
	private static function in_theme( $demo ) {
		$theme = wp_normalize_path( (string) realpath( get_stylesheet_directory() ) );
		$dir   = wp_normalize_path( (string) realpath( (string) ( $demo['dir'] ?? '' ) ) );
		if ( ! $theme || ! $dir || 0 !== strpos( $dir, $theme . '/' ) ) {
			return false;
		}
		$first = strtok( substr( $dir, strlen( $theme ) + 1 ), '/' );
		return ! in_array( $first, self::excluded(), true );
	}

	/**
	 * Top-level theme folders/files left out of the zip (.distignore when present).
	 *
	 * @return string[]
	 */
	private static function excluded() {
		$list = array( '.git', '.github', 'node_modules', 'vendor', 'tools', 'docs', 'server', 'demos', 'dist', 'build' );
		$file = get_stylesheet_directory() . '/.distignore';
		if ( is_readable( $file ) ) {
			foreach ( (array) file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
				$line = trim( preg_replace( '/#.*/', '', $line ) );
				if ( '' !== $line ) {
					$list[] = trim( $line, '/' );
				}
			}
		}
		return array_values( array_unique( $list ) );
	}

	/**
	 * Cache key for the theme zip: theme version plus newest file change.
	 *
	 * @return string
	 */
	private static function version() {
		$theme = wp_get_theme();
		return substr( md5( $theme->get( 'Version' ) . '|' . filemtime( get_stylesheet_directory() . '/style.css' ) . '|' . WPESSENTIAL_CORE_VERSION ), 0, 10 );
	}

	/**
	 * Blueprint JSON.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function blueprint( $request ) {
		$demo    = Demos::get( sanitize_key( (string) $request->get_param( 'demo' ) ) );
		$builder = sanitize_key( (string) $request->get_param( 'builder' ) );
		if ( ! $demo || 'bundled' !== $demo['source'] || empty( $demo['builders'][ $builder ] ) || '' === self::open_url( $demo, $builder ) ) {
			return new \WP_Error( 'not_found', __( 'Live preview is not available for this demo.', 'hi-wpessential' ), array( 'status' => 404 ) );
		}
		$steps = array(
			array(
				'step'      => 'installTheme',
				'themeData' => array(
					'resource' => 'url',
					'url'      => add_query_arg( 'v', self::version(), rest_url( Rest::NS . '/playground/theme' ) ),
				),
				'options'   => array( 'activate' => true ),
			),
		);
		foreach ( (array) $demo['builders'][ $builder ]['plugins'] as $plugin ) {
			if ( 'wporg' !== ( $plugin['source'] ?? 'wporg' ) || 'conflict' === ( $plugin['state'] ?? '' ) ) {
				continue;
			}
			$steps[] = array(
				'step'       => 'installPlugin',
				'pluginData' => array(
					'resource' => 'wordpress.org/plugins',
					'slug'     => sanitize_key( $plugin['slug'] ),
				),
				'options'    => array( 'activate' => true ),
			);
		}
		$steps[]  = array(
			'step'    => 'setSiteOptions',
			'options' => array(
				'blogname'               => $demo['title'],
				'permalink_structure'    => '/%postname%/',
				'wpessential_playground' => 1,
			),
		);
		$steps[]  = array(
			'step' => 'runPHP',
			'code' => '<?php
require "/wordpress/wp-load.php";
wp_set_current_user( 1 );
$started = WPEssential\Core\Importer::start( "' . sanitize_key( $demo['id'] ) . '", "' . $builder . '", array( "plugins" => false ) );
if ( is_wp_error( $started ) ) {
	echo $started->get_error_message();
	return;
}
for ( $i = 0; $i < 400; $i++ ) {
	$job = WPEssential\Core\Importer::run( 0 );
	if ( "running" !== $job["status"] ) {
		break;
	}
}
',
		);
		$response = rest_ensure_response(
			array(
				'$schema'           => 'https://playground.wordpress.net/blueprint-schema.json',
				'meta'              => array(
					'title'  => $demo['title'] . ' — ' . wp_get_theme()->get( 'Name' ),
					'author' => 'WPEssential Team',
				),
				'landingPage'       => '/',
				'preferredVersions' => array(
					'php' => '8.2',
					'wp'  => 'latest',
				),
				'features'          => array( 'networking' => true ),
				'login'             => true,
				'steps'             => $steps,
			)
		);
		$response->header( 'Access-Control-Allow-Origin', '*' );
		$response->header( 'Cache-Control', 'no-store' );
		return $response;
	}

	/**
	 * Stream this theme as a zip (built once per version, cached in uploads).
	 *
	 * @return \WP_Error|void
	 */
	public static function theme() {
		if ( ! self::enabled() ) {
			return new \WP_Error( 'not_found', __( 'Live preview is not available for this demo.', 'hi-wpessential' ), array( 'status' => 404 ) );
		}
		$file = self::theme_zip();
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Content-Type: application/zip' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename="' . get_stylesheet() . '.zip"' );
		header( 'Cache-Control: public, max-age=86400' );
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a generated zip.
		exit;
	}

	/**
	 * Build (or reuse) the theme zip.
	 *
	 * @return string|\WP_Error Path.
	 */
	private static function theme_zip() {
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpessential-playground';
		$slug    = get_stylesheet();
		$file    = $dir . '/' . $slug . '-' . self::version() . '.zip';
		if ( is_readable( $file ) ) {
			return $file;
		}
		wp_mkdir_p( $dir );
		foreach ( (array) glob( $dir . '/' . $slug . '-*.zip' ) as $old ) {
			wp_delete_file( $old );
		}
		$root     = wp_normalize_path( (string) realpath( get_stylesheet_directory() ) );
		$excluded = self::excluded();
		$zip      = new \ZipArchive();
		if ( true !== $zip->open( $file . '.tmp', \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
			return new \WP_Error( 'zip', __( 'Could not build the theme package for the live preview.', 'hi-wpessential' ), array( 'status' => 500 ) );
		}
		$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS ) );
		foreach ( $files as $item ) {
			$path     = wp_normalize_path( $item->getPathname() );
			$relative = substr( $path, strlen( $root ) + 1 );
			if ( $item->isDir() || in_array( strtok( $relative, '/' ), $excluded, true ) || in_array( $relative, $excluded, true ) ) {
				continue;
			}
			$zip->addFile( $path, $slug . '/' . $relative );
		}
		$zip->close();
		rename( $file . '.tmp', $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- Atomic swap of a generated file.
		return $file;
	}
}
