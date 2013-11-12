<?php /**
 * Helper functions to register settings pages
 *
 * @package vendorwp-fundaments
 */

function skt_get_timbhumb_url($url, $args = array()) {
	$thumburl = plugins_url("skt-fundaments/vendor/timthumb.php", 'skt-fundaments');
	$thumburl .= '?src=' . urlencode($url);
	
	if(is_array($args) && count($args) > 0) {
		$thumburl .= '&' . http_build_query($args);
	}
	
	return $thumburl;
}