<?php
/**
 * Title: Footer — Premium (dark, newsletter band)
 * Slug: wpessential/footer-premium
 * Categories: wpessential-footers
 * Block Types: core/template-part/footer
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"tagName":"footer","className":"wpe-footer wpe-section--md","layout":{"type":"constrained","contentSize":"1200px"}} -->
<footer class="wp-block-group wpe-footer wpe-section--md"><!-- wp:group {"className":"wpe-footer__newsletter","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group wpe-footer__newsletter"><!-- wp:columns {"verticalAlignment":"center","className":"wpe-section--sm"} -->
<div class="wp-block-columns are-vertically-aligned-center wpe-section--sm"><!-- wp:column {"verticalAlignment":"center","width":"66%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66%"><!-- wp:heading {"level":3,"textColor":"on-ink"} -->
<h3 class="wp-block-heading has-on-ink-color has-text-color"><?php esc_html_e( 'Get design tips & new demos', 'wpessential' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"on-ink"} -->
<p class="has-on-ink-color has-text-color"><?php esc_html_e( 'One email a month. No spam, unsubscribe anytime.', 'wpessential' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"34%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:34%"><!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-wpe-light"} -->
<div class="wp-block-button is-style-wpe-light"><a class="wp-block-button__link wp-element-button" href="/newsletter/"><?php esc_html_e( 'Subscribe', 'wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"wpe-footer__cols wpe-section--sm"} -->
<div class="wp-block-columns wpe-footer__cols wpe-section--sm"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:site-title /-->

<!-- wp:site-tagline /-->

<!-- wp:social-links {"className":"is-style-default"} -->
<ul class="wp-block-social-links is-style-default"><!-- wp:social-link {"url":"https://facebook.com/","service":"facebook"} /-->

<!-- wp:social-link {"url":"https://instagram.com/","service":"instagram"} /-->

<!-- wp:social-link {"url":"https://x.com/","service":"x"} /-->

<!-- wp:social-link {"url":"https://youtube.com/","service":"youtube"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Company', 'wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"},"fontSize":"small"} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'About us', 'wpessential' ); ?>","url":"/about/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Services', 'wpessential' ); ?>","url":"/services/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Pricing', 'wpessential' ); ?>","url":"/pricing/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Contact', 'wpessential' ); ?>","url":"/contact/"} /-->
<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Shop', 'wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"},"fontSize":"small"} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'All products', 'wpessential' ); ?>","url":"/shop/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'My account', 'wpessential' ); ?>","url":"/my-account/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Cart', 'wpessential' ); ?>","url":"/cart/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Track order', 'wpessential' ); ?>","url":"/track-order/"} /-->
<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Resources', 'wpessential' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"},"fontSize":"small"} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Blog', 'wpessential' ); ?>","url":"/blog/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'FAQ', 'wpessential' ); ?>","url":"/faq/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Documentation', 'wpessential' ); ?>","url":"https://wpessential.org/docs/"} /-->
<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Support', 'wpessential' ); ?>","url":"https://wpessential.org/support/"} /-->
<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"className":"wpe-footer__bottom","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"fontSize":"small"} -->
<div class="wp-block-group wpe-footer__bottom has-small-font-size"><!-- wp:paragraph -->
<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'wpessential' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="/privacy-policy/"><?php esc_html_e( 'Privacy', 'wpessential' ); ?></a>   ·   <a href="/terms/"><?php esc_html_e( 'Terms', 'wpessential' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></footer>
<!-- /wp:group -->
