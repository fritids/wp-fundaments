<?php if(!function_exists('wp_new_user_notification')) {
	function wp_new_user_notification($user_id, $plaintext_pass = '') {
		if(!defined('SKT_EMAIL_NEW_USERS') || SKT_EMAIL_NEW_USERS) {
			$user = get_userdata($user_id);
			$admin_email = get_option('admin_email');
			
			$site_name = wp_specialchars_decode(
				get_option('blogname'), ENT_QUOTES
			);
			
			$message = sprintf(__('New user registration on your site %s:'), $site_name) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
			$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";
			
			@wp_mail(
				$admin_email,
				sprintf(__('[%s] New User Registration'), $site_name),
				$message
			);
			
			$context = array(
				'site_name' => $site_name,
				'admin_email' => $admin_email,
				'username' => $user->user_login,
				'password' => $plaintext_pass,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name
			);
			
			require_once(ABSPATH . '/wp-admin/includes/user.php');
			if(skt_render_mail($user->user_email, 'welcome_user_notification', $context)) {
				return false;
			}
		}
		
		return true;
	}
}