<?php

function skt_render_field($field, $attrs = array()) {
	$GLOBALS['skt_fundaments']->input($field, $attrs);
}

function skt_field_label($field) {
	$GLOBALS['skt_fundaments']->label($field);
}