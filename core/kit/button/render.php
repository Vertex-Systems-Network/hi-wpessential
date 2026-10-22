<?php
/**
 * Button render. $a = values, $id = element id.
 *
 * @package WPEssential\Core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_icons = array(
	'arrow'    => '<path d="M7 17 17 7M8 7h9v9"/>',
	'right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
	'download' => '<path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14"/>',
	'play'     => '<path d="M8 5v14l11-7z"/>',
);
$wpe_icon  = isset( $wpe_icons[ $a['icon'] ] ) ? '<svg class="wpe-button__icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $wpe_icons[ $a['icon'] ] . '</svg>' : '';
$wpe_rel   = array_filter( array( $a['newTab'] ? 'noopener' : '', $a['nofollow'] ? 'nofollow' : '' ) );
$wpe_label = '<span class="wpe-button__text">' . esc_html( $a['text'] ) . '</span>';
$wpe_inner = $a['iconFirst'] ? $wpe_icon . $wpe_label : $wpe_label . $wpe_icon;
$wpe_class = 'wpe-button__link is-' . sanitize_key( $a['variant'] ) . ' is-' . sanitize_key( $a['size'] ) . ' wpe-hover-' . sanitize_key( $a['hover'] );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-button">
	<a class="<?php echo esc_attr( $wpe_class ); ?>" href="<?php echo esc_url( $a['url'] ? $a['url'] : '#' ); ?>"<?php echo $a['newTab'] ? ' target="_blank"' : ''; ?><?php echo $wpe_rel ? ' rel="' . esc_attr( implode( ' ', $wpe_rel ) ) . '"' : ''; ?><?php echo $a['ariaLabel'] ? ' aria-label="' . esc_attr( $a['ariaLabel'] ) . '"' : ''; ?>><?php echo $wpe_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></a>
</div>
