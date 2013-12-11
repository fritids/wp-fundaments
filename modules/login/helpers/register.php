<?php function skt_register_new_user($user_login, $user_email, $user_pass, $confirm_pass, $first_name, $last_name) {
	$errors = new WP_Error();
	$user_email = apply_filters('user_registration_email', $user_email);
	
	if(!defined('SKT_USERNAME_AUTH') || SKT_USERNAME_AUTH) {
		$sanitized_user_login = sanitize_user($user_login);
		if ($sanitized_user_login == '') {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
		} elseif (!validate_username($user_login)) {
			$errors->add('invalid_username',
				__('<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.')
			);
		
			$sanitized_user_login = '';
		} elseif (username_exists($sanitized_user_login)) {
			$errors->add('username_exists',
				__('<strong>ERROR</strong>: This username is already registered. Please choose another one.')
			);
		}
	}
	
	if ($user_email == '') {
		$errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.'));
	} elseif (!is_email($user_email)) {
		$errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
		$user_email = '';
	} elseif (email_exists($user_email)) {
		$errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));
	}
	
	if(defined('SKT_USERNAME_AUTH') && !SKT_USERNAME_AUTH) {
		$sanitized_user_login = str_replace(
			'@', '_',
			str_replace('.', '_', $user_email)
		);
	}
	
	if ($user_pass == '') {
		$errors->add('empty_pass', __('<strong>ERROR</strong>: Please type a password.'));
	} elseif (strlen($user_pass) < 7) {
		$errors->add('invalid_pass', __('<strong>ERROR</strong>: Please type a longer password (minimum 7 characters).'));
	} elseif ($user_pass != $confirm_pass) {
		$errors->add('pass_mismatch', __('<strong>ERROR</strong>: Please type the same password twie.'));
	}
	
	do_action('register_post', $sanitized_user_login, $user_email, $errors);
	$errors = apply_filters('registration_errors', $errors, $sanitized_user_login, $user_email);
	
	if ($errors->get_error_code()) {
		return $errors;
	}
	
	$args = array(
		'user_login' => $sanitized_user_login,
		'user_email' => $user_email,
		'user_pass' => $user_pass
	);
	
	$args['default_password_nag'] = true;
	$args['first_name'] = $first_name;
	$args['last_name'] = $last_name;
	$user_id = wp_insert_user($args);
	
	if (!$user_id) {
		$errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
		return $errors;
	}
	
	wp_new_user_notification($user_id, $user_pass);
	return $user_id;
}

function skt_register_form() {
	if (!empty($_POST) && !wp_verify_nonce($_POST['skt-fundaments-register'], basename(__file__))) {
		wp_die('Not a chance!');
	}
	
	if (!get_option('users_can_register')) {
		wp_redirect(
			get_bloginfo('wpurl') . '/wp-login.php?registration=disabled'
		);
		
		exit();
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		unset($_SESSION['security_code']);
		require_once(ABSPATH . WPINC . '/registration.php');
		
		$errors = skt_register_new_user(
			$_POST['user_login'],
			$_POST['user_email'],
			$_POST['user_pass'],
			$_POST['pass_confirm'],
			$_POST['first_name'],
			$_POST['last_name']
		);
		
		if (!is_wp_error($errors)) {
			wp_redirect('wp-login.php?checkemail=registered');
			exit();
		}
		
		$GLOBALS['skt_login_errors'] = $errors;
	}
	
	$path = get_template_directory(). '/wp-register.php';
	if(is_file($path)) {
		include($path);
	} else {
		$path = get_template_directory(). '/wp-login.php';
		if(is_file($path)) {
			include($path);
		}
	}
}

add_action('skt_login_form', 'skt_register_form_print');
function skt_register_form_print() {
	global $pagenow;
	if (!isset($_GET['action']) || $_GET['action'] != 'register') {
		return;
	} ?>
	
	<form class="loginform" name="registerform" id="registerform" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" method="post">
		<?php skt_open_signup_fieldset(
			apply_filters('skt_signup_fieldset1_title', _('About you'))
		);
		
		skt_signup_field('first_name',
			array(
				'value' => isset($_POST['first_name']) ? $_POST['first_name'] : null,
				'label' => apply_filters('skt_signup_field_label', __('First Name'), 'first_name'),
				'autocomplete' => 'off'
			)
		);
		
		skt_signup_field('last_name',
			array(
				'value' => isset($_POST['last_name']) ? $_POST['last_name'] : null,
				'label' => apply_filters('skt_signup_field_label', __('Last Name'), 'last_name'),
				'autocomplete' => 'off'
			)
		);
		
		if(!defined('SKT_USERNAME_AUTH') || SKT_USERNAME_AUTH) {
			skt_signup_field('user_login',
				array(
					'label' => apply_filters('skt_signup_field_label', __('Choose a Username'), 'user_login'),
					'value' => isset($_POST['user_login']) ? $_POST['user_login'] : null,
					'autocomplete' => 'off'
				)
			);
		}
		
		skt_signup_field('user_email',
			array(
				'type' => 'email',
				'label' => apply_filters('skt_signup_field_label', __('Email Address'), 'email'),
				'value' => isset($_POST['user_email']) ? $_POST['user_email'] : null,
				'autocomplete' => 'off'
			)
		);
		
		skt_signup_field('user_pass',
			array(
				'label' => apply_filters('skt_signup_field_label', __('Password'), 'user_pass'),
				'type' => 'password',
				'value' => isset($_POST['user_pass']) ? $_POST['user_pass'] : null,
				'autocomplete' => 'off'
			)
		);
		
		skt_signup_field('pass_confirm',
			array(
				'label' => apply_filters('skt_signup_field_label', __('Confirm Password'), 'pass_confirm'),
				'type' => 'password',
				'value' => isset($_POST['pass_confirm']) ? $_POST['pass_confirm'] : null,
				'autocomplete' => 'off'
			)
		);
		
		skt_close_signup_fieldset('');
		do_action('register_form'); ?>
		<p id="reg_passmail"><?php do_action('skt_register_form_footer') ?></p>
		<p class="submit">
			<?php wp_nonce_field(basename(__file__), 'skt-fundaments-register'); ?>
			<input tabindex="4" type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Sign up'); ?>" tabindex="100" />
		</p>
	</form>
<?php }