<?php

App::uses('Transaction', 'Transactions.Model');

/**
 * Transaction Test Case
 *
 */
class TransactionModelTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Transactions.Transaction',
		'plugin.Transactions.TransactionAddress',
		'plugin.Transactions.TransactionCoupon',
		'plugin.Transactions.TransactionItem',
		'plugin.Transactions.TransactionTax',
	    'plugin.Users.User',
	    'plugin.Users.Customer',
	    'plugin.Contacts.Contact',
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
 * Tests that User #9812938 has no cart
 */
	public function testNotRetrievingCart() {
		$userId = 9812938;
		$result = $this->Transaction->processCart($userId);
		$this->assertEqual($result, false);
	}

/**
 * Tests that User 1's subtotal was calculated
 * 
 * @todo make better test
 */
	public function testSubtotalCalculation() {
		$userId = 1;
		$result = $this->Transaction->processCart($userId);
        
		App::uses('Validation', 'Utility');
		$this->assertTrue(Validation::money($result['Transaction']['sub_total'])); // 1.00

	}

	public function testReassignGuestCart() {
        $conditions = array('conditions' => array('Transaction.customer_id' => '5738299d-9040-43c9-85b1-22d400000000'));
		$result = $this->Transaction->find('first', $conditions);
        $this->assertTrue(!empty($result)); // guest cart exists
		$this->Transaction->reassignGuestCart('5738299d-9040-43c9-85b1-22d400000000', 1);
        $result = $this->Transaction->find('first', $conditions);
        $this->assertTrue(empty($result)); // guest cart is re-assigned.
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
				'card_sec' => '123',
				'ach_routing_number' => '',
				'ach_account_number' => '',
				'ach_bank_name' => '',
				'ach_is_checking_account' => '',
				'quantity' => '',
                'status'=>'open',
                'sub_total' => '2,257.50',
                'customer_id' => '1',  
             
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
        
		//$result = $this->Transaction->finalizeTransactionData($submittedTransaction);
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
				//'card_number' => '4111111111111111',
//				'card_number' => '5454545454545454',
//				'card_exp_month' => '12',
//				'card_exp_year' => '2014',
//				'card_sec' => '999',
				'card_number' => '',
				'card_exp_month' => '',
				'card_exp_year' => '',
				'card_sec' => '',
//				'ach_routing_number' => '',
//				'ach_account_number' => '',
//				'ach_bank_name' => '',
				'ach_routing_number' => '307075259',
				'ach_account_number' => '751111111',
				'ach_bank_name' => 'Simply Bank',
				'ach_is_checking_account' => '1',
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

		  // echo "<pre>";
          // print_r($submittedTransaction);
            
        $result = $this->Transaction->finalizeUserData($submittedTransaction);
		#debug($result);break;
		
	}
	
	public function testShippingChargeCalculatedFromSetting() {
		define('__TRANSACTIONS_FLAT_SHIPPING_RATE', 5);
		$result = $this->Transaction->processCart(1);
		$result = $result['Transaction']['shipping_charge'];
		//$this->assertEqual($result, '5');
	}
    
    
     /**
 * testBeforePayment method
 *
 * @return void
 */
    public function testBeforePayment() {
        
       $data = array(
            'TransactionAddress' => array(
                array(
                    'email' => 'joel@example.com',
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
                //'quantity' => '',
                'sub_total' => 140.00
                ),
            'TransactionItem' => array(
                array(
                    'id' => '50773d75-cab4-40dd-b34c-187800000001',
                    'quantity' => '2'
                )
            ),
            'TransactionCoupon' => array(
                'code' => 'vini'
            )
        );
       
        $data = $this->Transaction->finalizeUserData($data);
        $data = $this->Transaction->TransactionCoupon->verify($data);
        $this->assertTrue(!empty($data['TransactionCoupon']));
    }
    
    
/**
 * I cannot run a test for this because 
 * I do not know how to create a mock Session
 * object in models (only controllers)
 * 
 * And one of the functions uses the session to look up the customer id.
 * 
 * @todo Maybe figure out how to get a mock session going in a model
    public function testBeforePaymentTaxes() {
        CakeSession::write('Auth.User.id', '8723994');
        $data = array(
            'TransactionAddress' => array(
                array(
                    'first_name' => 'Richard',
                    'last_name' => 'Kersey',
                    'email' => 'noemail@nowhere.com',
                    'country' => 'US',
                    'street_address_1' => '3942 Main St.',
                    'street_address_2' => 'No Street',
                    'city' => 'City',
                    'state' => 'US-FL',
                    'zip' => '32488',
                    'phone' => '82392399',
                    'shipping' => '0',
                    'type' => 'billing'
                ),
                array(
                    'country' => '',
                    'street_address_1' => '',
                    'street_address_2' => '',
                    'city' => '',
                    'state' => '',
                    'zip' => '',
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
                'sub_total' => '6.00',
                'hiddentotal' => '6.00'
            ),
            'TransactionItem' => array(
                array(
                    'id' => '50773d75-cab4-40dd-b34c-187800000003',
                    'quantity' => '1'
                )
            ),
            'TransactionCoupon' => array(
                'code' => ''
            )
        );
        $result = $this->Transaction->beforePayment($data);
    }
 */
	
}
