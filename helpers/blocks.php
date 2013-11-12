<?php /**
 * Helper functions for content block management
 *
 * @package wp-fundaments
 */

function skt_block($name) {
	
}

function skt_register_block_type() {
	if(current_theme_supports('skt_blocks')) {
		// register_post_type('skt_block',
		// 			array(
		// 				'label' => 'Content Block',
		// 				'labels' => array(
		// 					'name' => 'Content Blocks',
		// 					'singular_name' => 'Block',
		// 					'add_new_item' => 'Add New Block',
		// 					'edit_item' => 'Edit Block',
		// 					'new_item' => 'New Block',
		// 					'view_item' => 'View Block',
		// 					'search_items' => 'Search Blocks',
		// 					'not_found' => 'No blocks found',
		// 					'not_found_in_trash' => 'No blocks found in trash'
		// 				),
		// 				'description' => 'Custom blocks of content',
		// 				'public' => true,
		// 				'publicly_queryable' => false,
		// 				'capability_type' => 'page',
		// 				'supports' => array('title', 'slug', 'editor'),
		// 				'hierarchical' => false
		// 			)
		// 		);
	}
}

add_action('init', 'skt_register_block_type');