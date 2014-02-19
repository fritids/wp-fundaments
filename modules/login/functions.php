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
			
			if (isset($_REQUEST['checkemail'])) {
				$checkemail = $_REQUEST['checkemail'];
			} else {
				$checkemail = null;
			}
			
			switch($action) {
				case 'lostpassword': case 'retrievepassword':
					skt_password_form();
					die();
				case 'register':
					skt_register_form();
					die();
				default:
					switch($checkemail) {
						case 'registered':
							do_action('skt_register_redirect');
							skt_register_complete();
							die();
					}
					
					skt_login_form();
					die();
			}
		}
	}
	
	add_filter('wp_title','skt_login_title');
	function skt_login_title($title) {
		if(is_file(get_template_directory(). '/wp-login.php')) {
			if (isset($_REQUEST['action'])) {
				$action = $_REQUEST['action'];
			} else {
				$action = 'login';
			}
			
			switch($action) {
				case 'lostpassword': case 'retrievepassword':
					return apply_filters('skt_login_title', __('Reset Your Password'), $action);
				case 'register':
					return apply_filters('skt_login_title', __('Sign Up'), $action);
				default:
					return apply_filters('skt_login_title', __('Log in'), $action);
			}
		}
		
		return $title;
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
					$classes[] = 'wp-lostpassword';
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