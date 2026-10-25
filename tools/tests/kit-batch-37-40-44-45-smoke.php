<?php
/**
 * Six WPEssential Kit components in disposable WP + Elementor.
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
$wpe_block = static function ( $name, array $attributes = array() ) {
	return do_blocks( '<!-- wp:wpessential/' . $name . ' ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};
foreach ( array( 'comments', 'timeline', 'rating', 'alert', 'business-hours', 'image-comparison' ) as $wpe_name ) {
	$wpe_check( isset( Kit::defs()[ $wpe_name ] ), $wpe_name . ': Kit definition loaded' );
	$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/' . $wpe_name ), $wpe_name . ': Gutenberg block available' );
	$wpe_check( wp_style_is( 'wpessential-kit-' . $wpe_name, 'registered' ), $wpe_name . ': CSS registered' );
	$wpe_widget = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-' . $wpe_name ) : null;
	$wpe_check( is_object( $wpe_widget ) && 'wpe-' . $wpe_name === $wpe_widget->get_name(), $wpe_name . ': Elementor widget registered' );
}
$wpe_check( wp_script_is( 'wpessential-kit-alert', 'registered' ) && wp_script_is( 'wpessential-kit-image-comparison', 'registered' ), 'Alert and Image Comparison JS registered' );

// Rating clamps values and provides a single accessible label.
$wpe_rating = $wpe_block( 'rating', array( 'title' => '<script>Unsafe</script>Score', 'rating' => 4.5, 'scale' => '5', 'icon' => 'heart' ) );
$wpe_check( false !== strpos( $wpe_rating, 'aria-label="Rated 4.5 out of 5"' ), 'Rating: accessible fractional score' );
$wpe_check( 5 === preg_match_all( '/class="wpe-rating__symbol"/', $wpe_rating ), 'Rating: five symbols for five-point scale' );
$wpe_check( false !== strpos( $wpe_rating, '--wpek-rating-fill:50%' ), 'Rating: partial symbol fill computed' );
$wpe_check( false === strpos( $wpe_rating, '<script>' ), 'Rating: user title escaped' );
$wpe_overshoot = $wpe_block( 'rating', array( 'rating' => 999, 'scale' => '10', 'showNumber' => false ) );
$wpe_check( false !== strpos( $wpe_overshoot, 'Rated 10 out of 10' ), 'Rating: out-of-range values capped at max' );
$wpe_check( 10 === preg_match_all( '/class="wpe-rating__symbol"/', $wpe_overshoot ), 'Rating: ten symbols for ten-point scale' );
$wpe_check( false === strpos( $wpe_overshoot, 'class="wpe-rating__score"' ), 'Rating: numeric score can be hidden without losing accessible label' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['rating'], array( 'rating' => 3.5, 'iconSize' => array( 'size' => 34, 'unit' => 'px' ), 'showNumber' => 'yes' ) );
$wpe_check( false !== strpos( Kit::render( 'rating', $wpe_mapped ), '--wpek-rating-size:34px' ), 'Rating: Elementor size mapped to shared renderer' );

// Alert safely sanitizes rich content and reveals only JS-backed dismissal button.
$wpe_alert = $wpe_block( 'alert', array(
	'type' => 'warning',
	'title' => '<img src=x onerror=alert(1)>Warning',
	'content' => '<p><strong>Safe detail</strong></p><script>alert(1)</script>',
	'dismissible' => true,
	'remember' => true,
) );
$wpe_check( false !== strpos( $wpe_alert, 'role="alert"' ), 'Alert: warning uses alert role' );
$wpe_check( false !== strpos( $wpe_alert, '<strong>Safe detail</strong>' ), 'Alert: safe HTML kept' );
$wpe_check( false === strpos( $wpe_alert, '<script>' ) && false === strpos( $wpe_alert, '<img src=x' ), 'Alert: malicious markup removed' );
$wpe_check( false !== strpos( $wpe_alert, 'data-remember="1"' ) && false !== strpos( $wpe_alert, 'class="wpe-alert__dismiss"' ), 'Alert: dismissal and session mode configured' );
$wpe_check( false !== strpos( $wpe_alert, 'hidden>' ), 'Alert: dismiss control hidden without JS' );
$wpe_nodismiss = $wpe_block( 'alert', array( 'dismissible' => false, 'type' => 'success' ) );
$wpe_check( false === strpos( $wpe_nodismiss, 'wpe-alert__dismiss' ) && false !== strpos( $wpe_nodismiss, 'role="status"' ), 'Alert: nondismissable success mode' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['alert'], array( 'dismissible' => '', 'maxWidth' => array( 'size' => 760, 'unit' => 'px' ) ) );
$wpe_check( false === $wpe_mapped['dismissible'] && false !== strpos( Kit::render( 'alert', $wpe_mapped ), '--wpek-alert-max:760px' ), 'Alert: Elementor controls map correctly' );

// Timeline: safe rich descriptions, links and responsive style options.
$wpe_timeline = $wpe_block( 'timeline', array(
	'layout' => 'horizontal',
	'items' => array(
		array( 'title' => 'Launch', 'date' => '<script>Unsafe</script>2020', 'content' => '<p><em>Safe content</em></p>', 'link' => 'https://example.org/about', 'linkLabel' => 'More details' ),
		array( 'title' => 'Expansion', 'date' => '2024', 'content' => '<script>alert(1)</script>', 'link' => 'javascript:alert(1)', 'linkLabel' => 'Unsafe' ),
	),
) );
$wpe_check( false !== strpos( $wpe_timeline, '<ol class="wpe-timeline__list">' ) && 2 === preg_match_all( '/class="wpe-timeline__item"/', $wpe_timeline ), 'Timeline: semantic two-milestone list' );
$wpe_check( false !== strpos( $wpe_timeline, 'is-horizontal' ), 'Timeline: horizontal scroll layout' );
$wpe_check( false !== strpos( $wpe_timeline, '<em>Safe content</em>' ), 'Timeline: permitted rich formatting kept' );
$wpe_check( false === strpos( $wpe_timeline, '<script>' ) && false === strpos( $wpe_timeline, 'javascript:' ), 'Timeline: milestone content and URLs sanitized' );
$wpe_check( false !== strpos( $wpe_timeline, 'href="https://example.org/about"' ), 'Timeline: valid detail link preserved' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['timeline'], array( 'showImages' => '', 'gap' => array( 'size' => 32, 'unit' => 'px' ) ) );
$wpe_check( false === $wpe_mapped['showImages'] && false !== strpos( Kit::render( 'timeline', $wpe_mapped ), '--wpek-timeline-gap:32px' ), 'Timeline: Elementor toggle/responsive gap mapped' );

// Business Hours: validates clock, rejects duplicate weekdays, outputs local timezone.
$wpe_hours = $wpe_block( 'business-hours', array(
	'layout' => 'table',
	'rows' => array(
		array( 'day' => 'monday', 'closed' => false, 'open' => '09:30', 'close' => '18:00' ),
		array( 'day' => 'monday', 'closed' => true ),
		array( 'day' => 'tuesday', 'closed' => true ),
		array( 'day' => 'wednesday', 'closed' => false, 'open' => '25:20', 'close' => '17:00' ),
		array( 'day' => '<script>', 'closed' => true ),
	),
	'showTimezone' => true,
	'closedText' => '<script>Closed</script>',
) );
$wpe_check( false !== strpos( $wpe_hours, '<table class="wpe-business-hours__table">' ), 'Hours: accessible table format' );
$wpe_check( 2 === preg_match_all( '/<tr/', $wpe_hours ), 'Hours: duplicate/invalid weekdays and invalid clocks rejected' );
$wpe_check( false !== strpos( $wpe_hours, '09:30 – 18:00' ), 'Hours: valid opening times retained' );
$wpe_check( false === strpos( $wpe_hours, '<script>' ), 'Hours: closed label and days escaped' );
$wpe_check( false !== strpos( $wpe_hours, wp_timezone_string() ), 'Hours: site timezone printed' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['business-hours'], array( 'rows' => array( array( 'day' => 'friday', 'closed' => 'yes', 'open' => 'bad', 'close' => 'bad' ) ), 'gap' => array( 'size' => 13, 'unit' => 'px' ) ) );
$wpe_check( false !== strpos( Kit::render( 'business-hours', $wpe_mapped ), '--wpek-hours-gap:13px' ), 'Hours: Elementor responsive gap mapped' );

// Image Comparison: strict safe sources, native input semantics and invalid media fallback.
$wpe_compare = $wpe_block( 'image-comparison', array(
	'before' => array( 'url' => 'https://example.org/before.png' ),
	'after' => array( 'url' => 'https://example.org/after.png' ),
	'beforeLabel' => 'Before',
	'afterLabel' => '<svg onload=alert(1)>After',
	'beforeAlt' => 'Before renovation',
	'afterAlt' => 'After renovation',
	'start' => 35,
	'caption' => '<script>Unsafe</script>Our work',
) );
$wpe_check( false !== strpos( $wpe_compare, '--wpek-compare-progress:35%' ), 'Comparison: configurable initial reveal' );
$wpe_check( 2 === preg_match_all( '/class="wpe-image-comparison__image"/', $wpe_compare ), 'Comparison: before/after images rendered' );
$wpe_check( false !== strpos( $wpe_compare, 'type="range"' ) && false !== strpos( $wpe_compare, 'value="35"' ), 'Comparison: native keyboard range input' );
$wpe_check( false !== strpos( $wpe_compare, 'alt="Before renovation"' ), 'Comparison: image alt text respected' );
$wpe_check( false === strpos( $wpe_compare, '<script>' ) && false === strpos( $wpe_compare, '<svg onload=' ), 'Comparison: malicious labels/markup escaped' );
$wpe_check( false === strpos( $wpe_block( 'image-comparison', array( 'before' => array( 'url' => 'javascript:alert(1)' ) ) ), 'data-wpe-comparison' ), 'Comparison: invalid source or absent image creates no broken slider' );
$wpe_mapped = Kit::from_elementor( Kit::defs()['image-comparison'], array( 'start' => 70, 'height' => array( 'size' => 540, 'unit' => 'px' ) ) );
$wpe_check( false !== strpos( Kit::render( 'image-comparison', $wpe_mapped ), '--wpek-compare-height:540px' ) || $wpe_mapped['height']['base'] === 540, 'Comparison: Elementor responsive height mapped' );

// Comments: create actual public post + approved, pending and draft data.
$wpe_post = wp_insert_post( array( 'post_title' => 'WPE Comments Smoke', 'post_status' => 'publish', 'post_type' => 'post', 'comment_status' => 'open' ), true );
$wpe_draft = wp_insert_post( array( 'post_title' => 'Private draft comments smoke', 'post_status' => 'draft', 'post_type' => 'post', 'comment_status' => 'open' ), true );
$wpe_check( ! is_wp_error( $wpe_post ) && ! is_wp_error( $wpe_draft ), 'Comments: disposable public/draft posts created' );
$wpe_comment_ids = array();
try {
	$wpe_approved = wp_insert_comment( array(
		'comment_post_ID' => $wpe_post,
		'comment_author' => 'Approved Visitor',
		'comment_author_email' => 'visitor@example.org',
		'comment_content' => '<p>Approved comment content</p><script>alert(1)</script>',
		'comment_approved' => 1,
		'comment_type' => 'comment',
	) );
	$wpe_pending = wp_insert_comment( array(
		'comment_post_ID' => $wpe_post,
		'comment_author' => 'Pending Visitor',
		'comment_content' => 'Pending confidential text',
		'comment_approved' => 0,
		'comment_type' => 'comment',
	) );
	$wpe_comment_ids = array( $wpe_approved, $wpe_pending );
	$wpe_check( $wpe_approved > 0 && $wpe_pending > 0, 'Comments: approved and pending fixtures inserted' );
	$wpe_comments = $wpe_block( 'comments', array( 'postId' => (string) $wpe_post, 'showForm' => true, 'showPostTitle' => true ) );
	$wpe_check( false !== strpos( $wpe_comments, 'Approved Visitor' ) && false !== strpos( $wpe_comments, 'Approved comment content' ), 'Comments: approved comment visible' );
	$wpe_check( false === strpos( $wpe_comments, 'Pending confidential text' ), 'Comments: unapproved comment never exposed' );
	$wpe_check( false === strpos( $wpe_comments, '<script>' ), 'Comments: untrusted comment HTML sanitized' );
	$wpe_check( false !== strpos( $wpe_comments, 'WPE Comments Smoke' ), 'Comments: public post title link rendered' );
	$wpe_check( false !== strpos( $wpe_comments, 'commentform' ) || false !== strpos( $wpe_comments, 'reply-form' ), 'Comments: native WordPress reply form rendered for open post' );
	$wpe_check( false !== strpos( $wpe_comments, 'wpe-comments__permalink' ), 'Comments: approved permalink rendered' );
	$wpe_check( false === strpos( $wpe_block( 'comments', array( 'postId' => (string) $wpe_draft ) ), 'wpe-comments__item' ), 'Comments: no draft post comments exposed' );
	$wpe_check( false === strpos( $wpe_block( 'comments', array( 'postId' => '99999999' ) ), 'wpe-comments__list' ), 'Comments: unknown post renders no public feed' );
	$wpe_mapped = Kit::from_elementor( Kit::defs()['comments'], array( 'postId' => (string) $wpe_post, 'showAvatar' => '', 'gap' => array( 'size' => 20, 'unit' => 'px' ) ) );
	$wpe_check( false === $wpe_mapped['showAvatar'], 'Comments: Elementor avatar switcher mapped' );
	$wpe_html = Kit::render( 'comments', $wpe_mapped );
	$wpe_check( false !== strpos( $wpe_html, 'Approved Visitor' ) && false !== strpos( $wpe_html, '--wpek-comments-gap:20px' ), 'Comments: shared Elementor renderer and responsive gap' );
} finally {
	foreach ( $wpe_comment_ids as $wpe_comment_id ) {
		if ( $wpe_comment_id ) {
			wp_delete_comment( $wpe_comment_id, true );
		}
	}
	wp_delete_post( $wpe_post, true );
	wp_delete_post( $wpe_draft, true );
}
echo "Six Kit widgets WordPress + Elementor integration tests passed.\n";
