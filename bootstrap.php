<?php /*
Plugin Name: Fundaments
Plugin URI: https://github.com/substrakt/wp-fundaments
Description: A grown-up framework for building well-rounded WordPress plugins
Author: Mark Steadman
Version: 0.01a
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
$GLOBALS['skt_fundaments'] = new SktFundamentsContext();

global $pagenow;
if ($pagenow == "wp-login.php" && $_GET['action'] != 'logout' && !isset($_GET['key'])) {
	skt_load_module('login');
} elseif(is_admin()) {
	skt_load_module('profile');
}

function skt_fundaments_init() {
	wp_register_style('skt-fieldsets', WP_PLUGIN_URL . '/skt-fundaments/css/fieldsets.css');
	wp_register_script('skt-media-uploader', WP_PLUGIN_URL . '/skt-fundaments/js/media-uploader.js',
		array('jquery')
	);
	
	wp_register_script('skt-date', WP_PLUGIN_URL . '/skt-fundaments/js/date.js',
		array('jquery')
	);
	
	wp_register_script('skt-fieldsets', WP_PLUGIN_URL . '/skt-fundaments/js/fieldsets.js',
		array('jquery')
	);
	
	do_action('skt_bootstrap');
}

function skt_fundaments_admin_styles() {
	wp_enqueue_media();
	wp_enqueue_style('skt-fieldsets');
}

function skt_fundaments_admin_scripts() {
	wp_enqueue_script('skt-date');
	wp_enqueue_script('skt-fieldsets');
	wp_enqueue_script('skt-media-uploader');
}

function skt_fundaments_cron_schedules($schedules) {
	$schedules['skt_cron_' . SKT_SYNC_FREQ_MINUTE] = array(
		'interval' => SKT_SYNC_FREQ_MINUTE,
		'display' => __('Once Every Minute', 'skt-fundaments')
	);
	
	$schedules['skt_cron_' . SKT_SYNC_FREQ_HOUR] = array(
		'interval' => SKT_SYNC_FREQ_HOUR,
		'display' => __('Once Every Hour', 'skt-fundaments')
	);
	
	$schedules['skt_cron_' . SKT_SYNC_FREQ_DAY] = array(
		'interval' => SKT_SYNC_FREQ_DAY,
		'display' => __('Once Every Day', 'skt-fundaments')
	);
	
	$schedules['skt_cron_' . SKT_SYNC_FREQ_WEEK] = array(
		'interval' => SKT_SYNC_FREQ_WEEK,
		'display' => __('Once Every Week', 'skt-fundaments')
	);
	
	$schedules['skt_cron_' . SKT_SYNC_FREQ_MONTH] = array(
		'interval' => SKT_SYNC_FREQ_MONTH,
		'display' => __('Once Every 30 Days', 'skt-fundaments')
	);
	
	$schedules['skt_cron_' . SKT_SYNC_FREQ_YEAR] = array(
		'interval' => SKT_SYNC_FREQ_YEAR,
		'display' => __('Once Every Day', 'skt-fundaments')
	);
	
	return $schedules;
}

add_action('init', 'skt_fundaments_init');
add_action('admin_enqueue_scripts', 'skt_fundaments_admin_styles');
add_action('admin_enqueue_scripts', 'skt_fundaments_admin_scripts');
add_filter('cron_schedules', 'skt_fundaments_cron_schedules');