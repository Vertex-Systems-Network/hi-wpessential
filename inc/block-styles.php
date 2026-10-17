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
				'wpe-secondary' => __( 'Secondary', 'wpessential' ),
				'wpe-outline'   => __( 'Outline', 'wpessential' ),
				'wpe-ghost'     => __( 'Ghost', 'wpessential' ),
				'wpe-dark'      => __( 'Dark', 'wpessential' ),
				'wpe-light'     => __( 'Light (on dark)', 'wpessential' ),
				'wpe-link'      => __( 'Text link', 'wpessential' ),
				'wpe-pill'      => __( 'Pill', 'wpessential' ),
				'wpe-large'     => __( 'Large', 'wpessential' ),
			),
			'core/group'        => array(
				'wpe-card'         => __( 'Card', 'wpessential' ),
				'wpe-card-flat'    => __( 'Card — flat', 'wpessential' ),
				'wpe-card-dark'    => __( 'Card — dark', 'wpessential' ),
				'wpe-section-dark' => __( 'Section — dark', 'wpessential' ),
				'wpe-logos'        => __( 'Logo strip', 'wpessential' ),
			),
			'core/column'       => array(
				'wpe-card'      => __( 'Card', 'wpessential' ),
				'wpe-card-flat' => __( 'Card — flat', 'wpessential' ),
				'wpe-card-dark' => __( 'Card — dark', 'wpessential' ),
			),
			'core/list'         => array(
				'wpe-checklist' => __( 'Checklist', 'wpessential' ),
				'wpe-timeline'  => __( 'Timeline', 'wpessential' ),
			),
			'core/paragraph'    => array(
				'wpe-overline' => __( 'Overline', 'wpessential' ),
				'wpe-lead'     => __( 'Lead', 'wpessential' ),
			),
			'core/details'      => array(
				'wpe-flush' => __( 'Flush', 'wpessential' ),
			),
			'core/image'        => array(
				'wpe-shadow' => __( 'Shadow', 'wpessential' ),
				'wpe-frame'  => __( 'Glass frame', 'wpessential' ),
			),
			'core/post-terms'   => array(
				'wpe-pills'    => __( 'Pills', 'wpessential' ),
				'wpe-hashtags' => __( 'Hashtags', 'wpessential' ),
			),
			'core/social-links' => array(
				'wpe-soft' => __( 'Soft', 'wpessential' ),
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
