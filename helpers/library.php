<?php /**
 * Helper functions for dealing with third-party libraries
 *
 * @package wp-fundaments
 */

function skt_library_url($plugin, $library, $url) {
	return plugins_url("${plugin}/library/modules/${library}/${url}", $plugin);
}