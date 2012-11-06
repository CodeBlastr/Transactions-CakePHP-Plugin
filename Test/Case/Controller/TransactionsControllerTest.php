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
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Transactions);

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
	
	
	public function testCheckout() {
		$submittedTransaction = array(
			'TransactionPayment' => array(
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
					'shipping' => '0'
				)
			),
			'TransactionShipment' => array(
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
					'id' => '50773d75-cab4-40dd-b34c-187800000000',
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
		
		$result = $this->testAction('/transactions/transactions/checkout', array('data' => $submittedTransaction));
	    debug($result);
	}
	
}
