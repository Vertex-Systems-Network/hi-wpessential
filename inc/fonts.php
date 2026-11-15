<?php
/**
 * Google Fonts for the Site Settings, the block editor and the kit.
 *
 * The full Google Fonts list ships with the theme (assets/data/google-fonts.json, built by
 * tools/fonts/google.py). A chosen font is downloaded to the site on save (uploads/wpessential-fonts,
 * no request to Google from visitors) or, when the site prefers, loaded from Google's CDN.
 * Chosen fonts become theme.json font families, so they also appear in every block's
 * typography panel, in the kit elements and in Elementor.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Google Fonts list: family => [ category, weights, italic, variable range|null ].
 *
 * @return array
 */
function wpessential_google_fonts() {
	static $fonts = null;
	if ( null === $fonts ) {
		$fonts = array();
		$file  = WPESSENTIAL_DIR . '/assets/data/google-fonts.json';
		$rows  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		foreach ( (array) $rows as $r ) {
			$fonts[ $r[0] ] = array( $r[1], $r[2], (bool) $r[3], $r[4] );
		}
	}
	return $fonts;
}

/**
 * CSS fallback stack for a Google Fonts category.
 *
 * @param string $family Family.
 * @return string
 */
function wpessential_google_stack( $family ) {
	$cat   = wpessential_google_fonts()[ $family ][0] ?? 'sans';
	$after = array(
		'serif'   => 'Georgia, serif',
		'mono'    => 'ui-monospace, monospace',
		'hand'    => 'cursive',
		'display' => 'system-ui, sans-serif',
	)[ $cat ] ?? 'system-ui, sans-serif';
	return '"' . str_replace( '"', '', $family ) . '", ' . $after;
}

/**
 * Google Fonts CSS2 URL for families.
 *
 * @param string[] $families Families.
 * @return string
 */
function wpessential_google_url( array $families ) {
	$parts = array();
	foreach ( array_unique( $families ) as $family ) {
		$f = wpessential_google_fonts()[ $family ] ?? null;
		if ( ! $f ) {
			continue;
		}
		if ( $f[3] ) {
			$axis = ( $f[2] ? 'ital,wght@0,' . $f[3][0] . '..' . $f[3][1] . ';1,' . $f[3][0] . '..' . $f[3][1] : 'wght@' . $f[3][0] . '..' . $f[3][1] );
		} else {
			$weights = $f[1] ? $f[1] : array( 400 );
			$axis    = $f[2] ? 'ital,wght@' . implode( ';', array_merge( array_map( static fn( $w ) => '0,' . $w, $weights ), array_map( static fn( $w ) => '1,' . $w, $weights ) ) ) : 'wght@' . implode( ';', $weights );
		}
		$parts[] = 'family=' . str_replace( '%20', '+', rawurlencode( $family ) ) . ':' . $axis;
	}
	return $parts ? 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap' : '';
}

/**
 * Google families the site uses (body/heading/mono choices and the extra list).
 *
 * @return string[]
 */
function wpessential_google_families() {
	$v   = function_exists( 'wpessential_site_values' ) ? wpessential_site_values() : array();
	$out = array();
	foreach ( array( 'font_body', 'font_heading', 'font_mono' ) as $key ) {
		if ( ! empty( $v[ $key ] ) && 0 === strpos( $v[ $key ], 'google:' ) ) {
			$out[] = substr( $v[ $key ], 7 );
		}
	}
	foreach ( array_filter( array_map( 'trim', explode( '|', (string) ( $v['fonts_extra'] ?? '' ) ) ) ) as $family ) {
		$out[] = $family;
	}
	// Families picked in the Global typography sets.
	foreach ( $v as $key => $val ) {
		if ( 0 === strpos( $key, 'typo_' ) && function_exists( 'wpessential_site_json' ) ) {
			$family = (string) ( wpessential_site_json( $val )['family'] ?? '' );
			if ( 0 === strpos( $family, 'google:' ) ) {
				$out[] = substr( $family, 7 );
			}
		}
	}
	return array_values( array_unique( array_filter( $out, static fn( $f ) => isset( wpessential_google_fonts()[ $f ] ) ) ) );
}

/**
 * Fonts are served from the site (default) or from Google.
 *
 * @return bool
 */
function wpessential_fonts_local() {
	$v = function_exists( 'wpessential_site_values' ) ? wpessential_site_values() : array();
	return 'google' !== ( $v['fonts_delivery'] ?? '' );
}

/**
 * Download a Google family to uploads/wpessential-fonts and return its theme.json fontFace list.
 * Only the latin and latin-ext files are kept (what most sites need); the result is cached.
 *
 * @param string $family Family.
 * @return array
 */
