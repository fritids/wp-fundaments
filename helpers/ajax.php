<?php /**
 * Helper functions for working with AJAX requests
 *
 * @package wp-fundaments
 */

function skt_is_ajax_request() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}