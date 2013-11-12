<?php /**
 * Helper functions to register generic providers
 *
 * @package wp-fundaments
 */

function skt_register_provider($plugin, $type, $class) {
	$key = "skt_fundaments_providers_${plugin}_${type}";
	if(!isset($GLOBALS[$key])) {
		$GLOBALS[$key] = array();
	}
	
	$GLOBALS[$key][$class] = $class;
	
	if($provider = skt_get_provider($plugin, $type)) {
		$provider->enqueue();
	}
}

function skt_get_provider_choices($plugin, $type) {
	$key = "skt_fundaments_providers_${plugin}_${type}";
	$choices = array();
	
	if(isset($GLOBALS[$key])) {
		foreach($GLOBALS[$key] as $name => $class) {
			$obj = new $class($plugin);
			$choices[$name] = $obj->name;
		}
	}
	
	return $choices;
}

function skt_get_provider($plugin, $type) {
	$key = "skt_fundaments_provider_${plugin}_${type}";
	
	if($provider = get_option($key)) {
		if($class = isset($GLOBALS[$key][$provider]) ? $GLOBALS[$key][$provider] : null) {
			return new $class($plugin);
		}
	}
	
	$key = "skt_fundaments_providers_${plugin}_${type}";
	if(isset($GLOBALS[$key])) {
		foreach($GLOBALS[$key] as $name => $class) {
			return new $class($plugin);
		}
	}
	
	return null;
}