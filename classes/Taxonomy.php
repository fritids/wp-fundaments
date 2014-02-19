<?php /**
 * A base class for creating content taxonomies
 *
 * @package wp-fundaments
 */

abstract class SktTaxonomy extends SktCapable {
	protected $post_type = 'post';
	protected $admin_roles = array('administrator', 'editor');
	
	function __construct($plugin) {
		$this->register_taxonomy();
		parent::__construct($plugin);
		$this->stubborn_capabilities[] = 'edit_' . $this->post_type . 's';
		add_action($this->basename . '_add_form_fields', array(&$this, 'add_form_fields'));
		add_action($this->basename . '_edit_form_fields', array(&$this, 'edit_form_fields'));
		add_action('edited_' . $this->basename, array(&$this, 'save_form_fields'));  
		add_action('create_' . $this->basename, array(&$this, 'save_form_fields'));
	}
	
	protected function admin_capabilities() {
		return array(
			'manage_terms' => 'manage_' . $this->basename . 's',
			'edit_terms' => 'manage_' . $this->basename . 's',
			'delete_terms' => 'manage_' . $this->basename . 's',
			'assign_terms' => 'edit_' . $this->post_type . 's'
		);
	}
	
	protected function user_capabilities() {
		return array('view_' . $this->basename . 's');
	}
	
	private function register_taxonomy() {
		$basename = get_class($this);
		if(substr($basename, strlen($basename) - 8) == 'Taxonomy') {
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
		$singular = isset($this->singular) ? $this->singular : $new_friendly_name;
		$uSingular = skt_ucwords($singular);
		
		$plural = isset($this->plural) ? $this->plural : $singular . 's';
		$uPlural = skt_ucwords($plural);
		
		$args = array(
			'label' => isset($this->label) ? $this->label : $uPlural,
			'labels' => array(
				'name' => isset($this->name) ? $this->name : $uPlural,
				'singular_name' => $uSingular,
				'menu_name' => isset($this->menu_name) ? $this->menu_name : $uPlural,
				'all_items' => isset($this->all_items) ? $this->all_items : "All $uPlural",
				'edit_item' => isset($this->edit_item) ? $this->edit_item : "Edit $uSingular",
				'view_item' => isset($this->view_item) ? $this->view_item : "View $uSingular",
				'update_item' => isset($this->update_item) ? $this->update_item : "Update $uSingular",
				'add_new_item' => isset($this->add_new_item) ? $this->add_new_item : "Add New $uSingular",
				'new_item_name' => isset($this->new_item_name) ? $this->new_item_name : "New $singular name",
				'parent_item' => isset($this->parent_item) ? $this->parent_item : "Parent $uSingular",
				'search_items' => isset($this->search_items) ? $this->search_items : "Search $uPlural",
				'popular_items' => isset($this->popular_items) ? $this->popular_items : "Popular $uPlural",
				'separate_items_with_commas' => isset($this->separate_items_with_commas) ? $this->separate_items_with_commas : "Separate $plural with commas",
				'add_or_remove_items' => isset($this->add_or_remove_items) ? $this->add_or_remove_items : "Add or remove $plural",
				'choose_from_most_used' => isset($this->choose_from_most_used) ? $this->choose_from_most_used : "Choose from the most used $plural",
				'not_found' => isset($this->not_found) ? $this->not_found : "No $plural found"
			),
			'public' => isset($this->public) ? $this->public : true,
			'show_ui' => isset($this->show_ui) ? $this->show_ui : true,
			'show_admin_column' => isset($this->show_admin_column) ? $this->show_admin_column : true,
			'hierarchical' => isset($this->hierarchical) ? $this->hierarchical : true,
			'capabilities' => $this->admin_capabilities()
		);
		
		if(isset($this->parent_item_colon)) {
			$args['parent_item_colon'] = $this->parent_item_colon;
		} elseif(isset($args['parent_item'])) {
			$args['parent_item_colon'] = $args['parent_item'] . ':';
		}
		
		if(isset($this->show_in_nav_menus)) {
			$args['show_in_nav_menus'] = $this->show_in_nav_menus;
		} else {
			$args['show_in_nav_menus'] = $args['public'];
		}
		
		if(isset($this->show_tagcloud)) {
			$args['show_tagcloud'] = $this->show_tagcloud;
		} else {
			$args['show_tagcloud'] = $args['show_ui'];
		}
		
		if(isset($this->sort)) {
			$args['sort'] = $this->sort;
		}
		
		register_taxonomy($this->basename, $this->post_type, $args);
	}
	
	public function add_form_fields() {
		foreach($this->fields as $field => $opts) {
			if(is_array($opts)) {
				$key = $field;
				$attrs = $this->fieldattrs($field);
			} else {
				$key = $opts;
				$attrs = array();
			}
			
			if(!$this->fieldeditable($key)) {
				continue;
			}
			
			$fname = $this->fieldname($key);
			$type = $this->fieldtype($key); ?>
			<div class="form-field">
				<?php if($type != 'boolean') {
					echo $this->fieldlabel($key);
				} ?>
				<?php $GLOBALS['skt_fundaments']->input($fname, $attrs); ?>
			</div>
		<?php }
	}
	
	public function get_field($term, $field, $default = null) {
		$value = skt_unserialise_field_value(
			get_option($this->fieldname($field) . '_' . (is_object($term) ? $term->term_id : $term)),
			$this->fieldtype($field)
		);
		
		if($default == null) {
			$attrs = $this->fieldattrs($field);
			if(isset($attrs['default'])) {
				$default = $attrs['default'];
			}
		}
		
		return $value ? $value : $default;
	}
	
	public function set_field($term, $field, $value) {
		$type = $this->fieldtype($field);
		update_option(
			$this->fieldname($field) . '_' . (is_object($term) ? $term->term_id : $term),
			is_object($value) ? $value->ID : $value
		);
	}
	
	public function delete_field($term, $field) {
		delete_option(
			$this->fieldname($field) . '_' . (is_object($term) ? $term->term_id : $term)
		);
	}
	
	public function edit_form_fields($term) {
		foreach($this->fields as $field => $opts) {
			if(is_array($opts)) {
				$key = $field;
				$attrs = $this->fieldattrs($field);
			} else {
				$key = $opts;
				$attrs = array();
			}
			
			if(!$this->fieldeditable($key)) {
				continue;
			}
			
			$type = $this->fieldtype($key); ?>
			
			<tr class="form-field">
				<th scope="row" valign="top">
					<?php if($type != 'boolean') {
						echo $this->fieldlabel($key);
					} ?>
				</th>
				<td>
					<?php $attrs['value'] = $this->get_field($term, $key);
					$fname = $this->fieldname($key);
					$GLOBALS['skt_fundaments']->input($fname, $attrs); ?>
				</td>
			</tr>
		<?php }
	}
	
	public function save_form_fields($term_id) {
		foreach($this->fieldnames() as $field) {
			if(!$this->fieldeditable($field)) {
				continue;
			}
			
			if($value = $this->POST($field)) {
				$this->set_field($term_id, $field, $value);
			} else {
				$this->delete_field($term_id, $field);
			}
		}
	}
}