<?php /**
 * Helper functions for querying custom post types
 *
 * @package wp-fundaments
 */

function skt_query($post_type, $args = array()) {
	$context = $GLOBALS['skt_fundaments'];
	if(!$post_type) {
		wp_die('No post type given');
	}
	
	if($handler = $context->find_post_type($post_type)) {
		query_posts(
			http_build_query(
				$handler->query_args($args)
			)
		);
	} else {
		wp_die("Post type <code>$post_type</code> is not supported by the Fundaments plugin");
	}
}

function skt_get_latest($post_type, $args = array()) {
	skt_query($post_type,
		array_merge(
			$args,
			array(
				'posts_per_page' => 1
			)
		)
	);
	
	$posts = have_posts();
	if($posts) {
		the_post();
	}
	
	return $posts;
}