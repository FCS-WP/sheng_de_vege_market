<?php
/**
 * My Account page.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;
?>

<div class="zippy-account">
	<?php do_action('woocommerce_account_navigation'); ?>

	<div class="woocommerce-MyAccount-content zippy-account__content">
		<?php do_action('woocommerce_account_content'); ?>
	</div>
</div>
