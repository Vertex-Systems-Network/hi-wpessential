<?php
/**
 * Image render. $a = values, $id = element id.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_size = in_array( $a['size'], array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ? $a['size'] : 'large';
$wpe_load = in_array( $a['loading'], array( 'lazy', 'eager', 'priority' ), true ) ? $a['loading'] : 'lazy';

/**
 * One <img>: srcset from the media library when there is an id, else the plain url.
 */
$wpe_img = static function ( $img, $css_class ) use ( $a, $wpe_size, $wpe_load ) {
	$img = is_array( $img ) ? $img : array();
	$alt = $a['decorative'] ? '' : ( '' !== trim( (string) $a['alt'] ) ? $a['alt'] : ( ! empty( $img['id'] ) ? (string) get_post_meta( (int) $img['id'], '_wp_attachment_image_alt', true ) : '' ) );
	$att = array(
		'class'    => $css_class,
		'alt'      => $alt,
		'loading'  => 'lazy' === $wpe_load ? 'lazy' : 'eager',
		'decoding' => 'priority' === $wpe_load ? 'sync' : 'async',
	);
	if ( 'priority' === $wpe_load ) {
		$att['fetchpriority'] = 'high';
	}
	if ( ! empty( $img['id'] ) && wp_attachment_is_image( (int) $img['id'] ) ) {
		return wp_get_attachment_image( (int) $img['id'], $wpe_size, false, $att );
	}
	if ( empty( $img['url'] ) ) {
		return '';
	}
	$out = '<img src="' . esc_url( $img['url'] ) . '"';
	foreach ( $att as $k => $v ) {
		$out .= ' ' . $k . '="' . esc_attr( $v ) . '"';
	}
	return $out . '>';
};

$wpe_main = $wpe_img( $a['image'], 'wpe-image__img' );
$wpe_dark = $wpe_img( $a['darkImage'], 'wpe-image__img is-dark' );
if ( '' === $wpe_main ) {
	// Nothing chosen: empty in the front end, a hint only in the editors.
	if ( ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && ! ( did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) {
		return;
	}
	$wpe_main = '<span class="wpe-image__empty">' . esc_html__( 'Choose an image', 'hi-wpessential' ) . '</span>';
}
$wpe_media = $wpe_main . $wpe_dark;
if ( $a['link'] ) {
	$wpe_media = '<a class="wpe-image__link" href="' . esc_url( $a['link'] ) . '"' . ( $a['newTab'] ? ' target="_blank" rel="noopener"' : '' ) . '>' . $wpe_media . '</a>';
}
$wpe_class = array( 'wpe-image', 'wpe-image--' . sanitize_key( $a['hover'] ) );
if ( $wpe_dark ) {
	$wpe_class[] = 'has-dark';
}
$wpe_caption = trim( (string) $a['caption'] );
$wpe_tag     = '' !== $wpe_caption ? 'figure' : 'div';
?>
<<?php echo esc_html( $wpe_tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>"<?php echo $a['decorative'] ? ' aria-hidden="true"' : ''; ?>>
	<div class="wpe-image__media"><?php echo $wpe_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image / escaped above. ?></div>
	<?php if ( '' !== $wpe_caption ) : ?>
		<figcaption class="wpe-image__caption"><?php echo wp_kses_post( $wpe_caption ); ?></figcaption>
	<?php endif; ?>
</<?php echo esc_html( $wpe_tag ); ?>>
