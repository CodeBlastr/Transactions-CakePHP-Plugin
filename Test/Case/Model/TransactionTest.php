<?php

App::uses('Transaction', 'Model');

/**
 * Transaction Test Case
 *
 */
class TransactionTestCase extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.transactions.transaction',
		'plugin.transactions.transaction_address',
		'plugin.transactions.transaction_coupon',
	    'plugin.users.user',
		'plugin.transactions.transaction_item',
//	    'plugin.users.customer',
	    'plugin.contacts.contact',
//	    'plugin.users.assignee',
//	    'plugin.transactions.transaction_coupon'
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Transaction = ClassRegistry::init('Transactions.Transaction');
		$this->TransactionItem = ClassRegistry::init('Transactions.TransactionItem');
		$this->Session = ClassRegistry::init('Session');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Transaction);
		ClassRegistry::flush();

		parent::tearDown();
	}

	public function testGatherCheckoutOptions() {
		#debug($this->Transaction);break;
		$result = $this->Transaction->gatherCheckoutOptions();

		$this->assertInternalType('array', $result);
	}

	/**
	 * Tests that User #1 has a cart
	 */
	public function testRetrievingCart() {
		$userId = 1;
		$result = $this->Transaction->processCart($userId);
		$this->assertInternalType('array', $result);
	}

	/**
	 * Tests that User #2 has no cart
	 */
	public function testNotRetrievingCart() {
		$userId = 2;
		$result = $this->Transaction->processCart($userId);
		$this->assertEqual($result, FALSE);
	}

	/**
	 * Tests that User 1's subtotal was calculated
	 * @todo make better test
	 */
	public function testSubtotalCalculation() {
		$userId = 1;
		$result = $this->Transaction->processCart($userId);
		$this->assertInternalType('float', $result['Transaction']['order_charge']);
	}

	public function testReassignGuestCart() {
		$this->Transaction->reassignGuestCart('5738299d-9040-43c9-85b1-22d400000000', 1);
		$result = Set::extract('/Transaction/customer_id', $this->Transaction->find('all'));
		//debug($result);
		//break;
		
		$this->assertEqual($result[0], $result[1]);

		//debug($this->TransactionItem->find('all'));
		//break;
	}

	public function testFinalizeTransactionData_asGuest() {
		
		$submittedTransaction = array(
			'TransactionAddress' => array(
				array(
					'email' => 'joel@razorit.com',
					'first_name' => 'Joel',
					'last_name' => 'Byrnes',
					'street_address_1' => '123 Test Drive',
					'street_address_2' => '',
					'city' => 'North Syracuse',
					'state' => 'NY',
					'zip' => '13212',
					'country' => 'US',
					'shipping' => '0',
					'phone' => '1234567890',
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
				'quantity' => ''
			),
			'TransactionItem' => array(
				array(
					'id' => '50773d75-cab4-40dd-b34c-187800000001',
					'quantity' => '2' // different qty than what was originally in the cart
				)
			),
			'TransactionCoupon' => array(
				'code' => ''
			)
		);
		
		App::uses('CakeSession', 'Model');
		$this->Session = new CakeSession;

		$this->Session->write('Transaction._guestId', '5738299d-9040-43c9-85b1-22d400000000');

		$result = $this->Transaction->finalizeTransactionData($submittedTransaction);
		#debug($result);break;
	}

	
	public function testFinalizeUserData_asGuest() {
	
		$submittedTransaction = array(
			'TransactionAddress' => array(
				array(
					'email' => 'joel@razorit.com',
					'first_name' => 'Joel',
					'last_name' => 'Byrnes',
					'street_address_1' => '123 Test Drive',
					'street_address_2' => '',
					'city' => 'North Syracuse',
					'state' => 'NY',
					'zip' => '13212',
					'country' => 'US',
					'shipping' => '0',
					'phone' => '1234567890'
				),
				array(
					'street_address_1' => '',
					'street_address_2' => '',
					'city' => '',
					'state' => '',
					'zip' => '',
					'country' => 'US'
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
				'quantity' => ''
			),
			'TransactionItem' => array(
				array(
					'id' => '50773d75-cab4-40dd-b34c-187800000001',
					'quantity' => '2'
				)
			),
			'TransactionCoupon' => array(
				'code' => ''
			)
		);
		
		App::uses('CakeSession', 'Model');
		$this->Session = new CakeSession;

		$this->Session->write('Transaction._guestId', '5738299d-9040-43c9-85b1-22d400000000');

		$result = $this->Transaction->finalizeUserData($submittedTransaction);
		#debug($result);break;
		
	}
	
	public function testShippingChargeCalculatedFromSetting() {
		define('__TRANSACTIONS_FLAT_SHIPPING_RATE', 5);
		$result = $this->Transaction->processCart(1);
		$result = $result['Transaction']['shipping_charge'];
		$this->assertEqual($result, '5');
	}
	
}
