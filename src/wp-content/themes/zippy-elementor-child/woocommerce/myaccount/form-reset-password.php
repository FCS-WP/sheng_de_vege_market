<?php
/**
 * Lost password reset form.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_reset_password_form');
?>

<section class="zippy-password zippy-password--reset" aria-labelledby="zippy-reset-password-title">
	<div class="zippy-password__wrap">
		<div class="zippy-password__header">
			<p class="zippy-password__eyebrow"><?php esc_html_e('Password reset', 'woocommerce'); ?></p>
			<h2 id="zippy-reset-password-title" class="zippy-password__title"><?php esc_html_e('Create a new password', 'woocommerce'); ?></h2>
			<p class="zippy-password__subtitle">
				<?php echo esc_html(apply_filters('woocommerce_reset_password_message', __('Enter your new password below.', 'woocommerce'))); ?>
			</p>
		</div>

		<form method="post" class="woocommerce-ResetPassword lost_reset_password zippy-password__form zippy-password__form--two-fields">
			<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first zippy-password__field">
				<label for="password_1">
					<?php esc_html_e('New password', 'woocommerce'); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span>
				</label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" placeholder="<?php esc_attr_e('Create a password', 'woocommerce'); ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last zippy-password__field">
				<label for="password_2">
					<?php esc_html_e('Re-enter new password', 'woocommerce'); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span>
				</label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" placeholder="<?php esc_attr_e('Confirm your password', 'woocommerce'); ?>" required aria-required="true" />
			</p>

			<input type="hidden" name="reset_key" value="<?php echo esc_attr($args['key']); ?>" />
			<input type="hidden" name="reset_login" value="<?php echo esc_attr($args['login']); ?>" />

			<?php do_action('woocommerce_resetpassword_form'); ?>

			<p class="woocommerce-form-row form-row zippy-password__actions">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="woocommerce-Button button zippy-password__submit" value="<?php esc_attr_e('Save password', 'woocommerce'); ?>">
					<?php esc_html_e('Save password', 'woocommerce'); ?>
				</button>
			</p>

			<?php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); ?>
		</form>
	</div>
</section>

<?php
do_action('woocommerce_after_reset_password_form');
