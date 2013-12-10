<?php /**
 * A base class for handling content that has capability types
 *
 * @package wp-fundaments
 */

require_once('FieldManager.php');
abstract class SktCapable extends SktFieldManager {
	protected $stubborn_capabilities = array();
	protected $admin_roles = array();
	protected $user_roles = array();
	
	function __construct($plugin) {
		parent::__construct($plugin);
		
		if(is_user_logged_in()) {
			$this->register_capabilities();
		}
	}
	
	protected function capabilities() {
		return array();
	}
	
	public function register_capabilities() {
		global $wp_roles;
		
		foreach($wp_roles->get_names() as $r => $name) {
			if(in_array($r, $this->admin_roles)) {
				foreach($this->admin_capabilities() as $meta => $capability) {
					$wp_roles->add_cap($r, $capability);
				}
			}
			
			if(in_array($r, $this->user_roles)) {
				foreach($this->user_capabilities() as $capability) {
					$wp_roles->add_cap($r, $capability);
				}
			}
		}
	}
}