<?php
// Buyable Test
App::uses('BuyableBehavior', 'Transactions.Model/Behavior');


if (!class_exists('TransactionArticle')) {
	class TransactionArticle extends CakeTestModel {
	/**
	 *
	 */
		public $callbackData = array();

	/**
	 *
	 */
		public $actsAs = array(
			'Transactions.Buyable'
			);
	/**
	 *
	 */
		public $useTable = 'transaction_articles';

	/**
	 *
	 */
		public $name = 'Article';
	/**
	 *
	 */
		public $alias = 'Article';
	}
}


if (!class_exists('MockSession')) {
	class MockSession {
	/**
	 * read
	 */
		public function read() {
			return array('Auth' => array(
				'User', array(
					'id' => 2,
					'username' => 'admin',
					)
				));
		}
	}
}


/**
 * BuyableBehavior Test Case
 *
 */
class BuyableBehaviorTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Products.Product',
		
		'plugin.Transactions.TransactionArticle',
		'plugin.Transactions.Transaction',
		'plugin.Transactions.TransactionTax',
		'plugin.Transactions.TransactionItem',
		'plugin.Transactions.TransactionAddress',
		
		
		'plugin.Users.User',
		'plugin.Contacts.Contact',
		'plugin.Connections.Connection',
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Buyable = new BuyableBehavior();
		$this->Article = Classregistry::init('TransactionArticle');
		$this->Product = Classregistry::init('Products.Product');
		$this->Transaction = Classregistry::init('Transactions.Transaction');
		
		if (!class_exists('CakeSession')) {
			App::uses('CakeSession', 'Model/Datasource'); 
		} 
	}

/**
 * tearDown method
 *
 * @return void
 */
    public function tearDown() {
		unset($this->Draftable);
		unset($this->Article);
		unset($this->Product);
		unset($this->Transaction);
		ClassRegistry::flush();

		parent::tearDown();
	}
	

/**
 * Test behavior instance
 *
 * @return void
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Article->Behaviors->Buyable, 'BuyableBehavior'));
	}
	
/**
 * Test Buying
 */
	public function testPaysimple() {
		CakeSession::write('Auth.User.id', '2');
		$data = array(
			'TransactionAddress' => array(
				array(
					'email' => 'unit-test@razorit.com',
					'first_name' => 'Arb',
					'last_name' => 'Tester',
					'street_address_1' => '123 Test Drive',
					'street_address_2' => '',
					'city' => 'North Syracuse',
					'state' => 'NY',
					'zip' => '13212',
					'country' => 'US',
					'phone' => '1234567890',
					'shipping' => '0',
					'type' => 'billing'
				),
				array(
					'street_address_1' => '',
					'street_address_2' => '',
					'city' => '',
					'state' => '',
					'zip' => '',
					'country' => 'US',
					'type' => 'shipping'
				)
			),
			'Transaction' => array(
				'mode' => 'PAYSIMPLE.CC',
				'card_number' => '4111111111111111',
				'card_exp_month' => '1',
				'card_exp_year' => '2014',
				'card_sec' => '999',
				'ach_routing_number' => '',
				'ach_account_number' => '',
				'ach_bank_name' => '',
				'ach_is_checking_account' => '',
				'quantity' => '1',
				'total' => '5.00'
			),
			'TransactionItem' => array(
				array(
					'id' => '50773d75-cab4-40dd-b34c-187800000005',
					'quantity' => '1'
				)
			),
			'TransactionCoupon' => array(
				'code' => ''
			)
		);
		
		$result = $this->Product->buy($data);
		$transaction = $this->Transaction->find('first', array('conditions' => array('Transaction.Id' => $result['Transaction']['id'])));
		// transaction was bought and paid for
		$this->assertTrue($transaction['Transaction']['status'] == 'paid');
	}

	public function testBluepay() {
		CakeSession::write('Auth.User.id', '2');
		$data = array(
			'TransactionAddress' => array(
				array(
					'email' => 'unit-test@razorit.com',
					'first_name' => 'Arb',
					'last_name' => 'Tester',
					'street_address_1' => '123 Test Drive',
					'street_address_2' => '',
					'city' => 'North Syracuse',
					'state' => 'NY',
					'zip' => '13212',
					'country' => 'US',
					'phone' => '1234567890',
					'shipping' => '0',
					'type' => 'billing'
				),
				array(
					'street_address_1' => '',
					'street_address_2' => '',
					'city' => '',
					'state' => '',
					'zip' => '',
					'country' => 'US',
					'type' => 'shipping'
				)
			),
			'Transaction' => array(
				'mode' => 'BLUEPAY.CC',
				'card_number' => '4111111111111111',
				'card_exp_month' => '1',
				'card_exp_year' => '2014',
				'card_sec' => '999',
				'ach_routing_number' => '',
				'ach_account_number' => '',
				'ach_bank_name' => '',
				'ach_is_checking_account' => '',
				'quantity' => '1',
				'total' => '5.00'
			),
			'TransactionItem' => array(
				array(
					'id' => '50773d75-cab4-40dd-b34c-187800000005',
					'quantity' => '1'
				)
			),
			'TransactionCoupon' => array(
				'code' => ''
			)
		);
		
		$result = $this->Product->buy($data);
		$transaction = $this->Transaction->find('first', array('conditions' => array('Transaction.Id' => $result['Transaction']['id'])));
		// transaction was bought and paid for
		$this->assertTrue($transaction['Transaction']['status'] == 'paid');
	}
	




/**
 * Test finding 
 */ 
	public function testFinding() {
	}

}
