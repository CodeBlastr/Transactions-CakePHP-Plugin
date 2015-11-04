<?php
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
require_once(VENDORS . DS . 'braintree' . DS . 'lib' . DS . 'Braintree.php');

class BraintreePayment extends AppModel {

	public $name = 'Braintree';

	public $config = array();

	public $statusTypes = ''; // required var, sent from BuyableBehavior

	public $useTable = false;
	
	public $errors = false;
	
	public $response = array();
	
	public $recurring = false;
	
	public $modelName = '';
	
	public $addressModel = '';
	
	public $itemModel = '';

	
	public function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
		if (defined('__TRANSACTIONS_BRAINTREE')) {
            $this->config = unserialize(__TRANSACTIONS_BRAINTREE);
            Braintree_Configuration::environment($this->config['environment']);
            Braintree_Configuration::merchantId($this->config['merchantId']);
            Braintree_Configuration::publicKey($this->config['publicKey']);
            Braintree_Configuration::privateKey($this->config['privateKey']);
		} else{
            throw new Exception('Brain Tree config not found, please set in admin dash board');
        }
	}


/**
 * Pay method
 *
 * @param array $data
 * @return type
 * @throws Exception
 */
	public function pay($data = null) {
		$this->modelName = !empty($this->modelName) ? $this->modelName : 'Transaction';
		try {
			// Do we need to save a New Customer or are we using an Existing Customer     
			if (empty($data['Customer']['Connection'])) {
				// create their Customer
				$userData = $this->createCustomer($data);
              	$data['Customer']['Connection'][0]['value']['Customer']['Id'] = $userData;
			} else {
				// we have their customer, unserialize the data
				$data['Customer']['Connection'][0]['value'] = unserialize($data['Customer']['Connection'][0]['value']);
			}
			
			// Do we need to save a New Payment Method, or are they using a Saved Payment Method
			if (!empty($data[$this->modelName]['card_number'])) {   
				// Credit Card Account
				$accountData = $this->addCreditCard($data['Customer']['Connection'][0]['value']['Customer']['Id'], $data);
				$data['Customer']['Connection'][0]['value']['Account']['CreditCard'][] = $accountData;
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $accountData['Id'];
				// don't know what this is for // $data[$this->modelName]['paymentSubType'] = 'Moto';
			} else {
				// they are using a Saved Payment Method; defined by an Id
                $cc_count = count($data['Customer']['Connection'][0]['value']['Account']['CreditCard']);
                if($cc_count > 0) {
                   for($i=0; $i < $cc_count; $i++) {
                        if ($data[$this->modelName]['paysimple_account'] == $data['Customer']['Connection'][0]['value']['Account']['CreditCard'][$i]['Id']) {
                        	 $data[$this->modelName]['paymentSubType'] = 'Moto';  
						} 
                   } 
                }
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $data[$this->modelName]['braintree_account'];
			}

			if ($data[$this->modelName]['is_arb']) {
				return $this->createRecurringPayment($data);
			} else {
				$paymentData = $this->doSales($data); // this function checks for the 'braintree_account' field and whether to charge new card, or old payment method
			}
            if ($paymentData->success) {
                $data[$this->modelName]['processor_response'] = $paymentData->transaction->processorResponseText;
                $data[$this->modelName]['processor_transaction_id'] = $paymentData->transaction->id;
                $data[$this->modelName]['Payment'] = $paymentData;
                $data[$this->modelName]['status'] = $paymentData->success ? 'paid' : 'failed';
            } else {
            	throw new Exception($paymentData->_attributes['message']);
            }
            return $data;
		} catch (Exception $exc) {
			throw new Exception($exc->getMessage());
		}
	}

/**
 * this is member's funding
 * where release money will to
 */
    public function createSubMerchantFundingAccount($data) {
        $data['funding']['destination'] = Braintree_MerchantAccount::FUNDING_DESTINATION_BANK;
        $data['masterMerchantAccountId'] = $this->config['merchantAccount'];
        $data['tosAccepted'] = true;
		
        $result = Braintree_MerchantAccount::create($data);
        if($result->success){
           return $result->success;
        }else{
            throw new Exception($result->message);
        }
    }

/**
 * update member's funding account info
 */
    public function updateSubMerchantFundingAccount($data){
        $data['funding']['destination'] = Braintree_MerchantAccount::FUNDING_DESTINATION_BANK;
        $data['masterMerchantAccountId'] = $this->config['merchantAccount'];
        $data['tosAccepted'] = true;
        $accountId = $data['id'];
        unset($data['id']);
        $result = Braintree_MerchantAccount::update($accountId,$data);
        if ($result->success) {
            return $result->success;
        } else{
            throw new Exception($result->message);
        }
    }

