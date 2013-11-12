<?php /**
 * Helper functions for email sending
 *
 * @package wp-fundaments
 */

function skt_render_mail($to, $template, $context = array()) {
	return $GLOBALS['skt_fundaments']->render_to_mail($to, $template, $context);
}