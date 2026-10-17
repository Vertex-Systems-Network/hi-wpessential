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
 * Copy the theme.json palette and fonts into the active Elementor Kit.
 *
 * @return bool True when the kit was updated.
 */
function wpessential_sync_elementor_kit() {
	if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->kits_manager ) ) {
		return false;
	}
	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() ) {
		return false;
	}

	$palette = array();
	foreach ( wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $color ) {
		$palette[ $color['slug'] ] = $color['color'];
	}

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

	$fonts = array(
		'body'    => 'Inter',
		'heading' => 'Plus Jakarta Sans',
	);
	foreach ( wp_get_global_settings( array( 'typography', 'fontFamilies', 'theme' ) ) as $family ) {
		if ( isset( $fonts[ $family['slug'] ] ) && ! empty( $family['name'] ) ) {
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
	$kit->save( array( 'settings' => $settings ) );

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
		$palette = array();
		foreach ( wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $color ) {
			$palette[ $color['slug'] ] = $color['color'];
		}
		$hash = md5( wp_json_encode( $palette ) . WPESSENTIAL_VERSION );
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
