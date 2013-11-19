<?php /**
 * Shortcut functions to inclusion of third-party PHP files
 *
 * @package vendorwp-fundaments
 */

function skt_get_profile($profile, $user_id = null) {
	if(!$user_id) {
		$user_id = get_current_user_id();
	}
	
	if($profile = $GLOBALS['skt_fundaments']->find_profile($profile)) {
		$data = array();
		foreach($profile->fieldnames() as $field) {
			$data[$field] = $profile->get_field($user_id, $field);
		}
		
		return $data;
	}
}