<?php /**
* A form input class
*
* @package wp-fundaments
*/
require_once('View.php');
class SktInputView extends SktView {
	function __construct($name, $attrs) {
		if(!is_array($attrs)) {
			$attrs = array();
		}
		
		$attrs = apply_filters('skt_formfield_attrs', $attrs);
		$type = isset($attrs['type']) ? $attrs['type'] : 'text';
		
		if($type == 'media') {
			$this->html = '<div class="skt-media-handler">';
			$this->html .= '<input type="hidden" name="' . esc_attr($name) . '"';
			
			if(isset($attrs['value'])) {
				if(is_object($attrs['value'])) {
					$attrs['value'] = $attrs['value']->ID;
				}
				
				$this->html .= ' value="' . esc_attr($attrs['value']) . '"';
			}
			
			$this->html .= ' />';
			$this->html .= '<button class="button skt-media-upload-button" data-input="' . esc_attr($name) . '">Choose Media</button>';
			$this->html .= '<br /><small class="skt-media-url">';
			
			if(isset($attrs['value'])) {
				$this->html .= '<a href="' . get_attachment_link($attrs['value']) . '" target="_blank">' . get_the_title($attrs['value']) . '</a>';
			}
			
			$this->html .= '</small>';
			$this->html .= '</div>';
			return;
		}
		
		if($type == 'date' || $type == 'datetime') {
			$now = mktime(date('H'), date('i'), 0);
			$now_year = intVal(date('Y', $now));
			$date = isset($attrs['value']) ? intVal($attrs['value']) : $now;
			$year = intVal(date('Y', $date));
			$month = intVal(date('m', $date));
			$day = intVal(date('j', $date));
			$time = date('H:i:s', $date);
			
			if(!isset($attrs['id'])) {
				$id = 'id' . $name;
			} else {
				$id = $attrs['id'];
			}
			
			if(substr($time, strlen($time) - 3) == ':00') {
				$time = substr($time, 0, strlen($time) - 3);
			}
			
			$this->html .= '<div class="skt-date-handler">';
			$this->html .= '<select id="' . $id . '_day" name="' . $name . '_day" class="skt-date-day">';
			for($i = 1; $i <= 31; $i ++) {
				$this->html .= '<option value="' . $i . '"';
				
				if($i == $day) {
					$this->html .= ' selected';
				}
				
				$this->html .= '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
			}
			
			$this->html .= '</select><select id="' . $id . '_month" name="' . $name . '_month" class="skt-date-month">';
			for($i = 1; $i <= 12; $i ++) {
				$this->html .= '<option value="' . $i . '"';
				
				if($i == $month) {
					$this->html .= ' selected';
				}
				
				$this->html .= '>' . date('F', mktime(0, 0, 0, $i, 1, $now_year)) . '</option>';
			}
			
			$this->html .= '</select><select id="' . $id . '_year" name="' . $name . '_year" class="skt-date-year">';
			for($i = $now_year - 50; $i <= $now_year + 50; $i ++) {
				$this->html .= '<option value="' . $i . '"';
				
				if($i == $year) {
					$this->html .= ' selected';
				}
				
				$this->html .= '>' . $i . '</option>';
			}
			
			$this->html .= '</select>';
			
			if($type == 'datetime') {
				'<input id="' . $id . '_time" name="' . $name . '_time" type="text" maxlength="8" size="8" value="' . $time . '" class="skt-date-time" />';
			}
			
			$this->html .= '</div>';
			return;
		}
		
		if($type == 'fieldset') {
			$this->html = '<div class="skt-field-group" id="group-<?php echo esc_attr($name); ?>">';
			$view = new SktFieldset($name,
				isset($attrs['fields']) ? $attrs['fields'] : array(),
				isset($attrs['value']) ? $attrs['value'] : array()
			);
			
			$this->html .= $view->render();
			$this->html .= '</div>';
			return;
		}
		
		if(isset($attrs['value']) && is_object($attrs['value'])) {
			if(isset($attrs['value']->ID)) {
				$attrs['value'] = $attrs['value']->ID;
			} else {
				$attrs['value'] = (string)$attrs['value'];
			}
		}
		
		$tag = 'input';
		$self_closing = true;
		$label_id = '';
		$add_type = true;
		$embed_value = false;
		$label_after = false;
		
		if(!isset($attrs['id'])) {
			$attrs['id'] = 'id' . $name;
		}
		
		switch($type) {
			case 'select':
				$tag = 'select';
				$self_closing = false;
				$add_type = false;
				break;
			case 'textarea':
				$tag = 'textarea';
				$self_closing = false;
				$add_type = false;
				$embed_value = true;
				break;
			case 'html':
				$this->html = '';
				wp_editor(
					isset($attrs['value']) ? $attrs['value'] : '',
					$name,
					array('media_buttons' => false)
				);
				
				return;
			case 'checkbox': case 'radio':
				$label_after = true;
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
					$this->html .= '<ul';
					
					if(isset($attrs['class'])) {
						$this->html .= ' class="' . esc_attr($attrs['class']) . '"';
					}
					
					$this->html .= '>';
					foreach($attrs['choices'] as $key => $value) {
						$this->html .= '<li>';
						$this->html .= '<label><input type="' . $type . '" name="' . $name . '[]" value="' . esc_attr($key) . '"';
						
						if($attrs['value'] && in_array((string)$key, $attrs['value'])) {
							$this->html .= ' checked';
						}
						
						$this->html .= ' /> ' . esc_html($value) . '</label>';
						$this->html .= '</li>';
					}
					
					unset($attrs['choices']);
					unset($attrs['value']);
					$this->html .= '</ul>';
					return;
				} else {
					if($value = isset($attrs['value']) ? $attrs['value'] : '') {
						$attrs['checked'] = true;
					}
					
					$attrs['value'] = '1';
				}
				
				break;
			case 'float':
				$type = 'number';
				if(!isset($attrs['step'])) {
					$attrs['step'] = 'any';
				}
				
				break;
			default:
				if(substr($type, 0, 5) == 'post:') {
					$post_type = substr($type, 5);
					
					if(isset($attrs['multiple'])) {
						$multiple = $attrs['multiple'];
						unset($attrs['multiple']);
					} else {
						$multiple = false;
					}
					
					$value = isset($attrs['value']) ? $attrs['value'] : array();
					if(!is_array($value) && $value) {
						$value = array($value);
					}
					
					if(isset($attrs['value'])) {
						unset($attrs['value']);
					}
					
					$vv = array();
					foreach($value as $v) {
						if(is_object($v) && isset($v->ID)) {
							$vv[] = intVal($v->ID);
						} else {
							$vv[] = intVal($v);
						}
					}
					
					$posts = get_posts(
						array(
							'post_type' => $post_type,
							'posts_per_page' => -1,
							'orderby' => 'post_title',
							'order' => 'ASC'
						)
					);
					
					$this->html .= '<ul';
					if(isset($attrs['class'])) {
						$this->html .= ' class="' . esc_attr($attrs['class']) . '"';
					}
					
					$this->html .= '>';
					foreach($posts as $i => $p) {
						$this->html .= '<li>';
						$this->html .= '<label><input type="' . ($multiple ? 'checkbox' : 'radio') . '" name="' . $name . '[]" value="' . esc_attr($p->ID) . '"';
						if(in_array($p->ID, $vv)) {
							$this->html .= ' checked';
						}
						
						$this->html .= ' /> ' . esc_html($p->post_title) . '</label>';
						$this->html .= '</li>';
					}
					
					$this->html .= '</ul>';
					return;
				}
		}
		
