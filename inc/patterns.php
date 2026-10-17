<?php
/**
 * Pattern categories. Pattern files in /patterns are registered by core.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', static function () {
	$categories = array(
		'wpessential'          => __( 'WPEssential', 'wpessential' ),
		'wpessential-hero'     => __( 'WPEssential — Heroes', 'wpessential' ),
		'wpessential-sections' => __( 'WPEssential — Sections', 'wpessential' ),
		'wpessential-headers'  => __( 'WPEssential — Headers', 'wpessential' ),
		'wpessential-footers'  => __( 'WPEssential — Footers', 'wpessential' ),
		'wpessential-pages'    => __( 'WPEssential — Full pages', 'wpessential' ),
		'wpessential-shop'     => __( 'WPEssential — Shop', 'wpessential' ),
	);
	foreach ( $categories as $slug => $label ) {
		register_block_pattern_category( $slug, array( 'label' => $label ) );
	}
} );