/**
 * get sub merchant funding account
 */
    public function getSubMerchantFundingAccount($accountId){
        if (!empty($accountId)) {
            return Braintree_MerchantAccount::find($accountId);
        } else{
            throw new Exception('Funding Account Not Found: ' . $accountId);
        }
    }

/**
 * is need escrow
 */
    private function isNeedEscrow($data){
        $item = $data['TransactionItem'][0];
		// this is not a good way to check if escrow is needed
		// we need something that checks if it IS not IS NOT
        // if(is_null($item['is_virtual']) || empty($item['is_virtual'])){
            // return true;
        // }
        return false;
    }

/**
 * do sales
 */
    public function doSales($data) {	
		// You should not making custom fields like this, use the Transaction key
        // $data['brainTree']['creditCard']['expirationDate'] = $data['brainTree']['creditCard']['month'] . '/' . $data['brainTree']['creditCard']['year'];
        // unset($data['brainTree']['creditCard']['year'],$data['brainTree']['creditCard']['month']);		
        
        // this is the way fields should be mapped coming in (matches paysimple)
        // more field info here : https://www.braintreepayments.com/docs/php/transactions/create
        
        // card info
        if (!empty($data['Transaction']['braintree_account'])) {
        	$params['paymentMethodToken'] = $data['Transaction']['braintree_account'];
        } else {
	        $params['creditCard']['number'] = $data['Transaction']['card_number']; // was this, but should not be... $data['brainTree']['creditCard'];
	        $params['creditCard']['expirationDate'] = !empty($data['Transaction']['card_expire']['month']) && !empty($data['Transaction']['card_expire']['year']) ? $data['Transaction']['card_expire']['month'] . '/' . $data['Transaction']['card_expire']['year'] : $data['Transaction']['card_expire'];
			$params['creditCard']['cardholderName'] = $data['TransactionAddress'][0]['first_name'] . ' ' . $data['TransactionAddress'][0]['last_name'];
			$params['creditCard']['cvv'] = $data['Transaction']['card_sec'];
		}
		
		// customer info
		$params['customer']['firstName'] = $data['TransactionAddress'][0]['first_name'];
		$params['customer']['lastName'] = $data['TransactionAddress'][0]['last_name'];
		$params['customer']['company'] = $data['Contact']['company'];
		$params['customer']['phone'] = $data['TransactionAddress'][0]['phone'];
		// possible but doesn't exist on any form I know of // $params['customer']['fax'] = $data['TransactionAddress'][0][''];
		// possible but doesn't exist on any form I know of // $params['customer']['website'] = $data['TransactionAddress'][0][''];
		$params['customer']['email'] = $data['TransactionAddress'][0]['email'];
		
		// billing info
		$params['billing']['firstName'] = $data['TransactionAddress'][0]['first_name'];
		$params['billing']['lastName'] = $data['TransactionAddress'][0]['last_name'];
		$params['billing']['company'] = $data['Contact']['company'];
		$params['billing']['streetAddress'] = $data['TransactionAddress'][0]['street_address_1'];
		$params['billing']['extendedAddress'] = $data['TransactionAddress'][0]['street_address_2'];
		$params['billing']['locality'] = $data['TransactionAddress'][0]['city'];
		$params['billing']['region'] = $data['TransactionAddress'][0]['state'];
		$params['billing']['postalCode'] = $data['TransactionAddress'][0]['zip'];
		$params['billing']['countryCodeAlpha2'] = 'US'; // needs to be updated so that it isn't hard coded

		// shipping info
		$params['shipping']['firstName'] = !empty($data['TransactionAddress'][1]['first_name']) ? $data['TransactionAddress'][1]['first_name'] : $data['TransactionAddress'][0]['first_name'];
		$params['shipping']['lastName'] = !empty($data['TransactionAddress'][1]['last_name']) ? $data['TransactionAddress'][1]['last_name'] : $data['TransactionAddress'][0]['last_name'];
		$params['shipping']['company'] = $data['Contact']['company'];
		$params['shipping']['streetAddress'] = !empty($data['TransactionAddress'][1]['street_address_1']) ? $data['TransactionAddress'][1]['street_address_1'] : $data['TransactionAddress'][0]['street_address_1'];
		$params['billing']['extendedAddress'] = !empty($data['TransactionAddress'][1]['street_address_2']) ? $data['TransactionAddress'][1]['street_address_2'] : $data['TransactionAddress'][0]['street_address_2'];
		$params['shipping']['locality'] = !empty($data['TransactionAddress'][1]['city']) ? $data['TransactionAddress'][1]['city'] : $data['TransactionAddress'][0]['city'];
		$params['shipping']['region'] = !empty($data['TransactionAddress'][1]['state']) ? $data['TransactionAddress'][1]['state'] : $data['TransactionAddress'][0]['state'];
		$params['shipping']['postalCode'] = !empty($data['TransactionAddress'][1]['zip']) ? $data['TransactionAddress'][1]['zip'] : $data['TransactionAddress'][0]['zip'];
		$params['shipping']['countryCodeAlpha2'] = 'US'; // needs to be updated so that it isn't hard coded
		
		// transaction info
        $params['amount'] = $data['Transaction']['total'];
        $params['orderId'] = $data['Transaction']['id'];
		
		// options
        $params['options']['submitForSettlement'] = true; // probably shouldn't be set for all transaction types, eg. needs to be moved

        if ($this->isNeedEscrow($data)) {
            $subMerchant = $data['TransactionItem'][0]['_associated']['seller']['merchant_account'];
            if (empty($subMerchant)) {
               throw new Exception('Seller does not have funding account');
            }
            $params['options'] = array(
                'submitForSettlement' => true,
                'holdInEscrow' => true,
            	);
            $params['serviceFeeAmount'] = $data['Transaction']['total'] * ($this->config['serviceFee']/100);
            $params['merchantAccountId'] = $subMerchant;
        }
        return Braintree_Transaction::sale($params);
    }

