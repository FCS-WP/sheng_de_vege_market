<?php
/**
 * My Account Dashboard.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined('ABSPATH') || exit;

$display_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
$dashboard_links = array(
	array(
		'label' => __('Recent orders', 'woocommerce'),
		'text'  => __('Track purchases and view order history.', 'woocommerce'),
		'url'   => wc_get_endpoint_url('orders'),
	),
	array(
		'label' => __('Addresses', 'woocommerce'),
		'text'  => __('Manage billing and delivery details.', 'woocommerce'),
		'url'   => wc_get_endpoint_url('edit-address'),
	),
	array(
		'label' => __('Account details', 'woocommerce'),
		'text'  => __('Update your profile, email and password.', 'woocommerce'),
		'url'   => wc_get_endpoint_url('edit-account'),
	),
);
?>

<section class="zippy-account-dashboard" aria-labelledby="zippy-account-dashboard-title">
	<div class="zippy-account-dashboard__hero">
		<p class="zippy-account-dashboard__eyebrow"><?php esc_html_e('My account', 'woocommerce'); ?></p>
		<h2 id="zippy-account-dashboard-title" class="zippy-account-dashboard__title">
			<?php
			printf(
				/* translators: %s: user display name. */
				esc_html__('Hello %s', 'woocommerce'),
				esc_html($display_name)
			);
			?>
		</h2>
		<p class="zippy-account-dashboard__copy">
			<?php esc_html_e('Welcome back. You can review orders, update addresses, and manage your account details from here.', 'woocommerce'); ?>
		</p>
		<a class="zippy-account-dashboard__logout" href="<?php echo esc_url(wc_logout_url()); ?>">
			<?php esc_html_e('Log out', 'woocommerce'); ?>
		</a>
	</div>

	<div class="zippy-account-dashboard__grid">
		<?php foreach ($dashboard_links as $dashboard_link) : ?>
			<a class="zippy-account-dashboard__card" href="<?php echo esc_url($dashboard_link['url']); ?>">
				<span class="zippy-account-dashboard__card-title"><?php echo esc_html($dashboard_link['label']); ?></span>
				<span class="zippy-account-dashboard__card-text"><?php echo esc_html($dashboard_link['text']); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<?php
do_action('woocommerce_account_dashboard');
do_action('woocommerce_before_my_account');
do_action('woocommerce_after_my_account');
