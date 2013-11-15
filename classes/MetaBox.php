<?php /**
* A basic meta-box class
*
* @package wp-fundaments
*/

class SktMetaBox extends SktView {
	function __construct($post_type, $view, $fields = array(), $label = false) {
		$this->post_type = $post_type;
		$this->view = $view;
		$this->fields = $fields;
		$this->label = $label;
	}
	
	function render() {
		global $post;
		
		if($this->view && $GLOBALS['skt_fundaments']->view_exists($this->post_type->plugin, $this->view)) {
			$context = array('post' => $post);
			
			foreach($this->post_type->fieldnames() as $field) {
				$context[$field] = $this->post_type->get_field($post, $field);
				$handled_fields[] = $field;
			}
			
			$GLOBALS['skt_fundaments']->view($this->post_type->plugin, $this->view, $context);
		} elseif(count($this->fields) > 0) {
			foreach($this->fields as $field) {
				switch($field) {
					case '_parent':
						echo $this->post_type->fieldlabel($field) . '<br />';
						echo $GLOBALS['skt_fundaments']->input(
							$this->post_type->fieldname($field),
							$this->post_type->fieldattrs($field,
								array(
									'type' => 'post:' . $this->post_type->parent,
									"value" => $p->get_field($post, $field)
								)
							)
						);
						
						echo '<br />';
						break;
					default:
						$type = $this->post_type->fieldtype($field);
						$attrs = $this->post_type->fieldattrs($field,
							array(
								'value' => $this->post_type->get_field($post, $field)
							)
						);
						
						if($this->label) {
							echo $this->post_type->fieldlabel($field) . '<br />';
						}
						
						echo $GLOBALS['skt_fundaments']->input(
							$this->post_type->fieldname($field),
							$attrs
						);
						
						echo '<br />';
				}
			}
		}
	}
}