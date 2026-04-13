<?php

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if (! defined('ABSPATH')) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled('email_improvements');

/**
 * CHECK PRODUCT NEED NOTE
 */
$has_note = false;
$list_products_has_note = [764];

foreach ($order->get_items() as $item) {
	$product_id   = $item->get_product_id();

	if (in_array($product_id, $list_products_has_note, true)) {
		$has_note = true;
		break;
	}
}

/*
 * Email header
 */
do_action('woocommerce_email_header', $email_heading, $email);
?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>

<p>
	<?php
	if (! empty($order->get_billing_first_name())) {
		printf(
			esc_html__('Hi %s,', 'woocommerce'),
			esc_html($order->get_billing_first_name())
		);
	} else {
		esc_html_e('Hi,', 'woocommerce');
	}
	?>
</p>

<?php if ($email_improvements_enabled) : ?>
	<p><?php esc_html_e('We’ve received your order and will let you know when it’s on its way to you!', 'woocommerce'); ?></p>
	<p><?php esc_html_e('Here’s a reminder of what you’ve ordered:', 'woocommerce'); ?></p>
<?php else : ?>
	<p>
		<?php
		printf(
			esc_html__(
				'Just to let you know — we\'ve received your order #%s, and it is now being processed:',
				'woocommerce'
			),
			esc_html($order->get_order_number())
		);
		?>
	</p>
<?php endif; ?>

<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php
/*
 * Order details
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/*
 * Order meta
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
?>

<?php if ($has_note) : ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:20px;">
		<tr>
			<td>
				<h3>📩 How to Redeem Your Package</h3>
				<p>- To redeem your bath sessions, please present this email when you visit our store.</p>
				<p>- This email will serve as your redemption proof for the bath session package.</p>
			</td>
		</tr>
	</table>
<?php endif; ?>

<?php
/*
* Customer details
*/
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);



/**
 * Additional content from Woo settings
 */
if ($additional_content) {
	echo $email_improvements_enabled
		? '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="email-additional-content">'
		: '';

	echo wp_kses_post(wpautop(wptexturize($additional_content)));

	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/*
 * Footer
 */
do_action('woocommerce_email_footer', $email);
