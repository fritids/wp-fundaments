<?php /**
 * A base class for handling content that has capability types
 *
 * @package wp-fundaments
 */

require_once('FieldManager.php');
abstract class SktCapable extends SktFieldManager {
	protected $stubborn_capabilities = array();
	
	protected function capabilities() {
		return array();
	}
	
	public function register_capabilities() {
		global $wp_roles;
		
		foreach($wp_roles->get_names() as $r => $name) {
			if(in_array($r, $this->roles)) {
				foreach($this->capabilities() as $meta => $capability) {
					$wp_roles->add_cap($r, $capability);
				}
			} else {
				foreach($this->capabilities() as $meta => $capability) {
					// If the capability is marked as "stubborn", that means it's probably
					// referenced elsewhere by another class and shouldn't be removed
					
					if(!in_array($capability, $this->stubborn_capabilities)) {
						$wp_roles->remove_cap($r, $capability);
					}
				}
			}
		}
	}
}