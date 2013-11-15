<?php

function skt_render_field($field, $attrs = array()) {
	$GLOBALS['skt_fundaments']->input($field, $attrs);
}

function skt_field_label($field) {
	$GLOBALS['skt_fundaments']->label($field);
}

function skt_unserialise_field_value($value, $type = 'text') {
	switch($type) {
		case 'post': case 'media':
			if(is_array($value)) {
				$vv = array();
				foreach($value as $v) {
					$vv[] = get_post($v);
				}
				
				return $vv;
			} else {
				return $value ? get_post($value) : $deafult;
			}
		case 'number':
			return intVal($value);
		case 'checkbox':
			if(is_array($value)) {
				return $value;
			}
			
			return intVal($value) == 1;
		default:
			if(substr($type, 0, 5) == 'post:') {
				if(is_array($value)) {
					$vv = array();
					foreach($value as $v) {
						$vv[] = get_post($v);
					}
					
					return $vv;
				} else {
					return $value ? get_post($value) : $deafult;
				}
			}
			
			return $value;
	}
}

function skt_get_fieldname($field, $post_type = null) {
	if(!$post_type) {
		$post_type = get_post_type(get_the_ID());
	}
	
	$context = $GLOBALS['skt_fundaments'];
	
	if($handler = $context->find_post_type($post_type)) {
		return $handler->fieldname($field);
	} else {
		wp_die("Post type <code>$post_type</code> is not supported by the Fundaments plugin");
	}
	
	return null;
}

function skt_get_field($field, $post_id = null) {
	if(!$post_id) {
		$post_id = get_the_ID();
	}
	
	$type = get_post_type($post_id);
	$context = $GLOBALS['skt_fundaments'];
	
	if($handler = $context->find_post_type($type)) {
		return $handler->get_field($post_id, $field);
	} else {
		wp_die("Post type <code>$type</code> is not supported by the Fundaments plugin");
	}
	
	return null;
}

function skt_the_field($field, $post_id = null) {
	if(!$post_id) {
		$post_id = get_the_ID();
	}
	
	$type = get_post_type($post_id);
	$context = $GLOBALS['skt_fundaments'];
	
	if($handler = $context->find_post_type($type)) {
		$type = $handler->fieldtype($field);
		$value = $handler->get_field($post_id, $field);
		
		switch($type) {
			case 'html':
				echo apply_filters('the_content', $value);
				break;
			default:
				echo htmlentities($value);
		}
	} else {
		wp_die("Post type <code>$type</code> is not supported by the Fundaments plugin");
	}
}

function skt_update_field($field, $value, $post_id = null) {
	if(!$post_id) {
		$post_id = get_the_ID();
	}
	
	$type = get_post_type($post_id);
	$context = $GLOBALS['skt_fundaments'];
	
	if($handler = $context->find_post_type($type)) {
		return $handler->set_field($post_id, $field, $value);
	} else {
		wp_die("Post type <code>$type</code> is not supported by the Fundaments plugin");
	}
	
	return null;
}