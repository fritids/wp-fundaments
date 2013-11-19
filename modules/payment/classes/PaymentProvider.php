<?php abstract class PaymentProvider extends SktProviderBase {
	public function initialise() {
		$theme = basename(get_template_directory());
		$state = skt_get_setting($theme, 'payment', 'state');
		
		if(is_array($state)) {
			$state = $state[0];
		}
		
		$this->data = array(
			'key' => skt_get_setting($theme, 'payment', "${state}_api_key"),
			'secret' => skt_get_setting($theme, 'payment', "${state}_api_secret"),
			'signature' => skt_get_setting($theme, 'payment', "${state}_api_signature"),
			'state' => $state
		);
	}
	
	protected function cancel_url() {
		return plugins_url('skt-fundaments/modules/payment/services/subscription.php', 'skt-fundaments') .
			'?provider=' . urlencode(get_class($this)) . '&success=0';
	}
	
	protected function return_url() {
		return plugins_url('skt-fundaments/modules/payment/services/subscription.php', 'skt-fundaments') .
			'?provider=' . urlencode(get_class($this)) . '&success=1';
	}
	
	public function create_subscriptions($user_id, $subscriptions, $token = '') {
		$posts = array();
		$title_parts = array();
		
		foreach($subscriptions as $subscription) {
			$total = 0;
			
			foreach($subscription['items'] as $item) {
				$title_parts[] = $item['name'];
				if(isset($item['amount'])) {
					$total += $item['amount'];
				}
			}
			
			$id = wp_insert_post(
				array(
					'post_type' => 'subscription',
					'post_author' => $user_id,
					'post_title' => implode(', ', $title_parts),
					'post_status' => 'publish',
					'post_content' => $subscription['description']
				)
			);
			
			if(isset($subscription['cancel_url'])) {
				skt_update_field('cancel_url', $subscription['cancel_url'], $id);
			}
			
			if(isset($subscription['return_url'])) {
				skt_update_field('return_url', $subscription['return_url'], $id);
			}
			
			skt_update_field('provider', get_class($this), $id);
			skt_update_field('status', 'created', $id);
			skt_update_field('token', $token, $id);
			skt_update_field('total', $total, $id);
			skt_update_field('items', $subscription['items'], $id);
			skt_update_field('currency', $subscription['currency'], $id);
			skt_update_field('country', $subscription['country'], $id);
			skt_update_field('start', $subscription['start'], $id);
			skt_update_field('period', $subscription['period'], $id);
			skt_update_field('total', $total, $id);
			skt_update_field('trial_period', $subscription['trial_period'], $id);
			skt_update_field('trial_amount', intVal($subscription['trial_amount']), $id);
			$posts[] = get_post($id);
		}
		
		return $posts;
	}
	
	public function authorise($token) {
		global $post;
		
		$orders = new SKT_Query('subscription',
			array(
				'fields' => array('token' => $token)
			)
		);
		
		while($orders->have_posts()) {
			$orders->the_post();
			skt_update_field('status', 'authorised', $post->ID);
			if($redirect = skt_get_field('return_url', $post->ID)) {
				wp_redirect($redirect);
				die();
			}
		}
	}
}