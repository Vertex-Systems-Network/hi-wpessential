<?php
/**
 * Light / dark switching for visitors.
 *
 * The site's own palette (theme.json, a style variation or Site Editor colours) is the default
 * scheme. The other scheme comes from the theme: styles/dark.json when the site is light,
 * the base theme.json palette when the site is dark. Visitors flip between them with the
 * header toggle; the choice is stored in localStorage and the OS preference is used until then.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether visitors get the light/dark toggle.
 *
 * @return bool
 */
function wpessential_scheme_toggle_enabled() {
	return (bool) apply_filters( 'wpessential_scheme_toggle', true );
}

/**
 * Read a theme JSON file shipped with the theme.
 *
 * @param string $file Relative path inside the theme.
 * @return array
 */
function wpessential_read_theme_json_file( $file ) {
	$path = get_theme_file_path( $file );
	if ( ! is_readable( $path ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
	return is_array( $data ) ? $data : array();
}

/**
 * Build the CSS custom properties for one scheme.
 *
 * @param array $settings A theme.json "settings" array (color.palette, color.gradients, shadow.presets).
 * @return string
 */
function wpessential_scheme_vars( $settings ) {
	$css = '';
	foreach ( (array) ( $settings['color']['palette'] ?? array() ) as $color ) {
		if ( isset( $color['slug'], $color['color'] ) ) {
			$css .= '--wp--preset--color--' . sanitize_key( $color['slug'] ) . ':' . $color['color'] . ';';
		}
	}
	foreach ( (array) ( $settings['color']['gradients'] ?? array() ) as $gradient ) {
		if ( isset( $gradient['slug'], $gradient['gradient'] ) ) {
			$css .= '--wp--preset--gradient--' . sanitize_key( $gradient['slug'] ) . ':' . $gradient['gradient'] . ';';
		}
	}
	foreach ( (array) ( $settings['shadow']['presets'] ?? array() ) as $shadow ) {
		if ( isset( $shadow['slug'], $shadow['shadow'] ) ) {
			$css .= '--wp--preset--shadow--' . sanitize_key( $shadow['slug'] ) . ':' . $shadow['shadow'] . ';';
		}
	}
	return $css;
}

/**
 * Light and dark settings arrays, keyed "light" and "dark".
 *
 * @return array
 */
function wpessential_scheme_settings() {
	$base = wpessential_read_theme_json_file( 'theme.json' );
	$dark = wpessential_read_theme_json_file( 'styles/dark.json' );

	if ( wpessential_is_dark_scheme() ) {
		return array(
			'light' => $base['settings'] ?? array(),
			'dark'  => null,
		);
	}
	return array(
		'light' => null,
		'dark'  => $dark['settings'] ?? array(),
	);
}

// The default scheme is known on the server, so the markup starts correct even without JS.
add_filter(
	'language_attributes',
	static function ( $output ) {
		$scheme = wpessential_is_dark_scheme() ? 'dark' : 'light';
		return $output . ' data-wpe-scheme="' . esc_attr( $scheme ) . '"';
	}
);

// Apply the stored / OS preference before first paint.
add_action(
	'wp_head',
	static function () {
		if ( ! wpessential_scheme_toggle_enabled() ) {
			return;
		}
		$default = wpessential_is_dark_scheme() ? 'dark' : 'light';
		$follow  = (bool) apply_filters( 'wpessential_scheme_follow_os', true );
		$script  = sprintf(
			'(function(){try{var d=document.documentElement,s=localStorage.getItem("wpe-scheme");if(!s&&%1$s){s=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";}d.setAttribute("data-wpe-scheme",s||%2$s);}catch(e){}})();',
			$follow ? 'true' : 'false',
			wp_json_encode( $default )
		);
		wp_print_inline_script_tag( $script );
	},
	1
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$sets = wpessential_scheme_settings();
		$css  = '';
		if ( $sets['dark'] ) {
			$css .= ':root[data-wpe-scheme="dark"]{color-scheme:dark;' . wpessential_scheme_vars( $sets['dark'] ) . '}';
		}
		if ( $sets['light'] ) {
			$css .= ':root[data-wpe-scheme="light"]{color-scheme:light;' . wpessential_scheme_vars( $sets['light'] ) . '}';
		}
		wp_add_inline_style( 'wpessential', $css );
	},
	20
);
