<?php
/**
 * TransactionAddressFixture
 *
 */
class TransactionAddressFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'transaction_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'company' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 80, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 80, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'street_address_1' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'street_address_2' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'state' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'zip' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'country' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
		array(
			'id' => '509bd437-ff78-4d67-a95c-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-9190-453d-8f22-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-ed5c-4830-b576-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-46d0-48f8-ad2e-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-a814-481e-84e4-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-037c-4fb9-8f90-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-5d54-46ae-bd9e-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-b790-4b9e-b8d6-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-1104-43c0-87b3-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
		array(
			'id' => '509bd437-7054-4a4b-ae12-169c00000000',
			'transaction_id' => 'Lorem ipsum dolor sit amet',
			'type' => 'Lorem ipsum dolor sit amet',
			'company' => 'Lorem ipsum dolor sit amet',
			'first_name' => 'Lorem ipsum dolor sit amet',
			'last_name' => 'Lorem ipsum dolor sit amet',
			'email' => 'Lorem ipsum dolor sit amet',
			'street_address_1' => 'Lorem ipsum dolor sit amet',
			'street_address_2' => 'Lorem ipsum dolor sit amet',
			'city' => 'Lorem ipsum dolor ',
			'state' => 'Lorem ipsum dolor sit amet',
			'zip' => 'Lorem ipsum dolor ',
			'country' => 'Lorem ipsum dolor sit amet',
			'user_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-11-08 15:48:07',
			'modified' => '2012-11-08 15:48:07'
		),
	);
}
