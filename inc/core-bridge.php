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
/**
 * State of the recommended WPEssential plugin (wordpress.org/plugins/wpessential).
 *
 * @return string "active", "installed" or "missing".
 */
function wpessential_plugin_state() {
	if ( defined( 'WPE_VERSION' ) ) {
		return 'active';
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	return isset( get_plugins()['wpessential/wpessential.php'] ) ? 'installed' : 'missing';
}

/**
 * Whether the welcome banner shows for the current user and screen.
 *
 * @return bool
 */
function wpessential_plugin_banner_visible() {
	if ( 'active' === wpessential_plugin_state() || get_user_meta( get_current_user_id(), 'wpessential_dismiss_core_notice', true ) ) {
		return false;
	}
	$can    = 'missing' === wpessential_plugin_state() ? current_user_can( 'install_plugins' ) : current_user_can( 'activate_plugins' );
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return $can && $screen && in_array( $screen->id, array( 'dashboard', 'themes', 'plugins', 'update-core' ), true );
}

// Welcome banner: one click installs and activates WPEssential from WordPress.org.
add_action(
	'admin_notices',
	static function () {
		if ( ! wpessential_plugin_banner_visible() ) {
			return;
		}
		$state     = wpessential_plugin_state();
		$install   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wpessential' ), 'install-plugin_wpessential' );
		$activate  = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=wpessential/wpessential.php' ), 'activate-plugin_wpessential/wpessential.php' );
		$dismiss   = wp_nonce_url( add_query_arg( 'wpessential-dismiss', '1' ), 'wpessential-dismiss' );
		$features  = array(
			__( 'Elements for Gutenberg and Elementor', 'hi-wpessential' ),
			__( 'One-click demo sites', 'hi-wpessential' ),
			__( 'SEO, language switcher and custom code', 'hi-wpessential' ),
		);
		?>
		<div class="notice wpe-welcome is-dismissible" data-state="<?php echo esc_attr( $state ); ?>" data-activate="<?php echo esc_url( $activate ); ?>">
			<div class="wpe-welcome__art" aria-hidden="true">
				<svg viewBox="0 0 64 64" width="64" height="64"><rect width="64" height="64" rx="16" fill="url(#wpe-g)"/><path d="M16 22l7 21 6-15 6 15 7-21" fill="none" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="48" cy="20" r="4" fill="#fff"/><defs><linearGradient id="wpe-g" x1="0" y1="0" x2="64" y2="64"><stop offset="0" stop-color="#4f46e5"/><stop offset="1" stop-color="#e8701a"/></linearGradient></defs></svg>
			</div>
			<div class="wpe-welcome__body">
				<p class="wpe-welcome__eyebrow"><?php esc_html_e( 'Thanks for choosing Hi WPEssential', 'hi-wpessential' ); ?></p>
				<h2 class="wpe-welcome__title"><?php esc_html_e( 'Get the most from your theme with the free WPEssential plugin', 'hi-wpessential' ); ?></h2>
				<ul class="wpe-welcome__list">
					<?php foreach ( $features as $feature ) : ?>
						<li><?php echo esc_html( $feature ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="wpe-welcome__actions">
					<?php if ( 'missing' === $state ) : ?>
						<a class="button button-primary button-hero wpe-welcome__cta" href="<?php echo esc_url( $install ); ?>" data-action="install"><?php esc_html_e( 'Install WPEssential', 'hi-wpessential' ); ?></a>
					<?php else : ?>
						<a class="button button-primary button-hero wpe-welcome__cta" href="<?php echo esc_url( $activate ); ?>" data-action="activate"><?php esc_html_e( 'Activate WPEssential', 'hi-wpessential' ); ?></a>
					<?php endif; ?>
					<a class="wpe-welcome__more" href="<?php echo esc_url( 'https://wordpress.org/plugins/wpessential/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more on WordPress.org', 'hi-wpessential' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'hi-wpessential' ); ?></span></a>
					<span class="wpe-welcome__status" role="status" aria-live="polite"></span>
				</div>
				<p class="wpe-welcome__note"><?php esc_html_e( 'Free and optional: the theme works fully without it.', 'hi-wpessential' ); ?> <a href="<?php echo esc_url( $dismiss ); ?>" class="wpe-welcome__hide"><?php esc_html_e( 'Don\'t show this again', 'hi-wpessential' ); ?></a></p>
			</div>
		</div>
		<?php
	}
);

add_action(
	'admin_enqueue_scripts',
	static function () {
		if ( ! wpessential_plugin_banner_visible() ) {
			return;
		}
		wp_enqueue_style( 'wpessential-welcome', WPESSENTIAL_URI . '/assets/css/admin-welcome.css', array(), WPESSENTIAL_ASSET_VERSION );
		wp_enqueue_script( 'wpessential-welcome', WPESSENTIAL_URI . '/assets/js/admin-welcome.js', array( 'updates', 'wp-i18n' ), WPESSENTIAL_ASSET_VERSION, true );
		wp_set_script_translations( 'wpessential-welcome', 'hi-wpessential', WPESSENTIAL_DIR . '/languages' );
		wp_localize_script(
			'wpessential-welcome',
			'wpessentialWelcome',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wpessential-dismiss' ),
			)
		);
	}
);

// Dismiss for good (the X button, or the link without JavaScript).
add_action(
	'wp_ajax_wpessential_dismiss_welcome',
	static function () {
		check_ajax_referer( 'wpessential-dismiss' );
		update_user_meta( get_current_user_id(), 'wpessential_dismiss_core_notice', 1 );
		wp_send_json_success();
	}
);

add_action(
	'admin_init',
	static function () {
		if ( isset( $_GET['wpessential-dismiss'] ) && check_admin_referer( 'wpessential-dismiss' ) ) {
			update_user_meta( get_current_user_id(), 'wpessential_dismiss_core_notice', 1 );
			wp_safe_redirect( remove_query_arg( array( 'wpessential-dismiss', '_wpnonce' ) ) );
			exit;
		}
	}
);
