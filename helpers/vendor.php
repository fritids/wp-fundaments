<?phpp /**
 * Shortcut functions to inclusion of third-party PHP files
 *
 * @package vendorwp-fundaments
 */

function skt_vendor_include($name) {
	$GLOBALS['skt_fundaments']->include_vendor_library($name);
}