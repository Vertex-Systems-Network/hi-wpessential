<?php
/**
 * Posts grid renderer. Included by Kit::template() with $a and $id.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template-local variables.
$wpe_type   = sanitize_key( (string) $a['postType'] );
$wpe_object = get_post_type_object( $wpe_type );
if ( ! $wpe_object || ! $wpe_object->public ) {
	$wpe_type = 'post';
}
$wpe_per_page   = min( 24, max( 1, (int) $a['postsPerPage'] ) );
$wpe_tax        = sanitize_key( (string) $a['taxonomy'] );
$wpe_tax_object = $wpe_tax ? get_taxonomy( $wpe_tax ) : false;
$wpe_paged_key  = 'wpe_pg_' . substr( md5( wp_json_encode( array( $a['postType'], $a['taxonomy'], $a['terms'], $a['orderBy'], $a['order'], $a['postsPerPage'], $a['offset'], $a['cssClass'] ?? '' ) ) ), 0, 8 );
$wpe_filter_key = 'wpe_filter_' . substr( md5( wp_json_encode( array( $a['taxonomy'], $a['postType'], $a['cssClass'] ?? '' ) ) ), 0, 8 );
$wpe_filter_slug = ! empty( $_GET[ $wpe_filter_key ] ) ? sanitize_title( wp_unslash( $_GET[ $wpe_filter_key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only taxonomy filter.
$wpe_filter_terms = array();
if ( ! empty( $a['showFilterTabs'] ) && $wpe_tax_object && in_array( $wpe_type, (array) $wpe_tax_object->object_type, true ) ) {
	$wpe_filter_terms = get_terms( array( 'taxonomy' => $wpe_tax, 'hide_empty' => true ) );
	if ( is_wp_error( $wpe_filter_terms ) ) { $wpe_filter_terms = array(); }
	if ( $wpe_filter_slug && ! in_array( $wpe_filter_slug, wp_list_pluck( $wpe_filter_terms, 'slug' ), true ) ) { $wpe_filter_slug = ''; }
}
$wpe_page       = ! empty( $_GET[ $wpe_paged_key ] ) ? max( 1, absint( wp_unslash( $_GET[ $wpe_paged_key ] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination.
$wpe_query_args = array(
	'post_type'           => $wpe_type,
	'post_status'         => 'publish',
	'posts_per_page'      => $wpe_per_page,
	'offset'              => min( 100, max( 0, (int) $a['offset'] ) ) + ( $wpe_page - 1 ) * $wpe_per_page,
	'orderby'             => in_array( $a['orderBy'], array( 'date', 'modified', 'title', 'menu_order', 'comment_count' ), true ) ? $a['orderBy'] : 'date',
	'order'               => 'ASC' === $a['order'] ? 'ASC' : 'DESC',
	'ignore_sticky_posts' => ! array_key_exists( 'ignoreSticky', $a ) || ! empty( $a['ignoreSticky'] ),
	'no_found_rows'       => ! $a['showPagination'],
);
$wpe_author_ids = array_values( array_filter( array_map( 'absint', explode( ',', (string) ( $a['authorIds'] ?? '' ) ) ) ) );
if ( $wpe_author_ids ) { $wpe_query_args['author__in'] = $wpe_author_ids; }
$wpe_include_ids = array_values( array_filter( array_map( 'absint', explode( ',', (string) ( $a['includePosts'] ?? '' ) ) ) ) );
if ( $wpe_include_ids ) { $wpe_query_args['post__in'] = $wpe_include_ids; $wpe_query_args['orderby'] = 'post__in'; }
$wpe_exclude_ids = array_values( array_filter( array_map( 'absint', explode( ',', (string) ( $a['excludePosts'] ?? '' ) ) ) ) );
if ( ! empty( $a['excludeCurrent'] ) && get_the_ID() && get_post_type( get_the_ID() ) === $wpe_type ) { $wpe_exclude_ids[] = get_the_ID(); }
if ( $wpe_exclude_ids ) { $wpe_query_args['post__not_in'] = array_values( array_unique( $wpe_exclude_ids ) ); }
$wpe_tax_queries = array();
if ( $wpe_tax_object && in_array( $wpe_type, (array) $wpe_tax_object->object_type, true ) ) {
	$wpe_terms = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $a['terms'] ) ) ) );
	if ( $wpe_terms ) { $wpe_tax_queries[] = array( 'taxonomy' => $wpe_tax, 'field' => 'slug', 'terms' => $wpe_terms ); }
	if ( $wpe_filter_slug ) { $wpe_tax_queries[] = array( 'taxonomy' => $wpe_tax, 'field' => 'slug', 'terms' => array( $wpe_filter_slug ) ); }
}
if ( $wpe_tax_queries ) { $wpe_query_args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $wpe_tax_queries ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Explicit taxonomy filter.
}
$wpe_posts = new WP_Query( $wpe_query_args );
$wpe_tag   = in_array( $a['titleTag'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $a['titleTag'] : 'h3';
$wpe_layout = in_array( (string) ( $a['layout'] ?? 'grid' ), array( 'grid', 'list', 'masonry', 'carousel' ), true ) ? $a['layout'] : 'grid';
$wpe_class = 'wpe-posts-grid is-layout-' . sanitize_html_class( $wpe_layout ) . ( ! empty( $a['equalHeight'] ) ? ' has-equal-height' : '' ) . ' is-' . sanitize_key( $a['cardStyle'] ) . ' is-hover-' . sanitize_key( $a['hover'] ) . ' is-image-hover-' . sanitize_key( $a['imageHover'] );
$wpe_size  = in_array( $a['imageSize'], array( 'medium', 'medium_large', 'large', 'full' ), true ) ? $a['imageSize'] : 'large';
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $wpe_class ); ?>"<?php echo 'carousel' === $wpe_layout ? ' role="region" tabindex="0" aria-label="' . esc_attr__( 'Posts carousel', 'hi-wpessential' ) . '"' : ''; ?>>
	<?php if ( ! empty( $a['showFilterTabs'] ) && $wpe_filter_terms ) : ?>
		<nav class="wpe-posts-grid__filters" aria-label="<?php esc_attr_e( 'Filter posts by category', 'hi-wpessential' ); ?>">
			<?php $wpe_filter_base = remove_query_arg( array( $wpe_filter_key, $wpe_paged_key ) ); ?>
			<a href="<?php echo esc_url( $wpe_filter_base ); ?>"<?php echo '' === $wpe_filter_slug ? ' aria-current="true"' : ''; ?>><?php esc_html_e( 'All', 'hi-wpessential' ); ?></a>
			<?php foreach ( $wpe_filter_terms as $wpe_term ) : ?>
				<a href="<?php echo esc_url( add_query_arg( $wpe_filter_key, $wpe_term->slug, $wpe_filter_base ) ); ?>"<?php echo $wpe_filter_slug === $wpe_term->slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $wpe_term->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php if ( $wpe_posts->have_posts() ) : ?>
		<div class="wpe-posts-grid__items"<?php echo 'carousel' === $wpe_layout ? ' role="group" tabindex="0" aria-label="' . esc_attr__( 'Scrollable posts', 'hi-wpessential' ) . '"' : ''; ?>>
			<?php while ( $wpe_posts->have_posts() ) : ?>
				<?php $wpe_posts->the_post(); ?>
				<article <?php post_class( 'wpe-posts-grid__card' ); ?>>
					<?php if ( $a['showImage'] && has_post_thumbnail() ) : ?>
						<a class="wpe-posts-grid__image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Post title. */ __( 'Read %s', 'hi-wpessential' ), get_the_title() ) ); ?>">
							<?php echo get_the_post_thumbnail( get_the_ID(), $wpe_size, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress image markup. ?>
						</a>
					<?php endif; ?>
					<div class="wpe-posts-grid__body">
						<?php
						if ( $a['showTaxonomy'] && $wpe_tax_object && in_array( $wpe_type, (array) $wpe_tax_object->object_type, true ) ) {
							$wpe_post_terms = get_the_terms( get_the_ID(), $wpe_tax );
							if ( ! is_wp_error( $wpe_post_terms ) && $wpe_post_terms ) {
								echo '<span class="wpe-posts-grid__term">' . esc_html( $wpe_post_terms[0]->name ) . '</span>';
							}
						}
						?>
						<?php if ( ! empty( $a['showTags'] ) ) :
							$wpe_tags = get_the_terms( get_the_ID(), 'post_tag' );
							if ( $wpe_tags && ! is_wp_error( $wpe_tags ) ) : ?>
								<div class="wpe-posts-grid__tags"><?php foreach ( $wpe_tags as $wpe_tag_item ) : ?><span><?php echo esc_html( $wpe_tag_item->name ); ?></span><?php endforeach; ?></div>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( 'above' === ( $a['metaPosition'] ?? 'above' ) && ( $a['showDate'] || $a['showAuthor'] || ! empty( $a['showComments'] ) ) ) : ?>
							<div class="wpe-posts-grid__meta">
								<?php if ( $a['showDate'] ) : ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><?php endif; ?>
								<?php if ( $a['showAuthor'] ) : ?><span class="wpe-posts-grid__author"><?php if ( ! empty( $a['showAuthorImage'] ) ) echo get_avatar( get_the_author_meta( 'ID' ), 32, '', '', array( 'class' => 'wpe-posts-grid__avatar' ) ); ?><?php echo esc_html( get_the_author() ); ?></span><?php endif; ?>
								<?php if ( ! empty( $a['showComments'] ) ) : ?><span class="wpe-posts-grid__comments"><?php echo esc_html( sprintf( /* translators: %s: count. */ _n( '%s comment', '%s comments', (int) get_comments_number(), 'hi-wpessential' ), number_format_i18n( get_comments_number() ) ) ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
						<<?php echo esc_html( $wpe_tag ); ?> class="wpe-posts-grid__title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></<?php echo esc_html( $wpe_tag ); ?>>
						<?php if ( 'below' === ( $a['metaPosition'] ?? 'above' ) && ( $a['showDate'] || $a['showAuthor'] || ! empty( $a['showComments'] ) ) ) : ?>
							<div class="wpe-posts-grid__meta">
								<?php if ( $a['showDate'] ) : ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><?php endif; ?>
								<?php if ( $a['showAuthor'] ) : ?><span class="wpe-posts-grid__author"><?php if ( ! empty( $a['showAuthorImage'] ) ) echo get_avatar( get_the_author_meta( 'ID' ), 32, '', '', array( 'class' => 'wpe-posts-grid__avatar' ) ); ?><?php echo esc_html( get_the_author() ); ?></span><?php endif; ?>
								<?php if ( ! empty( $a['showComments'] ) ) : ?><span class="wpe-posts-grid__comments"><?php echo esc_html( sprintf( _n( '%s comment', '%s comments', (int) get_comments_number(), 'hi-wpessential' ), number_format_i18n( get_comments_number() ) ) ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( $a['showExcerpt'] ) : ?>
							<p class="wpe-posts-grid__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), min( 80, max( 5, (int) $a['excerptWords'] ) ) ) ); ?></p>
						<?php endif; ?>
						<?php if ( $a['showReadMore'] ) : ?>
							<a class="wpe-posts-grid__read-more" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Post title. */ __( 'Read more about %s', 'hi-wpessential' ), get_the_title() ) ); ?>"><?php echo esc_html( (string) $a['readMoreText'] ); ?><span aria-hidden="true"> →</span></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php
		if ( $a['showPagination'] ) {
			$wpe_total = max( 0, (int) $wpe_posts->found_posts - min( 100, max( 0, (int) $a['offset'] ) ) );
			$wpe_max   = (int) ceil( $wpe_total / $wpe_per_page );
			$wpe_pag_type = in_array( (string) ( $a['paginationType'] ?? 'numbers' ), array( 'numbers', 'prev_next', 'both' ), true ) ? $a['paginationType'] : 'numbers';
			if ( $wpe_max > 1 ) {
				echo '<nav class="wpe-posts-grid__pagination" aria-label="' . esc_attr__( 'Posts pagination', 'hi-wpessential' ) . '">';
				if ( in_array( $wpe_pag_type, array( 'prev_next', 'both' ), true ) && $wpe_page > 1 ) {
					echo '<a class="wpe-posts-grid__page-prev" href="' . esc_url( add_query_arg( $wpe_paged_key, $wpe_page - 1 ) ) . '">' . esc_html( (string) ( $a['previousPageText'] ?? __( 'Previous', 'hi-wpessential' ) ) ) . '</a>';
				}
				if ( in_array( $wpe_pag_type, array( 'numbers', 'both' ), true ) ) {
					for ( $wpe_i = 1, $wpe_limit = min( $wpe_max, 100 ); $wpe_i <= $wpe_limit; $wpe_i++ ) {
						$wpe_url = add_query_arg( $wpe_paged_key, $wpe_i );
						echo '<a href="' . esc_url( $wpe_url ) . '"' . ( $wpe_i === $wpe_page ? ' aria-current="page"' : '' ) . '>' . esc_html( (string) $wpe_i ) . '</a>';
					}
				}
				if ( in_array( $wpe_pag_type, array( 'prev_next', 'both' ), true ) && $wpe_page < $wpe_max ) {
					echo '<a class="wpe-posts-grid__page-next" href="' . esc_url( add_query_arg( $wpe_paged_key, $wpe_page + 1 ) ) . '">' . esc_html( (string) ( $a['nextPageText'] ?? __( 'Next', 'hi-wpessential' ) ) ) . '</a>';
				}
				echo '</nav>';
			}
		}
		?>
	<?php else : ?>
		<p class="wpe-posts-grid__empty"><?php echo esc_html( (string) $a['emptyText'] ); ?></p>
	<?php endif; ?>
</div>
<?php
wp_reset_postdata();
