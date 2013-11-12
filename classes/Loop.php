<?php /**
* A basic loop class
*
* @package wp-fundaments
*/

class SktLoop {
	private $data = array();
	private $index = 0;
	
	function __construct($data = array()) {
		foreach($data as $index => $value) {
			$this->data[] = $value;
		}
		
		$this->index = 0;
	}
	
	function have_items() {
		return $this->index < count($this->data);
	}
	
	function the_item() {
		$item = $this->data[$this->index];
		$this->index ++;
		return $item;
	}
	
	function rewind() {
		$this->index = 0;
	}
}