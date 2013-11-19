<?php /*
 * Helper functions to register third-party plugin modules
 *
 * @package wp-fundaments
 */

add_action('skt_enqueue_module_css', 'skt_enqueue_module_css', 10, 2);
function skt_enqueue_module_css($plugin, $name) {
	$path = ABSPATH . 'wp-content/plugins/' . $plugin;
	if(!is_dir($path)) {
		wp_die("Plugin $plugin not found");
	}
	
	$path .= '/library/modules/' . $name;
	if(!is_dir($path)) {
		wp_die("Module $name not found");
	}
	
	foreach (glob("${path}/css/*.css") as $filename) {
		$url = plugins_url("${plugin}/library/modules/${name}/css/" . basename($filename));
		wp_enqueue_style(basename($filename), $url);
	}
}

add_action('skt_enqueue_module_js', 'skt_enqueue_module_js', 10, 2);
function skt_enqueue_module_js($plugin, $name) {
	$path = ABSPATH . 'wp-content/plugins/' . $plugin;
	if(!is_dir($path)) {
		wp_die("Plugin $plugin not found");
	}
	
	$path .= '/library/modules/' . $name;
	if(!is_dir($path)) {
		wp_die("Module $name not found");
	}
	
	foreach (glob("${path}/js/*.js") as $filename) {
		$url = plugins_url("${plugin}/library/modules/${name}/js/" . basename($filename));
		wp_register_script(basename($filename), $url);
		wp_enqueue_script(basename($filename), $url);
	}
}

function skt_load_module($name) {
	$path = realpath(dirname(__file__) . '/../modules/' . $name);
	if(!is_dir($path)) {
		wp_die("Module <code>$name</code> not found in plugin");
	}
	
	foreach (glob($path . '/classes/*.php') as $filename) {
		require_once($filename);
	}
		
	$GLOBALS['skt_fundaments']->register($path);
	if(is_file($path . '/functions.php')) {
		require_once($path . '/functions.php');
	}
}