<?php /* Constants for use throught the framework
*
* @package wp-fundaments
*/

define('SKT_DEFAULT_MENU_POSITION', 4);
define('SKT_DEFAULT_CAPABILITY_TYPE', 'page');
define('SKT_DEFAULT_HIERARCHICAL', false);
define('SKT_DEFAULT_FIELD_TYPE', 'text');
define('SKT_DEFAULT_REWRITE', true);

define('SKT_SYNC_FREQ_MINUTE', 60);
define('SKT_SYNC_FREQ_HOUR', SKT_SYNC_FREQ_MINUTE * 60);
define('SKT_SYNC_FREQ_DAY', SKT_SYNC_FREQ_HOUR * 24);
define('SKT_SYNC_FREQ_WEEK', SKT_SYNC_FREQ_DAY * 7);
define('SKT_SYNC_FREQ_MONTH', SKT_SYNC_FREQ_WEEK * 4);
define('SKT_SYNC_FREQ_YEAR', SKT_SYNC_FREQ_DAY * 365);
define('SKT_SYNC_TIMEOUT', 30);