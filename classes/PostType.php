<?php /**
 * A base class for creating content types
 *
 * @package wp-fundaments
 */

abstract class SktPostType extends SktCapable {
	protected $supports = array('title', 'slug', 'editor', 'thumbnail');
	protected $admin_roles = array('administrator', 'editor');
	
	function __construct($plugin) {
		if(isset($this->rewrite) && is_string($this->rewrite)) {
			global $wp_rewrite;
			
			if(preg_match('/%([\w]+)%/', $this->rewrite, $matches)) {
				foreach($matches as $i => $match) {
					if($i == 0) {
						continue;
					}
					
					$wp_rewrite->add_rewrite_tag('%' . $match . '%', '([^/]+)', $match . '=');
					$wp_rewrite->add_permastruct($match, $this->rewrite, false);
				}
			}
		}
		
		$this->register_post_type();
		parent::__construct($plugin);
		
		add_filter('the_content', array(&$this, 'the_contents'));
		if(isset($this->list_fields)) {
			add_filter('manage_' . $this->basename . '_posts_columns', array(&$this, 'post_columns'));
			add_action('manage_' . $this->basename . '_posts_custom_column', array(&$this, 'post_columns_data'));
		}
		
		add_action('save_post', array(&$this, 'save_post'));
		add_filter('post_type_link', array(&$this, 'permalinks'), 10, 3);
		add_action('pre_get_posts', array(&$this, 'pre_get_posts'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_filter('skt_formfield_attrs_by_name', array(&$this, 'formfield_attrs'), 10, 2);
		add_action('template_redirect', array(&$this, 'template_redirect'));
		add_filter('body_class', array(&$this, 'body_class'));
	}
	
	protected function admin_capabilities() {
		return array(
			'publish_posts' => 'publish_' . $this->basename . 's',
			'edit_posts' => 'edit_' . $this->basename . 's',
			'edit_others_posts' => 'edit_others_' . $this->basename . 's',
			'delete_posts' => 'delete_' . $this->basename . 's',
			'delete_others_posts' => 'delete_others_' . $this->basename . 's',
			'read_private_posts' => 'read_private_' . $this->basename . 's',
			'edit_post' => 'edit_' . $this->basename,
			'delete_post' => 'delete_' . $this->basename,
			'read_post' => 'read_' . $this->basename
		);
	}
	
	protected function user_capabilities() {
		return array('view_' . $this->basename . 's');
	}
	
	public function fieldeditable($name) {
		foreach($this->meta_boxes as $key => $box) {
			if(is_array($box) && isset($box['fields']) && isset($box['capabilities'])) {
				if(in_array($name, $box['fields'])) {
					foreach($box['capabilities'] as $capability) {
						if(!current_user_can($capability)) {
							return false;
						}
					}
				}
			}
		}
		
		return parent::fieldeditable($name);
	}
	
	private function register_post_type() {
		$basename = get_class($this);
		if(substr($basename, strlen($basename) - 8) == 'PostType') {
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
		
		$this->basename = strtolower($new_basename);
		
		$types = array('post', 'page', 'attachment', 'revision', 'nav_menu_item');
		if(in_array($this->basename, $types)) {
			add_action('add_meta_boxes', array(&$this, 'register_meta_boxes'));
			return;
		}
		
		$singular = isset($this->singular) ? $this->singular : $new_friendly_name;
		$uSingular = skt_ucwords($singular);
		
		$plural = isset($this->plural) ? $this->plural : $singular . 's';
		$uPlural = skt_ucwords($plural);
		
		$args = array(
			'label' => isset($this->label) ? $this->label : $uPlural,
			'labels' => array(
				'name' => isset($this->name) ? $this->name : $uPlural,
				'singular_name' => $singular,
				'add_new_item' => isset($this->add_new_item) ? $this->add_new_item : "Add New $uSingular",
				'edit_item' => isset($this->edit_item) ? $this->edit_item : "Edit $uSingular",
				'new_item' => isset($this->new_item) ? $this->new_item : "New $uSingular",
				'view_item' => isset($this->view_item) ? $this->view_item : "View $uSingular",
				'search_items' => isset($this->search_items) ? $this->search_items : "Search $uPlural",
				'not_found' => isset($this->not_found) ? $this->not_found : "No $plural found",
				'not_found_in_trash' => isset($this->not_found_in_trash) ? $this->not_found_in_trash : "No $plural found in trash"
			),
			'description' => isset($this->description) ? $this->description : 'Custom post type',
			'public' => isset($this->public) ? $this->public : true,
			'show_ui' => isset($this->show_ui) ? $this->show_ui : true,
			'menu_position' => isset($this->menu_position) ? $this->menu_position : SKT_DEFAULT_MENU_POSITION,
			'supports' => $this->supports,
			'capabilities' => $this->admin_capabilities(),
			'map_meta_cap' => true,
			'hierarchical' => isset($this->hierarchical) ? $this->hierarchical : SKT_DEFAULT_HIERARCHICAL,
			'register_meta_box_cb' => array(&$this, 'register_meta_boxes')
		);
		
		if(isset($this->rewrite) && is_bool($this->rewrite)) {
			$args['rewrite'] = $this->rewrite;
		} elseif(isset($this->rewrite) && is_string($this->rewrite)) {
			$args['rewrite'] = array('slug' => $this->rewrite);
		} else {
			$args['rewrite'] = array('slug' => strtolower($new_basename) . 's');
		}
		
		if(isset($this->queryable)) {
			$args['publicly_queryable'] = $this->queryable;
		}
		
		if(isset($this->parent)) {
			$args['has_archive'] = false;
		} else {
			$args['has_archive'] = isset($this->has_archive) ? $this->has_archive : true;
		}
		
		if(isset($this->parent)) {
			$args['show_in_menu'] = 'edit.php?post_type=' . $this->parent;
		}
		
		register_post_type($this->basename, $args);
		// flush_rewrite_rules();
	}
	
	public function admin_menu() {
		if(isset($this->can_add) && !$this->can_add) {
			global $submenu;
			unset($submenu['edit.php?post_type=' . $this->basename][10]);
		}
	}
	
	public function get_field($obj, $field, $default = null) {
		if($field == '_parent') {
			$ancestors = get_post_ancestors(is_object($obj) ? $obj->ID : $obj);
			if(is_array($ancestors) && count($ancestors) > 0) {
				return get_post($ancestors[0]);
			}
			
			return $default;
		}
		
		if($default == null) {
			$attrs = $this->fieldattrs($field);
			if(isset($attrs['default'])) {
				$default = $attrs['default'];
			}
		}
		
		$value = skt_unserialise_field_value(
			get_post_meta(is_object($obj) ? $obj->ID : $obj, $this->fieldname($field), true),
			$this->fieldtype($field)
		);
		
		return $value ? $value : $default;
	}
	
	public function set_field($obj, $field, $value) {
		$type = $this->fieldtype($field);
		if($field == '_parent') {
			wp_update_post(
				array(
					'ID' => is_object($obj) ? $obj->ID : $obj,
					'post_parent' => is_object($value) ? $value->ID : $value
				)
			);
		} else {
			update_post_meta(is_object($obj) ? $obj->ID : $obj, $this->fieldname($field), $value);
		}
	}
	
	public function delete_field($obj, $field) {
		delete_post_meta(is_object($obj) ? $obj->ID : $obj, $this->fieldname($field));
	}
	
	public function register_meta_boxes() {
		global $post;
		
		$fields = $this->fieldnames();
		$handled_fields = array();
		
		if(isset($this->meta_boxes) && is_array($this->meta_boxes)) {
			foreach($this->meta_boxes as $key => $box) {
				$continue = true;
				if(is_array($box) && isset($box['conditions'])) {
					foreach($box['conditions'] as $k => $v) {
						if(is_array($v)) {
							if(!in_array($post->$k, $v)) {
								$continue = false;
								break;
							}
						} elseif($post->$k != $v) {
							$continue = false;
							break;
						}
					}
				}
				
				$has_view = false;
				if($GLOBALS['skt_fundaments']->view_exists($this->plugin, $view)) {
					if(isset($this->fields)) {
						foreach($fields as $field) {
							$handled_fields[] = $field;
						}
					}
					
					$has_view = true;
				}
				
				if(is_array($box) && isset($box['capabilities'])) {
					foreach($box['capabilities'] as $capability) {
						if(!current_user_can($capability)) {
							$continue = false;
						}
					}
				}
				
				if(is_array($box) && !$has_view) {
					if(isset($box['fields']) && is_array($box['fields'])) {
						foreach($box['fields'] as $field) {
							$handled_fields[] = $field;
						}
					}
				}
				
				if(!$continue) {
					continue;
				}
				
				$view = 'post_types/' . $this->basename . '/meta/' . (is_array($box) ? $key : $box);
				$func = '$g = $GLOBALS[\'skt_fundaments\']; ';
				$func .= '$p = $g->get_post_type("' . $this->plugin . '", "' . $this->basename . '"); ';
				$func .= 'global $post; ';
				
				$usable_fields = array();
				if(is_array($box) && isset($box['fields'])) {
					foreach($box['fields'] as $field) {
						if($this->fieldeditable($field)) {
							$usable_fields[] = $field;
						}
					}
				}
				
				if(!count($usable_fields)) {
					continue;
				}
				
				add_meta_box(
					$this->basename . '_' . (is_array($box) ? $key : $box),
					is_array($box) ? (
						isset($box['title']) ? $box['title'] : skt_ucwords(str_replace('_', ' ', $key))
					) : skt_ucwords(str_replace('_', ' ', $box)),
					array(
						new SktMetaBox($this, $view,
							$usable_fields,
							count($usable_fields) > 0
						), 'render'
					),
					$this->basename,
					is_array($box) ? (
						isset($box['context']) ? $box['context'] : 'normal'
					) : 'normal',
					is_array($box) ? (
						isset($box['priority']) ? $box['priority'] : 'core'
					) : 'core'
				);
			}
		}
		
		foreach($fields as $field) {
			if(in_array($field, $handled_fields)) {
				continue;
			}
			
			if(!$this->fieldeditable($field)) {
				continue;
			}
			
			$attrs = $this->fieldattrs($field);
			if(isset($attrs['visible']) && !$attrs['visible']) {
				continue;
			}
			
			add_meta_box(
				$this->basename . '_' . $field,
				$this->fieldlabel($field),
				array(
					new SktMetaBox($this, null, array($field), false),
					'render'
				),
				$this->basename
			);
		}
	}
	
	public function save_post($post_id) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}
		
		if($_POST['post_type'] != $this->basename) {
			return;
		}
		
		if(method_exists($this, "pre_save")) {
			call_user_func_array(
				array($this, "pre_save"), array($post_id)
			);
		}
		
		remove_action('save_post', array(&$this, 'save_post'));
		foreach($this->fieldnames() as $field) {
			if(!$this->fieldeditable($field)) {
				continue;
			}
			
			$fieldname = $this->fieldname($field);
			$value = $this->POST($field);
			
			if(method_exists($this, "save_field_${field}")) {
				call_user_func_array(array($this, "save_field_${field}"), array($post_id, $value));
			} else {
				if(isset($value) && !empty($value)) {
					$this->set_field($post_id, $field, $value);
				} else {
					$this->delete_field($post_id, $field);
				}
			}
		}
		
		if(method_exists($this, "post_save")) {
			call_user_func_array(
				array($this, "post_save"), array($post_id)
			);
		}
		
		add_action('save_post', array(&$this, 'save_post'));
	}
	
	public function permalinks($permalink, $post, $leavename) {
		if($post->post_type != $this->basename || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) {
			return $permalink;
		}
		
		if(method_exists($this, "permalink")) {
			$permalink = call_user_func_array(
				array($this, "permalink"), array($post, $permalink)
			);
		}
		
		return $permalink;
	}
	
	public function the_contents($content) {
		global $post;
		
		$type = get_post_type($post);
		if($type == $this->basename) {
			if(isset($this->user_roles) && is_array($this->user_roles) && count($this->user_roles)) {
				$ok = false;
				
				foreach($this->user_roles as $role) {
					if(current_user_can($role)) {
						$ok = true;
					}
				}
				
				if(!$ok) {
					if(is_single()) {
						return apply_filters('skt_content_forbidden',
							'Please <a href="' . wp_login_url() . '">log in</a> or ' .
							'<a href="' . wp_registration_url() . '">sign up</a> ' .
							'to view this content.'
						);
					} else {
						return '';
					}
				}
			}
		}
		
		if($type == $this->basename) {
			if(method_exists($this, 'the_content')) {
				return $this->the_content($post, $content);
			}
		}
		
		return $content;
	}
	
	public function post_columns($columns) {
		foreach($this->list_fields as $field) {
			$columns[$field] = $this->fieldlabel($field);
		}
		
		return $columns;
	}
	
	public function post_columns_data($column) {
		global $post;
		
		$datas = $this->get_field($post, $column);
		$type = $this->fieldtype($column);
		
		if(!is_array($datas)) {
			$datas = array($datas);
		}
		
		$attrs = $this->fieldattrs($column);
		
		foreach($datas as $i => $data) {
			if($i > 0) {
				echo '<br />';
			}
			
			switch($type) {
				case 'date':
					echo date('j M Y', $data);
					return;
				case 'datetime':
					echo date('j M Y', $data);
					echo '<br />';
					echo date('H:i:s', $data);
					return;
			}
			
			if(method_exists($this, "get_${column}_display")) {
				echo call_user_func_array(
					array($this, "get_${column}_display"),
					array($data)
				);
				
				continue;
			}
			
			switch($type) {
				case 'select': case 'radio':
					$choices = isset($attrs['choices']) ? $attrs['choices'] : array();
					foreach($choices as $key => $value) {
						if((string)$key == (string)$data) {
							echo htmlentities($value);
						}
					}
					
					return;
				case 'checkbox':
					$choices = isset($attrs['choices']) ? $attrs['choices'] : array();
					foreach($choices as $key => $value) {
						if((string)$data == (string)$key) {
							echo htmlentities($value);
						}
					}
					
					continue;
			}
			
			if(is_object($data) && get_class($data) == 'WP_Post') {
				echo edit_post_link($data->post_title, '', '', $post->ID);
				continue;
			}
			
			if($type != 'checkbox') {
				echo htmlentities($data);
			}
		}
	}
	
	public function query_args($args) {
		$defaults = array();
		
		if(isset($this->order_by)) {
			$fieldnames = $this->fieldnames();
			if(in_array($this->order_by, $fieldnames)) {
				$defaults['meta_key'] = $this->fieldname($this->order_by);
				switch($this->fieldtype($this->order_by)) {
					case 'integer': case 'number': case 'date':
						$defaults['orderby'] = 'meta_value_num';
						break;
					default:
						$defaults['orderby'] = 'meta_value';
				}
			} else {
				$defaults['orderby'] = $this->order_by;
			}
			
			$defaults['order'] = 'ASC';
		}
		
		if(isset($args['fields'])) {
			$fields = $args['fields'];
			unset($args['fields']);
			
			$meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();
			foreach($fields as $field => $value) {
				switch($field) {
					case '_parent':
						$args['post_parent'] = $value;
						break;
					default:
						$meta_query[] = array(
							'key' => $this->fieldname($field),
							'value' => $value
						);
				}
			}
			
			$args['meta_query'] = $meta_query;
		}
		
		$args = array_merge(
			array('post_type' => $this->basename),
			array_merge($defaults, is_array($args) ? $args : array())
		);
		
		return $args;
	}
	
	public function pre_get_posts($query) {
		if(is_admin() && $query->query_vars['post_type'] == $this->basename) {
			if(isset($this->order_by)) {
				$fieldnames = $this->fieldnames();
				
				if(in_array($this->order_by, $fieldnames) === true) {
					$query->set('meta_key', $this->fieldname($this->order_by));
					switch($this->fieldtype($this->order_by)) {
						case 'integer': case 'number': case 'date':
							$query->set('orderby', 'meta_value_num');
							break;
						default:
							$query->set('orderby', 'meta_value');
					}
				} else {
					$query->set('orderby', $this->order_by);
				}
				
				$query->set('order', 'ASC');
			}
		}
	}
	
	public function formfield_attrs($attrs, $name) {
		if(strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php')) {
			foreach($this->fieldnames() as $field) {
				$fullname = $this->fieldname($field);
				if($name == $fullname) {
					if(method_exists($this, "get_${field}_attrs")) {
						$new_attrs = call_user_func_array(
							array($this, "get_${field}_attrs")
						);
						
						return array_merge($attrs, $new_attrs);
					}
				}
			}
		}
		
		return $attrs;
	}
	
	public function template_redirect() {
		if((is_single() || is_archive()) && get_post_type() == $this->basename) {
			if(isset($this->user_roles) && is_array($this->user_roles) && count($this->user_roles) > 0) {
				foreach($this->user_roles as $role) {
					if(current_user_can($role)) {
						return;
					}
				}
				
				$GLOBALS['skt_content_forbidden'] = true;
				if(apply_filters('skt_redirect_forbidden', true)) {
					auth_redirect();
				}
			}
		}
	}
	
	public function body_class($classes) {
		if((is_single() || is_archive()) && get_post_type() == $this->basename) {
			if(isset($this->user_roles) && is_array($this->user_roles) && count($this->user_roles) > 0) {
				foreach($this->user_roles as $role) {
					if(current_user_can($role)) {
						return $classes;
					}
				}
				
				$classes[] = 'forbidden';
			}
		}
		
		return $classes;
	}
}