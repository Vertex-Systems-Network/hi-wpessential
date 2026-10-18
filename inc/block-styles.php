<?php
/**
 * Block style variations — the toolkit exposed in the editor's "Styles" panel.
 * CSS for every style lives in assets/css/theme.css.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		$styles = array(
			'core/button'       => array(
				'wpe-secondary' => __( 'Secondary', 'hi-wpessential' ),
				'wpe-outline'   => __( 'Outline', 'hi-wpessential' ),
				'wpe-ghost'     => __( 'Ghost', 'hi-wpessential' ),
				'wpe-dark'      => __( 'Dark', 'hi-wpessential' ),
				'wpe-light'     => __( 'Light (on dark)', 'hi-wpessential' ),
				'wpe-link'      => __( 'Text link', 'hi-wpessential' ),
				'wpe-pill'      => __( 'Pill', 'hi-wpessential' ),
				'wpe-large'     => __( 'Large', 'hi-wpessential' ),
			),
			'core/group'        => array(
				'wpe-card'         => __( 'Card', 'hi-wpessential' ),
				'wpe-card-flat'    => __( 'Card — flat', 'hi-wpessential' ),
				'wpe-card-dark'    => __( 'Card — dark', 'hi-wpessential' ),
				'wpe-section-dark' => __( 'Section — dark', 'hi-wpessential' ),
				'wpe-logos'        => __( 'Logo strip', 'hi-wpessential' ),
			),
			'core/column'       => array(
				'wpe-card'      => __( 'Card', 'hi-wpessential' ),
				'wpe-card-flat' => __( 'Card — flat', 'hi-wpessential' ),
				'wpe-card-dark' => __( 'Card — dark', 'hi-wpessential' ),
			),
			'core/list'         => array(
				'wpe-checklist' => __( 'Checklist', 'hi-wpessential' ),
				'wpe-timeline'  => __( 'Timeline', 'hi-wpessential' ),
			),
			'core/paragraph'    => array(
				'wpe-overline' => __( 'Overline', 'hi-wpessential' ),
				'wpe-lead'     => __( 'Lead', 'hi-wpessential' ),
			),
			'core/details'      => array(
				'wpe-flush' => __( 'Flush', 'hi-wpessential' ),
			),
			'core/image'        => array(
				'wpe-shadow' => __( 'Shadow', 'hi-wpessential' ),
				'wpe-frame'  => __( 'Glass frame', 'hi-wpessential' ),
			),
			'core/post-terms'   => array(
				'wpe-pills'    => __( 'Pills', 'hi-wpessential' ),
				'wpe-hashtags' => __( 'Hashtags', 'hi-wpessential' ),
			),
			'core/social-links' => array(
				'wpe-soft' => __( 'Soft', 'hi-wpessential' ),
			),
		);

		foreach ( $styles as $block => $variations ) {
			foreach ( $variations as $name => $label ) {
				register_block_style(
					$block,
					array(
						'name'  => $name,
						'label' => $label,
					)
				);
			}
		}
	}
);
