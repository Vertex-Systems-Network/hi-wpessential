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
	$rows = (array) get_option( 'wpessential_testimonials', array() );
	$out  = '';
	foreach ( $rows as $row ) {
		if ( empty( $row[0] ) ) {
			continue;
		}
		$name = (string) ( $row[1] ?? '' );
		$out .= '<figure class="wpe-ess-quote"><span class="wpe-ess-quote__stars" aria-label="' . esc_attr__( '5 out of 5', 'hi-wpessential' ) . '">★★★★★</span><blockquote>“' . esc_html( (string) $row[0] ) . '”</blockquote><figcaption><span class="wpe-ess-quote__avatar" aria-hidden="true">' . esc_html( mb_substr( $name, 0, 1 ) ) . '</span>'
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
