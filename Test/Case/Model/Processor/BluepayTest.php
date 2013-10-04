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
class BluepayTestCase extends CakeTestCase {
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
		App::uses('Bluepay', 'Transactions.Model/Processor');
		$this->Bluepay = new Bluepay;
		
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
	
	// 
	

/**
 * Test behavior instance
 *
 * @return void
 * @todo create rebill tests for all of the different types of rebilling
 * @todo monthly, annually, weekly, bi-weekly, 
 * @todo both credit and ach for each one
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Article->Behaviors->Buyable, 'BuyableBehavior'));
	}
	public function testSendTransactionRebillCredit() {
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
				'total' => '5.34' // randomize the last two numbers between 0 and 99
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
		$result = $this->Buyable->buy($this->Transaction, $data);
		debug($result);
		break;
		
		$this->assertTrue(!empty($result['Customer']['Connection'][0]['value']));
		$this->assertTrue(!empty($result['Transaction']['processor_response']));
	}

	public function testSendTransactionCredit() {
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
				'total' => '5.28'
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
		$result = $this->Bluepay->pay($data);
		$this->assertTrue(!empty($result['Customer']['Connection'][0]['value']));
		$this->assertTrue(!empty($result['Transaction']['processor_response']));
	}

/**
 * test send transaction ach
 */
	public function testSendTransactionAch() {
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
				'mode' => 'BLUEPAY.ACH',
				'card_number' => '',
				'card_exp_month' => '',
				'card_exp_year' => '',
				'card_sec' => '',
				'ach_routing_number' => '122287675',
				'ach_account_number' => '1234123412341234',
				'ach_bank_name' => 'HSBC',
				'ach_is_checking_account' => '1',
				'quantity' => '1',
				'total' => '5.26' // randomize the last two numbers between 0 and 99
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
		$result = $this->Bluepay->pay($data);
		$this->assertTrue(!empty($result['Customer']['Connection'][0]['value']));
		$this->assertTrue(!empty($result['Transaction']['processor_response']));
	}


	public function testFinalizeDataCredit() {
		CakeSession::write('Auth.User.id', '2');
		$data = array(
			'Transaction' => array(
				'mode' => 'BLUEPAY.CC',
				'card_number' => '4111111111111111',
				'card_exp_month' => '1',
				'card_exp_year' => '2014',
				'card_sec' => '999',
			)
		);
		$this->Bluepay->finalizeData($data);
		$this->assertEqual($this->Bluepay->cvv2, $data['Transaction']['card_sec']);
	}

	public function testFinalizeDataAch() {
		CakeSession::write('Auth.User.id', '2');
		$data = array(			
			'Transaction' => array(
				'mode' => 'BLUEPAY.ACH',				
				'ach_routing_number' => '3456789',
				'ach_account_number' => '3456789',
				'ach_bank_name' => 'HSBC',
				'ach_is_checking_account' => '1'
				
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
		$this->Bluepay->finalizeData($data);
		$this->assertEqual($this->Bluepay->payType, 'ACH'); 
	}

}
