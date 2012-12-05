<?php
App::uses('TransactionCoupon', 'Transactions.Model');   
 
 
/**
 * TransactionCoupon Test Case
 *
 */
class TransactionCouponTestCase extends CakeTestCase {
    
/**
 * Fixtures
 *
 * @var array
 */
	
  //public $fixtures = array('app.transaction_coupons', 'app.transactions');  
  public $fixtures = array('app.Condition','plugin.Transactions.TransactionCoupon');   

/**
 * setUp method
 *
 * @return void
 */      
	public function setUp() {
		parent::setUp();
		$this->TransactionCoupon = ClassRegistry::init('Transactions.TransactionCoupon');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TransactionCoupon);

		parent::tearDown();
	}  
    
      
    
    /**
    * testAdd method
    *
    * @return void
    */
    public function testAdd() {
        
        
         $this->data = array('id' => '1',
            'name' => 'Chrismas Offer',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'discount' => 10,
            'discount_type' => 'fixed',
            'discount_max' => 1,
            'discount_qty_x' => 1,
            'discount_shipping' => 1,
            'code' => 'google',
            'uses_allowed' => 1,
            'user_uses_allowed' => 1,
            'uses' => 1,
            'start_date' => '2012-11-01 19:39:12',
            'end_date' => '2012-12-30 19:39:12',
            'is_active' => 1,
            'creator_id' => 'Lorem ipsum dolor sit amet',
            'modifier_id' => 'Lorem ipsum dolor sit amet',
            'created' => '2012-11-27 01:39:12',
            'modified' => '2012-10-27 13:39:12');

      
        $this->TransactionCoupon->create();
        $this->TransactionCoupon->save($this->data);

        $result=$this->TransactionCoupon->find('all');  
        $this->assertTrue(!empty($result)); // transaction Coupon was created

    }
    
    
    /**
    * testEdit method
    *
    * Edit a Transaction Coupon
    */
public function testEdit() {

        $this->data = array('id' => '2',
            'name' => 'New year offer',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'discount' => 12,
            'discount_type' => 'fixed',
            'discount_max' => 1,
            'discount_qty_x' => 1,
            'discount_shipping' => 1,
            'code' => 'vini',
            'uses_allowed' => 1,
            'user_uses_allowed' => 1,
            'uses' => 1,
            'start_date' => '2012-11-01 19:39:12',
            'end_date' => '2012-12-30 19:39:12',
            'is_active' => 1,
            'creator_id' => 'Lorem ipsum dolor sit amet',
            'modifier_id' => 'Lorem ipsum dolor sit amet',
            'created' => '2012-11-27 01:39:12',
            'modified' => '2012-10-27 13:39:12');
        
        
        $this->TransactionCoupon->save($this->data);

        $result = $this->TransactionCoupon->find('first', array('conditions' => array('TransactionCoupon.id' =>2)));
        //debug($result);
        // break;
        $this->assertEqual($result['TransactionCoupon']['name'],$this->data['name']);

    
    }
    
    
    /**
    * testDelete method
    * 
    * Delete a Transaction Coupon   
    */
    public function testDelete() {

          $this->data = array('id' => '3',
            'name' => 'Summer offer',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'discount' => 5,
            'discount_type' => 'fixed',
            'discount_max' => 1,
            'discount_qty_x' => 1,
            'discount_shipping' => 1,
            'code' => 'summer',
            'uses_allowed' => 1,
            'user_uses_allowed' => 1,
            'uses' => 1,
            'start_date' => '2012-11-01 19:39:12',
            'end_date' => '2012-12-30 19:39:12',
            'is_active' => 1,
            'creator_id' => 'Lorem ipsum dolor sit amet',
            'modifier_id' => 'Lorem ipsum dolor sit amet',
            'created' => '2012-11-27 01:39:12',
            'modified' => '2012-10-27 13:39:12');

        $this->TransactionCoupon->save($this->data); 

        $result = $this->TransactionCoupon->find('first', array('conditions' => array('TransactionCoupon.id' => $this->TransactionCoupon->id)));
        $this->assertTrue(!empty($result)); // transaction coupon was created

        $this->TransactionCoupon->delete($this->TransactionCoupon->id);

        $result = $this->TransactionCoupon->find('first', array('conditions' => array('TransactionCoupon.id' => $this->TransactionCoupon->id)));
        $this->assertEqual($result, false); // transaction coupon should be gone


    } 
    
    /**
    * testLists method
    *
    */
    public function testLists() {
        
      
      $transactioncoupon=$this->TransactionCoupon->find('all');
      $this->assertTrue(!empty($transactioncoupon[0]));
     
    }   
    
   
}
