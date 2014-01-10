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
 * @todo Add LoggableBehavior and track who the referrer was from the stats in the session $this->triggerLog() in the model, if done right.
 */
class AppTransaction extends TransactionsAppModel {
 		
 	public $name = 'Transaction';

	public $actsAs = array(
		'Transactions.Buyable'
        );

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
    	'TransactionTax' => array(
			'className' => 'Transactions.TransactionTax',
			'foreignKey' => 'tax_id',
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
 * 
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
	    $options['countries'] = $this->TransactionTax->countries(array('type' => 'enabled'));
	    $options['states'] = $this->TransactionTax->states(array('type' => 'enabled'));
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
        } elseif ($transactionGuestId) {
            $userId = $transactionGuestId;
        } else {
            $userId = String::uuid();
            CakeSession::write('Transaction._guestId', $userId);
        }
        
        // Assign their Guest Cart to their Logged in self, if neccessary
        $this->reassignGuestCart($transactionGuestId, $authUserId);
        
        return $userId;
	}	

/**
 * Calculate
 * 
 * @param array $data
 * @return array
 */
	public function calculateTotal($data) {
        // defaults
        $data = $this->TransactionTax->applyTax($data);        
	    $subTotal = 0;
	    $shippingCharge = 0;
        
        // recalculate subtotal
        if (!empty($data['TransactionItem'])) {
		    foreach($data['TransactionItem'] as $txnItem) {
	            $subTotal += $txnItem['price'] * $txnItem['quantity'];
		    }
		}
        
		// overwrite the shipping_charge if there is a FlAT_SHIPPING_RATE set
        // GET THIS OUT OF HERE!!!!
		$defaultShippingCharge = defined('__TRANSACTIONS_FLAT_SHIPPING_RATE') ? __TRANSACTIONS_FLAT_SHIPPING_RATE : FALSE;
		if ($defaultShippingCharge !== FALSE) {
			$shippingCharge = number_format($defaultShippingCharge, 2, '.', false);
		}
	    $data['Transaction']['sub_total'] = number_format($subTotal, 2, '.', false);
	    $data['Transaction']['tax_charge'] = number_format($data['Transaction']['tax_charge'], 2, '.', false);
	    $data['Transaction']['shipping_charge'] = number_format($shippingCharge, 2, '.', false);
		$data['Transaction']['total'] = number_format($subTotal + $data['Transaction']['tax_charge'] + $shippingCharge, 2, '.', false);
        
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
	public function processCart($userId, $data = array()) {
		$options = $this->gatherCheckoutOptions();
		
	    $cart = Set::merge($this->find('first', array(
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
		    )), $data);
		$cart = $this->_prefillAddresses($cart);

	    if (empty($cart)) {
            return false;
        }
	    return $this->calculateTotal($cart);
	}


/**
 * Prefill Adresses
 * 
 * If the customer has checked out before we get their address and merge it into the cart data
 * 
 * @param array $cart 
 */
	protected function _prefillAddresses($data) {
		// get the last used address of the logged in user, if this transaction doesn't already have one
		if (empty($data['TransactionAddress']) && CakeSession::read('Auth.User.id')) {
			// billing first
			$transactionBilling = $this->TransactionAddress->find('first', array('conditions' => array('TransactionAddress.user_id' => CakeSession::read('Auth.User.id')), 'order' => array('TransactionAddress.modified' => 'DESC'), 'type' => 'billing'));
			if (!empty($transactionBilling)) {
				$data['TransactionAddress'][] = $transactionBilling['TransactionAddress'];
			}
			// billing first
			$transactionShipping = $this->TransactionAddress->find('first', array('conditions' => array('TransactionAddress.user_id' => CakeSession::read('Auth.User.id')), 'order' => array('TransactionAddress.modified' => 'DESC'), 'type' => 'shipping'));
			if (!empty($transactionBilling)) {
				$data['TransactionAddress'][] = $transactionBilling['TransactionAddress'];
			}
		}
		return $data;
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
         
		if (!$currentTransaction) {
			throw new Exception('Transaction missing.');
		}
       
		// update quantities
		$allHaveZeroQty = true;
		foreach ($submittedTransaction['TransactionItem'] as $submittedTxnItem) {
		    if ($submittedTxnItem['quantity'] > 0) {
                foreach ($currentTransaction['TransactionItem'] as $currentTxnItem) {
                    if ($currentTxnItem['id'] == $submittedTxnItem['id']) {
                        $currentTxnItem['quantity'] = $submittedTxnItem['quantity'];
                        $finalTxnItems[] = $currentTxnItem;  
                    }
                }
                $allHaveZeroQty = false;
		    }
		}
		if ($allHaveZeroQty) {
			throw new Exception('Cart is empty');
		}
		
		// unset the submitted TransactionItem's. They will be replaced after the merge.
		unset($submittedTransaction['TransactionItem']);
		
		// combine the Current and Submitted Transactions
		$officialTransaction = Set::merge($currentTransaction, $submittedTransaction);
		$officialTransaction['TransactionItem'] = $finalTxnItems;
      
        // add tax
        $officialTransaction = $this->TransactionTax->applyTax($officialTransaction);
		
		$officialTransaction = $this->calculateTotal($officialTransaction);
        
		// check for ARB Settings (will only be one TransactionItem @ this point if it's an ARB Transaction)
		$officialTransaction['Transaction']['is_arb'] = !empty($officialTransaction['TransactionItem'][0]['arb_settings']) ? 1 : 0;
        
        //Check Transaction Coupon code empty or not
        if ($officialTransaction['TransactionCoupon']['code']!='') {
           $officialTransaction = $this->TransactionCoupon->verify($officialTransaction); 
        }
		
   		$officialTransaction = $this->finalizeUserData($officialTransaction);
		
		// return the official transaction
		return $officialTransaction;
	}
	
	
/**
 * - Ensures that the necessary data is present to create a Customer
 * - Fills out the TransactionShipment fields when shipping info is same as billing info
 * 
 * @param array $transaction Transaction data that was posted by the cart form
 * @return array Same array with neccessary and completed fields
 */
	public function finalizeUserData($transaction) {
        // ensure their 'Customer' data has values when they are not logged in
        if (empty($transaction['Customer']['id']) || !isset($transaction['Customer'])) {
            $transaction['Customer']['first_name'] = $transaction['TransactionAddress'][0]['first_name'];
            $transaction['Customer']['last_name'] = $transaction['TransactionAddress'][0]['last_name'];
            $transaction['Customer']['email'] = $transaction['TransactionAddress'][0]['email']; // required
            $transaction['Customer']['username'] = $transaction['TransactionAddress'][0]['email']; // required
            
            // generate a temporary password: Aaa9999 (then shuffled)
            $transaction['Customer']['password'] = str_shuffle('*$' . chr(97 + mt_rand(0, 25)) . chr(97 + mt_rand(0, 25)) . strtoupper(chr(97 + mt_rand(0, 25))) . rand(1000, 9999)); // required
            
            // set their User Role Id
            $transaction['Customer']['user_role_id'] = (defined('__APP_DEFAULT_USER_REGISTRATION_ROLE_ID')) ? __APP_DEFAULT_USER_REGISTRATION_ROLE_ID : 3 ;
        }
		
		if (!empty($transaction['TransactionAddress'][0]['phone'])) {
			// make sure the phone is just numbers
			$transaction['TransactionAddress'][0]['phone'] = ZuhaInflector::numerate($transaction['TransactionAddress'][0]['phone']);
		}
        
        // copy Payment data to Shipment data if neccessary
        if (isset($transaction['TransactionAddress'][0]['shipping']) && $transaction['TransactionAddress'][0]['shipping'] == '0') {
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
	    foreach ($transactions as $transaction) {
            foreach ($transaction['TransactionItem'] as $transactionItem) {
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
			//$data['Transaction']['status'] = 'paid'; // this really belongs here, but we have an issue with processors 
			if (!$isLoggedIn) {
				$data['User'] = $data['Customer']; // add the customer data to the user alias so that it all gets saved right
				$this->Customer->add($data);
				// Refactor their $data with their new Customer.id  (it's kind of odd how you get $this->Contact here, but its because Customer is User and User uses Contact first then adds a User -- if that helps :)
				$userId = !empty($this->Contact->User->id) ? $this->Contact->User->id : $this->Customer->id;
				$data['Transaction']['customer_id'] = $userId;
				$data['Customer']['id'] = $userId;
				foreach ($data['TransactionAddress'] as &$transactionAddress) {
					$transactionAddress['transaction_id'] = $this->id;
					$transactionAddress['user_id'] = $userId;
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
 * @param array $data A payment object
 * @return array
 * @throws Exception
 */
	public function beforePayment($data) {
		try {
            $data = $this->finalizeTransactionData($data); 
            
			return $data;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	
	
/**
 * 
 * @param boolean $isLoggedIn
 * @param array $data A payment object
 * @throws Exception
 */
	public function afterSuccessfulPayment($isLoggedIn, $data) {
		try {
			$data = $this->completeUserAndTransactionData($isLoggedIn, $data);
			
			$data[$this->alias]['status'] = 'paid';

			$this->save($data);
			
			// run the afterSuccessfulPayment callbacks
            $transactionItems = $data['TransactionItem'];
            foreach ($transactionItems as $transactionItem) {
				App::uses($transactionItem['model'], ZuhaInflector::pluginize($transactionItem['model']) . '.Model');
				$Model = new $transactionItem['model'];
				if( method_exists($Model,'afterSuccessfulPayment') && is_callable(array($Model,'afterSuccessfulPayment')) ) {
				   $Model->afterSuccessfulPayment($data);
				}
            }
			
			// save TransactionItems, with relation to Transaction.id
			foreach ($data['TransactionItem'] as $txnItem) {
				$txnItem['transaction_id'] = $this->id;
				$this->TransactionItem->create();
				$this->TransactionItem->save($txnItem);
			}
			
			// save TransactionAddresses, with relation to Transaction.id
			foreach ($data['TransactionAddress'] as $txnAddr) {
				$txnAddr['transaction_id'] = $this->id;
				$txnAddr['user_id'] = empty($txnAddr['user_id']) ? CakeSession::read('Auth.User.id') : $txnAddr['user_id'];
				if (!empty($txnAddr['street_address_1'])) {
					$this->TransactionAddress->create();
					$this->TransactionAddress->save($txnAddr);
				}
			}
			
			// save Connection data, if any
			if (!empty($data['Customer']['Connection'])) {
				$options = $this->gatherCheckoutOptions();
				// connection[id] should be pre-filled or empty
				$connection['id'] = ( !empty($data['Customer']['Connection'][0]['id']) ) ? $data['Customer']['Connection'][0]['id'] : null;
				$connection['user_id'] = $data['Customer']['id'];
				$connection['type'] = $options['paymentMode'];
				// connection[value] should be directly from the payment processor
				$connection['value'] = serialize($data['Customer']['Connection'][0]['value']);
				$this->Customer->Connection->save($connection);
			}
			
            $this->_sendReceipt($data);
			
			return $data;

		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


    
/**
 * Send transaction email
 *
 * @param array $data
 */
    protected function _sendReceipt($data = array()) {
    	$subject = 'Thank You for Your Purchase';
    	$message = '<p><strong>Thank you for your purchase</strong></p>';
    	$items = '<table style="width:100%;"><tr><th>Qty</th><th>Name</th><th>Price</th><th>Action</th></tr>';
    	foreach ($data['TransactionItem'] as $item) {
    		$items .= __('<tr><td style="text-align:center">%s</td><td style="text-align:center">%s</td><td style="text-align:center">%s</td><td style="text-align:center"><a href="http://%s%s">View</a></td></tr>', $item['quantity'], $item['name'], $item['price'], $_SERVER['HTTP_HOST'], $item['_associated']['viewLink']);
    	}
    	$items .= '</table>';
    	$items .= '<table style="width:100%;">';
		$items .= '<tr><td>Transaction ID</td><td>'.$data['Transaction']['id'].'</td></tr>';
		$items .= '</table>';
    	$message = $message . $items;
    	if (defined('__TRANSACTIONS_RECEIPT_EMAIL')) {
    		$email = unserialize(__TRANSACTIONS_RECEIPT_EMAIL);
    		$subject = stripcslashes($email['subject']);
    		$message = str_replace('{element: transactionItems}', $items, stripcslashes($email['body']));
    	}
    	// this probably doesn't throw an exception if it fails
    	$this->__sendMail($data['TransactionAddress'][0]['email'], $subject, $message);
    }
	

	public function generateTransactionNumber() {
		return str_pad($this->find('count') + 1, 7, '0', STR_PAD_LEFT);
	}

	
/**
 * Retrieves various stats for dashboard display
 * 
 * @todo This could probably be done in one query, then shaped with PHP ?
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
                $rangeStart = date('Y-m-d', strtotime('last sunday'));
                break;
            case 'thisMonth':
                $rangeStart = date('Y-m-d', strtotime('first day of this month'));
                break;
            case 'thisYear':
                $rangeStart = date('Y') . '-01-01';
                break;
            case 'allTime':
                $rangeStart = '0000-00-00';
                break;
            default:
                break;
		}
		$rangeStart .= ' 00:00:00';
        // calculate data
        $data = $this->find('all', array(
            'conditions' => array(
                'OR' => array(
                    "created >= '$rangeStart'",
                    "modified >= '$rangeStart'",
                    ),
                'status' => 'paid'
                )
        ));
        $data['count'] = count($data);
        $data['value'] = 0;
        foreach ($data as $order) {
            $data['value'] += $order['Transaction']['total'];
        }
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

if ( !isset($refuseInit) ) {
	class Transaction extends AppTransaction {}
}
