<?php
/**
 * Title: Values — four principles
 * Slug: wpessential/values-grid
 * Categories: wpessential-sections
 * Viewport Width: 1440
 *
 * @package WPEssential
 */

$wpessential_icons  = array(
	'down' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16"/></svg></span>',
	'lines' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg></span>',
	'cart' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12zM6 6L5 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg></span>',
	'bolt' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L4 14h7l-1 8 9-12h-7z"/></svg></span>',
	'lock' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg></span>',
	'clock' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></span>',
	'check' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg></span>',
	'layers' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/></svg></span>',
	'user' => '<span class="wpe-icon-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg></span>',
);
$wpessential_values = array(
	array( 'bolt', __( 'Performance first', 'hi-wpessential' ), __( 'Every demo has a performance budget and is tested on a mid-range phone.', 'hi-wpessential' ) ),
	array( 'check', __( 'Accessible by default', 'hi-wpessential' ), __( 'WCAG 2.1 AA colors, focus states and semantic markup out of the box.', 'hi-wpessential' ) ),
	array( 'layers', __( 'No lock-in', 'hi-wpessential' ), __( 'Content lives in core blocks or Elementor — switch themes any time.', 'hi-wpessential' ) ),
	array( 'user', __( 'Human support', 'hi-wpessential' ), __( 'Real developers answer your tickets, usually within a day.', 'hi-wpessential' ) ),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"wpe-section","backgroundColor":"surface-alt","layout":{"type":"constrained","contentSize":"1200px"}} -->
<section class="wp-block-group alignfull wpe-section has-surface-alt-background-color has-background"><!-- wp:group {"className":"wpe-center","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group wpe-center"><!-- wp:paragraph {"className":"is-style-wpe-overline"} -->
<p class="is-style-wpe-overline"><?php esc_html_e( 'What we value', 'hi-wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Principles behind every release', 'hi-wpessential' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bx-values","layout":{"type":"grid","columnCount":4}} -->
<div class="wp-block-group bx-values"><?php foreach ( $wpessential_values as $wpessential_value ) : ?><!-- wp:group {"className":"is-style-wpe-card wpe-feature-card","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-wpe-card wpe-feature-card"><!-- wp:html -->
<?php echo $wpessential_icons[ $wpessential_value[0] ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
<!-- /wp:html -->

<!-- wp:heading {"level":3,"fontSize":"h5"} -->
<h3 class="wp-block-heading has-h-5-font-size"><?php echo esc_html( $wpessential_value[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text-muted"} -->
<p class="has-text-muted-color has-text-color"><?php echo esc_html( $wpessential_value[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
