<?php function skt_profile_init() {
	if (!empty($_POST) && !wp_verify_nonce($_POST['skt-fundaments-profile'], basename(__file__))) {
		wp_die('Not a chance!');
	}
	
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$path = get_template_directory(). '/wp-profile.php';
	
	if(is_file($path)) {
		if (!$current_user->has_cap('edit_posts')) {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				do_action('personal_options_update', $user_id);
				$errors = edit_user($user_id);
				if (!is_wp_error($errors)) {
					do_action('skt_user_edited', $user_id);
					wp_redirect(
						add_query_arg('updated', true,
							get_edit_user_link($user_id)
						)
					);
					
					exit();
				}
				
				$GLOBALS['skt_profile_errors'] = $errors;
			}
			
			wp_reset_vars(
				array('action', 'redirect', 'profile', 'user_id', 'wp_http_referer')
			);
			
			$wp_http_referer = remove_query_arg(
				array('update', 'delete_count'),
				stripslashes($wp_http_referer)
			);
			
			$user_id = (int)$user_id;
			$profileuser = get_user_to_edit($user_id);
			if (!current_user_can('edit_user', $user_id)) {
				wp_die(__('You do not have permission to edit this user.'));
			}
			
			include($path);
			die();
		}
	}
}

add_action('skt_profile_form', 'skt_profile_form_print');
function skt_profile_form_print() {
	if ($_GET['updated'] == true) {
		echo '<p class="message">Your profile has been updated.</p>';
	}
	
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$profileuser = get_user_to_edit($user_id); ?>
	
	<form name="profile" id="your-profile" action="" method="post">
		<div style="display: none;">
			<?php wp_nonce_field('update-user_' . $user_id) ?>
			<?php if ($wp_http_referer) : ?>
				<input type="hidden" name="wp_http_referer" value="<?php echo clean_url($wp_http_referer); ?>" />
			<?php endif; ?>
			<input type="hidden" name="from" value="profile" />
			<input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
		</div>
		
		<?php do_action('skt_open_profile_fieldset', 'About You');
		
		if(!defined('SKT_USERNAME_AUTH') || SKT_USERNAME_AUTH) {
			skt_profile_field('user_login',
				array(
					'label' => 'Username',
					'value' => isset($_POST['user_login']) ? $_POST['user_login'] : $profileuser->user_login,
					'autocomplete' => 'off',
					'disabled' => 'disabled',
					'readonly' => 'readonly'
				)
			);
		} else { ?>
			<input name="user_login" type="hidden" value="<?php echo $profileuser->user_login; ?>" />
		<?php }
		
		skt_profile_field('first_name',
			array(
				'label' => 'First Name',
				'value' => isset($_POST['first_name']) ? $_POST['first_name'] : $profileuser->first_name,
				'autocomplete' => 'off'
			)
		);
		
		skt_profile_field('last_name',
			array(
				'label' => 'Last Name',
				'value' => isset($_POST['last_name']) ? $_POST['last_name'] : $profileuser->last_name,
				'autocomplete' => 'off'
			)
		);
		
		skt_profile_field('email',
			array(
				'label' => 'Email Address',
				'type' => 'email',
				'value' => isset($_POST['email']) ? $_POST['email'] : $profileuser->user_email,
				'autocomplete' => 'off'
			)
		);
		
		do_action('skt_close_profile_fieldset');
		
		if (apply_filters('show_password_fields', true)) {
			do_action('skt_open_profile_fieldset', 'Your Password');
			skt_profile_field('pass1',
				array(
					'label' => 'Change Password',
					'type' => 'password',
					'value' => isset($_POST['pass1']) ? $_POST['pass1'] : '',
					'autocomplete' => 'off'
				)
			);
			
			skt_profile_field('pass2',
				array(
					'label' => 'Confirm New Password',
					'type' => 'password',
					'value' => isset($_POST['pass2']) ? $_POST['pass2'] : '',
					'autocomplete' => 'off'
				)
			);
			
			do_action('skt_close_profile_fieldset');
		}
		
		do_action('profile_personal_options');
		do_action('show_user_profile'); ?>
		
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
			<input type="submit" id="cycsubmit" value="<?php _e('Update Profile'); ?>" name="submit" />
			<?php wp_nonce_field(basename(__file__), 'skt-fundaments-profile'); ?>
		 </p>
	</form>
<?php }