function wpessential_font_download( $family ) {
	$saved = (array) get_option( 'wpessential_fonts_local', array() );
	if ( isset( $saved[ $family ] ) ) {
		return $saved[ $family ];
	}
	$url = wpessential_google_url( array( $family ) );
	if ( ! $url ) {
		return array();
	}
	// A modern browser user agent makes Google return woff2.
	$res = wp_remote_get(
		$url,
		array(
			'timeout'    => 20,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
		)
	);
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return array();
	}
	$css     = (string) wp_remote_retrieve_body( $res );
	$uploads = wp_upload_dir();
	$slug    = sanitize_title( $family );
	$dir     = trailingslashit( $uploads['basedir'] ) . 'wpessential-fonts/' . $slug;
	$base    = trailingslashit( $uploads['baseurl'] ) . 'wpessential-fonts/' . $slug;
	// Font files are written with the WordPress filesystem API (no direct file writes).
	global $wp_filesystem;
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! WP_Filesystem() || ! $wp_filesystem ) {
		return array();
	}
	$fs = $wp_filesystem;
	if ( ! wp_mkdir_p( $dir ) ) {
		return array();
	}
	$faces = array();
	if ( preg_match_all( '#/\*\s*([a-z-]+)\s*\*/\s*@font-face\s*\{([^}]+)\}#i', $css, $blocks, PREG_SET_ORDER ) ) {
		foreach ( $blocks as $b ) {
			if ( ! in_array( $b[1], array( 'latin', 'latin-ext' ), true ) ) {
				continue;
			}
			preg_match( '/font-style:\s*([a-z]+)/', $b[2], $style );
			preg_match( '/font-weight:\s*([0-9 ]+)/', $b[2], $weight );
			preg_match( '/src:\s*url\(([^)]+)\)/', $b[2], $src );
			preg_match( '/unicode-range:\s*([^;]+)/', $b[2], $range );
			if ( empty( $src[1] ) || 0 !== strpos( $src[1], 'https://fonts.gstatic.com/' ) ) {
				continue;
			}
			$file = sanitize_file_name( $slug . '-' . ( $style[1] ?? 'normal' ) . '-' . str_replace( ' ', '-', trim( $weight[1] ?? '400' ) ) . '-' . $b[1] . '.woff2' );
			if ( ! file_exists( $dir . '/' . $file ) ) {
				$font = wp_remote_get( $src[1], array( 'timeout' => 20 ) );
				if ( is_wp_error( $font ) || 200 !== wp_remote_retrieve_response_code( $font ) ) {
					continue;
				}
				$fs->put_contents( $dir . '/' . $file, wp_remote_retrieve_body( $font ), FS_CHMOD_FILE );
			}
			$faces[] = array(
				'fontFamily'   => $family,
				'fontStyle'    => $style[1] ?? 'normal',
				'fontWeight'   => trim( $weight[1] ?? '400' ),
				'fontDisplay'  => 'swap',
				'src'          => array( $base . '/' . $file ),
				'unicodeRange' => trim( $range[1] ?? '' ),
			);
		}
	}
	if ( $faces ) {
		$saved[ $family ] = $faces;
		update_option( 'wpessential_fonts_local', $saved, false );
	}
	return $faces;
}

/**
 * A theme.json font family entry for a Google family.
 *
 * @param string $slug   Slug.
 * @param string $family Family.
 * @return array
 */
function wpessential_google_family_entry( $slug, $family ) {
	$entry = array(
		'slug'       => $slug,
		'name'       => $family,
		'fontFamily' => wpessential_google_stack( $family ),
	);
	if ( wpessential_fonts_local() ) {
		$saved = (array) get_option( 'wpessential_fonts_local', array() );
		if ( ! empty( $saved[ $family ] ) ) {
			$entry['fontFace'] = $saved[ $family ];
		}
	}
	return $entry;
}

// Download the chosen fonts after the Site Settings are saved.
add_action(
	'customize_save_after',
	static function () {
		if ( ! wpessential_fonts_local() ) {
			return;
		}
		foreach ( wpessential_google_families() as $family ) {
			wpessential_font_download( $family );
		}
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}
	},
	5
);

/**
 * Google families that still need Google's CDN (CDN mode, or the download failed).
 *
 * @return string[]
 */
function wpessential_google_remote() {
	$saved = wpessential_fonts_local() ? (array) get_option( 'wpessential_fonts_local', array() ) : array();
	return array_values( array_filter( wpessential_google_families(), static fn( $f ) => empty( $saved[ $f ] ) ) );
}

// Front end and block editor: link Google's CSS only for fonts that are not on the site.
foreach ( array( 'wp_enqueue_scripts', 'enqueue_block_assets' ) as $wpessential_hook ) {
	add_action(
		$wpessential_hook,
		static function () {
			$url = wpessential_google_url( wpessential_google_remote() );
			if ( $url ) {
				wp_enqueue_style( 'wpessential-google-fonts', $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URL.
			}
		}
	);
}
unset( $wpessential_hook );
