<?php
/**
 * Title: Header actions (search, account, cart, CTA)
 * Slug: wpessential/header-actions
 * Inserter: no
 *
 * Account and mini-cart blocks are only added when WooCommerce is active.
 *
 * @package WPEssential
 */

?>
<!-- wp:group {"className":"wpe-header-actions","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group wpe-header-actions"><!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wpessential' ); ?>","showLabel":false,"buttonText":"<?php esc_attr_e( 'Search', 'wpessential' ); ?>","buttonPosition":"button-only","buttonUseIcon":true,"isSearchFieldHidden":true} /-->
<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","iconStyle":"line","iconClass":"wc-block-customer-account__account-icon"} /-->

<!-- wp:woocommerce/mini-cart /-->
<?php endif; ?>

<!-- wp:buttons {"className":"wpe-header-cta"} -->
<div class="wp-block-buttons wpe-header-cta"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact/"><?php esc_html_e( 'Get Started', 'wpessential' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
