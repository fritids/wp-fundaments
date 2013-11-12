<?php add_action('skt_login_messages', 'skt_login_form_messages');
function skt_login_form_messages() {
	$wp_error = isset($GLOBALS['skt_login_errors']) ? $GLOBALS['skt_login_errors'] : new WP_Error();
	
	if (!empty($wp_error) && $wp_error->get_error_code()) {
		$errors = '';
		$messages = '';
		
		foreach ($wp_error->get_error_codes() as $code) {
			$severity = $wp_error->get_error_data($code);
			foreach ($wp_error->get_error_messages($code) as $error) {
				if ($severity == 'message') {
					$messages .= ' ' . $error . "<br />\n";
				} else {
					$errors .= ' ' . $error . "<br />\n";
				}
			}
		}
		
		if (!empty($errors)) {
			echo '<p id="login_error">' . apply_filters('login_errors', $errors) . "</p>\n";
		}
		
		if (!empty($messages)) {
			echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
		}
	}
	
	if(isset($GLOBALS['skt_login_errors'])) {
		unset($GLOBALS['skt_login_errors']);
	}
}