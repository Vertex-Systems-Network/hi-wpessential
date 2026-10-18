<?php
/**
 * Elementor integration.
 *
 * - Registers Elementor Pro Theme Builder locations (header/footer/single/archive);
 *   the actual swap of header/footer happens in inc/layouts.php.
 * - Keeps Elementor's Global Kit (colors + fonts) in sync with theme.json, so a
 *   page built in Elementor looks exactly like one built with blocks.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'elementor/theme/register_locations',
	static function ( $manager ) {
		$manager->register_all_core_location();
	}
);

/**
 * The palette in use: theme.json, overridden by a style variation or Site Editor colours.
 *
 * @return array slug => hex.
 */
function wpessential_active_palette() {
	$palettes = wp_get_global_settings( array( 'color', 'palette' ) );
	$palette  = array();
	foreach ( array_merge( (array) ( $palettes['theme'] ?? array() ), (array) ( $palettes['custom'] ?? array() ) ) as $color ) {
		if ( isset( $color['slug'], $color['color'] ) ) {
			$palette[ $color['slug'] ] = $color['color'];
		}
	}
	return $palette;
}

/**
 * Copy the theme.json palette and fonts into the active Elementor Kit.
 *
 * @return bool True when the kit was updated.
 */
function wpessential_sync_elementor_kit() {
	if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->kits_manager ) ) {
		return false;
	}
	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() || ! get_post( $kit->get_id() ) ) {
		return false;
	}

	$palette = wpessential_active_palette();

	// Elementor's four system colors.
	$system_map = array(
		'primary'   => 'primary',
		'secondary' => 'secondary',
		'text'      => 'text',
		'accent'    => 'accent',
	);
	$system     = array();
	foreach ( $system_map as $id => $slug ) {
		if ( isset( $palette[ $slug ] ) ) {
			$system[] = array(
				'_id'   => $id,
				'title' => ucfirst( $id ),
				'color' => $palette[ $slug ],
			);
		}
	}

	// Everything else becomes a custom global color with a stable id.
	$custom = array();
	foreach ( array_diff_key( $palette, array_flip( $system_map ) ) as $slug => $hex ) {
		$custom[] = array(
			'_id'   => 'wpe' . substr( md5( $slug ), 0, 7 ),
			'title' => 'WPE ' . ucwords( str_replace( '-', ' ', $slug ) ),
			'color' => $hex,
		);
	}

	$fonts    = array(
		'body'    => 'Inter',
		'heading' => 'Plus Jakarta Sans',
	);
	$families = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
	foreach ( array_merge( (array) ( $families['theme'] ?? array() ), (array) ( $families['custom'] ?? array() ) ) as $family ) {
		if ( isset( $family['slug'], $fonts[ $family['slug'] ] ) && ! empty( $family['name'] ) ) {
			$fonts[ $family['slug'] ] = $family['name'];
		}
	}
	$typography = array(
		array(
			'_id'                    => 'primary',
			'title'                  => 'Primary',
			'typography_typography'  => 'custom',
			'typography_font_family' => $fonts['heading'],
			'typography_font_weight' => '700',
		),
		array(
			'_id'                    => 'secondary',
			'title'                  => 'Secondary',
			'typography_typography'  => 'custom',
			'typography_font_family' => $fonts['heading'],
			'typography_font_weight' => '600',
		),
		array(
			'_id'                    => 'text',
			'title'                  => 'Text',
			'typography_typography'  => 'custom',
			'typography_font_family' => $fonts['body'],
			'typography_font_weight' => '400',
		),
		array(
			'_id'                    => 'accent',
			'title'                  => 'Accent',
			'typography_typography'  => 'custom',
			'typography_font_family' => $fonts['body'],
			'typography_font_weight' => '600',
		),
	);

	$settings                      = $kit->get_settings();
	$settings['system_colors']     = $system;
	$settings['custom_colors']     = $custom;
	$settings['system_typography'] = $typography;
	// Kit "Site identity" writes back to blogname/blogdescription on save; keep it current.
	$settings['site_name']        = get_option( 'blogname' );
	$settings['site_description'] = get_option( 'blogdescription' );
	try {
		$kit->save( array( 'settings' => $settings ) );
	} catch ( \Exception $e ) {
		return false;
	}

	if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return true;
}

