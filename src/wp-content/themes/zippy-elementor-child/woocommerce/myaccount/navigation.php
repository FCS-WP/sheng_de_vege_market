<?php
/**
 * My Account navigation.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_navigation');
?>

<nav class="woocommerce-MyAccount-navigation zippy-account-nav" aria-label="<?php esc_attr_e('Account pages', 'woocommerce'); ?>">
	<ul class="zippy-account-nav__list">
		<?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
			<li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?> zippy-account-nav__item">
				<a class="zippy-account-nav__link" href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html($label); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>
