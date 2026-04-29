<?php
/**
 * Checkout Review Order Table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table zippy-checkout-review-order">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
			<th class="product-total"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action('woocommerce_review_order_before_cart_contents');

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
			$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

			if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
		?>
				<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
					<td class="product-name">
						<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
						<strong class="product-quantity">&times;&nbsp;<?php echo esc_html($cart_item['quantity']); ?></strong>
						<?php echo wc_get_formatted_cart_item_data($cart_item); ?>
						<?php
						if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
							echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
						}
						?>
					</td>
					<td class="product-total">
						<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key)); ?>
					</td>
				</tr>
		<?php
			}
		}

		do_action('woocommerce_review_order_after_cart_contents');
		?>
	</tbody>
	<tfoot>
		<tr class="cart-subtotal">
			<th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
				<th><?php wc_cart_totals_coupon_label($coupon); ?></th>
				<td><?php wc_cart_totals_coupon_html($coupon); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
			<?php do_action('woocommerce_review_order_before_shipping'); ?>
			<tr class="woocommerce-shipping-totals shipping zippy-review-shipping-heading">
				<th colspan="2"><?php esc_html_e('Shipping', 'woocommerce'); ?></th>
			</tr>
			<tr class="woocommerce-shipping-totals shipping zippy-review-shipping-methods">
				<td colspan="2">
					<?php
					$packages = WC()->shipping()->get_packages();
					$chosen_methods = WC()->session ? WC()->session->get('chosen_shipping_methods') : array();

					foreach ($packages as $package_index => $package) :
						$available_methods = isset($package['rates']) ? $package['rates'] : array();
						$chosen_method = isset($chosen_methods[$package_index]) ? $chosen_methods[$package_index] : '';
						$has_self_pickup = false;

						foreach ($available_methods as $available_method) {
							if ('local_pickup' === $available_method->method_id || false !== strpos(strtolower((string) $available_method->label), 'pick')) {
								$has_self_pickup = true;
								break;
							}
						}

						if (!$has_self_pickup && function_exists('zippy_build_self_pickup_rate')) {
							$self_pickup_rate = zippy_build_self_pickup_rate();

							if ($self_pickup_rate) {
								$available_methods[$self_pickup_rate->id] = $self_pickup_rate;
							}
						}

						if ((!$chosen_method || !isset($available_methods[$chosen_method])) && !empty($available_methods)) {
							$first_method = reset($available_methods);
							$chosen_method = $first_method ? $first_method->id : '';
						}
					?>
						<?php if (!empty($available_methods)) : ?>
							<ul id="shipping_method" class="woocommerce-shipping-methods">
								<?php foreach ($available_methods as $method) : ?>
									<?php $input_id = 'shipping_method_' . $package_index . '_' . sanitize_title($method->id); ?>
									<li>
										<input type="radio" name="shipping_method[<?php echo esc_attr($package_index); ?>]" data-index="<?php echo esc_attr($package_index); ?>" id="<?php echo esc_attr($input_id); ?>" value="<?php echo esc_attr($method->id); ?>" class="shipping_method" <?php checked($method->id, $chosen_method); ?>>
										<label for="<?php echo esc_attr($input_id); ?>"><?php echo wp_kses_post(wc_cart_totals_shipping_method_label($method)); ?></label>
										<?php do_action('woocommerce_after_shipping_rate', $method, $package_index); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<?php wc_cart_no_shipping_available_html(); ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</td>
			</tr>
			<?php do_action('woocommerce_review_order_after_shipping'); ?>
		<?php endif; ?>

		<?php foreach (WC()->cart->get_fees() as $fee) : ?>
			<tr class="fee">
				<th><?php echo esc_html($fee->name); ?></th>
				<td><?php wc_cart_totals_fee_html($fee); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) : ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
						<th><?php echo esc_html($tax->label); ?></th>
						<td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_review_order_before_order_total'); ?>

		<tr class="order-total">
			<th><?php esc_html_e('Total', 'woocommerce'); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action('woocommerce_review_order_after_order_total'); ?>
	</tfoot>
</table>
