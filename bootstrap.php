<?php /*
Plugin Name: Fundaments
Plugin URI: https://github.com/substrakt/wp-fundaments
Description: A grown-up framework for building well-rounded WordPress plugins
Author: Mark Steadman
Version: 0.1
Author URI: http://substrakt.co.uk/
*
* @package wp-fundaments
*/

foreach (glob(dirname(__FILE__) . '/classes/*.php') as $filename) {
	require_once($filename);
}

foreach (glob(dirname(__FILE__) . '/includes/*.php') as $filename) {
	require_once($filename);
}

foreach (glob(dirname(__FILE__) . '/helpers/*.php') as $filename) {
	require_once($filename);
}

require_once('constants.php');

function skt_fundaments_init() {
	$GLOBALS['skt_fundaments'] = new SktFundamentsContext();
	do_action('skt_bootstrap');
}

add_action('init', 'skt_fundaments_init');