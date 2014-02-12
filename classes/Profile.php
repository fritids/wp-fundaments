<?php /**
 * A base user-profile section class
 *
 * @package wp-fundaments
 */

abstract class SktProfile extends SktFieldManager {
	function __construct($plugin) {
		parent::__construct($plugin);
		$basename = get_class($this);
		if(substr($basename, strlen($basename) - 7) == 'Profile') {
			$basename = substr($basename, 0, strlen($basename) - 7);
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
		
		$this->basename = strtolower($new_basename);
		if(!isset($this->name)) {
			$this->name = skt_ucwords($new_friendly_name);
		}
		
		add_filter('show_user_profile', array(&$this, 'form_fields'));
		add_filter('edit_user_profile', array(&$this, 'form_fields'));
		
		add_action('edit_user_profile_update', array(&$this, 'save_form_fields'));
		add_action('personal_options_update', array(&$this, 'save_form_fields'));
		add_action('skt_user_edited', array(&$this, 'edited'));
		add_action('user_register', array(&$this, 'user_register'), 1000);
		
		add_action('register_form', array(&$this, 'register_form'));
		add_filter('registration_errors', array(&$this, 'registration_errors'), 10, 3);
		
		add_filter('authenticate', array(&$this, 'authenticate'), 40, 3);
		add_filter('retrieve_password', array(&$this, 'retrieve_password'));
		
		add_action('wp_logout', array(&$this, 'logout'));
	}
	
	public function get_field($user, $field, $default = null) {
		if($default == null) {
			$attrs = $this->fieldattrs($field);
			if(isset($attrs['default'])) {
				$default = $attrs['default'];
			}
		}
		
		$value = skt_unserialise_field_value(
			get_user_meta(is_object($user) ? $user->ID : $user, $this->fieldname($field), true),
			$this->fieldtype($field)
		);
		
		return $value ? $value : $default;
	}
	
	public function set_field($user, $field, $value) {
		$type = $this->fieldtype($field);
		update_user_meta(is_object($user) ? $user->ID : $user, $this->fieldname($field), $value);
	}
	
	public function delete_field($user, $field) {
		delete_user_meta(is_object($user) ? $user->ID : $user, $this->fieldname($field));
	}
	
	public function form_fields($user) {
		$path = get_template_directory(). '/wp-profile.php';
		if(is_file($path)) {
			if(!$user) {
				$user = wp_get_current_user();
			}
			
			if (!current_user_can('edit_posts')) {
				skt_open_profile_fieldset($this->name);
				foreach($this->fields as $field => $opts) {
					if(is_array($opts)) {
						$key = $field;
						$attrs = $this->fieldattrs($field);
					} else {
						$key = $opts;
						$attrs = array();
					}
					
					if(isset($attrs['signup']) && $attrs['signup'] == false) {
						continue;
					}
					
					if($user->ID) {
						if(isset($attrs['edit']) && $attrs['edit'] == false) {
							continue;
						}
					}
					
					if(!isset($attrs['label'])) {
						$attrs['label'] = $this->fieldlabel($key);
					}
					
					$attrs['value'] = $this->get_field($user, $key);
					$fname = $this->fieldname($key);
					skt_profile_field($fname, $attrs);
				}
				
				skt_close_profile_fieldset();
				return;
			}
		}
		
		echo('<h2>' . htmlentities($this->name) . '</h2>');
		echo('<table class="form-table">');
		
		foreach($this->fields as $field => $opts) {
			if(is_array($opts)) {
				$key = $field;
				$attrs = $this->fieldattrs($field);
			} else {
				$key = $opts;
				$attrs = array();
			}
			
			$type = $this->fieldtype($key); ?>
			
			<tr>
				<th scope="row" valign="top">
					<?php if($type != 'boolean') {
						echo $this->fieldlabel($key);
					} ?>
				</th>
				<td>
					<?php $attrs['value'] = $this->get_field($user, $key);
					$fname = $this->fieldname($key);
					$GLOBALS['skt_fundaments']->input($fname, $attrs); ?>
				</td>
			</tr>
		<?php }
		
		echo('</table>');
	}
	
	public function save_form_fields($user_id) {
		foreach($this->fieldnames() as $field) {
			$attrs = $this->fieldattrs($field);
			
			if(isset($attrs['edit']) && $attrs['edit'] == false) {
				continue;
			}
			
			if($value = $this->POST($field)) {
				$this->set_field($user_id, $field, $value);
			} else {
				$this->delete_field($user_id, $field);
			}
		}
	}
	
	public function edited($user_id) {
		$data = array();
		
		foreach($this->fieldnames() as $field) {
			$attrs = $this->fieldattrs($field);
			$data[$field] = $this->get_field($user_id, $field,
				isset($attrs['default']) ? $attrs['default'] : null
			);
		}
		
		$this->user_updated($user_id, $data);
		if(isset($_POST['pass1']) && !empty($_POST['pass1'])) {
			if(isset($_POST['pass2']) && !empty($_POST['pass2'])) {
				if($_POST['pass1'] == $_POST['pass2']) {
					$this->user_changed_password($user_id, $_POST['pass2'], $data);
				}
			}
		}
	}
	
	public function register_form() {
		skt_open_signup_fieldset($this->name);
		foreach($this->fields as $field => $opts) {
			if(is_array($opts)) {
				$key = $field;
				$attrs = $this->fieldattrs($field);
			} else {
				$key = $opts;
				$attrs = array();
			}
			
			if(isset($attrs['signup']) && $attrs['signup'] == false) {
				continue;
			}
			
			if(!isset($attrs['label'])) {
				$attrs['label'] = $this->fieldlabel($key);
			}
			
			$attrs['value'] = $this->POST($key);
			$fname = $this->fieldname($key);
			
			if(method_exists($this, "render_${key}")) {
				call_user_method("render_${key}", $this, $attrs);
			} else {
				skt_signup_field($fname, $attrs);
			}
		}
		
		skt_close_signup_fieldset();
	}
	
	public function registration_errors($errors, $sanitized_user_login, $user_email) {
		$data = array();
		
		foreach($this->fields as $field => $opts) {
			if(is_array($opts)) {
				$key = $field;
				$attrs = $this->fieldattrs($field);
			} else {
				$key = $opts;
				$attrs = array();
			}
			
			if(isset($attrs['signup']) && $attrs['signup'] == false) {
				continue;
			}
			
			if($value = $this->POST($key)) {
				if(method_exists($this, "validate_${key}")) {
					call_user_method("validate_${key}", $this, $value, $errors);
				}
				
				continue;
			}
			
			if(isset($attrs['required']) && $attrs['required']) {
				$errors->add("empty_${key}",
					'<strong>ERROR</strong>: ' . _('The ' . skt_ucwords(str_replace('_', ' ', $key)) . '</strong> field is required.')
				);
			}
			
			$data[$key] = $value;
		}
		
		if(method_exists($this, 'validate')) {
			call_user_method('validate', $this, $data, $errors);
		}
		
		return $errors;
	}
	
	public function user_register($user_id) {
		$data = array();
		
		foreach($this->fieldnames() as $field) {
			$attrs = $this->fieldattrs($field);
			
			if(isset($attrs['register']) && $attrs['register'] == false) {
				continue;
			}
			
			$attrs = $this->fieldattrs($field);
			if($value = $this->POST($field)) {
				$this->set_field($user_id, $field, $value);
				$data[$field] = skt_unserialise_field_value(
					$value,
					isset($attrs['type']) ? $attrs['type'] : 'text'
				);
			} elseif(isset($attrs['default'])) {
				$this->set_field($user_id, $field, $attrs['default']);
				$data[$field] = $attrs['default'];
			}
		}

		if(!is_admin()) {
			$this->user_registered($user_id, $data);
		} else {
			$this->user_created($user_id, $data);
		}
	}
	
	protected function user_registered($user_id, $data) {
		// Runs when a user registers him or herself
	}
	
	protected function user_created($user_id, $data) {
		// Runs when a user is added via the admin site
	}
	
	protected function user_updated($user_id, $data) {
		// Runs when a user's details is updated
	}
	
	public function authenticate($user, $username, $password) {
		// Adds custom authentication
		return $user;
	}
	
	protected function user_changed_password($user_id, $password, $data) {
		// User password updates
	}
	
	public function retrieve_password($username) {
		if(method_exists($this, 'reset_password')) {
			if($user = get_user_by('login', $username)) {
				if($this->reset_password($user)) {
					wp_redirect(
						wp_login_url() . '?checkemail=confirm'
					);
					
					die();
				}
			}
		}
	}
	
	public function logout() {
		$this->user_logged_out(
			get_current_user_id()
		);
	}
	
	protected function user_logged_out($user_id) {
		// Runs when a user's details is updated
	}
}