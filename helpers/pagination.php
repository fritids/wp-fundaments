<?php /**
* Pagination helper functions
*
* @package vendorwp-fundaments
*/

function skt_have_pagination_links() {
	if(!isset($GLOBALS['skt_pagination_index'])) {
		$GLOBALS['skt_pagination_index'] = 1;
	} else {
		$GLOBALS['skt_pagination_index'] ++;
	}
	
	global $wp_query;
	if((int)$GLOBALS['skt_pagination_index'] <= (int)$wp_query->max_num_pages) {
		return true;
	} else {
		unset($GLOBALS['skt_pagination_index']);
	}
}

function skt_is_current_page() {
	if(!isset($GLOBALS['skt_pagination_index'])) {
		$GLOBALS['skt_pagination_index'] = 1;
	}
	
	return get_query_var('paged') ? (
		(int)get_query_var('paged') == $GLOBALS['skt_pagination_index']
	) : (
		$GLOBALS['skt_pagination_index'] == 1
	);
}

function skt_pagination_number() {
	echo isset($GLOBALS['skt_pagination_index']) ? $GLOBALS['skt_pagination_index'] : 1;
}

function skt_pagination_link() {
	if(!isset($GLOBALS['skt_pagination_index'])) {
		$GLOBALS['skt_pagination_index'] = 1;
	}
	
	echo get_pagenum_link(
		$GLOBALS['skt_pagination_index']
	);
}