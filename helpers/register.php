<?php /**
 * Helper functions to register Fundaments-based plugins
 *
 * @package wp-fundaments
 */

function skt_register_plugin($path) {
	$base = basename($path);
	if(!is_dir($path)) {
		wp_die("Plugin <code>$base</code> not found");
	}
	
	$GLOBALS['skt_fundaments']->register($path);
	foreach(glob($path . '/mail/*.php') as $filename) {
		$GLOBALS['skt_fundaments']->add_email_template($filename, 'plugin');
	}
}

function skt_register_theme() {
	$path = get_template_directory() . '/fundaments';
	if(!is_dir($path)) {
		wp_die("Directory <code>fundmanets</code> not found in theme");
	}
	
	$GLOBALS['skt_fundaments']->register($path,
		basename(get_template_directory())
	);
	
	foreach(glob($path . '/mail/*.php') as $filename) {
		$GLOBALS['skt_fundaments']->add_email_template($filename, 'plugin');
	}
}