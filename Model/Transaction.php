<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * Transaction Model
 *
 * @property TransactionShipment $TransactionShipment
 * @property TransactionPayment $TransactionPayment
 * @property TransactionShipment $TransactionShipment
 * @property TransactionCoupon $TransactionCoupon
 * @property Customer $Customer
 * @property Contact $Contact
 * @property Assignee $Assignee
 * @property TransactionItem $TransactionItem
 * @property TransactionPayment $TransactionPayment
 */
class Transaction extends TransactionsAppModel {
 public $name = 'Transaction';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'TransactionShipment' => array(
			'className' => 'Transactions.TransactionShipment',
			'foreignKey' => 'transaction_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'TransactionPayment' => array(
			'className' => 'Transactions.TransactionPayment',
			'foreignKey' => 'transaction_payment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
//		'TransactionShipment' => array(
//			'className' => 'Transactions.TransactionShipment',
//			'foreignKey' => 'transaction_shipment_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		),
//		'TransactionCoupon' => array(
//			'className' => 'Transactions.TransactionCoupon',
//			'foreignKey' => 'transaction_coupon_id',
//			'conditions' => '',
//			'fields' => '',
//			'order' => ''
//		),
		'Customer' => array(
			'className' => 'Users.User',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Contact' => array(
			'className' => 'Contacts.Contact',
			'foreignKey' => 'contact_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Assignee' => array(
			'className' => 'Users.User',
			'foreignKey' => 'assignee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'TransactionItem' => array(
			'className' => 'Transactions.TransactionItem',
			'foreignKey' => 'transaction_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'TransactionPayment' => array(
			'className' => 'Transactions.TransactionPayment',
			'foreignKey' => 'transaction_id',
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

	
	/**
	 * The checkout page has options.
	 * This function's job is to get those options.
	 * @return array
	 */
	public function gatherCheckoutOptions() {
	    $options['ssl'] = defined('__ORDERS_SSL') ? unserialize(__ORDERS_SSL) : null;
	    $options['trustLogos'] = !empty($ssl['trustLogos']) ? $ssl['trustLogos'] : null;
	    $options['enableShipping'] = defined('__ORDERS_ENABLE_SHIPPING') ? __ORDERS_ENABLE_SHIPPING : false;
	    $options['fedexSettings'] = defined('__ORDERS_FEDEX') ? unserialize(__ORDERS_FEDEX) : null;
	    $options['paymentMode'] = defined('__ORDERS_DEFAULT_PAYMENT') ? __ORDERS_DEFAULT_PAYMENT : null;
	    $options['paymentOptions'] = defined('__ORDERS_ENABLE_PAYMENT_OPTIONS') ? unserialize(__ORDERS_ENABLE_PAYMENT_OPTIONS) : null;

	    if (defined('__ORDERS_ENABLE_SINGLE_PAYMENT_TYPE')) {
		  $options['singlePaymentKeys'] = $this->Session->read('OrderPaymentType');
		  if (!empty($options['singlePaymentKeys'])) {
			  $options['singlePaymentKeys'] = array_flip($options['singlePaymentKeys']);
			  $options['paymentOptions'] = array_intersect_key($options['paymentOptions'], $options['singlePaymentKeys']);
		  }
		}

	    $options['defaultShippingCharge'] = defined('__ORDERS_FLAT_SHIPPING_RATE') ? __ORDERS_FLAT_SHIPPING_RATE : 0;
	    
	    return $options;
	}
	
	
	/**
	 * This function returns the UUID to use for a User by first checking the Auth Session, then by checking for a Transaction Guest session,
	 * and finally, creating a Transaction Guest session if necessary.
	 *  
	 * @todo This should probably be in the User model in some fashion..
	 * 
	 * @return string The UUID of the User
	 */
	public function getCustomersId() {
	  $authUserId = CakeSession::read('Auth.User.id');
	  $transactionGuestId = CakeSession::read('Transaction._guestId');
	  if ($authUserId) {
		$userId = $authUserId;
	  } elseif($transactionGuestId) {
		$userId = $transactionGuestId;
	  } else {
		$userId = String::uuid();
		CakeSession::write('Transaction._guestId', $userId);
	  }

	  // Assign their Guest Cart to their Logged in self, if neccessary
	  $this->reassignGuestCart($transactionGuestId, $authUserId);
	  
	  return $userId;
	}

	
//	/** MOVED TO TransactionsAppModel
//	
//	 * This function is meant to transfer a cart when a guest shopper logs in.
//	 * After doing so, it deletes their Transaction._guestId session.
//	 * 
//	 * @param mixed $fromId
//	 * @param mixed $toId
//	 * @return boolean
//	 * @throws Exception 
//	 */
//	public function reassignGuestCart($fromId, $toId) {
//	  if($fromId && $toId) {
//		if ($this->updateAll(array('customer_id' => $toId), array('customer_id' => $fromId))) {
//		  return true;
//		} else {
//		  throw new Exception(__d('transactions', 'Guest cart merge failed'));
//		}
//	  }
//	}
	

/**
 * We could do all sorts of processing in here
 * @param string $userId
 * @return boolean|array
 */
	public function processCart($userId) {
	    
	    $theCart = $this->find('first', array(
		  'conditions' => array('customer_id' => $userId),
		  'contain' => array(
			  'TransactionItem',
			  'TransactionShipment',  // saved shipping addresses
			  'TransactionPayment',   // saved billing addresses
			  'Customer'			  // customer's user data
			  )
		));
	    
	    if(!$theCart) {
		  return FALSE;
	    }
	    
	    // figure out the subTotal
	    $subTotal = 0;
	    foreach($theCart['TransactionItem'] as $txnItem) {
		  $subTotal += $txnItem['price'] * $txnItem['quantity'];
	    }
	    
	    $theCart['Transaction']['order_charge'] = $subTotal;
	    
	    return $theCart;
	}
	
	
	/**
	 * Combine the pre-checkout and post-checkout Transactions.
	 * 
	 * @todo Handle being passed empty carts
	 * @param integer $userId
	 * @param array $data
	 * @return type
	 */
	public function finalizeTransactionData($userId, $submittedTransaction) {
		// get their current transaction (pre checkout page)
		$currentTransaction = $this->find('first', array(
		    'conditions' => array('customer_id' => $userId),
		    'contain' => array(
			  'TransactionItem',
			  'TransactionShipment',  // saved shipping addresses
			  'TransactionPayment',	  // saved billing addresses
			  'Customer'			  // customer's user data
			  )
		));

	    // update quantities
		foreach($submittedTransaction['TransactionItem'] as $submittedTxnItem) {
		    if($submittedTxnItem['quantity'] > 0) {
			  foreach($currentTransaction['TransactionItem'] as $currentTxnItem) {
				  if($currentTxnItem['id'] == $submittedTxnItem['id']) {
					$currentTxnItem['quantity'] = $submittedTxnItem['quantity'];
					$finalTxnItems[] = $currentTxnItem;
				  }
			  }
		    }
		}
			
		// unset the submitted TransactionItem's. They will be replaced after the merge.
		unset($submittedTransaction['TransactionItem']);
		
		// combine the Current and Submitted Transactions
		$officialTransaction = Set::merge($currentTransaction, $submittedTransaction);
		$officialTransaction['TransactionItem'] = $finalTxnItems;
		
		// figure out the subTotal
		$officialTransaction['Transaction']['order_charge'] = 0;
		foreach($officialTransaction['TransactionItem'] as $txnItem) {
		    $officialTransaction['Transaction']['order_charge'] += $txnItem['price'] * $txnItem['quantity'];
		}
				
		// return the official transaction
		return $officialTransaction;
	}
	
	
	
	public function finalizeUserData($transaction) {
	  
	  // ensure their 'Customer' data has values
	  if($transaction['Customer']['id'] == NULL) {
		$transaction['Customer']['id'] = $transaction['Transaction']['customer_id'];
		$transaction['Customer']['first_name'] = $transaction['TransactionPayment']['first_name'];
		$transaction['Customer']['last_name'] = $transaction['TransactionPayment']['last_name'];
		$transaction['Customer']['email'] = $transaction['TransactionPayment']['email'];
		$transaction['Customer']['phone'] = $transaction['TransactionPayment']['phone'];
	  }
	  
	  $transaction['TransactionPayment']['user_id'] = $transaction['Transaction']['customer_id'];
	  // copy Payment data to Shipment data if neccessary
	  if($transaction['TransactionPayment']['shipping'] !== 'checked') {
		$transaction['TransactionShipment']['transaction_id'] = $transaction['TransactionPayment']['transaction_id'];
		$transaction['TransactionShipment']['first_name'] = $transaction['TransactionPayment']['first_name'];
		$transaction['TransactionShipment']['last_name'] = $transaction['TransactionPayment']['last_name'];
		$transaction['TransactionShipment']['email'] = $transaction['TransactionPayment']['email'];
		$transaction['TransactionShipment']['street_address_1'] = $transaction['TransactionPayment']['street_address_1'];
		$transaction['TransactionShipment']['street_address_2'] = $transaction['TransactionPayment']['street_address_2'];
		$transaction['TransactionShipment']['city'] = $transaction['TransactionPayment']['city'];
		$transaction['TransactionShipment']['state'] = $transaction['TransactionPayment']['state'];
		$transaction['TransactionShipment']['zip'] = $transaction['TransactionPayment']['zip'];
		$transaction['TransactionShipment']['country'] = $transaction['TransactionPayment']['country'];
		$transaction['TransactionShipment']['phone'] = $transaction['TransactionPayment']['phone'];
		$transaction['TransactionShipment']['user_id'] = $transaction['TransactionPayment']['user_id'];
	  }
	  
	  return $transaction;
	}

	
	/**
	 * 
	 * @param array $transactions Multiple transactions
	 * @return array A single Transaction
	 */
	public function combineTransactions($transactions) {

	  foreach($transactions as $transaction) {
		foreach($transaction['TransactionItem'] as $transactionItem) {
		  if( ! isset($finalTransactionItems[$transactionItem['foreign_key']]) ) {
			$finalTransactionItems[$transactionItem['foreign_key']] = $transactionItem;
		  } else {
			$finalTransactionItems[$transactionItem['foreign_key']]['quantity'] += $transactionItem['quantity'];
		  }
		}
	  }

	  // reset the keys back to 0,1,2,3..
	  $finalTransaction['TransactionItem'] = array_values($finalTransactionItems);

	  // reuse the Transaction data from the 1st Transaction (ideally, the newest Transaction)
	  $finalTransaction['Transaction'] = $transactions[0]['Transaction'];

	  return $finalTransaction;
	}
	
	
	/**
	 * An array of options for select inputs
	 *
	 */
	public function statuses() {
	    $statuses = array();
	    foreach (Zuha::enum('ORDER_TRANSACTION_STATUS') as $status) {
		  $statuses[Inflector::underscore($status)] = $status;
	    }
	    return Set::merge(array('failed' => 'Failed', 'paid' => 'Paid', 'shipped' => 'Shipped'), $statuses);
	}
	
	
}
