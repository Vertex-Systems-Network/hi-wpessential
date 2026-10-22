<?php
/**
 * Core bootstrap.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the modules together.
 */
final class Plugin {

	/**
	 * Singleton.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook everything.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( new Rest(), 'register' ) );
		add_action( 'rest_api_init', array( Playground::class, 'routes' ) );
		if ( is_admin() ) {
			( new Admin() )->hooks();
		}
		License::schedule();
		Forms::hooks();
		Mcp::hooks();
		McpBuilder::hooks();
		Kit::hooks();
		PageSettings::hooks();
	}
}
