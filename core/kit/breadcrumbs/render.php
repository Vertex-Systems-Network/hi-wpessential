<?php
/**
 * Accessible breadcrumbs and BreadcrumbList structured data.
 *
 * @package WPEssential\\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit::template().

$wpe_items = array();
$wpe_home_url = trim( (string) ( $a['homeUrl'] ?? '' ) );
$wpe_home_url = $wpe_home_url ? esc_url_raw( $wpe_home_url, array( 'http', 'https' ) ) : home_url( '/' );
$wpe_home_label = trim( (string) ( $a['homeLabel'] ?? '' ) );
if ( $wpe_home_label && $wpe_home_url ) {
	$wpe_items[] = array( 'label' => $wpe_home_label, 'url' => $wpe_home_url );
}
foreach ( array_slice( is_array( $a['items'] ?? null ) ? $a['items'] : array(), 0, 30 ) as $wpe_item ) {
	if ( ! is_array( $wpe_item ) ) {
		continue;
	}
	$wpe_label = sanitize_text_field( (string) ( $wpe_item['label'] ?? '' ) );
	$wpe_url = esc_url_raw( (string) ( $wpe_item['url'] ?? '' ), array( 'http', 'https' ) );
	if ( '' !== $wpe_label && '' !== $wpe_url ) {
		$wpe_items[] = array( 'label' => $wpe_label, 'url' => $wpe_url );
	}
}
$wpe_current = trim( (string) ( $a['currentLabel'] ?? '' ) );
if ( '' === $wpe_current ) {
	$wpe_current = is_singular() ? get_the_title() : '';
}
$wpe_current = sanitize_text_field( $wpe_current );
$wpe_show_current = ! empty( $a['showCurrent'] ) && '' !== $wpe_current;
$wpe_schema_items = $wpe_items;
if ( $wpe_show_current ) {
	$wpe_schema_items[] = array(
		'label' => $wpe_current,
		'url' => is_singular() ? get_permalink() : '',
	);
}
$wpe_schema = array();
foreach ( $wpe_schema_items as $wpe_index => $wpe_item ) {
	$wpe_entry = array(
		'@type' => 'ListItem',
		'position' => $wpe_index + 1,
		'name' => $wpe_item['label'],
	);
	if ( ! empty( $wpe_item['url'] ) ) {
		$wpe_entry['item'] = $wpe_item['url'];
	}
	$wpe_schema[] = $wpe_entry;
}
$wpe_separator = trim( (string) ( $a['separator'] ?? '/' ) );
$wpe_label = sanitize_text_field( (string) ( $a['label'] ?? __( 'Breadcrumb', 'hi-wpessential' ) ) );
?>
<nav id="<?php echo esc_attr( $id ); ?>" class="wpe-breadcrumbs" aria-label="<?php echo esc_attr( $wpe_label ); ?>">
	<ol class="wpe-breadcrumbs__list">
		<?php foreach ( $wpe_items as $wpe_index => $wpe_item ) : ?>
			<li class="wpe-breadcrumbs__item">
				<a href="<?php echo esc_url( $wpe_item['url'] ); ?>"><?php echo esc_html( $wpe_item['label'] ); ?></a>
				<?php if ( $wpe_index < count( $wpe_items ) - 1 || $wpe_show_current ) : ?><span class="wpe-breadcrumbs__separator" aria-hidden="true"><?php echo esc_html( $wpe_separator ); ?></span><?php endif; ?>
			</li>
		<?php endforeach; ?>
		<?php if ( $wpe_show_current ) : ?>
			<li class="wpe-breadcrumbs__item" aria-current="page"><span><?php echo esc_html( $wpe_current ); ?></span></li>
		<?php endif; ?>
	</ol>
</nav>
<?php if ( $wpe_schema ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $wpe_schema ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* prevents script termination. ?></script>
<?php endif; ?>