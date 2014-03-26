<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * TransactionCoupon Model
 *
 * @property Transaction $Transaction
 */
class TransactionCoupon extends TransactionsAppModel {
    
public $name = 'TransactionCoupon';  
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'is_active' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Transaction' => array(
			'className' => 'Transaction',
			'foreignKey' => 'transaction_coupon_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	
	public function verify($data, $conditions = null) {
        // Get Current Date
        $mktime = mktime();
        $current_date = date('Y-m-d H:i:s', $mktime);
        
        // Check status is in Active or not and also check start date and end date valid or not.  
        $conditions = array('TransactionCoupon.is_active' => 1,'TransactionCoupon.start_date <=' => $current_date,'TransactionCoupon.end_date >=' => $current_date);
        
        // similar to apply but don't mark as used
		if (!empty($data['TransactionCoupon']['code'])) {
			$condtions = Set::merge(array('TransactionCoupon.code' => $data['TransactionCoupon']['code']), $conditions);
			$coupon = $this->find('first', array('conditions' => $condtions));
           	
			if (empty($coupon)) {
				throw new Exception('Code out of date or does not apply.');
			} else {
				$data = $this->_applyPriceChange(
					$coupon['TransactionCoupon']['discount_type'],
					$coupon['TransactionCoupon']['discount'],
					$data);
                $data['Transaction']['transaction_coupon_id']=$coupon['TransactionCoupon']['id'];    
				$data['TransactionCoupon'] = $coupon['TransactionCoupon']; 
				return $data;
			}
		} else {
			throw new Exception('Coupon code was empty.'); 
		}   
	} 
	
	private function _applyPriceChange($type = 'fixed', $discount = 0, $data = null) {
        $data['Transaction']['sub_total'] = ereg_replace(",", "", $data['Transaction']['sub_total']);
       
		if ($type == 'percent') {
			// for now it does the total 
			$data['Transaction']['sub_total'] = ZuhaInflector::pricify(((100 - $discount) / 100) * $data['Transaction']['sub_total']);    
		} else {
			// do fixed coupon price change
			$data['Transaction']['sub_total'] = ZuhaInflector::pricify($data['Transaction']['sub_total'] - $discount);
            
		}
	    $data['Transaction']['total'] = ereg_replace(",", "", $data['Transaction']['sub_total']);
		return $data;
	}
	
	public function apply($data) {
		// find the coupon (make sure it can be applied)
		try {
			$data = $this->verify($data);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		
		// make the coupon as used
		$coupon['TransactionCoupon']['id'] = $data['TransactionCoupon']['id'];
		$coupon['TransactionCoupon']['uses'] = $data['TransactionCoupon']['uses'] + 1;
		$this->validate = false;
		if ($this->save($coupon)) {
			return !empty($data['Transaction']['total']) ? $data['Transaction']['total'] : $data['Transaction']['sub_total'];
		} else {
			throw new Exception('Code apply failed.');
		}
	}
	
	
	public function types() {
		return array(
			'fixed' => 'Fixed discount for cart total.',
			'percent' => 'Percent discount for cart total.',
			);
	}

}
