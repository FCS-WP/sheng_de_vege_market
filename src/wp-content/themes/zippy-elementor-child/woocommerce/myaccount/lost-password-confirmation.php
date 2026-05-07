<?php
/**
 * Lost password confirmation text.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined('ABSPATH') || exit;
?>

<section class="zippy-password zippy-password--confirmation" aria-labelledby="zippy-lost-password-confirmation-title">
	<div class="zippy-password__wrap">
		<div class="zippy-password__header">
			<p class="zippy-password__eyebrow"><?php esc_html_e('Check your inbox', 'woocommerce'); ?></p>
			<h2 id="zippy-lost-password-confirmation-title" class="zippy-password__title">
				<?php esc_html_e('Password reset email sent', 'woocommerce'); ?>
			</h2>
			<p class="zippy-password__subtitle">
				<?php echo esc_html(apply_filters('woocommerce_lost_password_confirmation_message', __('A password reset email has been sent to the email address on file for your account. It may take several minutes to arrive, so please wait at least 10 minutes before trying again.', 'woocommerce'))); ?>
			</p>
		</div>

		<?php do_action('woocommerce_before_lost_password_confirmation_message'); ?>

		<p class="zippy-password__footer zippy-password__footer--confirmation">
			<a class="zippy-password__submit" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
				<?php esc_html_e('Back to login', 'woocommerce'); ?>
			</a>
		</p>

		<?php do_action('woocommerce_after_lost_password_confirmation_message'); ?>
	</div>
</section>
