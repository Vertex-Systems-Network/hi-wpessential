<?php
/**
 * WordPress + Elementor Kit Mega Menu regression tests.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_assert = static function ( $check, $label ) {
	if ( ! $check ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	echo 'PASS: ' . $label . "\n";
};
$wpe_render = static function ( array $attrs ) {
	return Kit::render( 'menu', $attrs );
};

$wpe_menu_id = wp_create_nav_menu( 'Kit Mega Regression' );
$wpe_other_id = wp_create_nav_menu( 'Kit Other Menu' );
$wpe_assert( ! is_wp_error( $wpe_menu_id ) && ! is_wp_error( $wpe_other_id ), 'Test menus created' );
$wpe_parent = wp_update_nav_menu_item(
	$wpe_menu_id,
	0,
	array(
		'menu-item-type'   => 'custom',
		'menu-item-title'  => 'Solutions',
		'menu-item-url'    => home_url( '/solutions/' ),
		'menu-item-status' => 'publish',
	)
);
$wpe_child = wp_update_nav_menu_item(
	$wpe_menu_id,
	0,
	array(
		'menu-item-type'      => 'custom',
		'menu-item-title'     => 'Engineering',
		'menu-item-url'       => home_url( '/engineering/' ),
		'menu-item-parent-id' => $wpe_parent,
		'menu-item-status'    => 'publish',
	)
);
$wpe_other = wp_update_nav_menu_item(
	$wpe_other_id,
	0,
	array(
		'menu-item-type'   => 'custom',
		'menu-item-title'  => 'Unrelated',
		'menu-item-url'    => home_url( '/unrelated/' ),
		'menu-item-status' => 'publish',
	)
);
$wpe_synced = wp_insert_post(
	array(
		'post_type'    => 'wp_block',
		'post_status'  => 'publish',
		'post_title'   => 'Kit Mega Synced Pattern',
		'post_content' => '<!-- wp:paragraph --><p>QA mega reusable pattern</p><!-- /wp:paragraph -->',
	),
	true
);
$wpe_draft = wp_insert_post(
	array(
		'post_type'    => 'wp_block',
		'post_status'  => 'draft',
		'post_title'   => 'Kit Mega Unpublished',
		'post_content' => '<!-- wp:paragraph --><p>Secret Draft Template</p><!-- /wp:paragraph -->',
	),
	true
);
$wpe_assert( ! is_wp_error( $wpe_synced ) && ! is_wp_error( $wpe_draft ), 'Reusable pattern fixtures created' );

try {
	$wpe_rows = array(
		array(
			'menuItemId' => (string) $wpe_parent,
			'templateId' => (string) $wpe_synced,
			'source'     => 'wp_block',
		),
	);
	$wpe_markup = $wpe_render(
		array(
			'menu'          => (string) $wpe_menu_id,
			'templateItems' => $wpe_rows,
			'mega'          => true,
			'desktopTrigger' => 'click',
		)
	);
	$wpe_assert( false !== strpos( $wpe_markup, 'QA mega reusable pattern' ), 'Published pattern renders in menu panels' );
	$wpe_assert( 2 === substr_count( $wpe_markup, 'QA mega reusable pattern' ), 'Desktop and mobile panels independently render' );
	$wpe_assert( false !== strpos( $wpe_markup, 'Engineering' ), 'Standard child links preserved beside mega panel' );
	$wpe_assert( false !== strpos( $wpe_markup, 'wpe-menu__has-template' ), 'Mapped item has template marker' );
	$wpe_assert( false !== strpos( $wpe_markup, 'is-trigger-click' ), 'Desktop trigger setting applied' );
	$wpe_assert( ! has_filter( 'walker_nav_menu_start_el', null ) || false === strpos( $wpe_render( array( 'menu' => (string) $wpe_other_id ) ), 'QA mega reusable pattern' ), 'Menu filters scoped to renderer' );

	$wpe_invalid = $wpe_render(
		array(
			'menu'          => (string) $wpe_menu_id,
			'templateItems' => array(
				array( 'menuItemId' => (string) $wpe_parent, 'templateId' => (string) $wpe_draft, 'source' => 'wp_block' ),
				array( 'menuItemId' => (string) $wpe_other, 'templateId' => (string) $wpe_synced, 'source' => 'wp_block' ),
				array( 'menuItemId' => '0);alert(1)', 'templateId' => (string) $wpe_synced, 'source' => 'wp_block' ),
				array( 'menuItemId' => (string) $wpe_child, 'templateId' => (string) $wpe_synced, 'source' => 'post' ),
			),
		)
	);
	$wpe_assert( false === strpos( $wpe_invalid, 'Secret Draft Template' ), 'Unpublished synced pattern never rendered' );
	$wpe_assert( false === strpos( $wpe_invalid, 'QA mega reusable pattern' ), 'Foreign menu IDs and invalid source rejected' );
	$wpe_assert( false === strpos( $wpe_invalid, 'alert(1)' ), 'Malformed IDs cannot inject markup' );
	$wpe_assert( false !== strpos( $wpe_invalid, 'Engineering' ), 'Invalid mapping preserves ordinary menu links' );

	$wpe_page = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Confidential not a template',
			'post_content' => 'Never expose non-template page',
		)
	);
	try {
		$wpe_html = $wpe_render(
			array(
				'menu'          => (string) $wpe_menu_id,
				'templateItems' => array(
					array( 'menuItemId' => (string) $wpe_parent, 'templateId' => (string) $wpe_page, 'source' => 'wp_block' ),
				),
			)
		);
		$wpe_assert( false === strpos( $wpe_html, 'Never expose non-template page' ), 'Published normal pages cannot be used as mega templates' );
	} finally {
		wp_delete_post( $wpe_page, true );
	}

	$wpe_elementor_settings = Kit::from_elementor(
		Kit::defs()['menu'],
		array(
			'menu' => (string) $wpe_menu_id,
			'templateItems' => $wpe_rows,
		)
	);
	$wpe_assert( false !== strpos( Kit::render( 'menu', $wpe_elementor_settings ), 'QA mega reusable pattern' ), 'Elementor repeater maps to shared template renderer' );
} finally {
	wp_delete_post( $wpe_synced, true );
	wp_delete_post( $wpe_draft, true );
	wp_delete_nav_menu( $wpe_menu_id );
	wp_delete_nav_menu( $wpe_other_id );
}
echo "Kit Mega Menu templates security / editor integration tests passed.\n";
