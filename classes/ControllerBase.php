<?php /**
* A base controller class
*
* @package wp-fundaments
*/

class SktFundamentsControllerBase {
	public $menus = array();
	public $notified = false;
	public $ajax_actions = array();
	
	function __construct($baseController = null) {
		$this->baseController = $baseController;
		
		add_action('init', array(&$this, 'init'));
		add_action('wp_loaded', array(&$this, 'loaded'));
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_notices', array(&$this, 'admin_notices'));
		add_action('widgets_init', array(&$this, 'widgets_init'));
		
		foreach($this->ajax_actions as $action) {
			add_action("wp_ajax_skt_spektrix_$action", array(&$this, $action));
			add_action("wp_ajax_nopriv_skt_spektrix_$action", array(&$this, $action));
		}
	}
	
	function init() {
		// init hook
	}
	
	function loaded() {
		// loaded hook
	}
	
	function admin_init() {
		// admin_init hook
	}
	
	function admin_notices() {
		// admin_notices hook
	}
	
	function widgets_init() {
		foreach (glob(dirname(__FILE__) . '/../../plugin/widgets/*.php') as $filename) {
			$name = basename($filename);
			$name = substr($name, 0, strlen($name) - 4);
			
			if(!class_exists('SktFundaments' . $name)) {
				wp_die("Widget '" . $name . "' not found.");
			}
			
			register_widget('SktFundaments' . $name);
		}
	}
	
	function controller($name) {
		$class = 'SktFundaments' . ucwords($name) . 'Controller';
		
		if(!class_exists($class)) {
			$filename = dirname(__FILE__) . '/../../plugin/controllers/' . ucwords($name) . 'Controller.php';
			if(is_file($filename)) {
				require_once($filename);
				if(!class_exists($class)) {
					wp_die("Class '$class' not found in file $filename.");
				}
			} else {
				wp_die("Controller '$name' not found.");
			}
		}
		
		return new $class($this);
	}
	
	function view($name) {
		$controller = substr(get_class($this), 8);
		$controller = strtolower(substr($controller, 0, strlen($controller) - 10));
		
		return new SktFundamentsViewFactory($controller, $name, $this);
	}
	
	function model($name) {
		$class = 'SktFundaments' . ucwords($name);
		
		if(!class_exists($class)) {
			$filename = dirname(__FILE__) . '/../../plugin/models/' . ucwords($name) . '.php';
			
			if(is_file($filename)) {
				require_once($filename);
				if(!class_exists($class)) {
					wp_die("Class '$class' not found in file $filename.");
				}
			} else {
				wp_die("Model '$name' not found.");
			}
		}
		
		return new $class($name);
	}
	
	function admin_menu() {
		foreach($this->menus as $slug => $menu) {
			if(method_exists($this, $menu['action'])) {
				$action = array($this, $menu['action']);
			} else {
				$action = array($this->view($menu['action']), 'render');
			}
			
			add_submenu_page(
				isset($menu['parent']) ? $menu['parent'] : 'options-general.php',
				$menu['page_title'],
				$menu['menu_title'],
				isset($menu['capability']) ? $menu['capability'] : 'administrator',
				$slug, $action
			);
		}
	}
	
	function setting($name, $value = null) {
		if(isset($value) && $value != null) {
			update_option("spektrix_$name", $value);
			return $value;
		} else {
			return get_option("spektrix_$name");
		}
	}
	
	function notice($text) {
		echo '<div class="updated"><p>' . $text . '</p></div>';
		$this->notified = true;
	}
	
	function is_ajax_request() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
}