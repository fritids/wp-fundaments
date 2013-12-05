<?php add_action('load-profile.php', 'skt_profile_init', 98);

if(is_file(get_template_directory(). '/wp-profile.php')) {
	add_theme_support('admin-bar',
		array('callback' => '__return_false')
	);
	
	add_action('wp_head', 'skt_profile_head', 5);
	function skt_profile_head() {
		if(!current_user_can('edit_posts')) { ?>
			<style>#wpadminbar { display: none !important; }</style>
		<?php }
	}
	
	add_action('body_class', 'skt_profile_body_class');
	function skt_profile_body_class($classes) {
		if(in_array('admin-bar', $classes)) {
			$classes = array_diff($classes, array('admin-bar'));
		}
		
		return $classes;
	}
	
	function skt_profile_admin_init() {
		global $pagenow;
		if(!current_user_can('edit_posts') && $pagenow != 'profile.php') {
			global $wp_query;
			$wp_query->is_404 = true;
			get_template_part('404');
			die();
		}
	}
	
	add_action('admin_init', 'skt_profile_admin_init', 1);
}