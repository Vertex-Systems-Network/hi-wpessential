<?php
/**
 * Scroll animations with niche presets. CSS + IntersectionObserver by default,
 * The "dynamic" preset adds parallax, a marquee and counters in plain JavaScript.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

/**
 * Motion presets.
 *
 * @return array
 */
function wpessential_motion_presets() {
	return apply_filters(
		'wpessential_motion_presets',
		array(
			'none'      => array(
				'label' => __( 'None', 'hi-wpessential' ),
				'help'  => __( 'No animations.', 'hi-wpessential' ),
			),
			'subtle'    => array(
				'label' => __( 'Subtle', 'hi-wpessential' ),
				'help'  => __( 'Short fade-up. Corporate, SaaS, finance, medical.', 'hi-wpessential' ),
			),
			'editorial' => array(
				'label' => __( 'Editorial', 'hi-wpessential' ),
				'help'  => __( 'Slow image masks and line reveals. Fashion, magazine, luxury.', 'hi-wpessential' ),
			),
			'playful'   => array(
				'label' => __( 'Playful', 'hi-wpessential' ),
				'help'  => __( 'Springy scale and stagger. Food, kids, events, creative.', 'hi-wpessential' ),
			),
			'dynamic'   => array(
				'label' => __( 'Dynamic', 'hi-wpessential' ),
				'help'  => __( 'Parallax, counters and staggered grids. Events, sport, launches.', 'hi-wpessential' ),
			),
		)
	);
}

/**
 * Current motion settings.
 *
 * @return array{preset: string, dynamic: bool}
 */
function wpessential_motion() {
	// Site Settings > Hover & motion; the old Theme Options value is still read until it is saved there.
	$site   = wpessential_site_values();
	$legacy = (array) get_option( 'wpessential_motion', array() );
	$preset = (string) ( $site['motion_preset'] ?? ( $legacy['preset'] ?? 'subtle' ) );
	if ( ! empty( $site['motion_off'] ) ) {
		$preset = 'none';
	}
	$preset = array_key_exists( $preset, wpessential_motion_presets() ) ? $preset : 'subtle';
	return array(
		'preset'  => $preset,
		'dynamic' => 'dynamic' === $preset,
	);
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		$motion = wpessential_motion();
		if ( 'none' === $motion['preset'] ) {
			return;
		}
		wp_enqueue_style( 'wpessential-motion', WPESSENTIAL_URI . '/assets/css/motion.css', array( 'wpessential' ), WPESSENTIAL_ASSET_VERSION );
		wp_enqueue_script( 'wpessential-motion', WPESSENTIAL_URI . '/assets/js/motion.js', array(), WPESSENTIAL_ASSET_VERSION, array( 'strategy' => 'defer' ) );
		wp_localize_script(
			'wpessential-motion',
			'wpessentialMotion',
			array(
				'preset' => $motion['preset'],
				'dynamic' => $motion['dynamic'],
				'auto'   => (string) apply_filters( 'wpessential_motion_auto_selector', '.wpe-section .wp-block-heading, .wpe-section > .wp-block-group > p, .wpe-section .wp-block-buttons, .wpe-card, .wpe-section .wp-block-image, .wp-block-post-template > li, ul.products > li, .wc-block-grid__product, .wpe-stat' ),
			)
		);
	}
);

// The preset class is added before first paint so elements start hidden only when JS runs.
add_action(
	'wp_head',
	static function () {
		$motion = wpessential_motion();
		if ( 'none' === $motion['preset'] ) {
			return;
		}
		wp_print_inline_script_tag( 'if(!window.matchMedia("(prefers-reduced-motion: reduce)").matches){document.documentElement.classList.add("wpe-motion","wpe-motion--' . esc_js( $motion['preset'] ) . '");}' );
	},
	2
);
