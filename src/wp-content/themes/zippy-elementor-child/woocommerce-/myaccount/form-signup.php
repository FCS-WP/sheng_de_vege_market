<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('zippy_normalize_phone_number')) {
	function zippy_normalize_phone_number($phone)
	{
		$phone = trim((string) $phone);
		$phone = preg_replace('/[^\d+]/', '', $phone);

		if (0 === strpos($phone, '+')) {
			return '+' . preg_replace('/\D/', '', substr($phone, 1));
		}

		return preg_replace('/\D/', '', $phone);
	}
}

if (!function_exists('zippy_find_user_by_phone')) {
	function zippy_find_user_by_phone($phone)
	{
		$phone = wc_clean($phone);
		$normalized_phone = zippy_normalize_phone_number($phone);

		if ('' === $normalized_phone) {
			return false;
		}

		$user_query = new WP_User_Query(array(
			'number'     => 1,
			'fields'     => 'ID',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'   => 'billing_phone_normalized',
					'value' => $normalized_phone,
				),
				array(
					'key'   => 'billing_phone',
					'value' => $phone,
				),
				array(
					'key'   => 'billing_phone',
					'value' => $normalized_phone,
				),
			),
		));

		$user_ids = $user_query->get_results();

		if (!empty($user_ids)) {
			return get_user_by('id', $user_ids[0]);
		}

		$legacy_users = get_users(array(
			'fields'     => array('ID'),
			'meta_key'   => 'billing_phone',
			'meta_value' => '',
			'meta_compare' => '!=',
		));

		foreach ($legacy_users as $legacy_user) {
			$legacy_phone = get_user_meta($legacy_user->ID, 'billing_phone', true);

			if ($normalized_phone === zippy_normalize_phone_number($legacy_phone)) {
				update_user_meta($legacy_user->ID, 'billing_phone_normalized', $normalized_phone);
				return get_user_by('id', $legacy_user->ID);
			}
		}

		return false;
	}
}

if (!function_exists('zippy_is_woocommerce_login_request')) {
	function zippy_is_woocommerce_login_request()
	{
		return (
			isset($_POST['woocommerce-login-nonce']) &&
			wp_verify_nonce(wp_unslash($_POST['woocommerce-login-nonce']), 'woocommerce-login')
		);
	}
}

if (!function_exists('zippy_authenticate_phone_login')) {
	function zippy_authenticate_phone_login($user, $username, $password)
	{
		if ($user instanceof WP_User || empty($username) || empty($password) || is_email($username)) {
			return $user;
		}

		$matched_user = zippy_find_user_by_phone($username);

		if (!$matched_user instanceof WP_User) {
			if (zippy_is_woocommerce_login_request()) {
				return new WP_Error(
					'invalid_phone_email',
					__('The telephone or email address is not registered.', 'woocommerce')
				);
			}

			return $user;
		}

		if (!wp_check_password($password, $matched_user->user_pass, $matched_user->ID)) {
			return new WP_Error(
				'incorrect_password',
				sprintf(
					/* translators: %s: Lost password URL. */
					__('The password you entered is incorrect. <a href="%s">Lost your password?</a>', 'woocommerce'),
					esc_url(wp_lostpassword_url())
				)
			);
		}

		return $matched_user;
	}
}

if (!function_exists('zippy_update_phone_email_login_error')) {
	function zippy_update_phone_email_login_error($user, $username, $password)
	{
		if (!is_wp_error($user) || !zippy_is_woocommerce_login_request()) {
			return $user;
		}

		$error_codes = $user->get_error_codes();
		$login_error_codes = array('invalid_username', 'invalid_email', 'invalid_phone_email');

		if (array_intersect($login_error_codes, $error_codes)) {
			return new WP_Error(
				'invalid_phone_email',
				__('The telephone or email address is not registered.', 'woocommerce')
			);
		}

		return $user;
	}
}

add_filter('authenticate', 'zippy_authenticate_phone_login', 19, 3);
add_filter('authenticate', 'zippy_update_phone_email_login_error', 999, 3);

if (!function_exists('zippy_get_signup_post_data')) {
	function zippy_get_signup_post_data()
	{
		return array(
			'phone' => isset($_POST['zippy_signup_phone']) ? wc_clean(wp_unslash($_POST['zippy_signup_phone'])) : '',
			'email' => isset($_POST['zippy_signup_email']) ? sanitize_email(wp_unslash($_POST['zippy_signup_email'])) : '',
			'date_of_birth' => isset($_POST['zippy_signup_date_of_birth']) ? wc_clean(wp_unslash($_POST['zippy_signup_date_of_birth'])) : '',
			'password' => isset($_POST['zippy_signup_password']) ? (string) wp_unslash($_POST['zippy_signup_password']) : '',
			'confirm_password' => isset($_POST['zippy_signup_confirm_password']) ? (string) wp_unslash($_POST['zippy_signup_confirm_password']) : '',
			'pdpa_agreement' => isset($_POST['zippy_signup_pdpa']) ? wc_clean(wp_unslash($_POST['zippy_signup_pdpa'])) : '',
		);
	}
}

