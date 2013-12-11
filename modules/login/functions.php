<?php if(empty($_GET['interim-login']) || $_GET['interim-login'] != '1') {
	add_action('init', 'skt_login_init', 98);
	function skt_login_init() {
		$path = get_template_directory(). '/wp-login.php';
		if(is_file($path)) {
			require(ABSPATH . '/wp-load.php');
			
			if (isset($_REQUEST['action'])) {
				$action = $_REQUEST['action'];
			} else {
				$action = 'login';
			}
			
			add_filter('wp_title','skt_login_title');
			function skt_login_title() {
				return 'Login';
			}
			
			switch($action) {
				case 'lostpassword': case 'retrievepassword':
					skt_password_form();
					break;
				case 'register':
					skt_register_form();
					break;
				default:
					skt_login_form();
			}
			
			die();
		}
	}
	
	add_filter('body_class', 'skt_login_body_class');
	function skt_login_body_class($classes) {
		$path = get_template_directory(). '/wp-login.php';
		if(is_file($path)) {
			if (isset($_REQUEST['action'])) {
				$action = $_REQUEST['action'];
			} else {
				$action = 'login';
			}
			
			switch($action) {
				case 'lostpassword': case 'retrievepassword':
					$classes[] = 'wp-lost-password';
					break;
				case 'register':
					$classes[] = 'wp-register';
					break;
				default:
					if(isset($_GET['loggedout']) && $_GET['loggedout'] == 'true') {
						$classes[] = 'wp-logged-out';
					}
					
					$classes[] = 'wp-login';
			}
		}
		
		return $classes;
	}
}