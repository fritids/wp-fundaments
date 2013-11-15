<?php function skt_login_form() {
	$redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : admin_url();
	
	if (is_ssl() && force_ssl_login() && !force_ssl_admin() && (strpos($redirect_to, 'https') != 0) && (strpos($redirect_to, 'http') != 0)) {
		$secure_cookie = false;
	} else {
		$secure_cookie = '';
	}
	
	$user = wp_signon('', $secure_cookie);
	$redirect_to = apply_filters('login_redirect',
		$redirect_to,
		isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '',
		$user
	);
	
	if (!is_wp_error($user)) {
		if (!$user->has_cap('edit_posts') && (empty($redirect_to) || $redirect_to == 'wp-admin/')) {
			$redirect_to = admin_url('profile.php');
		}
		
		wp_safe_redirect($redirect_to);
		exit();
	}
	
	$errors = $user;
	if (!empty($_GET['loggedout'])) {
		$errors = new WP_Error();
	}
	
	if (isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE])) {
		$errors->add('test_cookie', __("Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));		
	}
	
	if (isset($_GET['loggedout']) && $_GET['loggedout']) {
		$errors->add('loggedout', __('You are now logged out.'), 'message');
	} elseif (isset($_GET['registration']) && $_GET['registration'] == 'disabled') {
		$errors->add('registerdisabled', __('User registration is currently not allowed.'));
	} elseif (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') {
		$errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
	} elseif (isset($_GET['checkemail']) && $_GET['checkemail'] == 'newpass') {
		$errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
	} elseif (isset($_GET['checkemail']) && $_GET['checkemail'] == 'registered') {
		$errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');
	}
	
	$GLOBALS['skt_login_errors'] = $errors;
	
	$path = get_template_directory(). '/wp-login.php';
	if(is_file($path)) {
		include($path);
	}
}

add_action('skt_login_form', 'skt_login_form_print');
function skt_login_form_print() {
	global $pagenow;
	if (isset($_GET['action']) && $_GET['action'] != 'login') {
		return;
	} ?>
	
	<form class="loginform" action="<?php bloginfo('wpurl'); ?>/wp-login.php" method="post">
		<?php skt_open_signup_fieldset(
			apply_filters('skt_signup_firldset1_title', _('About you'))
		);
		
		skt_signup_field('log',
			array(
				'label' => 'Username',
				'value' => isset($_POST['log']) ? $_POST['log'] : null
			)
		);
		
		skt_signup_field('pwd',
			array(
				'label' => 'Password',
				'type' => 'password'
			)
		);
		
		skt_close_signup_fieldset(); ?>
		
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Login'); ?>" />
			<input type="hidden" name="testcookie" value="1" />
		</p>
	</form>
<?php }