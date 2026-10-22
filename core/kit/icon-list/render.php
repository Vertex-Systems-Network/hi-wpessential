<?php
/**
 * Icon list render. $a = values, $id = element id.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$items = (array) $a['items'];
if ( ! $items ) {
	return;
}
$tag     = in_array( $a['tag'], array( 'ul', 'ol', 'div' ), true ) ? $a['tag'] : 'ul';
$item    = 'div' === $tag ? 'div' : 'li';
$marker  = in_array( $a['marker'], array( 'icon', 'number', 'none' ), true ) ? $a['marker'] : 'icon';
$classes = array(
	'wpe-icon-list',
	'is-' . sanitize_key( $a['layout'] ),
	'is-shape-' . sanitize_key( $a['iconShape'] ),
	'is-hover-' . sanitize_key( $a['hover'] ),
);
if ( $a['divider'] ) {
	$classes[] = 'has-divider';
}
?>
<<?php echo esc_html( $tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php
	foreach ( $items as $i => $row ) :
		$row  = (array) $row;
		$text = (string) ( $row['text'] ?? '' );
		$desc = trim( (string) ( $row['description'] ?? '' ) );
		$link = (string) ( $row['link'] ?? '' );
		$tab  = ! empty( $row['newTab'] ) ? ' target="_blank" rel="noopener"' : '';
		if ( 'number' === $marker ) {
			$icon = '<span class="wpe-icon-list__icon is-number" aria-hidden="true">' . ( (int) $i + 1 ) . '</span>';
		} elseif ( 'icon' === $marker ) {
			$svg  = \WPEssential\Core\Kit::icon_html( ! empty( $row['icon']['value'] ) ? $row['icon'] : $a['icon'], 'wpe-icon-list__svg' );
			$icon = $svg ? '<span class="wpe-icon-list__icon" aria-hidden="true">' . $svg . '</span>' : '';
		} else {
			$icon = '';
		}
		$label = '<span class="wpe-icon-list__text">' . wp_kses_post( $text ) . '</span>';
		if ( $link && 'text' === $a['linkArea'] ) {
			$label = '<a class="wpe-icon-list__text" href="' . esc_url( $link ) . '"' . $tab . '>' . wp_kses_post( $text ) . '</a>';
		}
		$body  = '<span class="wpe-icon-list__body">' . $label . ( '' !== $desc ? '<span class="wpe-icon-list__desc">' . wp_kses_post( $desc ) . '</span>' : '' ) . '</span>';
		$inner = $icon . $body;
		if ( $link && 'text' !== $a['linkArea'] ) {
			$inner = '<a class="wpe-icon-list__link" href="' . esc_url( $link ) . '"' . $tab . '>' . $inner . '</a>';
		}
		?>
		<<?php echo esc_html( $item ); ?> class="wpe-icon-list__item"><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></<?php echo esc_html( $item ); ?>>
	<?php endforeach; ?>
</<?php echo esc_html( $tag ); ?>>
