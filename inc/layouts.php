<?php
/**
 * Header / footer resolver.
 *
 * Templates always contain block template parts (`header*`, `footer*`), so the
 * theme works on its own. Before a part renders, the theme asks — in order:
 *
 *   1. Hi WPEssential Core (or any plugin) via `wpessential_layout_html`
 *      — e.g. a Site Builder layout built with Gutenberg or Elementor and
 *        matched by display conditions;
 *   2. Elementor Pro Theme Builder locations (`header` / `footer`);
 *   3. otherwise the block template part itself.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map a template-part slug to a layout area.
 *
 * @param string $slug Template part slug, e.g. "header-shop".
 * @return string Area name ("header", "footer") or empty string.
 */
function wpessential_layout_area( $slug ) {
	$areas = apply_filters( 'wpessential_layout_areas', array( 'header', 'footer' ) );
	foreach ( $areas as $area ) {
		if ( $slug === $area || 0 === strpos( $slug, $area . '-' ) ) {
			return $area;
		}
	}
	return '';
}

/**
 * HTML that should replace the block template part for an area, or null.
 *
 * @param string $area Layout area.
 * @param string $slug Requested template part slug (lets plugins respect the chosen variation).
 * @return string|null
 */
function wpessential_external_layout( $area, $slug = '' ) {
	/**
	 * Return rendered HTML to take over a layout area. Return null to pass.
	 *
	 * @param string|null $html Rendered HTML.
	 * @param string      $area Layout area ("header", "footer").
	 * @param string      $slug Template part slug the template asked for.
	 */
	$html = apply_filters( 'wpessential_layout_html', null, $area, $slug );
	if ( is_string( $html ) ) {
		return $html;
	}

	if ( function_exists( 'elementor_theme_do_location' ) ) {
		ob_start();
		$done = elementor_theme_do_location( $area );
		$out  = ob_get_clean();
		if ( $done ) {
			return $out;
		}
	}

	return null;
}

add_filter(
	'render_block',
	static function ( $content, $block ) {
		if ( 'core/template-part' !== $block['blockName'] || is_admin() ) {
			return $content;
		}
		$slug = isset( $block['attrs']['slug'] ) ? $block['attrs']['slug'] : '';
		$area = wpessential_layout_area( $slug );
		if ( ! $area ) {
			return $content;
		}

		// Render each area at most once per request (templates may nest parts).
		static $rendered = array();
		if ( isset( $rendered[ $area ] ) ) {
			return $content;
		}

		$html = wpessential_external_layout( $area, $slug );
		if ( null === $html ) {
			return $content;
		}
		$rendered[ $area ] = true;

		/** Fires right after an external layout replaced a block template part. */
		do_action( 'wpessential_layout_rendered', $area, $slug );

		return $html;
	},
	10,
	2
);
