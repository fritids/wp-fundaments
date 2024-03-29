<?php /**
 * A base class for handling objects that have settings
 *
 * @package wp-fundaments
 */

abstract class SktSettingsPageBase extends SktFieldManager {
	function __construct($plugin) {
		$this->plugin = $plugin;
		$basename = get_class($this);
		if(substr($basename, strlen($basename) - 12) == 'SettingsPage') {
			$basename = substr($basename, 0, strlen($basename) - 12);
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
		
		if(!isset($this->pagename)) {
			$this->pagename = str_replace('_', ' ', $new_basename);
		}
		
		$this->basename = strtolower($new_basename);
		add_action('admin_menu', array(&$this, 'add_settings_section'));
	}
	
	public function add_settings_section() {
		$class = get_class($this);
		add_submenu_page(
			'options-general.php',
			isset($this->pagetitle) ? $this->pagetitle : ($this->pagename . ' Settings'),
			$this->pagename,
			'administrator',
			$class,
			array(&$this, 'settings_page')
		);
		
		$registered = array();
		if(isset($this->sections) && is_array($this->sections)) {
			foreach($this->sections as $section => $opts) {
				if(is_array($opts)) {
					$view = new SktView(
						isset($opts['description']) ? wpautop($opts['description']) : ''
					);
					
					add_settings_section(
						"${class}_${section}",
						isset($opts['title']) ? $opts['title'] : skt_ucwords(str_replace('_', ' ', $section)),
						array($view, 'render'),
						$class
					);
					
					if(isset($opts['fields'])) {
						foreach($opts['fields'] as $k) {
							$this->add_field($k, $section);
							$registered[] = $k;
						}
					}
				} else {
					$view = new SktView();
					add_settings_section(
						"${class}_${opts}",
						skt_ucwords(str_replace('_', ' ', $opts)),
						array($view, 'render'),
						$class
					);
				}
			}
		}
		
		$added_remainder = false;
		foreach($this->fieldnames() as $key) {
			if(in_array($key, $registered)) {
				continue;
			}
			
			if(!$added_remainder) {
				add_settings_section(
					"${class}_", '',
					array(new SktView(), 'render'),
					$class
				);
				
				$added_remainder = true;
			}
			
			$this->add_field($key, '');
		}
	}
	
	protected function add_field($key, $section) {
		$class = get_class($this);
		$opts = $this->fieldattrs($key);
		$type = $this->fieldtype($key);
		
		if(isset($opts['type']) && substr($opts['type'], 0, 9) == 'provider:') {
			$parts = explode(':', substr($opts['type'], 9));
			if($parts[0] == '_theme') {
				$parts[0] = basename(get_template_directory());
			}
			
			$fieldname = 'skt_fundaments_provider_' . $parts[0] . '_' . $parts[1];
			$opts['value'] = get_option($fieldname);
		} else {
			$fieldname = $this->fieldname($key);
			$opts['value'] = $this->get_field($key);
		}
		
		if(isset($opts['default'])) {
			if(!isset($opts['value']) || empty($opts['value'])) {
				$opts['value'] = $opts['default'];
			}
			
			unset($opts['default']);
		}
		
		if(isset($opts['type']) && $opts['type'] == 'checkbox') {
			$opts['label'] = isset($opts['label']) ? $opts['label'] : skt_ucwords($key);
			$label = '';
		} else {
			$label = $this->fieldlabel($key);
		}
		
		register_setting($class, $fieldname);
		$widget = new SktInputView($fieldname, $opts);
		
		add_settings_field(
			$fieldname,
			$label,
			array($widget, 'render'),
			$class,
			"${class}_${section}"
		);
	}
	
	public function settings_page() { ?>
		<div class="wrap">
			<div class="icon32" id="icon-tools"><br /></div>
			<h2><?php _e(isset($this->pagetitle) ? $this->pagetitle : ($this->pagename . ' Settings')); ?></h2>
			
			<?php if(isset($this->description)) {
				echo wpautop($this->description);
			} ?>
			
			<form method="post" action="options.php">
				<?php settings_fields(get_class($this)); ?>
				<?php do_settings_sections(get_class($this)); ?>
				
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
				</p>
			</form>
		</div>
	<?php }
	
	public function get_field($field, $default = null) {
		$value = skt_unserialise_field_value(
			get_option($this->fieldname($field)),
			$this->fieldtype($field)
		);
		
		return $value ? $value : $default;
	}
	
	public function set_field($field, $value) {
		$type = $this->fieldtype($field);
		update_option(
			$this->fieldname($field),
			is_object($value) ? $value->ID : $value
		);
	}
	
	public function delete_field($field) {
		delete_option(
			$this->fieldname($field)
		);
	}
}