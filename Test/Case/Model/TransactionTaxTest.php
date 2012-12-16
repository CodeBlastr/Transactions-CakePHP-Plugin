<?php

App::uses('TransactionTax', 'Transactions.Model');

/**
 * TransactionTax Test Case
 *
 */
class TransactionTaxModelTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.transactions.transaction',
		'plugin.transactions.transaction_address',
		'plugin.transactions.transaction_coupon',
        'plugin.Transactions.TransactionTax',
		'plugin.transactions.transaction_item',
//        'plugin.transactions.transaction_coupon',
        'plugin.users.user',
	    'plugin.users.customer',
	    'plugin.contacts.contact',
//	    'plugin.users.assignee',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TransactionTax = ClassRegistry::init('Transactions.TransactionTax');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TransactionTax);
		ClassRegistry::flush();

		parent::tearDown();
	}

/**
 * testUsSave method
 *
 * @return void
 */
    public function testSave() {
        $data = array(
            'TransactionTax' => array(
            	'label' => 'National Tax',
        		'code' => 'AF',
        		'rate' => '5.00',
                )
            );
        $result = $this->TransactionTax->save($data);
        $this->assertTrue(!empty($this->TransactionTax->id));
        
        $this->TransactionTax->contain('Children');
        $result = $this->TransactionTax->findById($this->TransactionTax->id);
        $this->assertTrue(empty($result['Children'])); // Afghanistan should have no children
    }

/**
 * testUsSave method
 *
 * @return void
 */
    public function testUsSave() {
        $data = array(
            'TransactionTax' => array(
        		'label' => 'National Tax',
        		'code' => 'US',
        		'rate' => '0.00',
                )
            );
        $result = $this->TransactionTax->save($data);
        $this->assertTrue(!empty($this->TransactionTax->id));
        
        $this->TransactionTax->contain('Children');
        $result = $this->TransactionTax->findById($this->TransactionTax->id);
        $this->assertTrue(!empty($result['Children']));
    }

/**
 * testCaSave method
 *
 * @return void
 */
    public function testCaSave() {
        $data = array(
            'TransactionTax' => array(
            	'label' => 'National Tax',
        		'code' => 'CA',
        		'rate' => '0.00',
                )
            );
        $result = $this->TransactionTax->save($data);
        $this->assertTrue(!empty($this->TransactionTax->id));
        
        $this->TransactionTax->contain('Children');
        $result = $this->TransactionTax->findById($this->TransactionTax->id);
        $this->assertTrue(!empty($result['Children']));
    }

/**
 * testAuSave method
 *
 * @return void
 */
    public function testAuSave() {
        $data = array(
            'TransactionTax' => array(
                'label' => 'National Tax',
        		'code' => 'AU',
        		'rate' => '0.00',
                )
            );
        $result = $this->TransactionTax->save($data);
        $this->assertTrue(!empty($this->TransactionTax->id));
        
        $this->TransactionTax->contain('Children');
        $result = $this->TransactionTax->findById($this->TransactionTax->id);
        $this->assertTrue(!empty($result['Children']));
    }
	
}
