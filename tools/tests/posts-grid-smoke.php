<?php
/**
 * WordPress runtime smoke tests for the Posts Grid kit element.
 *
 * Usage: wp eval-file tools/tests/posts-grid-smoke.php --path=/path/to/wordpress
 * Runs on a temporary CI installation, not a production website.
 */
use WPEssential\Core\Kit;

$check = static function ( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo "PASS: " . $message . "\n";
};

$check( class_exists( Kit::class ), 'WPEssential Kit loads in WordPress' );
$check( isset( Kit::defs()['posts-grid'] ), 'Posts Grid definition loads' );
$check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/posts-grid' ), 'Gutenberg block registered' );
$check( wp_style_is( 'wpessential-kit-posts-grid', 'registered' ), 'Posts Grid stylesheet registered' );

$slug = 'wpe-smoke-' . wp_generate_password( 10, false );
$term = wp_insert_term( 'Smoke Posts Grid', 'category', array( 'slug' => $slug ) );
$check( ! is_wp_error( $term ), 'Test taxonomy term created' );
$term_id = (int) $term['term_id'];
$posts = array();

for ( $i = 1; $i <= 7; $i++ ) {
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_title'   => sprintf( 'WPE Grid Smoke %02d', $i ),
			'post_content' => 'Test post ' . $i,
			'post_excerpt' => 'Excerpt ' . $i,
			'post_status'  => 'publish',
		),
		true
	);
	$check( ! is_wp_error( $post_id ), 'Test post ' . $i . ' created' );
	wp_set_post_terms( $post_id, array( $term_id ), 'category' );
	$posts[] = $post_id;
}

$draft_id = wp_insert_post( array(
	'post_type'   => 'post',
	'post_title'  => 'WPE Grid Smoke 00 Draft',
	'post_status' => 'draft',
), true );
$check( ! is_wp_error( $draft_id ), 'Draft test post created' );
wp_set_post_terms( $draft_id, array( $term_id ), 'category' );
$posts[] = $draft_id;

$attrs = array(
	'postType'        => 'post',
	'taxonomy'        => 'category',
	'terms'           => $slug,
	'postsPerPage'    => 3,
	'orderBy'         => 'title',
	'order'           => 'ASC',
	'excludeCurrent'  => false,
	'showPagination'  => true,
);
$block = static function ( $attributes ) {
	$serialized = '<!-- wp:wpessential/posts-grid ' . wp_json_encode( $attributes ) . ' /-->';
	return do_blocks( $serialized );
};
$count_cards = static function ( $html ) {
	preg_match_all( '/<article\\b[^>]*class="[^"]*\\bwpe-posts-grid__card\\b/', $html, $matches );
	return count( $matches[0] );
};
try {
	$html = $block( $attrs );
	$check( $count_cards( $html ) === 3, 'Gutenberg renders three cards on first page' );
	$check( strpos( $html, 'WPE Grid Smoke 01' ) !== false, 'First page shows first result' );
	$check( strpos( $html, 'WPE Grid Smoke 04' ) === false, 'First page excludes page two' );
	$check( strpos( $html, 'Draft' ) === false, 'Draft content is not exposed' );
	$check( strpos( $html, 'class="wpe-posts-grid__pagination"' ) !== false, 'Pagination navigation renders' );
	$check( preg_match( '/wpe_pg_[a-f0-9]{8}/', $html, $page_key ) === 1, 'Stable pagination query parameter renders' );

	$_GET[ $page_key[0] ] = '2';
	$page2 = $block( $attrs );
	$check( $count_cards( $page2 ) === 3, 'Page two renders three cards' );
	$check( strpos( $page2, 'WPE Grid Smoke 04' ) !== false, 'Page two shows next set of posts' );
	$check( strpos( $page2, 'WPE Grid Smoke 01' ) === false, 'Page two does not repeat first result' );
	$check( strpos( $page2, $page_key[0] ) !== false, 'Pagination key is stable across requests' );

	$_GET[ $page_key[0] ] = '3';
	$page3 = $block( $attrs );
	$check( $count_cards( $page3 ) === 1, 'Page three renders the remaining post' );
	$check( strpos( $page3, 'WPE Grid Smoke 07' ) !== false, 'Page three shows last result' );

	unset( $_GET[ $page_key[0] ] );
	$missing = $attrs;
	$missing['terms'] = 'does-not-exist-' . $slug;
	$empty = $block( $missing );
	$check( $count_cards( $empty ) === 0, 'Unmatched taxonomy terms return no cards' );
	$check( strpos( $empty, 'No posts found.' ) !== false, 'Empty state renders' );

	$malicious = $attrs;
	$malicious['readMoreText'] = '<img src=x onerror=alert(1)>';
	$safe = $block( $malicious );
	$check( strpos( $safe, '<img src=x onerror=' ) === false, 'Read-more label is HTML escaped' );

	$widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-posts-grid' ) : null;
	$check( is_object( $widget ) && $widget->get_name() === 'wpe-posts-grid', 'Elementor Posts Grid widget registered' );

	$mapped = Kit::from_elementor(
		Kit::defs()['posts-grid'],
		array(
			'postType'       => 'post',
			'postsPerPage'   => array( 'size' => 3, 'unit' => 'px' ),
			'columns'        => array( 'size' => 3, 'unit' => '' ),
			'columns_mobile' => array( 'size' => 1, 'unit' => '' ),
			'showPagination' => 'yes',
		)
	);
	$check( $mapped['postsPerPage'] === 3, 'Elementor post count mapped correctly' );
	$check( $mapped['columns']['base'] === 3 && $mapped['columns']['480'] === 1, 'Elementor responsive columns mapped correctly' );
	$check( $mapped['showPagination'] === true, 'Elementor toggle mapped correctly' );
	$mapped['terms'] = $slug;
	$mapped['taxonomy'] = 'category';
	$mapped['showImage'] = true;
	$mapped['showReadMore'] = true;
	$mapped['excludeCurrent'] = false;
	$mapped['orderBy'] = 'title';
	$mapped['order'] = 'ASC';
	$check( $count_cards( Kit::render( 'posts-grid', $mapped ) ) === 3, 'Elementor-mapped content renders through the shared renderer' );
	echo "Posts Grid WordPress integration smoke tests passed.\n";
} finally {
	unset( $_GET[ $page_key[0] ?? '' ] );
	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	wp_delete_term( $term_id, 'category' );
}
