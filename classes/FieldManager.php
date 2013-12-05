<?php /**
 * A base class for managing items with custom fields
*
* @package wp-fundaments
*/

abstract class SktFieldManager {
	public $plugin;
	public $basename;
	protected $fields = array();
	
	function __construct($plugin) {
		$this->plugin = $plugin;
	}
	
	public function fieldname($name) {
		return '_' . str_replace('-', '_', $this->plugin . '_' . $this->basename . '_' . $name);
	}
	
	public function fieldlabel($name, $id = null) {
		$attrs = $this->fieldattrs($name);
		$type = $this->fieldtype($name);
		
		if(isset($attrs['label'])) {
			$label = __($attrs['label']);
		} else {
		 	$label = __(skt_ucwords(str_replace('_', ' ', $name)));
		}
		
		if(!$id) {
			if(isset($attrs['id'])) {
				$id = $attrs['id'];
			} else {
				$id = 'id' . $this->fieldname($name);
			
				if($type == 'date' || $type == 'datetime') {
					$id .= '_year';
				}
			}
		}
		
		return '<label for="' . esc_attr($id) . '">' . htmlentities($label) . '</label>';
	}
	
	public function fieldtype($name) {
		if(isset($this->fields) && is_array($this->fields)) {
			if(isset($this->fields[$name])) {
				return isset($this->fields[$name]['type']) ? $this->fields[$name]['type'] : SKT_DEFAULT_FIELD_TYPE;
			}
		}
		
		return SKT_DEFAULT_FIELD_TYPE;
	}
	
	public function fieldattrs($name, $extra = array()) {
		$attrs = array();
		
		if(isset($this->fields) && is_array($this->fields)) {
			if(isset($this->fields[$name])) {
				$attrs = $this->fields[$name];
			}
		};
		
		if(!isset($attrs['type'])) {
			$attrs['type'] = SKT_DEFAULT_FIELD_TYPE;
		}
		
		if(isset($extra) && is_array($extra)) {
			$attrs = array_merge($attrs, $extra);
		}
		
		return $attrs;
	}
	
	public function fieldnames() {
		$fields = array();
		
		if(isset($this->fields) && is_array($this->fields)) {
			foreach($this->fields as $i => $field) {
				if(is_array($field)) {
					$fields[] = $i;
				} else {
					$fields[] = $field;
				}
			}
		}
		
		if(isset($this->parent) && $this->parent) {
			$fields[] = '_parent';
		}
		
		return $fields;
	}
	
	public function fieldeditable($name) {
		$attrs = $this->fieldattrs($name);
		
		if(isset($attrs['capabilities'])) {
			foreach($attrs['capabilities'] as $capability) {
				if(!current_user_can($capability)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	protected function POST($field) {
		$attrs = $this->fieldattrs($field);
		$fieldname = $this->fieldname($field);
		$type = $this->fieldtype($field);
		
		if($field == '_parent') {
			$type = 'post';
		}
		
		switch($type) {
			case 'date':
				$year = isset($_POST["${fieldname}_year"]) ? intVal($_POST["${fieldname}_year"]) : 0;
				$month = isset($_POST["${fieldname}_month"]) ? intVal($_POST["${fieldname}_month"]) : 0;
				$day = isset($_POST["${fieldname}_day"]) ? intVal($_POST["${fieldname}_day"]) : 0;
				
				return mktime(0, 0, 0, $month, $day, $year);
			case 'datetime':
				$year = isset($_POST["${fieldname}_year"]) ? intVal($_POST["${fieldname}_year"]) : 0;
				$month = isset($_POST["${fieldname}_month"]) ? intVal($_POST["${fieldname}_month"]) : 0;
				$day = isset($_POST["${fieldname}_day"]) ? intVal($_POST["${fieldname}_day"]) : 0;
				$time = isset($_POST["${fieldname}_time"]) ? explode(':', $_POST["${fieldname}_time"]) : array();
				$hour = isset($time[0]) ? intVal($time[0]) : 0;
				$minute = isset($time[1]) ? intVal($time[1]) : 0;
				$second = isset($time[2]) ? intVal($time[2]) : 0;
				
				return mktime($hour, $minute, $second, $month, $day, $year);
			case 'fieldset':
				$mgmt = isset($_POST["${fieldname}__mgmt"]) ? $_POST["${fieldname}__mgmt"] : null;
				$count = isset($_POST["${fieldname}__count"]) ? intVal($_POST["${fieldname}__count"]) : null;
				
				if($mgmt) {
					$mgmt = unserialize(base64_decode($mgmt));
				} else {
					wp_die("No management form found for field <code>$field</code>");
				}
				
				$data = array();
				for($i = 0; $i < $count; $i ++) {
					$row = array();
					foreach($mgmt as $field => $index) {
						$row[$field] = $_POST["${fieldname}__${i}__${field}"];
					}
					
					$data[] = $row;
				}
				
				return $data;
			default:
				if(substr($type, 0, 5) == 'post:' || $type == 'post') {
					if(isset($attrs['multiple']) && $attrs['multiple']) {
						return isset($_POST[$fieldname]) ? $_POST[$fieldname] : null;
					} else {
						$v = isset($_POST[$fieldname]) ? $_POST[$fieldname] : null;
						return is_array($v) ? $v[0] : $v;
					}
				} elseif(isset($attrs['multiple']) && $attrs['multiple']) {
					return isset($_POST[$fieldname]) ? $_POST[$fieldname] : null;
				} elseif(isset($_POST[$fieldname]) && is_array($_POST[$fieldname]) && count($_POST[$fieldname]) > 0) {
					return $_POST[$fieldname][0];
				}
				
				return isset($_POST[$fieldname]) ? $_POST[$fieldname] : null;
		}
	}
}