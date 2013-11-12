<?php /**
 * Helper functions to register settings pages
 *
 * @package wp-fundaments
 */

function skt_get_setting($plugin, $page, $setting, $default = '') {
	if($page = $GLOBALS['skt_fundaments']->get_settings_page($plugin, $page)) {
		if($value = $page->get_field($setting)) {
			return $value;
		}
	}
	
	return $default;
}