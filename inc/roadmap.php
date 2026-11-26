<?php
/**
 * Roadmap votes.
 *
 * @package WPEssential
 */

/**
 * Roadmap vote button (Figma 11). Votes are counted in the wpessential_roadmap_votes option;
 * the browser remembers what it voted for, so one visitor counts once per item.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function wpessential_vote( $atts ) {
	$id    = sanitize_key( (string) ( shortcode_atts( array( 'id' => '' ), $atts, 'wpessential_vote' )['id'] ) );
	$votes = (array) get_option( 'wpessential_roadmap_votes', array() );
	if ( '' === $id ) {
		return '';
	}
	static $script = false;

	$out = '<button type="button" class="wpe-vote" data-wpe-vote="' . esc_attr( $id ) . '"><span aria-hidden="true">▲</span> ' . esc_html__( 'Vote', 'hi-wpessential' ) . ' <b>' . ( empty( $votes[ $id ] ) ? '' : (int) $votes[ $id ] ) . '</b></button>';
	if ( ! $script ) {
		$script = true;
		$out   .= '<script>document.addEventListener("click",function(e){var b=e.target.closest("[data-wpe-vote]");if(!b)return;var k="wpe-vote-"+b.dataset.wpeVote;try{if(localStorage.getItem(k))return;}catch(x){}b.disabled=true;var f=new FormData();f.append("action","wpessential_vote");f.append("id",b.dataset.wpeVote);f.append("nonce",' . wp_json_encode( wp_create_nonce( 'wpessential_vote' ) ) . ');'
			. 'fetch(' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ',{method:"POST",body:f,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(r){if(r&&r.success){b.querySelector("b").textContent=r.data;b.classList.add("is-voted");try{localStorage.setItem(k,1);}catch(x){}}});});'
			. 'document.querySelectorAll("[data-wpe-vote]").forEach(function(b){try{if(localStorage.getItem("wpe-vote-"+b.dataset.wpeVote)){b.classList.add("is-voted");b.disabled=true;}}catch(x){}});</script>';
	}
	return $out;
}
add_shortcode( 'wpessential_vote', 'wpessential_vote' );

/** Counts one roadmap vote. */
function wpessential_vote_ajax() {
	check_ajax_referer( 'wpessential_vote', 'nonce' );
	$id = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
	if ( '' === $id || strlen( $id ) > 80 ) {
		wp_send_json_error();
	}
	$votes = (array) get_option( 'wpessential_roadmap_votes', array() );
	if ( ! isset( $votes[ $id ] ) && count( $votes ) >= 200 ) {
		wp_send_json_error(); // Caps unknown ids so the option cannot be flooded.
	}
	$votes[ $id ] = ( isset( $votes[ $id ] ) ? (int) $votes[ $id ] : 0 ) + 1;
	update_option( 'wpessential_roadmap_votes', $votes, false );
	wp_send_json_success( $votes[ $id ] );
}
add_action( 'wp_ajax_wpessential_vote', 'wpessential_vote_ajax' );
add_action( 'wp_ajax_nopriv_wpessential_vote', 'wpessential_vote_ajax' );

/**
 * Testimonials (Figma 01) from the wpessential_testimonials option: [quote, name, role] rows.
 *
 * @return string
 */
function wpessential_testimonials() {
	// Testimonial posts (CPT from the WPEssential plugin), with role and rating fields; the option is the fallback.
	$rows  = array();
	$posts = post_type_exists( 'wpe_testimonial' ) ? get_posts(
		array(
			'post_type'   => 'wpe_testimonial',
			'numberposts' => 3,
			'orderby'     => 'menu_order date',
			'order'       => 'ASC',
		)
	) : array();
	foreach ( $posts as $post ) {
		$rows[] = array( wp_strip_all_tags( $post->post_content ), $post->post_title, (string) get_post_meta( $post->ID, 'wpe_role', true ), (int) get_post_meta( $post->ID, 'wpe_rating', true ) );
	}
	if ( ! $rows ) {
		$rows = (array) get_option( 'wpessential_testimonials', array() );
	}
	$out = '';
	foreach ( $rows as $row ) {
		if ( empty( $row[0] ) ) {
			continue;
		}
		$name  = (string) ( $row[1] ?? '' );
		$stars = max( 0, min( 5, (int) ( $row[3] ?? 5 ) ) );
		/* translators: %d: rating out of 5. */
		$out .= '<figure class="wpe-ess-quote"><span class="wpe-ess-quote__stars" aria-label="' . esc_attr( sprintf( __( '%d out of 5', 'hi-wpessential' ), $stars ) ) . '">' . str_repeat( '★', $stars ) . '</span><blockquote>“' . esc_html( (string) $row[0] ) . '”</blockquote><figcaption><span class="wpe-ess-quote__avatar" aria-hidden="true">' . esc_html( mb_substr( $name, 0, 1 ) ) . '</span>'
			. '<span><b>' . esc_html( $name ) . '</b><small>' . esc_html( (string) ( $row[2] ?? '' ) ) . '</small></span></figcaption></figure>';
	}
	return '' === $out ? '' : '<div class="wpe-ess-quotes">' . $out . '</div>';
}
add_shortcode( 'wpessential_testimonials', 'wpessential_testimonials' );

