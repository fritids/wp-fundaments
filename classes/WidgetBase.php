<?php /**
 * A base WP_Widget class
 *
 * @package wp-fundaments
 */

class SktWidgetBase extends WP_Widget {
	protected $plugin;
	protected $factory;
	
	public function __construct() {
		$this->factory = $GLOBALS['skt_fundaments']->get_widget(
			$this->plugin, $this->factory
		);
		
		parent::__construct(
			$this->factory->basename,
			__($this->factory->title, $this->plugin),
			array(
				'description' => $this->factory->description
			)
		);
	}
	
	public function widget($args, $instance) {
		echo $args['before_widget'];
		
		$new_instance = array();
		foreach($this->factory->fieldnames() as $field) {
			$attrs = $this->factory->fieldattrs($field);
			$type = isset($attrs['type']) ? $attrs['type'] : 'text';
			
			if(isset($instance[$field])) {
				$new_instance[$field] = skt_unserialise_field_value(
					$instance[$field], $type
				);
			}
		}
		
		$this->factory->render($new_instance);
		echo $args['after_widget'];
	}
	
 	public function form($instance) {
		$this->factory->form($instance, $this);
	}
	
	public function update($new_instance, $old_instance) {
		return $this->factory->update($new_instance);
	}
}