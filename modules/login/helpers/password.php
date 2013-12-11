<?php function skt_password_form() {
	if (!empty($_POST) && !wp_verify_nonce($_POST['skt-fundaments-password'], basename(__file__))) {
		wp_die('Not a chance!');
	}
	
	$errors = new WP_Error();
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$errors = retrieve_password();
		if (!is_wp_error($errors)) {
			wp_redirect('wp-login.php?checkemail=confirm');
			exit();
		}
	}
	
	if ('invalidkey' == $_GET['error']) {
		$errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
	}
	
	do_action('lost_password');
	do_action('lostpassword_post');
	
	$GLOBALS['skt_login_errors'] = $errors;
	$path = get_template_directory(). '/wp-login.php';
	if(is_file($path)) {
		include($path);
	}
}

add_action('skt_login_form', 'skt_reset_form_print');
function skt_reset_form_print() {
	global $pagenow;
	if (!isset($_GET['action']) || $_GET['action'] != 'lostpassword') {
		return;
	} ?>
	
	<form class="loginform" name="lostpasswordform" id="lostpasswordform" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" method="post">
		<?php skt_open_signup_fieldset(
			apply_filters('skt_signup_fieldset1_title', 'Reset your password')
		);
		
		skt_signup_field('user_login',
			array(
				'label' => apply_filters('skt_signup_field_label',
					__((defined('SKT_USERNAME_AUTH') && SKT_USERNAME_AUTH ? 'Username or e' : 'E') . 'mail address'),
					'user_login'
				),
				'value' => isset($_POST['user_login']) ? $_POST['user_login'] : null
			)
		);
		
		do_action('lostpassword_form');
		skt_close_signup_fieldset(); ?>
		
		<p class="submit">
			<?php wp_nonce_field(basename(__file__), 'skt-fundaments-password'); ?>
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Get New Password'); ?>" tabindex="100" />
		</p>
	</form>
<?php }