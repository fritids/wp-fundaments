<?php

class SKT_Query extends WP_Query {
	function __construct($post_type, $args = array()) {
		$context = $GLOBALS['skt_fundaments'];
		
		if($handler = $context->find_post_type($post_type)) {
			parent::__construct(
				http_build_query(
					$handler->query_args($args)
				)
			);
		} else {
			wp_die("Post type <code>$post_type</code> is not supported by the Fundaments plugin");
		}
	}
} ?>