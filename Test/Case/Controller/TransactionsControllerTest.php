<?php
App::uses('TransactionsController', 'Transactions.Controller');
/**
 * @see <http://book.cakephp.org/2.0/en/development/testing.html#testing-controllers>
 */
//class TransactionModel extends CakeTestModel {
//
///**
// * useTable
// *
// * @var string
// */
//	public $useTable = 'transactions';
//}

/**
 * TestTransactionsController *
 */
class TestTransactionsController extends TransactionsController {
/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

/**
 * TransactionsController Test Case
 *
 */
class TransactionsControllerTestCase extends ControllerTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.transactions.transaction',
		'plugin.users.user',
		'plugin.transactions.transaction_payment',
		'plugin.transactions.transaction_item',
		'plugin.transactions.transaction_shipment',
		'plugin.users.customer',
		'plugin.users.contact',
		'plugin.users.assignee',
		'plugin.transactions.transaction_coupon',
		//'plugin.conditions.condition'
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Transactions = new TestTransactionsController();
		$this->Transactions->constructClasses();
		$this->Transactions->Session->initialize($this->Transactions);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Transactions);
		ClassRegistry::flush();
		parent::tearDown();
	}


/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
	    $result = $this->testAction('/transactions/transactions/index');
	    debug($result);
	}
/**
 * testView method
 *
 * @return void
 */
	public function testView() {

	}
/**
 * testAdd method
 *
 * @return void
 */
	public function testAdd() {
	    $result = $this->testAction('/transactions/transactions/add');
	    debug($result);
	}
/**
 * testEdit method
 *
 * @return void
 */
	public function testReqestingBadEditUrl() {
	    try {
		$result = $this->testAction('/transactions/transactions/edit');
		debug($result);
	    } catch (Exception $e) {}
	}
	
	public function testReqestingGoodEditUrl() {
	    $random = $this->Transactions->Transaction->find('first');
	    $result = $this->testAction('/transactions/transactions/edit/'.$random['Transaction']['id']);
	    debug($result);
	}
	
	public function testEditingWithGoodData() {
	    $random = $this->Transactions->Transaction->find('first');
	    $result = $this->testAction('/transactions/transactions/edit/'.$random['Transaction']['id'], array('data' => $random, 'method' => 'post'));
	    debug($result);
	}
/**
 * testDelete method
 *
 * @return void
 */
	public function testDelete() {

	}
	
	
	public function testCheckoutAsGuest() {
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
		
		// give them a guest id that has TransactionItems in our fixture
		$this->Transactions->Session->write('Transaction._guestId', '5738299d-9040-43c9-85b1-22d400000000');
		
		$this->testAction('/transactions/transactions/checkout', array('data' => $submittedTransaction));
		
		$result = $this->headers['Location'];
		$expected = '/transactions/transactions/success';
		
		$this->assertTrue(
				strstr($result, $expected),
				$this->Transactions->Session->read('Message.flash.message') . "\r\n"
				. 'Checkout redirected to '.$result.' instead of '.$expected
				);

	}
	
	
//	public function testCheckoutAsUser() {
//		$submittedTransaction = array(
//			'TransactionPayment' => array(
//				array(
//					'email' => 'joel@razorit.com',
//					'first_name' => 'Joel',
//					'last_name' => 'Byrnes',
//					'street_address_1' => '123 Test Drive',
//					'street_address_2' => '',
//					'city' => 'North Syracuse',
//					'state' => 'NY',
//					'zip' => '13212',
//					'country' => 'US',
//					'shipping' => '0'
//				)
//			),
//			'TransactionShipment' => array(
//				array(
//					'street_address_1' => '',
//					'street_address_2' => '',
//					'city' => '',
//					'state' => '',
//					'zip' => '',
//					'country' => 'US'
//				)
//			),
//			'Transaction' => array(
//				'mode' => 'PAYSIMPLE.CC',
//				'card_number' => '4111111111111111',
//				'card_exp_month' => '1',
//				'card_exp_year' => '2014',
//				'card_sec' => '999',
//				'ach_routing_number' => '',
//				'ach_account_number' => '',
//				'ach_bank_name' => '',
//				'ach_is_checking_account' => '',
//				'quantity' => ''
//			),
//			'TransactionItem' => array(
//				array(
//					'id' => '50773d75-cab4-40dd-b34c-187800000001',
//					'quantity' => '2'
//				)
//			),
//			'TransactionCoupon' => array(
//				'code' => ''
//			)
//		);
//		
//		// give them a guest id that has TransactionItems in our fixture
//		$this->Transactions->Session->write('Auth.User', array(
//			'id' => 1,
//			'username' => 'admin',
//		));
//		
//		$this->testAction('/transactions/transactions/checkout', array('data' => $submittedTransaction));
//
//		$result = $this->headers['Location'];
//		$expected = '/transactions/transactions/success';
//		
//		$this->assertTrue(
//				strstr($result, $expected),
//				$this->Transactions->Session->read('Message.flash.message') . "\r\n"
//				. 'Checkout redirected to '.$result.' instead of '.$expected
//				);
//		
//
//
//	}
	
}
