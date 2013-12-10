<?php /**
* Access-control helper functions
*
* @package vendorwp-fundaments
*/

function skt_is_content_allowed() {
	return !isset($GLOBALS['skt_content_forbidden']) || !$GLOBALS['skt_content_forbidden'];
}

function skt_is_content_forbidden() {
	return isset($GLOBALS['skt_content_forbidden']) && $GLOBALS['skt_content_forbidden'];
}