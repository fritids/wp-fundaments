<?php /**
 * Helper functions for working with AJAX requests
 *
 * @package wp-fundaments
 */

function skt_is_ajax_request() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function skt_query_json($post_type, $args = array()) {
	$rows = array();
	skt_query($post_type, $args);
	
	$context = $GLOBALS['skt_fundaments'];
	$handler = $context->find_post_type($post_type);
	
	while(have_posts()) {
		the_post();
		$row = array(
			'id' => get_the_ID(),
			'title' => get_the_title(),
			'url' => get_permalink(),
			'date' => get_the_time('U') * 1000,
			'excerpt' => get_the_excerpt(),
			'content' => get_the_content()
		);
		
		foreach($handler->fieldnames() as $field) {
			$row[$field] = $handler->get_field(get_the_ID(), $field);
		}
		
		$rows[] = $row;
	}
	
	wp_reset_query();
	echo json_encode($rows);
}