<?php /**
* A base widget class
*
* @package wp-fundaments
*/

class SktWidget {
	public $html = '';
	private $defaults = array(
		'type' => 'text',
		'value' => '',
		'options' => array()
	);
	
	function __construct($name, $options = null) {
		if(is_array($options)) {
			$options = array_merge($this->defaults, $options);
		} else {
			$options = array_merge($this->defaults);
		}
		
		$name = "spektrix_$name";
		extract($options);
		$html = '';
		
		switch($type) {
			case 'color': case 'date': case 'datetime': case 'datetime-local':
			case 'email': case 'month': case 'number': case 'range': case 'text':
			case 'search': case 'tel': case 'time': case 'url': case 'week':
				$html .= '<input name="' . esc_attr($name) . '" type="' . $type . '"';
				if(isset($value) && !empty($value) && !is_null($value)) {
					$html .= ' value="' . esc_attr($value) . '"';
				}
				
				if(isset($placeholder)) {
					$html .= ' placeholder="' . esc_attr($placeholder) . '"';
				}
				
				$html .= ' />';
				break;
			
			case 'select': case 'list':
				if($type == 'list') {
					$name .= '[]';
				}
				
				$html .= '<select name="' . $name . '"';
				
				if($type == 'list') {
					$html .= ' multiple';
				}
				
				$html .= '>';
				foreach($options as $key => $label) {
					$html .= '<option value="' . esc_attr($key) . '"';
					
					if($type == 'list' && is_array($value)) {
						if(in_array($key, $value)) {
							$html .= ' selected';
						}
					} elseif((string)$key == (string)$value) {
						$html .= ' selected';
					}
					
					$html .= '>' . htmlentities($label) . '</option>';
				}
				
				$html .= '</select>';
				break;
		}
		
		$this->html = $html;
	}
	
	function render() {
		echo $this->html;
	}
}