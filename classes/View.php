<?php /**
* A basic view class
*
* @package wp-fundaments
*/

class SktView {
	public function __construct($html = '') {
		$this->html = $html;
	}
	
	public function render($context = array()) {
		echo $this->html;
	}
}