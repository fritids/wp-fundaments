<?php /**
 * Helper functions to register settings pages
 *
 * @package vendorwp-fundaments
 */

function skt_get_timbhumb_url($url, $args = array()) {
	$uploads = wp_upload_dir();
	$url = str_replace($uploads['baseurl'], $uploads['basedir'], $url);
	
	if(substr($url, 0, strlen(ABSPATH)) == ABSPATH) {
		$url = substr($url, strlen(ABSPATH));
	}
	
	$thumburl = plugins_url("skt-fundaments/vendor/timthumb.php", 'skt-fundaments') . '?src=' . urlencode($url);
	
	if(is_array($args) && count($args) > 0) {
		$thumburl .= '&' . http_build_query($args);
	}
	
	return $thumburl;
}