/**
 * Blog category chips (Figma 14): an "All" chip first, marked current on the posts page.
 *
 * @param string $html  Block markup.
 * @param array  $block Block.
 * @return string
 */
function wpessential_blog_all_chip( $html, $block ) {
	if ( false === strpos( (string) ( $block['attrs']['className'] ?? '' ), 'wpe-blog-cats' ) ) {
		return $html;
	}
	$page = (int) get_option( 'page_for_posts' );
	$url  = $page ? (string) get_permalink( $page ) : home_url( '/' );
	$li   = '<li class="cat-item cat-item-all' . ( is_home() ? ' current-cat' : '' ) . '"><a href="' . esc_url( $url ) . '">' . esc_html__( 'All', 'hi-wpessential' ) . '</a></li>';
	return (string) preg_replace( '#(<ul[^>]*>)#', '$1' . $li, $html, 1 );
}
add_filter( 'render_block_core/categories', 'wpessential_blog_all_chip', 10, 2 );

/**
 * Byline for demo posts (Figma 15): a post's _wpe_author_name meta replaces the user's display name.
 *
 * @param string $name Display name.
 * @return string
 */
function wpessential_post_byline( $name ) {
	$post   = get_post();
	$byline = $post ? (string) get_post_meta( $post->ID, '_wpe_author_name', true ) : '';
	return '' !== $byline ? $byline : $name;
}
add_filter( 'get_the_author_display_name', 'wpessential_post_byline' );


/**
 * Team members (Figma 13) from the wpe_team post type with its Role field.
 *
 * @return string
 */
function wpessential_team( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'over'  => '',
			'title' => '',
			'lead'  => '',
			'map'   => '',
			'card'  => '',
		),
		$atts,
		'wpessential_team'
	);
	// Team posts (wpe_team CPT with a Role field); the wpessential_team option ([name, role] rows) is the fallback.
	$members = array();
	$posts   = post_type_exists( 'wpe_team' ) ? get_posts( array( 'post_type' => 'wpe_team', 'numberposts' => 8, 'orderby' => 'menu_order date', 'order' => 'ASC' ) ) : array();
	foreach ( $posts as $post ) {
		$members[] = array( $post->post_title, (string) get_post_meta( $post->ID, 'wpe_role', true ), get_the_post_thumbnail( $post, 'medium', array( 'alt' => '' ) ) );
	}
	if ( ! $members ) {
		$members = (array) get_option( 'wpessential_team', array() );
	}
	$out = '';
	foreach ( $members as $member ) {
		$name     = (string) ( $member[0] ?? '' );
		$initials = implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_slice( preg_split( '/\s+/', trim( $name ) ), 0, 2 ) ) );
		$photo    = (string) ( $member[2] ?? '' );
		$out     .= '<figure>' . ( '' !== $photo ? $photo : '<span aria-hidden="true">' . esc_html( $initials ) . '</span>' ) . '<figcaption><b>' . esc_html( $name ) . '</b><small>' . esc_html( (string) ( $member[1] ?? '' ) ) . '</small></figcaption></figure>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core thumbnail markup.
	}
	$people = '' === $out ? '' : '<div class="wpe-ess-people">' . $out . '</div>';
	if ( '' === $atts['map'] ) {
		return $people;
	}
	// Team beside an office map: map = address for Google Maps, card = lines for the address card ("|" between lines).
	$q     = rawurlencode( (string) $atts['map'] );
	$lines = array_map( 'esc_html', explode( '|', (string) $atts['card'] ) );
	return '<div class="wpe-ess-teamgrid"><div>' . ( '' !== $atts['over'] ? '<p class="wpe-ess-over">' . esc_html( $atts['over'] ) . '</p>' : '' )
		. '<h2 class="wpe-ess-h2">' . esc_html( $atts['title'] ) . '</h2><p class="wpe-ess-lead">' . esc_html( $atts['lead'] ) . '</p>' . $people . '</div>'
		. '<div class="wpe-ess-map"><iframe title="' . esc_attr__( 'Office location on Google Maps', 'hi-wpessential' ) . '" src="https://maps.google.com/maps?q=' . $q . '&amp;z=15&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
		. '<div class="wpe-ess-map__card"><strong>' . array_shift( $lines ) . '</strong><span>' . implode( '<br>', $lines ) . '</span>'
		. '<a href="https://maps.google.com/?q=' . $q . '" target="_blank" rel="noopener">' . esc_html__( 'Get directions ↗', 'hi-wpessential' ) . '</a></div></div></div>';
}
add_shortcode( 'wpessential_team', 'wpessential_team' );
