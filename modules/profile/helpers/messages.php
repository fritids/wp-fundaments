<?php add_action('skt_profile_messages', 'skt_profile_form_messages');
function skt_profile_form_messages() {
	$wp_error = isset($GLOBALS['skt_profile_errors']) ? $GLOBALS['skt_profile_errors'] : new WP_Error();
	
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
			echo '<p id="profile_error">' . apply_filters('profile_errors', $errors) . "</p>\n";
		}
		
		if (!empty($messages)) {
			echo '<p class="message">' . apply_filters('profile_messages', $messages) . "</p>\n";
		}
	}
	
	if(isset($GLOBALS['skt_profile_errors'])) {
		unset($GLOBALS['skt_profile_errors']);
	}
}