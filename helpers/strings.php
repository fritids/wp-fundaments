<?php /**
 * String helper functions
 *
 * @package vendorwp-fundaments
 */

function skt_ucwords($words) {
	$littlens = array('a', 'for', 'in', 'it', 'on', 'the', 'to');
	$words = explode(' ', $words);
	$return = array();
	
	foreach($words as $i => $word) {
		if($i == 0) {
			$return[] = ucfirst($word);
			continue;
		}
		
		if(in_array($word, $littlens)) {
			$return[] = $word;
		} else {
			$return[] = ucfirst($word);
		}
	}
	
	return implode(' ', $return);
}