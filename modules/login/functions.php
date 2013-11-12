<?php add_action('init', 'skt_login_init', 98);
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