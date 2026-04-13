<?php
defined('ABSPATH') || exit;

$customer_id = get_current_user_id();

if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __('Billing address', 'woocommerce'),
			'shipping' => __('Shipping address', 'woocommerce'),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __('Billing address', 'woocommerce'),
		),
		$customer_id
	);
}
?>

<div class="wc-address-wrapper">

	<p class="wc-address-desc">
		<?php echo apply_filters(
			'woocommerce_my_account_my_address_description',
			esc_html__('The following addresses will be used on the checkout page by default.', 'woocommerce')
		); ?>
	</p>

	<div class="wc-address-list">
		<?php foreach ($get_addresses as $name => $address_title) : ?>
			<?php $address = wc_get_account_formatted_address($name); ?>

			<div class="wc-address-card">
				<div class="wc-address-card__header">
					<h2 class="wc-address-card__title"><?php echo esc_html($address_title); ?></h2>

					<a
						href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>"
						class="wc-address-card__edit">
						<?php
						printf(
							$address ? esc_html__('Edit %s', 'woocommerce') : esc_html__('Add %s', 'woocommerce'),
							esc_html($address_title)
						);
						?>
					</a>
				</div>

				<div class="wc-address-card__body">
					<?php
					echo $address
						? wp_kses_post($address)
						: esc_html__('You have not set up this type of address yet.', 'woocommerce');

					do_action('woocommerce_my_account_after_my_address', $name);
					?>
				</div>
			</div>

		<?php endforeach; ?>
	</div>

</div>