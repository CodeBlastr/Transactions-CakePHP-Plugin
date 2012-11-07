<?php
/**
 * TransactionItemFixture
 *
 */
class TransactionItemFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'transaction_payment_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'transaction_shipment_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'transaction_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'quantity' => array('type' => 'float', 'null' => false, 'default' => '1'),
		'price' => array('type' => 'float', 'null' => false, 'default' => '0'),
		'weight' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'height' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'width' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'length' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => true, 'default' => 'incart', 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => '\'\',\'pending\',\'sent\',\'successful\',\'paid\',\'frozen\',\'cancelled\',\'incart\',\'requestReturn\',\'return\'', 'charset' => 'utf8'),
		'tracking_no' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'location' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'deadline' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'arb_settings' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'payment_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'featured' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'foreign_key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_virtual' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'hours_expire' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'comment' => 'Used to denote how long a catalog item should be available after purchase.'),
		'customer_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'contact_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'assignee_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'creator_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modifier_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// two items for the Customer.id 5738299d-9040-43c9-85b1-22d400000000
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000001',
			'name' => 'Test Item #1',
			'transaction_payment_id' => '',
			'transaction_shipment_id' => '',
			'transaction_id' => '5077241d-9040-43c9-85b1-22d400000000',
			'quantity' => 1,
			'price' => 1,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000000',
			'contact_id' => '',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
		// this one might acutally be simulating if one was added to the cart during checkout? not having a Transaction.id ?
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000002',
			'name' => 'Test Item #2',
			'transaction_payment_id' => '',
			'transaction_shipment_id' => '',
			'transaction_id' => '',
			'quantity' => 1,
			'price' => 1,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'payment_type' => 'Lorem ipsum dolor sit amet',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000000',
			'contact_id' => '',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:45:55',
			'modified' => '2012-10-11 21:45:55'
		),
		// a single item for the logged in User.id 1
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000003',
			'name' => 'Test Item #3',
			'transaction_payment_id' => '',
			'transaction_shipment_id' => '',
			'transaction_id' => '',
			'quantity' => 1,
			'price' => 1,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '1',
			'contact_id' => '',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
	);
}
