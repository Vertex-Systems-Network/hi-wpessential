<?php
/**
 * Dashboard screen.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Admin menu and app shell.
 */
class Admin {

	const SLUG = 'wpessential';

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'after_switch_theme', array( $this, 'flag_welcome' ) );
		add_action( 'admin_init', array( $this, 'welcome_redirect' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Menu entries. Sub-items deep-link into the app's tabs.
	 */
	public function menu() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a7aaad" d="M10 1.5 2 5.75v8.5L10 18.5l8-4.25v-8.5L10 1.5Zm0 2.3 5.6 3L10 9.8 4.4 6.8 10 3.8Zm-6 4.7 5 2.7v5.1l-5-2.7V8.5Zm7 7.8v-5.1l5-2.7v5.1l-5 2.7Z"/></svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Menu icon data URI.
		add_menu_page( self::brand(), self::brand(), 'manage_options', self::SLUG, array( $this, 'render' ), $icon, 59 );
		$tabs = array(
			'welcome' => __( 'Dashboard', 'hi-wpessential' ),
			'demos'   => __( 'Demo Library', 'hi-wpessential' ),
			'plugins' => __( 'Plugins', 'hi-wpessential' ),
			'license' => __( 'License', 'hi-wpessential' ),
			'status'  => __( 'System Status', 'hi-wpessential' ),
		);
		foreach ( $tabs as $tab => $label ) {
			add_submenu_page( self::SLUG, $label, $label, 'manage_options', 'welcome' === $tab ? self::SLUG : self::SLUG . '#' . $tab, 'welcome' === $tab ? array( $this, 'render' ) : '__return_null' );
		}
	}

	/**
	 * Dashboard name (White Label licenses can rename it).
	 *
	 * @return string
	 */
	public static function brand() {
		$name = License::is_active() && ! empty( License::data()['white_label'] ) ? (string) get_option( 'wpessential_core_brand', '' ) : '';
		return $name ? $name : __( 'WPEssential', 'hi-wpessential' );
	}

	/**
	 * Mark the first visit after activation.
	 */
	public function flag_welcome() {
		set_transient( 'wpessential_core_welcome', 1, 60 );
	}

	/**
	 * Open the dashboard once after the theme is activated.
	 */
	public function welcome_redirect() {
		if ( ! get_transient( 'wpessential_core_welcome' ) || wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		delete_transient( 'wpessential_core_welcome' );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Body class for the full-bleed layout.
	 *
	 * @param string $classes Classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		$screen = get_current_screen();
		return $screen && 'toplevel_page_' . self::SLUG === $screen->id ? $classes . ' wpe-core-screen' : $classes;
	}

	/**
	 * Enqueue the app.
	 *
	 * @param string $hook Screen hook.
	 */
	public function assets( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wpessential-core-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts.
		wp_enqueue_style( 'wpessential-core', wpessential_core_url( 'assets/admin.css' ), array(), WPESSENTIAL_CORE_ASSET_VERSION );
		wp_enqueue_script( 'wpessential-core', wpessential_core_url( 'assets/admin.js' ), array( 'wp-api-fetch', 'wp-i18n' ), WPESSENTIAL_CORE_ASSET_VERSION, true );
		wp_set_script_translations( 'wpessential-core', 'hi-wpessential', defined( 'WPESSENTIAL_DIR' ) ? WPESSENTIAL_DIR . '/languages' : '' );

		$theme = wp_get_theme();
		$user  = wp_get_current_user();
		wp_localize_script(
			'wpessential-core',
			'wpessentialCore',
			array(
				'version'   => WPESSENTIAL_CORE_VERSION,
				'theme'     => array(
					'name'    => $theme->get( 'Name' ),
					'version' => $theme->get( 'Version' ),
					'shot'    => $theme->get_screenshot(),
				),
				'user'      => $user->display_name,
				'site'      => home_url( '/' ),
				'admin'     => admin_url(),
				'editor'    => admin_url( 'site-editor.php' ),
				'elementor' => did_action( 'elementor/loaded' ) > 0,
				'woo'       => class_exists( 'WooCommerce' ),
				'docs'      => 'https://wpessential.org/docs/',
				'support'   => 'https://wpessential.org/support/',
				'steps'     => Importer::step_labels(),
				'brand'     => self::brand(),
				'pricing'   => 'https://wpessential.org/pricing/',
			)
		);
	}

	/**
	 * App root. The page works without JS only to explain that JS is needed.
	 */
	public function render() {
		echo '<div id="wpe-app" class="wpe-app" aria-live="polite"><noscript><p>' . esc_html__( 'The WPEssential dashboard needs JavaScript. Enable it in your browser to import demos.', 'hi-wpessential' ) . '</p></noscript></div>';
	}
}