/**
 * get customer list
 * this looks like PaySimple but wasn't 100% sure (delete if everything is working)
 */
	// public function getCustomerList() {
		// return $this->_sendRequest('GET', '/customer');
	// }

/**
 * get customer
 * 
 */
 	public function getCustomer($customerId = null) {
 		return Braintree_Customer::find($customerId);
	}

/**
 * create customer
 */
    public function createCustomer($data) {
		$result = Braintree_Customer::create(array(
			'firstName' => $data['Customer']['first_name'],
			'lastName' => $data['Customer']['last_name'],
//			'company' => 'Jones Co.',
			'email' => $data['Customer']['email'],
			'phone' => $data['TransactionAddress'][0]['phone'],
//			'fax' => '419.555.1235',
//			'website' => 'http://example.com'
		));

		if ($result->success) {
			return $result->customer->id; // Generated customer id
		} else {
			return false;
		}
	}

/**
 * 
 * @param type $customerId
 * @param type $data
 * @return boolean
 */
	public function addCreditCard($customerId, $data) {
		$result = Braintree_CreditCard::create(array(
			'customerId' => $customerId,
			'number' => $data['Transaction']['card_number'],
			'expirationDate' => empty($data['Transaction']['card_expire']) ? $data['Transaction']['card_expire'] : $data['Transaction']['card_expire']['month'] . '/' . $data['Transaction']['card_expire']['year'],
			'cardholderName' => $data['Customer']['first_name'] . ' ' . $data['Customer']['last_name'],
			'cvv' => $data['Transaction']['card_sec']
//			'options' => array(
//				'failOnDuplicatePaymentMethod' => true
//			)
		));

		if ($result->success) {
			return array(
				'Id' => $result->creditCard->token,
				'Issuer' => $result->creditCard->cardType,
				'CreditCardNumber' => $result->creditCard->maskedNumber,
				'ExpirationDate' => $result->creditCard->expirationDate
			);
		} else {
			return false;
		}
	}

/**
 * get recurring payments
 * https://developers.braintreepayments.com/javascript+php/reference/request/subscription/find
 * 
 */
 	public function getRecurringPayments($subscriptionId = null) {
 		return Braintree_Subscription::find($subscriptionId);
	}

/**
 * find subscriptions by customer
 * https://developers.braintreepayments.com/javascript+php/reference/request/customer/find
 * https://developers.braintreepayments.com/javascript+php/reference/request/subscription/find
 * 
 */
 	public function getCustomerSubscriptions($customerId = null) {
 		$customer = $this->getCustomer($customerId);
		$subscriptions = array();
		foreach ($customer->creditCards as $card) {
			$subscriptions = array_merge($subscriptions, $card->subscriptions);
		}
		return $subscriptions;
 	}
	
