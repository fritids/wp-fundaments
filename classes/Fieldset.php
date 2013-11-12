<?php /**
 * A class for managing custom fieldsets
*
* @package wp-fundaments
*/

class SktFieldset extends SktFieldManager {
	protected $name;
	protected $fields = array();
	protected $values = array();
	
	function __construct($name, $fields, $values) {
		$this->name = $name;
		$this->fields = $fields;
		$this->values = $values;
	}
	
	private function row($index, $values = array()) {
		$html = '';
		
		foreach($this->fieldnames() as $field) {
			$attrs = $this->fieldattrs($field);
			$label = isset($attrs['label']) ? $attrs['label'] : $this->fieldlabel($field);
			$type = $this->fieldtype($field);
			
			if(isset($attrs['label'])) {
				unset($attrs['label']);
			}
			
			$attrs['value'] = isset($values[$field]) ? $values[$field] : null;
			$name = $this->name . '__' . $index . '__' . $field;
			$view = new SktInputView($name, $attrs);
			$html .= '<td class="skt-field-' . $type . '">' . $view->html . '</td>';
		}
		
		$html .= '<td class="skt-field-delete">';
		$html .= '<button class="button skt-delete-row">-</button>';
		$html .= '</td>';
		
		return $html;
	}
	
	function render() {
		$html = '<table class="skt-fieldset-table" data-prefix="' . esc_attr($this->name) . '"><thead><tr>';
		$mgmt = array();
		
		foreach($this->fieldnames() as $field) {
			$attrs = $this->fieldattrs($field);
			$label = isset($attrs['label']) ? $attrs['label'] : $this->fieldlabel($field);
			$type = $this->fieldtype($field);
			
			if($type == 'fieldset') {
				wp_die('Nested fieldsets are currently not supported');
			}
			
			if(isset($attrs['label'])) {
				unset($attrs['label']);
			}
			
			$html .= '<th class="skt-field-' . $type . '">' . htmlentities($label) . '</th>';
			$mgmt[$field] = $attrs;
		}
		
		$html .= '<th class="skt-field-delete"></th>';
		$html .= '</tr></thead><tbody>';
		
		if(count($this->values)) {
			foreach($this->values as $i => $value) {
				$html .= '<tr>';
				$html .= $this->row($i, $value);
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr class="skt-empty-row"><td colspan="' . count($this->fields) . '">Click the <strong>Add New</strong> button to add some data to this field</td></tr>';
		}
		
		$html .= '<tr class="skt-new-row">';
		$html .= $this->row(-1);
		$html .= '</tr>';
		
		$html .= '</tbody></table>';
		$html .= '<input type="hidden" name="' . esc_attr($this->name . '__mgmt') . '" value="' . base64_encode(serialize($this->fields)) . '" />';
		$html .= '<input type="hidden" name="' . esc_attr($this->name . '__count') . '" value="' . count($this->values) . '" />';
		$html .= '<button class="button skt-add-row">' . _('Add New') . '</button>';
		return $html;
	}
}