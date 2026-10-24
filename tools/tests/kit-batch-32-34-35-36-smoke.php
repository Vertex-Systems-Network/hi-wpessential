<?php
/**
 * Native Gutenberg/Elementor regression: Social Feed, CTA, Calendar and Search.
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $ok, $description ) {
	if ( ! $ok ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};
$wpe_block = static function ( $slug, array $attrs = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $slug . ' ' . wp_json_encode( (object) $attrs ) . ' /-->' );
};

foreach ( array( 'social-feed', 'call-to-action', 'calendar', 'search' ) as $wpe_name ) {
	$wpe_check( isset( Kit::defs()[ $wpe_name ] ), $wpe_name . ': Kit definition loaded' );
	$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_name ), $wpe_name . ': Gutenberg block registered' );
	$wpe_check( wp_style_is( 'wpessential-kit-' . $wpe_name, 'registered' ), $wpe_name . ': stylesheet registered' );
	$wpe_widget = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_name ) : null;
	$wpe_check( is_object( $wpe_widget ) && 'wpe-' . $wpe_name === $wpe_widget->get_name(), $wpe_name . ': Elementor widget registered' );
}
$wpe_check( wp_script_is( 'wpessential-kit-social-feed', 'registered' ), 'Social Feed progressive enhancement script registered' );

// CTA: markup escaping, safe rich HTML, malicious external destinations rejected.
$wpe_cta = $wpe_block(
	'call-to-action',
	array(
		'eyebrow'         => '<script>alert(1)</script>Special',
		'title'           => '<img src=x onerror=alert(2)>Limited offer',
		'description'     => '<p><strong>Good content</strong></p><iframe src="javascript:alert(1)"></iframe><script>alert(4)</script>',
		'primaryText'     => 'Buy now',
		'primaryUrl'      => 'https://example.com/shop',
		'primaryNewTab'   => true,
		'secondaryText'   => 'Unsafe',
		'secondaryUrl'    => 'javascript:alert(6)',
		'titleTag'        => 'h3',
		'image'           => array( 'url' => 'https://example.com/banner.png' ),
		'imageAlt'        => 'Product image',
		'imagePosition'   => 'left',
	)
);
$wpe_check( false !== strpos( $wpe_cta, '<h3 class="wpe-call-to-action__title">' ), 'CTA selectable heading level' );
$wpe_check( false !== strpos( $wpe_cta, '<strong>Good content</strong>' ), 'CTA safe rich HTML preserved' );
$wpe_check( false === strpos( $wpe_cta, '<script>' ) && false === strpos( $wpe_cta, '<iframe' ) && false === strpos( $wpe_cta, '<img src=x' ), 'CTA XSS markup not executable' );
$wpe_check( false !== strpos( $wpe_cta, 'href="https://example.com/shop"' ), 'CTA HTTPS primary link rendered' );
$wpe_check( false !== strpos( $wpe_cta, 'rel="noopener noreferrer"' ), 'CTA external tab protected' );
$wpe_check( false === strpos( $wpe_cta, 'href="javascript:' ) && false === strpos( $wpe_cta, '>Unsafe</a>' ), 'CTA malicious secondary link removed' );
$wpe_check( false !== strpos( $wpe_cta, 'alt="Product image"' ) && false !== strpos( $wpe_cta, 'is-image-left' ), 'CTA media and layout render' );
$wpe_cta_mapped = Kit::from_elementor( Kit::defs()['call-to-action'], array(
	'primaryUrl'  => 'https://example.org',
	'primaryText' => 'Elementor action',
	'primaryNewTab' => 'yes',
	'maxWidth' => array( 'size' => 720, 'unit' => 'px' ),
) );
$wpe_check( true === $wpe_cta_mapped['primaryNewTab'], 'CTA Elementor boolean mapping' );
$wpe_check( false !== strpos( Kit::render( 'call-to-action', $wpe_cta_mapped ), '--wpek-cta-max:720px' ), 'CTA Elementor responsive CSS output' );

// Search: accessible GET + safe scope controls + native expandable form.
$wpe_search = $wpe_block( 'search', array(
	'placeholder' => '" autofocus onfocus="alert(1)',
	'label' => 'Find product',
	'buttonText' => '<img src=x onerror=alert(4)>Go',
	'scope' => 'post',
	'mode' => 'expand',
	'buttonStyle' => 'icon',
) );
$wpe_check( false !== strpos( $wpe_search, '<form ' ) && false !== strpos( $wpe_search, 'method="get"' ) && false !== strpos( $wpe_search, 'name="s"' ), 'Search native GET form submits to WP' );
$wpe_check( false !== strpos( $wpe_search, 'role="search"' ) && false !== strpos( $wpe_search, 'type="search"' ), 'Search form/input semantics' );
$wpe_check( false !== strpos( $wpe_search, 'name="post_type" value="post"' ), 'Search post-only scope submits trusted post_type' );
$wpe_check( false !== strpos( $wpe_search, '<details class="wpe-search__details">' ), 'Search expand mode works without JavaScript' );
$wpe_check( false !== strpos( $wpe_search, 'screen-reader-text' ), 'Search icon-only submit has accessible text' );
$wpe_check( false === strpos( $wpe_search, ' autofocus ' ) && false === strpos( $wpe_search, '<img src=x' ), 'Search text attributes escaped' );
$wpe_check( false === strpos( $wpe_block( 'search', array( 'scope' => 'all' ) ), 'name="post_type"' ), 'Search all-content mode omits post_type restriction' );
$wpe_check( false === strpos( $wpe_block( 'search', array( 'scope' => 'product' ) ), 'value="product"' ), 'Search product scope falls back if WooCommerce unavailable' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['search'], array( 'showLabel' => 'yes', 'scope' => 'page', 'maxWidth' => array( 'size' => 540, 'unit' => 'px' ) ) );
$wpe_check( true === $wpe_mapped['showLabel'], 'Search Elementor visible label maps correctly' );
$wpe_check( false !== strpos( Kit::render( 'search', $wpe_mapped ), '--wpek-search-max:540px' ), 'Search Elementor responsive max width' );

// Calendar: valid dates, ordering, no HTML injection, past events and calendar grid.
$wpe_events = array(
	array( 'title' => 'Late', 'date' => '2030-05-22', 'time' => '16:00', 'allDay' => false, 'location' => 'Hall B', 'url' => 'https://example.org/event' ),
	array( 'title' => '<svg onload=alert(1)>Early', 'date' => '2030-05-08', 'time' => '09:30', 'allDay' => false, 'description' => '<script>alert(1)</script>Details' ),
	array( 'title' => 'All-day meeting', 'date' => '2030-05-12', 'allDay' => true ),
	array( 'title' => 'Invalid day', 'date' => '2030-02-30', 'allDay' => true ),
	array( 'title' => 'Invalid time', 'date' => '2030-05-11', 'time' => '25:20', 'allDay' => false ),
	array( 'title' => 'Past', 'date' => '2020-01-01', 'allDay' => true ),
);
$wpe_list = $wpe_block( 'calendar', array( 'events' => $wpe_events, 'hidePast' => true, 'view' => 'list' ) );
$wpe_check( false !== strpos( $wpe_list, 'Early' ) && false !== strpos( $wpe_list, 'Late' ), 'Calendar future events render' );
$wpe_check( strpos( $wpe_list, 'Early' ) < strpos( $wpe_list, 'Late' ), 'Calendar event list sorted chronologically' );
$wpe_check( false === strpos( $wpe_list, 'Invalid day' ) && false === strpos( $wpe_list, 'Invalid time' ) && false === strpos( $wpe_list, '>Past</h3>' ), 'Calendar invalid date/time and past events filtered' );
$wpe_check( false === strpos( $wpe_list, '<svg onload' ) && false === strpos( $wpe_list, '<script>' ), 'Calendar content escaped against XSS' );
$wpe_check( false !== strpos( $wpe_list, 'href="https://example.org/event"' ), 'Calendar safe details link rendered' );
$wpe_check( false !== strpos( $wpe_list, 'Hall B' ), 'Calendar location rendered' );
$wpe_check( false !== strpos( $wpe_list, 'datetime="2030-05-08T09:30:00' ), 'Calendar outputs machine-readable date' );
$wpe_month = $wpe_block( 'calendar', array( 'events' => $wpe_events, 'view' => 'month', 'month' => '2030-05' ) );
$wpe_check( false !== strpos( $wpe_month, '<table class="wpe-calendar__table">' ), 'Calendar month has accessible table' );
$wpe_check( 7 === preg_match_all( '/<th scope="col">/', $wpe_month ), 'Calendar month renders 7 weekday headers' );
$wpe_check( false !== strpos( $wpe_month, 'datetime="2030-05-08"' ) && false !== strpos( $wpe_month, 'All-day meeting' ), 'Calendar month places events on correct dates' );
$wpe_check( false === strpos( $wpe_month, 'Invalid day' ), 'Calendar month also rejects invalid date' );
$wpe_bad = $wpe_block( 'calendar', array( 'events' => array(), 'view' => 'list', 'emptyMessage' => 'No plans' ) );
$wpe_check( false !== strpos( $wpe_bad, 'No plans' ), 'Calendar empty state' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['calendar'], array( 'view' => 'month', 'month' => '2030-05', 'events' => $wpe_events, 'gap' => array( 'size' => 24, 'unit' => 'px' ) ) );
$wpe_check( false !== strpos( Kit::render( 'calendar', $wpe_mapped ), '--wpek-calendar-gap:24px' ), 'Calendar Elementor responsive gap' );

// Curated feed: whitelist platform, HTTPS link, no remote fetch, original WP image markup and no-JS.
$wpe_posts = array(
	array( 'platform' => 'instagram', 'caption' => 'Photo 1', 'username' => 'Brand', 'date' => '2030-05-08', 'url' => 'https://instagram.com/post/1', 'image' => array( 'url' => 'https://example.org/one.jpg' ) ),
	array( 'platform' => 'linkedin', 'caption' => '<script>alert(1)</script>Work update', 'url' => 'javascript:alert(2)' ),
	array( 'platform' => 'fakeplatform', 'caption' => 'Third update', 'date' => '2030-02-30', 'url' => 'http://insecure.example.org' ),
);
$wpe_feed = $wpe_block( 'social-feed', array( 'source' => 'curated', 'items' => $wpe_posts, 'initialVisible' => 2, 'showImage' => true, 'showCaption' => true, 'buttonText' => 'View original' ) );
$wpe_check( 3 === preg_match_all( '/data-wpe-feed-item/', $wpe_feed ), 'Social curated posts render without JavaScript' );
$wpe_check( false !== strpos( $wpe_feed, 'Photo 1' ) && false !== strpos( $wpe_feed, 'Work update' ), 'Social captions render' );
$wpe_check( false === strpos( $wpe_feed, '<script>' ) && false === strpos( $wpe_feed, 'javascript:' ), 'Social malicious content/URL blocked' );
$wpe_check( false === strpos( $wpe_feed, 'http://insecure.example.org' ) && false === strpos( $wpe_feed, '2030-02-30' ), 'Social invalid HTTPS/date filtered' );
$wpe_check( false !== strpos( $wpe_feed, 'data-initial="2"' ) && false !== strpos( $wpe_feed, 'class="wpe-social-feed__more" hidden' ), 'Social load-more only activates with JS' );
$wpe_check( false !== strpos( $wpe_feed, 'href="https://instagram.com/post/1"' ), 'Social original HTTPS post link shown' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['social-feed'], array( 'source' => 'curated', 'items' => $wpe_posts, 'columns' => array( 'size' => 2, 'unit' => '' ), 'showCaption' => 'yes' ) );
$wpe_check( false !== strpos( Kit::render( 'social-feed', $wpe_mapped ), '--wpek-feed-cols:2' ), 'Social Elementor grid size maps' );

// Published WP post feed: creates temporary content and verifies escaping/publication.
$wpe_pub = wp_insert_post( array( 'post_title' => 'WPE Social Published', 'post_content' => 'Published article details', 'post_excerpt' => 'First news', 'post_status' => 'publish', 'post_type' => 'post' ), true );
$wpe_draft = wp_insert_post( array( 'post_title' => 'WPE Social Draft', 'post_content' => 'Not public', 'post_status' => 'draft', 'post_type' => 'post' ), true );
$wpe_check( ! is_wp_error( $wpe_pub ) && ! is_wp_error( $wpe_draft ), 'Fixture posts created' );
try {
	$wpe_wp_feed = $wpe_block( 'social-feed', array( 'source' => 'wp-posts', 'postType' => 'post', 'postCount' => 12 ) );
	$wpe_check( false !== strpos( $wpe_wp_feed, 'WPE Social Published' ), 'Social Feed queries published WP posts' );
	$wpe_check( false === strpos( $wpe_wp_feed, 'WPE Social Draft' ), 'Social Feed never exposes draft posts' );
	$wpe_check( false !== strpos( $wpe_wp_feed, get_permalink( $wpe_pub ) ), 'Social WordPress links point to local posts' );
} finally {
	wp_delete_post( $wpe_pub, true );
	wp_delete_post( $wpe_draft, true );
}
echo "Social Feed, CTA, Calendar and Search integration tests passed.\n";
