<?php /**
 * A class to handle calls to AJAX functions defined in Fundaments
 *
 * @package wp-fundaments
 */

class SktAjaxCall {
	public function __construct($filename) {
		$this->filename = $filename;
	}
	
	function action() {
		require_once($this->filename);
		die();
	}
}