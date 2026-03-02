<?php
if (!defined('ABSPATH')) exit;

do_action('woocommerce_before_customer_login_form'); ?>

<div id="customer_login" class="myacc-wrapper">

	<!-- LOGIN FORM -->
	<div id="myacc-login" class="myacc-login-section">

		<h2 class="myacc-title"><?php esc_html_e('Login', 'woocommerce'); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login myacc-login-form" method="post">

			<?php do_action('woocommerce_login_form_start'); ?>

			<p class="woocommerce-form-row form-row form-row-wide">
				<label for="username">Email *</label>
				<input type="text" name="username" id="username" autocomplete="email" required />
			</p>

			<p class="woocommerce-form-row form-row form-row-wide">
				<label for="password">Password *</label>
				<input type="password" name="password" id="password" autocomplete="current-password" required />
			</p>

			<?php do_action('woocommerce_login_form'); ?>

			<p class="form-row myacc-login-actions">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
					<input class="woocommerce-form__input-checkbox" name="rememberme" type="checkbox" value="forever" />
					<span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
				</label>

				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

				<button type="submit" class="woocommerce-button button myacc-login-btn" name="login">
					<?php esc_html_e('Log in', 'woocommerce'); ?>
				</button>
			</p>

			<div class="more-options">
				<p class="woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
						<?php esc_html_e('Lost your password?', 'woocommerce'); ?>
					</a>
				</p>

				<!-- CREATE NEW ACCOUNT BUTTON (TOP) -->
				<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
					<div class="myacc-toggle-wrapper">
						<a href="#" id="toggle-register" class="myacc-toggle-btn">
							<?php esc_html_e('Create new account', 'woocommerce'); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
			<?php do_action('woocommerce_login_form_end'); ?>

		</form>

	</div>

	<!-- REGISTER FORM -->
	<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
		<div id="myacc-register" class="myacc-register-section" style="display:none;">

			<h2 class="myacc-title"><?php esc_html_e('Register', 'woocommerce'); ?></h2>

			<form method="post" class="woocommerce-form woocommerce-form-register register myacc-register-form">

				<?php do_action('woocommerce_register_form_start'); ?>

				<p class="woocommerce-form-row form-row form-row-wide">
					<label for="reg_email">Email *</label>
					<input type="email" name="email" id="reg_email" autocomplete="email" required />
				</p>

				<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
					<p class="woocommerce-form-row form-row form-row-wide">
						<label for="reg_password">Password *</label>
						<input type="password" name="password" id="reg_password" autocomplete="new-password" required />
					</p>
				<?php endif; ?>

				<?php do_action('woocommerce_register_form'); ?>

				<p class="woocommerce-form-row form-row">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="woocommerce-button button myacc-register-btn" name="register">
						<?php esc_html_e('Register', 'woocommerce'); ?>
					</button>
				</p>

				<?php do_action('woocommerce_register_form_end'); ?>

			</form>

		</div>
	<?php endif; ?>

</div>

<script>
	document.getElementById("toggle-register")?.addEventListener("click", function(e) {
		e.preventDefault();
		document.getElementById("myacc-login").style.display = "none";
		document.getElementById("myacc-register").style.display = "block";
		this.style.display = "none"; // ẩn nút Create account
	});
</script>

<?php do_action('woocommerce_after_customer_login_form'); ?>