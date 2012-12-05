<?php
App::uses('TransactionAddress', 'Transactions.Model');

/**
 * TransactionAddress Test Case
 *
 */
class TransactionAddressTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.transaction_address',
		'app.transaction',
		'app.user',
		'app.transaction_item',
		'app.customer', 
		'app.contact',
		'app.assignee',
		'app.creator',
		'app.modifier'
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TransactionAddress = ClassRegistry::init('Transactions.TransactionAddress');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TransactionAddress);

		parent::tearDown();
	}
    
     /**
 * testAdd method
 *
 * @return void
 */
    public function testAdd() {
        
        
    }

}
