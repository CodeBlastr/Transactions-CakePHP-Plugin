<?php
/**
 * TransactionAcoFixture
 *
 */
class TransactionAcoFixture extends CakeTestFixture {

/**
 * Import
 *
 * @var array
 */
	public $import = array('config' => 'Aco');
	

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'parent_id' => null,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'controllers',
			'lft' => 1,
			'rght' => 12,
			'type' => 'controller',
		),
		array(
			'id' => 2,
			'parent_id' => 1,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'Admin',
			'lft' => 2,
			'rght' => 3,
			'type' => 'controller',
		),
		array(
			'id' => 3,
			'parent_id' => 1,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'Transactions',
			'lft' => 4,
			'rght' => 11,
			'type' => 'controller',
		),
		array(
			'id' => 4,
			'parent_id' => 3,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'Transactions',
			'lft' => 5,
			'rght' => 10,
			'type' => 'controller',
		),
		array(
			'id' => 5,
			'parent_id' => 3,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'index',
			'lft' => 6,
			'rght' => 7,
			'type' => 'controller',
		),
		array(
			'id' => 6,
			'parent_id' => 3,
			'model' => null,
			'foreign_key' => null,
			'alias' => 'edit',
			'lft' => 8,
			'rght' => 9,
			'type' => 'controller',
		),
	);
}
