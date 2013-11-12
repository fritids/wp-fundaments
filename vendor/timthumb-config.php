<?php $current_root = realpath(dirname(__file__) . '/../../../../');
$doc_root = $_SERVER['DOCUMENT_ROOT'];

if($current_root != $doc_root) {
	error_log(
		'Document root from the auto-discovered directory. ' .
		'This will only work for sites where WordPress is installed in the web root directory.'
	);
	
	define('FILE_CACHE_DIRECTORY',
		$doc_root . '/wp-content/timthumb.cache'
	);
} else {
	define('FILE_CACHE_DIRECTORY',
		$current_root . '/wp-content/timthumb.cache'
	);
}