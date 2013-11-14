<?php /**
* A class that allows plugins to refer to a single Fundaments context
*
* @package wp-fundaments
*/

class SktFundamentsContext {
	private $post_types = array();
	private $taxonomies = array();
	private $settings = array();
	private $widgets = array();
	private $profiles = array();
	private $sync_controllers = array();
	private $email_templates = array();
	private $vendor_libs = array();
	
	function register($path) {
		$base = basename($path);
		
		foreach(glob($path . '/helpers/*.php') as $filename) {
			require_once($filename);
		}
		
		foreach(glob($path . '/providers/*') as $filename) {
			if(is_dir($filename)) {
				$type = basename($filename);
				if(!$type) {
					continue;
				}
				
				foreach(glob($filename . '/_*.php') as $f) {
					require_once($f);
				}
				
				foreach(glob($filename . '/*.php') as $n) {
					$basename = basename($n);
					if(substr($basename, strlen($basename) - 4) == '.php') {
						$basename = substr($basename, 0, strlen($basename) - 4);
					}
					
					if(substr($basename, 0, 1) == '_') {
						continue;
					}
					
					require_once($n);
					$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'Provider';
					if(!class_exists($class)) {
						wp_die(
							skt_ucwords(str_replace('_', ' ', $type)) . ' ' .
							"Provider <code>$basename</code> detected, but no <code>$class</code> class found"
						);
					}
					
					skt_register_provider($base, $type, $class);
				}
			}
		}
		
		foreach(glob($path . '/post_types/*.php') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'PostType';
			if(!class_exists($class)) {
				wp_die("Content type <code>$basename</code> detected, but no <code>$class</code> class found");
			}
			
			$this->add_post_type($base, $basename, $class);
		}
		
		foreach(glob($path . '/taxonomies/*.php') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'Taxonomy';
			if(!class_exists($class)) {
				wp_die("Taxonomy <code>$basename</code> detected, but no <code>$class</code> class found");
			}
			
			$this->add_taxonomy($base, $basename, $class);
		}
		
		foreach(glob($path . '/settings/*.php') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'SettingsPage';
			if(!class_exists($class)) {
				wp_die("Settings page <code>$basename</code> detected, but no <code>$class</code> class found");
			}
			
			$this->add_settings_page($base, $basename, $class);
		}
		
		foreach(glob($path . '/widgets/*.php') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'Widget';
			if(!class_exists($class)) {
				wp_die("Widget <code>$basename</code> detected, but no <code>$class</code> class found");
			}
			
			$this->add_widget($base, $class);
		}
		
		foreach(glob($path . '/profiles/*.php') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'Profile';
			if(!class_exists($class)) {
				wp_die("Profile section <code>$basename</code> detected, but no <code>$class</code> class found");
			}
			
			$this->add_profile($base, $basename, $class);
		}
		
		foreach(glob($path . '/controllers/sync/*') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$class = str_replace(' ', '', skt_ucwords(str_replace('_', ' ', $basename))) . 'SyncController';
			if(!class_exists($class)) {
				wp_die("Sync controller for <code>$basename</code> type detected, but no <code>$class</code> class found");
			}
			
			$this->add_sync_controller($base, $basename, $class);
		}
		
		foreach(glob($path . '/ajax/*.php') as $filename) {
			$nopriv = false;
			$basename = basename($filename);
			
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			if(substr($basename, 0, 1) == '@') {
				$nopriv = true;
				$basename = substr($basename, 1);
			}
			
			$action = str_replace('-', '_', $base) . '_' . $basename;
			add_action('wp_ajax_' . $action,
				array(
					new SktAjaxCall($filename),
					'action'
				)
			);
			
			if($nopriv) {
				add_action('wp_ajax_nopriv_' . $action,
					array(
						new SktAjaxCall($filename),
						'action'
					)
				);
			}
		}
		
		foreach(glob($path . '/vendor/*') as $filename) {
			require_once($filename);
			$basename = basename($filename);
			if(substr($basename, strlen($basename) - 4) == '.php') {
				$basename = substr($basename, 0, strlen($basename) - 4);
			}
			
			$this->vendor_libs[$basename] = $filename;
		}
	}
	
	function input($name, $attrs = array()) {
		$widget = new SktInputView($name, $attrs);
		$widget->render();
	}
	
	function label($name) {
		echo skt_ucwords(str_replace('_', ' ', $name));
	}
	
	function view_exists($plugin, $name) {
		return is_file(ABSPATH . "/wp-content/plugins/$plugin/views/$name.php");
	}
	
