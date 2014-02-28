<?php class SubscriptionPostType extends SktPostType {
	protected $queryable = false;
	protected $fields = array(
		'token' => array('readonly' => 'readonly'),
		'status' => array('readonly' => 'readonly'),
		'transaction_id' => array('label' => 'Transaction ID', 'readonly' => 'readonly'),
		'provider' => array(
			'type' => 'provider:_theme:payment',
			'readonly' => 'readonly'
		),
		'items' => array(
			'type' => 'fieldset',
			'fields' => array(
				'name' => array('readonly' => 'readonly'),
				'amount' => array('type' => 'float', 'readonly' => 'readonly'),
				'description' => array('type' => 'textarea', 'readonly' => 'readonly')
			)
		),
		'cancel_url' => array('label' => 'Cancel URL', 'readonly' => 'readonly'),
		'return_url' => array('label' => 'Return URL', 'readonly' => 'readonly'),
		'start' => array('type' => 'datetime', 'readonly' => 'readonly'),
		'period' => array(
			'type' => 'select',
			'choices' => array(
				'd' => 'Day',
				'w' => 'Week',
				'm' => 'Month',
				'y' => 'Year'
			),
			'readonly' => 'readonly'
		),
		'total' => array('type' => 'float', 'readonly' => 'readonly'),
		'trial_period' => array(
			'type' => 'select',
			'choices' => array(
				'd' => 'Day',
				'w' => 'Week',
				'm' => 'Month',
				'y' => 'Year'
			),
			'readonly' => 'readonly'
		),
		'trial_amount' => array('type' => 'number', 'readonly' => 'readonly'),
		'currency' => array('default' => 'GBP', 'readonly' => 'readonly'),
		'country' => array('default' => 'UK', 'readonly' => 'readonly')
	);
	
	protected $meta_boxes = array(
		'payment' => array(
			'fields' => array('token', 'transaction_id', 'start', 'period', 'total', 'currency', 'country')
		),
		'trial' => array(
			'fields' => array('trial_period', 'trial_amount')
		),
		'urls' => array(
			'label' => 'URLs',
			'fields' => array('cancel_url', 'return_url')
		)
	);
	
	protected $list_fields = array('transaction_id', 'status', 'provider');
	protected $supports = array('author', 'editor', 'excerpt');
	protected $can_add = false;
}