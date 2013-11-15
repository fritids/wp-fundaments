<?php /**
 * A base class for creating content types
 *
 * @package wp-fundaments
 */

abstract class SktPostType extends SktFieldManager {
	protected $supports = array('title', 'slug', 'editor', 'thumbnail');
	
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
		
		$this->plugin = $plugin;
		$this->register_post_type();
		
		if(method_exists($this, "the_content")) {
			add_filter('the_content', array(&$this, 'the_contents'));
		}
		
		if(isset($this->list_fields)) {
			add_filter('manage_' . $this->basename . '_posts_columns', array(&$this, 'post_columns'));
			add_action('manage_' . $this->basename . '_posts_custom_column', array(&$this, 'post_columns_data'));
		}
		
		add_action('save_post', array(&$this, 'save_post'));
		add_filter('post_type_link', array(&$this, 'permalinks'), 10, 3);
		add_action('pre_get_posts', array(&$this, 'pre_get_posts'));
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
		
		$args = array(
			'label' => isset($this->label) ? $this->label : skt_ucwords($new_friendly_name),
			'labels' => array(
				'name' => isset($this->name) ? $this->name : skt_ucwords($new_friendly_name . 's'),
				'singular_name' => isset($this->singular_name) ? $this->singular_name : skt_ucwords($new_friendly_name),
				'add_new_item' => isset($this->add_new_item) ? $this->add_new_item : ('Add New ' . skt_ucwords($new_friendly_name)),
				'edit_item' => isset($this->edit_item) ? $this->edit_item : ('Edit ' . skt_ucwords($new_friendly_name)),
				'new_item' => isset($this->new_item) ? $this->new_item : ('New ' . skt_ucwords($new_friendly_name)),
				'view_item' => isset($this->view_item) ? $this->view_item : ('View ' . skt_ucwords($new_friendly_name)),
				'search_items' => isset($this->search_items) ? $this->search_items : ('Search ' . skt_ucwords($new_friendly_name) . 's'),
				'not_found' => isset($this->not_found) ? $this->not_found : ('No ' . $new_friendly_name . 's found'),
				'not_found_in_trash' => isset($this->not_found_in_trash) ? $this->not_found_in_trash : ('No ' . $new_friendly_name . 's found in trash')
			),
			'description' => isset($this->description) ? $this->description : 'Custom post type',
			'public' => isset($this->public) ? $this->public : true,
			'show_ui' => isset($this->show_ui) ? $this->show_ui : true,
			'menu_position' => isset($this->menu_position) ? $this->menu_position : SKT_DEFAULT_MENU_POSITION,
			'capability_type' => isset($this->capability_type) ? $this->capability_type : SKT_DEFAULT_CAPABILITY_TYPE,
			'supports' => $this->supports,
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
		
		if(isset($this->capabilities)) {
			$args['capabilities'] = $thiscapabilities;
		}
		
		register_post_type($this->basename, $args);
		// flush_rewrite_rules();
	}
	
	public function get_field($post, $field, $default = null) {
		if($field == '_parent') {
			$ancestors = get_post_ancestors(is_object($post) ? $post->ID : $post);
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
			get_post_meta(is_object($post) ? $post->ID : $post, $this->fieldname($field), true),
			$this->fieldtype($field)
		);
		
		return $value ? $value : $default;
	}
	
	public function set_field($post, $field, $value) {
		$type = $this->fieldtype($field);
		if($field == '_parent') {
			wp_update_post(
				array(
					'ID' => is_object($post) ? $post->ID : $post,
					'post_parent' => is_object($value) ? $value->ID : $value
				)
			);
		} else {
			update_post_meta(is_object($post) ? $post->ID : $post, $this->fieldname($field), $value);
		}
	}
	
	public function delete_field($post, $field) {
		delete_post_meta(is_object($post) ? $post->ID : $post, $this->fieldname($field));
	}
	
	public function register_meta_boxes() {
		$fields = $this->fieldnames();
		$handled_fields = array();
		
		if(isset($this->meta_boxes) && is_array($this->meta_boxes)) {
			foreach($this->meta_boxes as $key => $box) {
				$view = 'post_types/' . $this->basename . '/meta/' . (is_array($box) ? $key : $box);
				$func = '$g = $GLOBALS[\'skt_fundaments\']; ';
				$func .= '$p = $g->get_post_type("' . $this->plugin . '", "' . $this->basename . '"); ';
				$func .= 'global $post; ';
				$has_view = false;
				
				if($GLOBALS['skt_fundaments']->view_exists($this->plugin, $view)) {
					$func .= '$g->view("' . $this->plugin . '", "' . $view . '", array("post" => $post, ';
					
					if(isset($this->fields)) {
						foreach($fields as $i => $field) {
							$func .= '"' . $field . '" => $p->get_field($post, "' . $field . '")';
							if($i < count($fields) - 1) {
								$func .= ', ';
							}
							
							$handled_fields[] = $field;
						}
					}
					
					$func .= '));';
					$has_view = true;
				}
				
				if(is_array($box)) {
					if(!$has_view) {
						if(isset($box['fields']) && is_array($box['fields'])) {
							foreach($box['fields'] as $field) {
								switch($field) {
									case '_parent':
										$func .= 'echo $p->fieldlabel("' . $field . '") . "<br />"; $g->input($p->fieldname("' . $field . '"), $p->fieldattrs("' . $field . '", array("type" => "post:' . $this->parent . '", "value" => $p->get_field($post, "' . $field . '")))); print("<br />");';
										break;
									default:
										if($this->fieldtype($field) != 'boolean') {
											$func .= 'echo $p->fieldlabel("' . $field . '") . "<br />";';
										}
										
										$func .= 'echo $g->input($p->fieldname("' . $field . '"), $p->fieldattrs("' . $field . '", array("value" => $p->get_field($post, "' . $field . '")))); print("<br />");';
								}
								
								$handled_fields[] = $field;
							}
						}
					}
					
					add_meta_box(
						$this->basename . '_' . $key,
						isset($box['title']) ? $box['title'] : skt_ucwords(str_replace('_', ' ', $key)),
						create_function('', $func),
						$this->basename,
						isset($box['context']) ? $box['context'] : 'normal',
						isset($box['priority']) ? $box['priority'] : 'core'
					);
				} else {
					add_meta_box(
						$this->basename . '_' . $box,
						skt_ucwords(str_replace('_', ' ', $box)),
						create_function('', $func),
						$this->basename
					);
				}
			}
		}
		
		foreach($fields as $field) {
			if(in_array($field, $handled_fields)) {
				continue;
			}
			
			$attrs = $this->fieldattrs($field);
			if(is_array($attrs) && isset($attrs['visible']) && !$attrs['visible']) {
				return;
			}
			
			$func = '$g = $GLOBALS[\'skt_fundaments\']; ';
			$func .= '$p = $g->get_post_type("' . $this->plugin . '", "' . $this->basename . '"); ';
			$func .= 'global $post; ';
			
			switch($field) {
				case '_parent':
					$func .= '$g->input($p->fieldname("' . $field . '"), $p->fieldattrs("' . $field . '", array("type" => "post:' . $this->parent . '", "value" => $p->get_field($post, "' . $field . '")))); print("<br />");';
					break;
				default:
					$func .= '$g->input($p->fieldname("' . $field . '"), $p->fieldattrs("' . $field . '", array("value" => $p->get_field($post, "' . $field . '")))); print("<br />");';
			}
			
			add_meta_box(
				$this->basename . '_' . $field,
				$this->fieldlabel($field),
				create_function('', $func),
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
			return $this->the_content($post, $content);
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
}