	function view($plugin, $name, $args = array()) {
		$path = ABSPATH . "/wp-content/plugins/$plugin/views/$name.php";
		if(!is_file($path)) {
			wp_die('View <code>' . basename($name) . "</code> for plugin <code>$plugin</code> not found. Create it at <code>$path</code>");
		}
		
		if(is_array($args)) {
			extract($args);
		}
		
		require_once($path);
	}
	
	private function add_post_type($plugin, $post_type_name, $post_type_class) {
		$this->post_types[$plugin][$post_type_name] = new $post_type_class($plugin);
	}
	
	function get_post_type($plugin, $post_type) {
		return $this->post_types[$plugin][$post_type];
	}
	
	function find_post_type($post_type) {
		foreach($this->post_types as $plugin => $types) {
			if(isset($types[$post_type])) {
				return $types[$post_type];
			}
		}
		
		return null;
	}
	
	private function add_taxonomy($plugin, $taxonomy_name, $taxonomy_class) {
		$this->taxonomies[$plugin][$taxonomy_name] = new $taxonomy_class($plugin);
	}
	
	function get_taxonomy($plugin, $taxonomy) {
		return $this->taxonomies[$plugin][$taxonomy];
	}
	
	function find_taxonomy($taxonomy) {
		foreach($this->taxonomies as $plugin => $taxonomies) {
			if(isset($taxonomies[$taxonomy])) {
				return $taxonomies[$taxonomy];
			}
		}
		
		return null;
	}
	
	private function add_settings_page($plugin, $page_name, $settings_class) {
		$this->settings[$plugin][$page_name] = new $settings_class($plugin);
	}
	
	function get_settings_page($plugin, $page) {
		return $this->settings[$plugin][$page];
	}
	
	private function add_widget($plugin, $widget_class) {
		$obj = new $widget_class($plugin);
		$this->widgets[$plugin][$widget_class] = $obj;
		$obj->register();
	}
	
	function get_widget($plugin, $widget_class) {
		return $this->widgets[$plugin][$widget_class];
	}
	
	private function add_profile($plugin, $profile_name, $profile_class) {
		$this->taxonomies[$plugin][$profile_name] = new $profile_class($plugin);
	}

	function get_profile($plugin, $profile) {
		return $this->taxonomies[$plugin][$profile];
	}

	function find_profile($profile) {
		foreach($this->taxonomies as $plugin => $taxonomies) {
			if(isset($taxonomies[$profile])) {
				return $taxonomies[$profile];
			}
		}

		return null;
	}
	
	private function add_sync_controller($plugin, $post_type, $sync_class) {
		if(isset($this->sync[$plugin][$post_type])) {
			wp_die("A sync controller for the <code>$post_type</code> has already been defined.");
		}
		
		$this->sync[$plugin][$post_type] = new $sync_class($plugin, $post_type);
	}
	
	function get_sync_controller($plugin, $post_type) {
		return $this->sync[$plugin][$post_type];
	}
	
	function add_email_template($filename, $type) {
		$name = basename($filename);
		
		if(substr($name, strlen($name) - 4) == '.php') {
			$name = substr($name, 0, strlen($name) - 4);
		}
		
		$this->email_templates[$name][$type] = $filename;
	}
	
	function render_to_mail($to, $template, $context = array()) {
		if(isset($this->email_templates[$template])) {
			if(isset($this->email_templates[$template]['theme'])) {
				$filename = $this->email_templates[$template]['theme'];
			} elseif(isset($this->email_templates[$template]['plugin'])) {
				$filename = $this->email_templates[$template]['plugin'];
			} else {
				return false;
			}
			
			ob_start();
			extract($context);
			require($filename);
			$mail = ob_get_contents();
			ob_end_clean();
			
			$headers = array();
			$body = array();
			$in_body = false;
			$subject = skt_ucwords(str_replace('_', ' ', $template));
			
			foreach(explode("\n", $mail) as $line) {
				if(substr($line, strlen($line) - 1) == "\r") {
					$line = substr($line, 0, strlen($line) - 1);
				}
				
				if(!trim($line)) {
					if(count($headers) > 0) {
						$in_body = true;
						continue;
					}
				}
				
				if($in_body) {
					$body[] = $line . ' ';
				} elseif(substr($line, 0, 8) == 'Subject:') {
					$subject = substr($line, 8) . ' ';
				} else {
					$headers[] = $line . ' ';
				}
			}
			
			$headers = apply_filters("skt_${template}_email_headers", $headers);
			$message = apply_filters("skt_${template}_email_body",
				implode("\r\n", $body)
			);
			
			wp_mail($to, $subject, $message, $headers);
			return true;
		}
		
		return false;
	}
	
	public function include_vendor_library($name) {
		if(isset($this->vendor_libs[$name])) {
			require_once($this->vendor_libs[$name]);
			return true;
		}
		
		return false;
	}
}