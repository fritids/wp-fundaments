<?php

add_action('init', 'skt_excerpt_init');
function skt_excerpt_init() {
	if(defined('SKT_EXCERPT_LENGTH')) {
		add_filter('excerpt_length', 'skt_filter_excerpt_length', 999);
		function skt_filter_excerpt_length( $length ) {
			return SKT_EXCERPT_LENGTH;
		}
	}
}