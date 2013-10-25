<?php /**
* A base form controller class
*
* @package wp-fundaments
*/

abstract class SktFundamentsFormControllerBase extends SktFundamentsControllerBase {
	function widget($name, $options = null) {
		return new Widget($name, $options);
	}
}