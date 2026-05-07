<?php
/**
 * Lost password form.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
?>

<section class="zippy-password zippy-password--lost" aria-labelledby="zippy-lost-password-title">
	<div class="zippy-password__wrap">
		<div class="zippy-password__header">
			<p class="zippy-password__eyebrow"><?php esc_html_e('Account help', 'woocommerce'); ?></p>
			<h2 id="zippy-lost-password-title" class="zippy-password__title"><?php esc_html_e('Reset your password', 'woocommerce'); ?></h2>
			<p class="zippy-password__subtitle">
				<?php echo esc_html(apply_filters('woocommerce_lost_password_message', __('Enter your username or email address and we will send you a secure password reset link.', 'woocommerce'))); ?>
			</p>
		</div>

		<form method="post" class="woocommerce-ResetPassword lost_reset_password zippy-password__form">
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide zippy-password__field">
				<label for="user_login">
					<?php esc_html_e('Username or email', 'woocommerce'); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span>
				</label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" placeholder="<?php esc_attr_e('Enter your username or email', 'woocommerce'); ?>" required aria-required="true" />
			</p>

			<?php do_action('woocommerce_lostpassword_form'); ?>

			<p class="woocommerce-form-row form-row zippy-password__actions">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="woocommerce-Button button zippy-password__submit" value="<?php esc_attr_e('Send reset link', 'woocommerce'); ?>">
					<?php esc_html_e('Send reset link', 'woocommerce'); ?>
				</button>
			</p>

			<p class="zippy-password__footer">
				<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
					<?php esc_html_e('Back to login', 'woocommerce'); ?>
				</a>
			</p>

			<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
		</form>
	</div>
</section>

<?php
do_action('woocommerce_after_lost_password_form');
