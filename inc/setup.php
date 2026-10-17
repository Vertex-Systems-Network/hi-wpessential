<?php
/**
 * Theme setup.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', static function () {
	load_theme_textdomain( 'wpessential', WPESSENTIAL_DIR . '/languages' );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );

	add_editor_style( 'assets/css/theme.css' );

	add_image_size( 'wpessential-card', 720, 450, true );

	// Classic menu locations: used by Elementor nav widgets and as the
	// fallback menu source for the block navigation.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'wpessential' ),
			'mobile'  => __( 'Mobile Menu', 'wpessential' ),
			'footer'  => __( 'Footer Menu', 'wpessential' ),
			'topbar'  => __( 'Top Bar Menu', 'wpessential' ),
		)
	);
} );

/**
 * Body classes the toolkit CSS keys off.
 */
add_filter( 'body_class', static function ( $classes ) {
	$classes[] = 'wpessential';
	if ( did_action( 'elementor/loaded' ) && is_singular() && class_exists( '\Elementor\Plugin' ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
		if ( $document && $document->is_built_with_elementor() ) {
			$classes[] = 'wpe-built-with-elementor';
		}
	}
	return $classes;
} );

/**
 * "Back to top" button, printed once in the footer.
 */
add_action( 'wp_footer', static function () {
	if ( ! apply_filters( 'wpessential/back_to_top', true ) ) {
		return;
	}
	printf(
		'<button type="button" class="wpe-back-to-top" aria-label="%s"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg></button>',
		esc_attr__( 'Back to top', 'wpessential' )
	);
} );