if (!function_exists('zippy_create_signup_customer')) {
	function zippy_create_signup_customer($data)
	{
		$errors = new WP_Error();

		if (empty($data['phone'])) {
			$errors->add('phone_required', __('Please enter your phone number.', 'woocommerce'));
		}

		if (empty($data['email']) || !is_email($data['email'])) {
			$errors->add('email_invalid', __('Please enter a valid email address.', 'woocommerce'));
		}

		if (!empty($data['email']) && email_exists($data['email'])) {
			$errors->add('email_exists', __('An account is already registered with this email address.', 'woocommerce'));
		}

		if (!empty($data['phone']) && zippy_find_user_by_phone($data['phone'])) {
			$errors->add('phone_exists', __('An account is already registered with this phone number.', 'woocommerce'));
		}

		if (empty($data['date_of_birth'])) {
			$errors->add('date_of_birth_required', __('Please enter your date of birth.', 'woocommerce'));
		}

		if (empty($data['password'])) {
			$errors->add('password_required', __('Please enter a password.', 'woocommerce'));
		}

		if ($data['password'] !== $data['confirm_password']) {
			$errors->add('password_mismatch', __('Passwords do not match.', 'woocommerce'));
		}

		if ('yes' !== $data['pdpa_agreement']) {
			$errors->add('pdpa_required', __('Please agree to the PDPA terms before registering.', 'woocommerce'));
		}

		if ($errors->has_errors()) {
			return $errors;
		}

		$_POST['mpda_consent'] = 'yes';

		$customer_id = function_exists('wc_create_new_customer')
			? wc_create_new_customer($data['email'], '', $data['password'])
			: wp_create_user($data['email'], $data['password'], $data['email']);

		if (is_wp_error($customer_id)) {
			return $customer_id;
		}

		update_user_meta($customer_id, 'billing_phone', $data['phone']);
		update_user_meta($customer_id, 'billing_phone_normalized', zippy_normalize_phone_number($data['phone']));
		update_user_meta($customer_id, 'date_of_birth', $data['date_of_birth']);
		update_user_meta($customer_id, 'pdpa_agreement', 'yes');

		return array(
			'customer_id' => $customer_id,
			'message' => __('Registration successful. You will be redirected to the login page shortly.', 'woocommerce'),
			'redirect_url' => home_url('/my-account/'),
		);
	}
}

if (!function_exists('zippy_signup_ajax_register')) {
	function zippy_signup_ajax_register()
	{
		if (!isset($_POST['zippy_signup_nonce']) || !wp_verify_nonce(wp_unslash($_POST['zippy_signup_nonce']), 'zippy_signup_register')) {
			wp_send_json_error(array(
				'message' => __('Security check failed. Please refresh and try again.', 'woocommerce'),
			));
		}

		$result = zippy_create_signup_customer(zippy_get_signup_post_data());

		if (is_wp_error($result)) {
			wp_send_json_error(array(
				'message' => implode('<br>', $result->get_error_messages()),
			));
		}

		wp_send_json_success(array(
			'message' => $result['message'],
			'redirect_url' => $result['redirect_url'],
		));
	}
}

add_action('wp_ajax_nopriv_zippy_signup_register', 'zippy_signup_ajax_register');
add_action('wp_ajax_zippy_signup_register', 'zippy_signup_ajax_register');

