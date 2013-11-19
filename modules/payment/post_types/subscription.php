<?php class SubscriptionPostType extends SktPostType {
	protected $queryable = false;
	protected $fields = array(
		'token', 'status',
		'provider' => array(
			'type' => 'provider:_theme:payment'
		),
		'items' => array(
			'type' => 'fieldset',
			'fields' => array(
				'name',
				'amount' => array('type' => 'float'),
				'description' => array('type' => 'textarea')
			)
		),
		'cancel_url' => array('label' => 'Cancel URL'),
		'return_url' => array('label' => 'Return URL'),
		'start' => array('type' => 'datetime'),
		'period' => array(
			'type' => 'select',
			'choices' => array(
				'd' => 'Day',
				'w' => 'Week',
				'm' => 'Month',
				'y' => 'Year'
			)
		),
		'total' => array('type' => 'float'),
		'trial_period' => array(
			'type' => 'select',
			'choices' => array(
				'd' => 'Day',
				'w' => 'Week',
				'm' => 'Month',
				'y' => 'Year'
			)
		),
		'trial_amount' => array('type' => 'number'),
		'currency' => array('default' => 'GBP'),
		'country' => array('default' => 'UK')
	);
	
	protected $meta_boxes = array(
		'payment' => array(
			'fields' => array('token', 'start', 'period', 'total', 'currency', 'country')
		),
		'trial' => array(
			'fields' => array('trial_period', 'trial_amount')
		),
		'urls' => array(
			'label' => 'URLs',
			'fields' => array('cancel_url', 'return_url')
		)
	);
	
	protected $list_fields = array('token', 'status', 'provider');
	protected $supports = array('author', 'editor', 'excerpt');
	protected $can_add = false;
}