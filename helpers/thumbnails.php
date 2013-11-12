<?php /**
 * Helper functions to register settings pages
 *
 * @package wp-fundaments
 */

function skt_get_thumbnail($width, $height = null, $post = null) {
	if(!$height) {
		$height = $width;
	}
	
	if(!$post) {
		$post = get_post_thumbnail_id(get_the_ID());
	}
	
	$src = wp_get_attachment_image_src($post, 'full');
	if(!is_array($src) || !count($src)) {
		return null;
	}
	
	return skt_get_timbhumb_url($src[0],
		array(
			'w' => $width,
			'h' => $height
		)
	);
}

function skt_the_thumbnail($width, $height = null, $post = null) {
	if($url = skt_get_thumbnail($width, $height, $post)) {
		echo $url;
	}
}