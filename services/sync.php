<?php /**
* A script that syncs posts with a third-party service, and provides realtime feedback
*
* @package wp-fundaments
*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-blog-header.php');
header('HTTP/1.0 200 OK');

function skt_sync_log($text) { ?>
	<script>
		if(typeof(window.parent) != 'undefined' && window.parent != null) {
			window.parent.postMessage(
				{
					'skt_sync_log': <?php echo json_encode($text); ?>
				},
				'<?php echo $_SERVER['HTTP_REFERER']; ?>'
			);
		} else {
			document.write(<?php echo json_encode($text); ?> + '<br />');
		}
	</script>
	<?php echo "$text<br />"; flush();
}

$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

if(!$plugin) {
	wp_die('No plugin specified');
}

if(!$type) {
	wp_die('No type specified');
} ?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Sync</title>
	</head>
	
	<body>
		<?php if($controller = $GLOBALS['skt_fundaments']->get_sync_controller($plugin, $type)) {
			$controller->sync('skt_sync_log');
		} else {
			wp_die('Sync controller not found');
		} ?>
	</body>
</html>