		if($label_after && isset($attrs['label'])) {
			$this->html .= '<label>';
		}
		
		$this->html .= '<' . $tag . ' name="' . $name . '"';
		
		if($add_type) {
			$this->html .= ' type="' . esc_attr($type) . '"';
		}
		
		if(isset($attrs['default'])) {
			if(!isset($attrs['value']) || $attrs['value'] == null) {
				$attrs['value'] = $attrs['default'];
			}
			
			unset($attrs['default']);
		}
		
		foreach($attrs as $attr => $value) {
			if($attr != 'type' && $attr != 'name' && $attr != 'label') {
				if($type == 'select' && ($attr == 'value' || $attr == 'choices')) {
					continue;
				}
				
				if($key == 'value' && $embed_value) {
					continue;
				}
				
				$this->html .= ' ' . $attr;
				if(!is_bool($value)) {
					$this->html .= '="' . esc_attr($value) . '"';
				}
			}
		}
		
		if($self_closing) {
			$this->html .= ' />';
		} else {
			$this->html .= '>';
			
			if($type == 'select' && is_array($attrs['choices'])) {
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
					foreach($attrs['choices'] as $key => $value) {
						$this->html .= '<option value="' . esc_attr($key) . '"';
						if($attrs['value'] && in_array((string)$key, $attrs['value'])) {
							$this->html .= ' selected';
						}
						
						$this->html .= '>' . esc_html($value) . '</option>';
					}
					
					$this->html .= '</' . $tag . '>';
					return;
				}
			} elseif($embed_value && isset($attrs['value'])) {
				$this->html .= stripslashes($attrs['value']);
			}
			
			$this->html .= '</' . $tag . '>';
		}
		
		if($label_after && isset($attrs['label'])) {
			$this->html .= ' ' . esc_html($attrs['label']) . '</label>';
		}
	}
}