/**
 * Re-sync whenever the palette/fonts change (theme update, style variation,
 * Site Editor → Styles) — compared by hash so it runs only when needed.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$hash = md5( wp_json_encode( wpessential_active_palette() ) . WPESSENTIAL_VERSION );
		if ( get_option( 'wpessential_kit_hash' ) === $hash ) {
			return;
		}
		if ( wpessential_sync_elementor_kit() ) {
			update_option( 'wpessential_kit_hash', $hash, false );
		}
	}
);

// Core plugin fires this after importing an Elementor demo.
add_action(
	'wpessential_demo_imported',
	static function ( $demo, $builder ) {
		if ( 'elementor' === $builder ) {
			wpessential_sync_elementor_kit();
		}
	},
	10,
	2
);

/*
 * Quiet wp-admin: no Elementor banners, promotions or notification feeds while this theme is active.
 * Errors that block Elementor itself (PHP/WordPress version) and database update notices stay.
 * Turn off with add_filter( 'wpessential_elementor_quiet_admin', '__return_false' ).
 */
if ( is_admin() && apply_filters( 'wpessential_elementor_quiet_admin', true ) ) {
	// Notice classes added by Elementor and its add-ons, the notifications bell feed and editor announcements.
	add_filter( 'elementor/core/admin/notices', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'elementor/core/admin/notifications', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'elementor/announcements/raw_announcements', '__return_empty_array', PHP_INT_MAX );

	/**
	 * Remove Elementor's own admin notice printers (rating, tracking, connect) and promotion banners
	 * (e.g. "Elevate your site with Elementor Pro" in the header, the rating request in the footer).
	 */
	function wpessential_elementor_remove_notices() {
		global $wp_filter;
		foreach ( array( 'admin_notices', 'all_admin_notices', 'network_admin_notices', 'in_admin_header', 'admin_footer_text' ) as $hook ) {
			if ( empty( $wp_filter[ $hook ] ) ) {
				continue;
			}
			foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$function = $callback['function'];
					$object   = is_array( $function ) && is_object( $function[0] ) ? $function[0] : null;
					if ( ! $object ) {
						continue;
					}
					$class = get_class( $object );
					if ( 0 !== strpos( $class, 'Elementor' ) || false !== stripos( $class, 'Upgrade' ) || false !== stripos( $class, 'TemplateLibrary' ) ) {
						continue;
					}
					// In the header keep Elementor's navigation (top bar, sidebar); drop only promotions.
					if ( 'in_admin_header' === $hook && false === strpos( $class, 'Promotions' ) ) {
						continue;
					}
					remove_action( $hook, $function, $priority );
				}
			}
		}
	}
	add_action( 'current_screen', 'wpessential_elementor_remove_notices', PHP_INT_MAX );

	// No "Elementor Overview" dashboard widget.
	add_action(
		'wp_dashboard_setup',
		static function () {
			remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal' );
		},
		PHP_INT_MAX
	);

	// No redirect to Elementor's welcome screen after activation, and no onboarding prompt.
	add_action(
		'admin_init',
		static function () {
			delete_transient( 'elementor_activation_redirect' );
		},
		1
	);
	add_filter( 'pre_option_elementor_onboarded', '__return_true' );

	// Promotional boxes Elementor prints without a hook (dismissible promos, "Get Pro" plugin row link).
	add_action(
		'admin_head',
		static function () {
			echo '<style id="wpessential-elementor-quiet">.e-notice--dismissible,.e-notice--extended,.elementor-plugins-gopro,#e-conversion-banner{display:none!important}</style>';
		}
	);
}
