<?php /**
 * A basic widget class
 *
 * @package wp-fundaments
 */

abstract class SktWidget extends SktFieldManager {
	public $basename = '';
	public $plugin = '';
	public $title = '';
	public $description = '';
	
	function __construct($plugin) {
		$this->plugin = $plugin;
		$basename = get_class($this);
		if(substr($basename, strlen($basename) - 6) == 'Widget') {
			$basename = substr($basename, 0, strlen($basename) - 6);
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
		
		if(!$this->title) {
			$this->title = $new_basename;
		}
		
		$this->basename = strtolower($new_basename);
	}
	
	function register() {
		$cls = 'SKT_Widget_' . ucfirst($this->basename);
		eval("class $cls extends SktWidgetBase { protected \$plugin = '" . $this->plugin . "'; protected \$factory = '" . get_class($this) . "'; };");
		register_widget($cls);
	}
	
	function render($data) { ?>
		<p>Define a <code>render</code> method in your <code><?php echo get_class($this); ?></code> class to print out the HTML</p>
	<?php }
	
	function form($instance, $widget) {
		foreach($this->fieldnames() as $key) {
			$opts = $this->fieldattrs($key);
			if (isset($instance[$key])) {
				$opts['value'] = $instance[$key];
			} elseif(isset($opts['default'])) {
				$opts['value'] = $opts['default'];
				unset($opts['default']);
			}
			
			$field = new SktInputView($widget->get_field_name($key), $opts);
			echo('<p>');
			
			if($type != 'boolean') {
			 	echo($this->fieldlabel($key, 'id' . $widget->get_field_name($key)) . '<br />');
			}
			
			$field->render();
			echo('</p>');
		}
	}
	
	function update($data) {
		$instance = array();
		foreach($this->fieldnames() as $field) {
			$instance[$field] = (!empty($data[$field])) ? strip_tags($data[$field]) : '';
		}
		
		return $instance;
	}
}