if (!function_exists('zippy_render_signup_form_shortcode')) {
	function zippy_render_signup_form_shortcode()
	{
		$errors = array();
		$success_message = '';

		$posted_data = zippy_get_signup_post_data();
		$phone = $posted_data['phone'];
		$email = $posted_data['email'];
		$date_of_birth = $posted_data['date_of_birth'];

		if (
			isset($_POST['zippy_signup_action']) &&
			'zippy_signup_register' === wc_clean(wp_unslash($_POST['zippy_signup_action']))
		) {
			if (!isset($_POST['zippy_signup_nonce']) || !wp_verify_nonce(wp_unslash($_POST['zippy_signup_nonce']), 'zippy_signup_register')) {
				$errors[] = __('Security check failed. Please refresh and try again.', 'woocommerce');
			} else {
				$result = zippy_create_signup_customer($posted_data);

				if (is_wp_error($result)) {
					$errors = $result->get_error_messages();
				} else {
					$success_message = $result['message'];
					$phone = '';
					$email = '';
					$date_of_birth = '';
				}
			}
		}

		ob_start();
		?>
		<section class="zippy-signup" aria-labelledby="zippy-signup-title">
			<div class="zippy-signup__wrap">
				<div class="zippy-signup__header">
					<p class="zippy-signup__eyebrow"><?php esc_html_e('New account', 'woocommerce'); ?></p>
					<h2 id="zippy-signup-title" class="zippy-signup__title"><?php esc_html_e('Register', 'woocommerce'); ?></h2>
					<p class="zippy-signup__subtitle">
						<?php esc_html_e('Create your account to save your details and checkout faster next time.', 'woocommerce'); ?>
					</p>
				</div>

				<?php if (!empty($errors)) : ?>
					<div class="zippy-signup__notice zippy-signup__notice--error" role="alert">
						<?php foreach ($errors as $error) : ?>
							<p><?php echo esc_html($error); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($success_message)) : ?>
					<div class="zippy-signup__notice zippy-signup__notice--success" role="status">
						<p><?php echo esc_html($success_message); ?></p>
					</div>
				<?php endif; ?>

				<div class="zippy-signup__ajax-notice" data-zippy-signup-notice aria-live="polite"></div>

				<form class="zippy-signup__form" method="post" data-zippy-signup-form data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-ajax-action="zippy_signup_register">
					<p class="zippy-signup__field">
						<label for="zippy_signup_phone"><?php esc_html_e('Phone number', 'woocommerce'); ?></label>
						<input type="tel" id="zippy_signup_phone" name="zippy_signup_phone" value="<?php echo esc_attr($phone); ?>" autocomplete="tel" placeholder="<?php esc_attr_e('Enter your phone number', 'woocommerce'); ?>" required>
					</p>

					<p class="zippy-signup__field">
						<label for="zippy_signup_email"><?php esc_html_e('Email', 'woocommerce'); ?></label>
						<input type="email" id="zippy_signup_email" name="zippy_signup_email" value="<?php echo esc_attr($email); ?>" autocomplete="email" placeholder="<?php esc_attr_e('name@example.com', 'woocommerce'); ?>" required>
					</p>

					<p class="zippy-signup__field">
						<label for="zippy_signup_date_of_birth"><?php esc_html_e('Date of Birth', 'woocommerce'); ?></label>
						<input type="date" id="zippy_signup_date_of_birth" name="zippy_signup_date_of_birth" value="<?php echo esc_attr($date_of_birth); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>" required>
					</p>

					<p class="zippy-signup__field">
						<label for="zippy_signup_password"><?php esc_html_e('Password', 'woocommerce'); ?></label>
						<input type="password" id="zippy_signup_password" name="zippy_signup_password" autocomplete="new-password" placeholder="<?php esc_attr_e('Create a password', 'woocommerce'); ?>" required>
					</p>

					<p class="zippy-signup__field">
						<label for="zippy_signup_confirm_password"><?php esc_html_e('Retype password', 'woocommerce'); ?></label>
						<input type="password" id="zippy_signup_confirm_password" name="zippy_signup_confirm_password" autocomplete="new-password" placeholder="<?php esc_attr_e('Confirm your password', 'woocommerce'); ?>" required>
						<span class="zippy-signup__message" data-zippy-password-message aria-live="polite"></span>
					</p>

					<p class="zippy-signup__pdpa">
						<span class="zippy-signup__pdpa-copy">
							<?php esc_html_e('Sheng De Vege Market may collect, use and disclose your personal data, which you have provided in this form, for providing marketing material that you have agreed to receive, in accordance with the Personal Data Protection Act 2012 and our data protection policy.', 'woocommerce'); ?>
						</span>
						<label for="zippy_signup_pdpa">
							<input type="checkbox" id="zippy_signup_pdpa" name="zippy_signup_pdpa" value="yes" required>
							<input type="hidden" name="mpda_consent" value="yes">
							<span><?php esc_html_e('I have read and agree with the terms and conditions.', 'woocommerce'); ?></span>
						</label>
					</p>

					<?php wp_nonce_field('zippy_signup_register', 'zippy_signup_nonce'); ?>
					<input type="hidden" name="action" value="zippy_signup_register">
					<input type="hidden" name="zippy_signup_action" value="zippy_signup_register">

					<button class="zippy-signup__submit" type="submit" data-default-text="<?php esc_attr_e('Register', 'woocommerce'); ?>" data-loading-text="<?php esc_attr_e('Registering...', 'woocommerce'); ?>">
						<span class="zippy-signup__spinner" aria-hidden="true"></span>
						<span class="zippy-signup__submit-text"><?php esc_html_e('Register', 'woocommerce'); ?></span>
					</button>
				</form>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}

add_shortcode('zippy_signup_form', 'zippy_render_signup_form_shortcode');
