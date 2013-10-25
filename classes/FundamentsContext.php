<?php /**
* A class that allows plugins to refer to a single Fundaments context
*
* @package wp-fundaments
*/

class SktFundamentsContext {
	private $post_types = array();
	
	function input($name, $attrs = array()) {
		if(!is_array($attrs)) {
			$attrs = array();
		}
		
		$type = isset($attrs['type']) ? $attrs['type'] : 'text';
		if(isset($attrs['value']) && is_object($attrs['value'])) {
			if(isset($attrs['value']->ID)) {
				$attrs['value'] = $attrs['value']->ID;
			} else {
				$attrs['value'] = (string)$attrs['value'];
			}
		}
		
		$tag = 'input';
		$html = '';
		$self_closing = true;
		$label_id = '';
		
		switch($type) {
			case 'select':
				$tag = 'select';
				$self_closing = false;
				break;
			case 'checkbox': case 'radio':
				if(isset($attrs['choices'])) {
					if(isset($attrs['value'])) {
						if(!is_array($attrs['value'])) {
							$attrs['value'] = array((string)$attrs['value']);
						}
					} else {
						$attrs['value'] = array();
					}
					
					$nv = array();
					foreach($attrs['value'] as $v) {
						$nv[] = (string)$v;
					}
					
					$attrs['value'] = $nv;
					foreach($attrs['choices'] as $key => $value) { ?>
						<label>
							<input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo $key; ?>"<?php if($attrs['value'] && in_array((string)$key, $attrs['value'])) { ?> checked<?php } ?> />
							<?php echo esc_html($value); ?>
						</label><br />
					<?php }
					unset($attrs['choices']);
					unset($attrs['value']);
					return;
				}
			default:
				if(substr($type, 0, 5) == 'post:') {
					$post_type = substr($type, 5);
					if(isset($attrs['value'])) {
						$attrs['value'] = get_post($attrs['value']);
						if($attrs['value']) {
							$attrs['value'] = $attrs['value']->ID;
						}
					}
					
					$posts = get_posts(array('post_type' => $post_type));
					foreach($posts as $i => $p) { ?>
						<label>
							<input type="radio" name="<?php echo $name; ?>" value="<?php echo $p->ID; ?>"<?php if($attrs['value'] && (string)$attrs['value'] == (string)$p->ID) { ?> checked<?php } ?> />
							<?php echo esc_html($p->post_title); ?>
						</label>
					<?php if($i < count($posts) - 1) {
							echo('<br />');
						}
					}
					
					return;
				}
		}
		
		if(!isset($attrs['id'])) {
			$attrs['id'] = 'id' . $name;
		}
		
		if(isset($attrs['label'])) {
			$html = '<label for="' . $attrs['id'] . '">' . esc_html($attrs['label']) . '</label>';
			unset($attrs['label']);
		} else {
			$html = '';
		}
		
		$html .= '<' . $tag . ' name="' . $name . '" type="' . $type . '"';
		foreach($attrs as $attr => $value) {
			$html .= ' ' . $attr . '="' . esc_attr($value) . '"';
		}
		
		if($self_closing) {
			$html .= ' />';
		} else {
			$html .= '></' . $tag . '>';
		}
		
		echo $html;
	}
	
	function label($name) {
		echo ucwords(str_replace('_', ' ', $name));
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
	
	function add_post_type($plugin, $post_type_name, $post_type_class) {
		$this->post_types[$plugin][$post_type_name] = new $post_type_class($plugin);
	}
	
	function get_post_type($plugin, $post_type) {
		return $this->post_types[$plugin][$post_type];
	}
}