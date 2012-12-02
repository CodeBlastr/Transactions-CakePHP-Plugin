<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * Transaction Model
 *
 * @property TransactionCoupon $TransactionCoupon
 * @property Customer $Customer
 * @property Contact $Contact
 * @property Assignee $Assignee
 * @property TransactionItem $TransactionItem
 * @property TransactionAddress $TransactionAddress
 */
class Transaction extends TransactionsAppModel {
 public $name = 'Transaction';

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'TransactionCoupon' => array(
			'className' => 'Transactions.TransactionCoupon',
			'foreignKey' => 'transaction_coupon_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
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
		'TransactionAddress' => array(
			'className' => 'Transactions.TransactionAddress',
			'foreignKey' => 'transaction_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	
/**
 * The checkout page has options.
 * This function's job is to get those options.
 * @return array
 */
	public function gatherCheckoutOptions() {
	    $options['ssl'] = defined('__TRANSACTIONS_SSL') ? unserialize(__TRANSACTIONS_SSL) : null;
	    $options['trustLogos'] = !empty($ssl['trustLogos']) ? $ssl['trustLogos'] : null;
	    $options['enableShipping'] = defined('__TRANSACTIONS_ENABLE_SHIPPING') ? __TRANSACTIONS_ENABLE_SHIPPING : false;
	    $options['fedexSettings'] = defined('__TRANSACTIONS_FEDEX') ? unserialize(__TRANSACTIONS_FEDEX) : null;
	    $options['paymentMode'] = defined('__TRANSACTIONS_DEFAULT_PAYMENT') ? __TRANSACTIONS_DEFAULT_PAYMENT : null;
	    $options['paymentOptions'] = defined('__TRANSACTIONS_ENABLE_PAYMENT_OPTIONS') ? unserialize(__TRANSACTIONS_ENABLE_PAYMENT_OPTIONS) : null;

	    if (defined('__TRANSACTIONS_ENABLE_SINGLE_PAYMENT_TYPE')) {
		  $options['singlePaymentKeys'] = $this->Session->read('OrderPaymentType');
		  if (!empty($options['singlePaymentKeys'])) {
			  $options['singlePaymentKeys'] = array_flip($options['singlePaymentKeys']);
			  $options['paymentOptions'] = array_intersect_key($options['paymentOptions'], $options['singlePaymentKeys']);
		  }
		}

	    $options['defaultShippingCharge'] = defined('__TRANSACTIONS_FLAT_SHIPPING_RATE') ? __TRANSACTIONS_FLAT_SHIPPING_RATE : false;
	    
	    return $options;
	}
	
	
/**
 * This function returns the UUID to use for a User by first checking the Auth Session, then by checking for a Transaction Guest session,
 * and finally, creating a Transaction Guest session if necessary.
 *  
 * @todo This should probably be in the User model in some fashion..?
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
 * figure out the subTotal and shippingCharge
 * 
 * @param array $data
 * @return array
 */
	public function calculateSubtotalAndShipping($data) {
	
	    $subTotal = 0;
	    $shippingCharge = 0;
		
	    foreach($data['TransactionItem'] as $txnItem) {
		  $subTotal += $txnItem['price'] * $txnItem['quantity'];
		  //$shippingCharge += $txnItem['shipping_charge'];
	    }
	    
	    $data['Transaction']['order_charge'] = number_format($subTotal, 2, '.', false);
	    $data['Transaction']['shipping_charge'] = number_format($shippingCharge, 2, '.', false);
		$data['Transaction']['total'] = number_format($subTotal + $shippingCharge, 2, '.', false);
		
		// overwrite the shipping_charge if there is a FlAT_SHIPPING_RATE set
		$defaultShippingCharge = defined('__TRANSACTIONS_FLAT_SHIPPING_RATE') ? __TRANSACTIONS_FLAT_SHIPPING_RATE : FALSE;
		if($defaultShippingCharge !== FALSE) {
			$data['Transaction']['shipping_charge'] = number_format($defaultShippingCharge, 2, '.', false);
		}
		
		return $data;
		
	}
	

/**
 * We could do all sorts of processing in here
 * 
 * @todo How to get saved addresses for returning users??
 * 
 * @param string $userId
 * @return boolean|array
 */
	public function processCart($userId) {
	    
		$options = $this->gatherCheckoutOptions();
		
	    $theCart = $this->find('first', array(
		  'conditions' => array(
			  'Transaction.customer_id' => $userId,
			  'Transaction.status' => 'open'
			  ),
		  'contain' => array(
			  'TransactionItem',
			  'TransactionAddress',
			  'Customer' => array(
				  'Connection' => array(
					'conditions' => array('Connection.type' => $options['paymentMode'])  
					)
				  )
			  )
		));

	    if(!$theCart) {
		  return FALSE;
	    }
	    
	    $theCart = $this->calculateSubtotalAndShipping($theCart);

	    return $theCart;
	}
	
	
/**
 * Combine the pre-checkout and post-checkout Transactions.
 *  
 * @param integer $userId
 * @param array $data
 * @return type
 */
	public function finalizeTransactionData($submittedTransaction) {
		$userId = $this->getCustomersId();
		$options = $this->gatherCheckoutOptions();
		// get their current transaction (pre checkout page)
		$currentTransaction = $this->find('first', array(
		    'conditions' => array(
				'Transaction.customer_id' => $userId,
				'Transaction.status' => 'open'
				),
		    'contain' => array(
			  'TransactionItem',
			  'TransactionAddress',
			  'Customer' => array(
				  'Connection' => array(
					'conditions' => array('Connection.type' => $options['paymentMode'])  
					)
				  )
			  )
		));

		if(!$currentTransaction) {
			throw new Exception('Transaction missing.');
		}
		
//debug($currentTransaction);
//debug($submittedTransaction);
//break;

		// update quantities
		$allHaveZeroQty = true;
		foreach($submittedTransaction['TransactionItem'] as $submittedTxnItem) {
		    if($submittedTxnItem['quantity'] > 0) {
			  foreach($currentTransaction['TransactionItem'] as $currentTxnItem) {
				  if($currentTxnItem['id'] == $submittedTxnItem['id']) {
					$currentTxnItem['quantity'] = $submittedTxnItem['quantity'];
					$finalTxnItems[] = $currentTxnItem;
				  }
			  }
			  $allHaveZeroQty = false;
		    }
		}
			
		if($allHaveZeroQty) {
			throw new Exception('Cart is empty');
		}
		
		// unset the submitted TransactionItem's. They will be replaced after the merge.
		unset($submittedTransaction['TransactionItem']);
		
		// combine the Current and Submitted Transactions
		$officialTransaction = Set::merge($currentTransaction, $submittedTransaction);
		$officialTransaction['TransactionItem'] = $finalTxnItems;
		
		$officialTransaction = $this->calculateSubtotalAndShipping($officialTransaction);
		
		// check for ARB Settings (will only be one TransactionItem @ this point if it's an ARB Transaction)
		$officialTransaction['Transaction']['is_arb'] = !empty($officialTransaction['TransactionItem'][0]['arb_settings']) ? 1 : 0;
		
		// return the official transaction
		return $officialTransaction;
	}
	
	
/**
 * - Ensures that the necessary data is present to create a Customer
 * - Fills out the TransactionShipment fields when shipping info is same as billing info
 * 
 * @param array $transaction Transaction data that was posted by the checkout/cart form
 * @return array Same array with neccessary and completed fields
 */
	public function finalizeUserData($transaction) {

	  // ensure their 'Customer' data has values when they are not logged in
	  if(empty($transaction['Customer']['id']) || !isset($transaction['Customer'])) {
		//$transaction['Customer']['id'] = $transaction['Transaction']['customer_id'];
		$transaction['Customer']['first_name'] = $transaction['TransactionAddress'][0]['first_name'];
		$transaction['Customer']['last_name'] = $transaction['TransactionAddress'][0]['last_name'];
		$transaction['Customer']['email'] = $transaction['TransactionAddress'][0]['email']; // required
		$transaction['Customer']['username'] = $transaction['TransactionAddress'][0]['email']; // required
		//$transaction['Customer']['phone'] = $transaction['TransactionAddress'][0]['phone']; // required
		//debug($transaction);
		// generate a temporary password: ANNNN
		$transaction['Customer']['password'] = chr(97 + mt_rand(0, 25)) . rand(1000, 9999); // required
		
		// set their User Role Id
		$transaction['Customer']['user_role_id'] = (defined('__APP_DEFAULT_USER_REGISTRATION_ROLE_ID')) ? __APP_DEFAULT_USER_REGISTRATION_ROLE_ID : 3 ;
	  }

	  // copy Payment data to Shipment data if neccessary
	  if($transaction['TransactionAddress'][0]['shipping'] == '0') {
		  $transaction['TransactionAddress'][1] = $transaction['TransactionAddress'][0];
		  $transaction['TransactionAddress'][1]['type'] = 'shipping';
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
 * 
 * @todo make this better
 * 
 * @param boolean $isLoggedIn
 * @param array $data
 * @return array
 */
	public function completeUserAndTransactionData($isLoggedIn, $data) {
		try {
			$data['Transaction']['status'] = 'paid';

			if (!$isLoggedIn) {
				$this->Customer->save($data);
				//break;
				// Refactor their $data with their new Customer.id
				$data['Transaction']['customer_id'] = $this->Customer->id;
				$data['Customer']['id'] = $this->Customer->id;
				foreach ($data['TransactionAddress'] as &$transactionAddress) {
					$transactionAddress['transaction_id'] = $this->id;
					$transactionAddress['user_id'] = $this->Customer->id;
				}
			} else {
				$data['Transaction']['customer_id'] = CakeSession::read('Auth.User.id');
				$data['Customer']['id'] = CakeSession::read('Auth.User.id');
			}
			foreach ($data['TransactionItem'] as &$transactionItem) {
				$transactionItem['status'] = 'paid';
			}

			return $data;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	
	
/**
 * 
 * @param array $data
 * @return array
 * @throws Exception
 */
	public function beforePayment($data) {
		try {
			$data = $this->finalizeTransactionData($data);
			$data = $this->finalizeUserData($data);

			return $data;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	
/**
 * 
 * @param boolean $isLoggedIn
 * @param array $data
 * @throws Exception
 */
	public function afterSuccessfulPayment($isLoggedIn, $data) {
		try {
			$data = $this->completeUserAndTransactionData($isLoggedIn, $data);

			$this->save($data);

			foreach($data['TransactionItem'] as $txnItem) {
				$txnItem['transaction_id'] = $this->id;
				$this->TransactionItem->create();
				$this->TransactionItem->save($txnItem);
			}
			foreach($data['TransactionAddress'] as $txnAddr) {
				$txnAddr['transaction_id'] = $this->id;
				$this->TransactionAddress->create();
				$this->TransactionAddress->save($txnAddr);
			}
			
			// Create OR Update their payment processor data
			if($data['Customer']['Connection']) {
				$options = $this->gatherCheckoutOptions();
				// connection[id] should be pre-filled or empty
				$connection['id'] = (!empty($data['Customer']['Connection'][0]['id'])) ? $data['Customer']['Connection'][0]['id'] : null;
				$connection['user_id'] = $data['Customer']['id'];
				$connection['type'] = $options['paymentMode'];
				// connection[value] should be directly from the payment processor
				$connection['value'] = serialize($data['Customer']['Connection'][0]['value']);

				$this->Customer->Connection->save($connection);
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	
/**
 * Retrieves various stats for dashboard display
 * 
 * @param string $param
 * @return array|boolean
 */
        public function salesStats($period) {
            // configure period
            switch ($period) {
                case 'today':
                    $rangeStart = date('Y-m-d', strtotime('today'));
                    break;
                case 'thisWeek':
                    $rangeStart = date('Y-m-d', strtotime('this sunday'));
                    break;
                case 'thisMonth':
                    $rangeStart = date('Y-m-d', strtotime('first day of this month'));
                    break;
                case 'thisYear':
                    $rangeStart = date('Y-m-d', strtotime('first day of this year'));
                    break;
                case 'allTime':
                    $rangeStart = '0000-00-00';
                    break;
                default:
                    break;
            }
            
            // calculate data
            $data = $this->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        "created >= '$rangeStart'",
                        "modified >= '$rangeStart'",
                        ),
                    'status' => 'success'
                    )
            ));
            $data['count'] = count($data);
            $data['value'] = 0;
            foreach ($data as $order) {
                $data['value'] += $order['Transaction']['total'];
            }
           // debug($data); break;
           return ($data) ? $data : false;
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
