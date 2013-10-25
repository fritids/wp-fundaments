<?php /**
 * Helper functions to register Fundaments-based plugins
 *
 * @package wp-fundaments
 */

function skt_register_plugin($path) {
	$base = basename($path);
	if(!is_dir($path)) {
		throw new Exception("Plugin $base not found");
	}
	
	foreach (glob($path . '/helpers/*.php') as $filename) {
		require_once($filename);
	}
	
	foreach (glob($path . '/post_types/*.php') as $filename) {
		require_once($filename);
		$basename = basename($filename);
		if(substr($basename, strlen($basename) - 4) == '.php') {
			$basename = substr($basename, 0, strlen($basename) - 4);
		}
		
		$class = str_replace(' ', '', ucwords(str_replace('_', ' ', $basename))) . 'PostType';
		if(!class_exists($class)) {
			wp_die("Content type <code>$basename</code> detected, but no <code>$class</code> class found");
		}
		
		$GLOBALS['skt_fundaments']->add_post_type($base, $basename, $class);
	}
}