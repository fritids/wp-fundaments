<?php /**
 * A base class for handling generic, pluggable provider-based functionality
 *
 * @package wp-fundaments
 */

abstract class SktProviderBase {
	protected $mappings = array();
	protected $defaults = array();
	public $name = '';
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		$basename = get_class($this);
		
		if(substr($basename, strlen($basename) - 8) == 'Provider') {
			$basename = substr($basename, 0, strlen($basename) - 8);
		}
		
		$new_basename = '';
		$new_friendly_name = '';
		
		for($i = 0; $i < strlen($basename); $i ++) {
			$c = substr($basename, $i, 1);
			if($c == strtoupper($c)) {
				if($new_basename) {
					$new_basename .= '_';
				}
				
				if($new_friendly_name) {
					$new_friendly_name .= ' ';
				}
				
				$new_friendly_name .= strtolower($c);
			} else {
				$new_friendly_name .= $c;
			}
			
			$new_basename .= $c;
		}
		
		if(!$this->name) {
			$this->name = skt_ucwords($new_friendly_name);
		}
	}
	
	function enqueue() {
		if(method_exists($this, 'init')) {
			$this->init();
		}
		
		if(method_exists($this, 'head')) {
			add_action('init', array(&$this, 'head'));
		}
		
		if(method_exists($this, 'footer')) {
			add_action('wp_footer', array(&$this, 'footer'));
		}
	}
	
	protected function translate_options($options = array()) {
		$return = array();
		
		foreach($this->defaults as $key => $value) {
			if(isset($this->mappings[$key])) {
				$return[$this->mappings[$key]] = $value;
			} else {
				$return[$key] = $value;
			}
		}
		
		if(is_array($options)) {
			foreach($options as $key => $value) {
				if(isset($this->mappings[$key])) {
					$return[$this->mappings[$key]] = $value;
				} else {
					$return[$key] = $value;
				}
			}
		}
		
		return $return;
	}
}