/**
 * @todo https://developers.braintreepayments.com/javascript+php/guides/recurring-billing/plans#add-ons-and-discounts
 * @param type $data
 * @return string
 * @throws Exception
 */
	public function createRecurringPayment($data) {
		// ensure we have a Braintree Customer ID
		$data['Customer']['Connection'][0]['value'] = unserialize($data['Customer']['Connection'][0]['value']);
//		debug($data);
		if (empty($data['Customer']['Connection'][0]['value']['Account']['Id'])) {
			$customerId = $this->createCustomer($data);
			if (!$customerId) {
				throw new Exception('Unable to create customer payment account.');
			}
			$data['Customer']['Connection'][0]['value']['Account']['Id'] = $customerId;
		}
		// ensure we have a credit card id to bill 
		if (empty($data['Customer']['Connection'][0]['value']['Account']['CreditCard'][0]['Id'])) {
			$creditCardData = $this->addCreditCard($customerId, $data);
			if (!$creditCardData) {
				throw new Exception('Unable to create customer payment method.');
			}
			$data['Customer']['Connection'][0]['value']['Account']['CreditCard'][] = $creditCardData;
		}
		// purchase the plan
		$arbSettings = unserialize($data['TransactionItem'][0]['arb_settings']);
		$result = Braintree_Subscription::create(array(
			'paymentMethodToken' => $data['Customer']['Connection'][0]['value']['Account']['CreditCard'][0]['Id'],
			'planId' => $arbSettings['BraintreePlan']
		));

		if ($result->success) {
			$data[$this->modelName]['Payment'] = $result;
			$data[$this->modelName]['status'] = 'paid';
		} else {
			$data[$this->modelName]['status'] = 'failed';
		}

		return $data;
	}
	
	public function createRecurringPaymentPS($data) {
		$this->itemModel = !empty($this->itemModel) ? $this->itemModel : 'TransactionItem';
		// this was in the pay() function above, we moved it here, but aren't sure that $paymentData will still be equal to the right info
		// if(empty($data[$this->itemModel][0]['price'])) {
			// // When price is empty, there is a free trial. In this case, set up an ARB payment as usual.
			// $paymentData = $this->createRecurringPayment($data);
		// } else {
			// // When a price is set, we charge that as a normal payment, then setup an ARB who's 1st payment is in $StartDate days.
			// $paymentData = $this->createPayment($data);  
			// $paymentData = $this->createRecurringPayment($data);
		// }
		
		if(!empty($data[$this->itemModel][0]['price'])) {
			$this->createPayment($data);  
		}
		$arbSettings = unserialize($data[$this->itemModel][0]['arb_settings']);
		
		// determine & format StartDate
		$arbSettings['StartDate'] = empty($arbSettings['StartDate']) ? 0 : $arbSettings['StartDate'];
		$arbSettings['StartDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['StartDate'].' days'));

		// determine & format EndDate
		if(!empty($arbSettings['EndDate'])) {
			$arbSettings['EndDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['arb_settings']['EndDate'].' days'));
		}
		// determine & format FirstPaymentDate
		if(!empty($arbSettings['FirstPaymentDate'])) {
			$arbSettings['FirstPaymentDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['arb_settings']['FirstPaymentDate'].' days'));
		}
		
		if (!empty($arbSettings['ExecutionFrequencyType']) && is_string($arbSettings['ExecutionFrequencyType'])) {
			$possibilities = array(
				'Daily' => 1,
				'Weekly' => 2,
				'BiWeekly' => 3,
				'FirstofMonth' => 4,
				'SpecificDayofMonth' => 5,
				'Monthly' => 5, // does not exist with PaySimple, this is an auto setup for us
				'LastofMonth' => 6,
				'Quarterly' => 7,
				'SemiAnnually' => 8,
				'Annually' => 9
				);
			$arbSettings['ExecutionFrequencyType'] = $possibilities[$arbSettings['ExecutionFrequencyType']];
		}
		if ($arbSettings['ExecutionFrequencyType'] == 5 && empty($arbSettings['ExecutionFrequencyParameter'])) {
			// set the date of the payment to today for monthly subscriptions, without a specified date
			$arbSettings['ExecutionFrequencyParameter'] = date('j'); // 1 - 31
		}
		$params = array(
			'PaymentAmount' => $arbSettings['PaymentAmount'], // required
			'FirstPaymentAmount' => $arbSettings['FirstPaymentAmount'],
			'FirstPaymentDate' => $arbSettings['FirstPaymentDate'],
			'AccountId' => $data['Customer']['Connection'][0]['value']['Account']['Id'], // required
			'InvoiceNumber' => NULL,
			'OrderId' => $data[$this->modelName]['id'],
			'PaymentSubType' => $data[$this->modelName]['paymentSubType'], // required
			'StartDate' => $arbSettings['StartDate'], // required
			'EndDate' => $arbSettings['EndDate'],
			'ScheduleStatus' => 1, // required // Active
			'ExecutionFrequencyType' => $arbSettings['ExecutionFrequencyType'], // required
			'ExecutionFrequencyParameter' => $arbSettings['ExecutionFrequencyParameter'], // "required":false, "type":"integer","description":"The execution frequency parameter specifies the day of month for a SpecificDayOfMonth frequency or specifies day of week for Weekly or BiWeekly schedule. It is required when ExecutionFrequncyType is SpecificDayofMonth, Weekly or BiWeekly.",
			'Description' => __SYSTEM_SITE_NAME,
			'Id' => 0
		);
		return $this->_sendRequest('POST', '/recurringpayment', $params);
	}


}