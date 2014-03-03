<?php require_once('../../../../../../wp-blog-header.php');
skt_load_module('payment');

$theme = basename(get_template_directory());
if($provider = skt_get_provider($theme, 'payment')) {
	$success = $_GET['success'] == '1';
	$token = $_GET['token'];
	$provider->initialise();
	
	try {
		$status = $provider->get_order_status($token);
	} catch (Exception $ex) {
		wp_die($ex->getMessage());
	}
	
	if($success) {
		try {
			$provider->authorise($status);
		} catch (Exception $ex) {
			$orders = new SKT_Query('subscription',
				array(
					'fields' => array('token' => $token)
				)
			);
			
			global $post;
			while($orders->have_posts()) {
				$orders->the_post();
				$url = skt_get_field('cancel_url');
				skt_update_field('status', 'failed');
				do_action('skt_payment_cancelled', $status, $post->ID);
				
				if($url) {
					wp_redirect($url);
					die();
				} else {
					wp_die($ex->getMessage());
				}
			}
		}
	}
	
	$orders = new SKT_Query('subscription',
		array(
			'fields' => array('token' => $token)
		)
	);
	
	global $post;
	while($orders->have_posts()) {
		$orders->the_post();
		
		if($success) {
			$url = skt_get_field('return_url');
			skt_update_field('status', 'complete');
			skt_update_field('transaction_id', $status['transaction_id']);
			do_action('skt_payment_complete', $status, $post->ID);
		} else {
			$url = skt_get_field('cancel_url');
			skt_update_field('status', 'cancelled', $post->ID);
			do_action('skt_payment_cancelled');
		}
		
		if($url) {
			wp_redirect($url);
			die();
		} else {
			wp_die(
				'Your payment has been ' . ($success ? 'completed' : 'cancelled') . '. ' .
				'<a href="' . get_bloginfo('wourl') . '">Return home</a>'
			);
		}
	}
	
	wp_die(
		'No transaction could be found matching the payment provider\'s given token. ' .
		'<a href="' . get_bloginfo('wpurl') . '">Return home</a>'
	);
} else {
	wp_die('Unable to instantiate payment provider');
}