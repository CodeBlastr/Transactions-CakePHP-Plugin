<?php

/**
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2012
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 * 
 * @package			zuha
 * @subpackage		zuha.app.plugins.transactions
 * @author			Joel Byrnes <joel@razorit.com>
 * @link			https://sandbox-api.paysimple.com/v4/Help/
 */
App::uses('HttpSocket', 'Network/Http');

class PaysimpleComponent extends Component {

	public $name = 'Paysimple';
	public $config = array(
		'environment' => 'sandbox',
		'apiUsername' => '',
		'sharedSecret' => '',
	);
	public $errors = false;
	public $response = array();

	public function __construct(ComponentCollection $collection, $config = array()) {
		parent::__construct($collection, $config);
		if (defined('__TRANSACTIONS_PAYSIMPLE')) {
			$settings = unserialize(__TRANSACTIONS_PAYSIMPLE);
            $this->config = Set::merge($this->config, $config, $settings);
		}

        // check required config
        if (empty($this->config['apiUsername']) || empty($this->config['sharedSecret'])) {
            throw new Exception('Payment configuration NOT setup, contact admin with error code : 923804892030123');
        }
		if (!in_array('Connections', CakePlugin::loaded())) {
            throw new Exception('Connections plugin is required, contact admin with error code : 72984359283745');
		}
		$this->_httpSocket = new HttpSocket();
	}


/**
 * 
 * @param array $data
 * @return type
 * @throws Exception
 */
	public function Pay($data) {
		try {      
			/**
			 *	Prepare the Customer data
			 *  Do we need to save a New Customer or are we using an Existing Customer
			 */
			if ( empty($data['Customer']['Connection']) ) {
				// create their Customer
				$userData = $this->createCustomer($data);  
              	$data['Customer']['Connection'][0]['value']['Customer']['Id'] = $userData['Id'];
			} else {
				// we have their customer, unserialize the data
				$data['Customer']['Connection'][0]['value'] = unserialize($data['Customer']['Connection'][0]['value']);
			}

			/**
			 *  Prepare their chosen Payment Method for use.
			 *  Do we need to save a New Payment Method, or are they using a Saved Payment Method
			 */
			if ( !empty($data['Transaction']['ach_account_number']) ) {
				// ACH Account
				$accountData = $this->addAchAccount($data);
				$data['Customer']['Connection'][0]['value']['Account']['Ach'][] = $accountData;
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $accountData['Id'];
				$data['Transaction']['paymentSubType'] = 'Web';
			} elseif ( !empty($data['Transaction']['card_number']) ) {
				// Credit Card Account
				$accountData = $this->addCreditCardAccount($data);
				$data['Customer']['Connection'][0]['value']['Account']['CreditCard'][] = $accountData;
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $accountData['Id'];
				$data['Transaction']['paymentSubType'] = 'Moto';
			} else {
				// Saved Payment Methods
                $ach_count = count($data['Customer']['Connection'][0]['value']['Account']['Ach']);
                $cc_count = count($data['Customer']['Connection'][0]['value']['Account']['CreditCard']);
                if ( $ach_count > 0 ) {
                   for ( $i = 0; $i < $ach_count; $i++ ) {
                       if ( $data['Transaction']['paysimple_account'] == $data['Customer']['Connection'][0]['value']['Account']['Ach'][$i]['Id'] ) {
						   $data['Transaction']['paymentSubType'] = 'Web';
					   }
                   } 
                }
                if ( $cc_count > 0 ) {
                   for ( $i = 0; $i < $cc_count; $i++ ) {
                        if ( $data['Transaction']['paysimple_account'] == $data['Customer']['Connection'][0]['value']['Account']['CreditCard'][$i]['Id'] ) {
							$data['Transaction']['paymentSubType'] = 'Moto';
						}
                   } 
                }
				// they are using a Saved Payment Method; defined by an Id
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $data['Transaction']['paysimple_account'];
			}

            /**
			 * make the actual payment
			 */
			// ARB Payments
			if ( $data['Transaction']['is_arb'] ) {
				if ( empty($data['TransactionItem'][0]['price']) ) {
					// When price is empty, there is a free trial. In this case, set up an ARB payment as usual.
					$paymentData = $this->createRecurringPayment($data);
				} else {
					// When a price is set, we charge that as a normal payment, then setup an ARB who's 1st payment is in $StartDate days.
					$paymentData = $this->createPayment($data);  
					$paymentData = $this->createRecurringPayment($data);
				}
				$data['Customer']['Connection'][0]['value']['Arb']['scheduleId'] = $paymentData['Id'];
				$data['Transaction']['processor_response'] = $paymentData['ScheduleStatus'];
			} else {
				// ACH or Credit Card Payments
				$paymentData = $this->createPayment($data);   
				$data['Transaction']['processor_response'] = $paymentData['Status'];
			}

			if ( $data['Transaction']['processor_response'] == 'Failed' ) {
				throw new Exception($paymentData['ProviderAuthCode']);
			}

			$data['Transaction']['Payment'] = $paymentData;

			return $data;

		} catch (Exception $exc) {
			throw new Exception($exc->getMessage());
		}

	}

/**
 *
 * @return boolean|array
 */
	public function getCustomerList() {
		return $this->_sendRequest('GET', '/customer');
	}

/**
 * Creates a Customer record when provided with a Customer object
 * @link https://sandbox-api.paysimple.com/v4/Help/Customer#post-customer
 * 
 * @param array $data
 * @return boolean|array
 */
	public function createCustomer($data) {
		$safeStateCode = str_replace('US-', '', $data['TransactionAddress'][0]['state']);
		$safeStateCode = str_replace('CA-', '', $safeStateCode);
		
		$params = array(
			'FirstName' => $data['TransactionAddress'][0]['first_name'],
			'LastName' => $data['TransactionAddress'][0]['last_name'],
			//'Company' => $data['Meta']['company'],
			'BillingAddress' => array(
				'StreetAddress1' => $data['TransactionAddress'][0]['street_address_1'],
				'StreetAddress2' => $data['TransactionAddress'][0]['street_address_2'],
				'City' => $data['TransactionAddress'][0]['city'],
				'StateCode' => $safeStateCode,
				'Country' => $data['TransactionAddress'][0]['country'],
				'ZipCode' => $data['TransactionAddress'][0]['zip'],
			),
			'ShippingSameAsBilling' => true,
			'Email' => $data['Customer']['email'],
			//'Phone' => $data['Customer']['phone'],
			'Phone' => $data['TransactionAddress'][0]['phone'],
		);

		if (isset($data['TransactionAddress'][0]['shipping']) && $data['TransactionAddress'][0]['shipping'] == 'checked') {
			// their shipping is not the same as their billing
			$safeStateCode = str_replace('US-', '', $data['TransactionAddress'][1]['state']);
			$safeStateCode = str_replace('CA-', '', $safeStateCode);
			$params['ShippingSameAsBilling'] = false;
			$params['BillingAddress'] = array(
				'StreetAddress1' => $data['TransactionAddress'][1]['street_address_1'],
				'StreetAddress2' => $data['TransactionAddress'][1]['street_address_2'],
				'City' => $data['TransactionAddress'][1]['city'],
				'StateCode' => $safeStateCode,
				'ZipCode' => $data['TransactionAddress'][1]['zip'],
			);
		}
		return $this->_sendRequest('POST', '/customer', $params);
	}

/**
 *
 * @param integer $userId
 * @return boolean|array
 */
	public function getAccounts($userId) {
		return $this->_sendRequest('GET', '/customer/' . $userId . '/accounts');
	}

/**
 * Creates a Credit Card Account record when provided with a Credit Card Account object
 * @link https://sandbox-api.paysimple.com/v4/Help/Account#post-ccaccount
 * 
 * @param array $data
 * @return boolean|array
 */
	public function addCreditCardAccount($data) {        
		// ensure that the month is in 2-digit form || last ditch validation
		$data['Transaction']['card_exp_month'] = str_pad($data['Transaction']['card_exp_month'], 2, '0', STR_PAD_LEFT);
		
		$params = array(
			'Id' => 0,
			'IsDefault' => true,
			'Issuer' => $this->getIssuer($data['Transaction']['card_number']),
			'CreditCardNumber' => $data['Transaction']['card_number'],
			'ExpirationDate' => $data['Transaction']['card_exp_month'] . '/' . $data['Transaction']['card_exp_year'],
			'CustomerId' => $data['Customer']['Connection'][0]['value']['Customer']['Id'],
		);
		return $this->_sendRequest('POST', '/account/creditcard', $params);
	}

/**
 * Creates an ACH Account record when provided with an ACH Account object
 * @link https://sandbox-api.paysimple.com/v4/Help/Account#post-achaccount
 * 
 * @param array $data
 * @return boolean|array
 */
	public function addAchAccount($data) {
		if(empty($data['Transaction']['ach_is_checking_account'])) $data['Transaction']['ach_is_checking_account'] = false;
		
		$params = array(
			'Id' => 0,
			'IsDefault' => true,
			'IsCheckingAccount' => $data['Transaction']['ach_is_checking_account'],
			'RoutingNumber' => $data['Transaction']['ach_routing_number'],
			'AccountNumber' => $data['Transaction']['ach_account_number'],
			'BankName' => $data['Transaction']['ach_bank_name'],
			'CustomerId' => $data['Customer']['Connection'][0]['value']['Customer']['Id']
		);

		return $this->_sendRequest('POST', '/account/ach', $params);
	}

/**
 * Creates a Payment record when provided with a Payment object.
 * This is a one-time payment that will be created on the current date for the Customer with the specified Account Id.
 * @link https://sandbox-api.paysimple.com/v4/Help/Payment#post-payment
 * 
 * @param array $data
 * @return boolean|array
 */
	public function createPayment($data) {
		$params = array(
			'AccountId' => $data['Customer']['Connection'][0]['value']['Account']['Id'],
			'InvoiceId' => NULL,
			'Amount' => $data['Transaction']['total'],
			'IsDebit' => false, // IsDebit indicates whether this Payment is a refund.
			'InvoiceNumber' => NULL,
			'PurchaseOrderNumber' => NULL,
			'OrderId' => $data['Transaction']['id'],
			'Description' => __SYSTEM_SITE_NAME, //$data['Transaction']['description'],
			'CVV' => $data['Transaction']['card_sec'],
			'PaymentSubType' => $data['Transaction']['paymentSubType'],
			'Id' => 0
		);   
		return $this->_sendRequest('POST', '/payment', $params);
	}
	
	
/**
 * Creates a Payment Schedule record when provided with a Payment Schedule object
 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#post-recurringpayment
 * 
 * @param array $data
 * @return boolean|array
 */
	public function createRecurringPayment($data) {

		$arbSettings = unserialize($data['TransactionItem'][0]['arb_settings']);
		
		// determine & format StartDate
		$arbSettings['StartDate'] = empty($arbSettings['StartDate']) ? 0 : $arbSettings['StartDate'];
		$arbSettings['StartDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['StartDate'].' days'));

		// determine & format EndDate
		if ( !empty($arbSettings['EndDate']) ) {
			$arbSettings['EndDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['arb_settings']['EndDate'].' days'));
		}
		// determine & format FirstPaymentDate
		if ( !empty($arbSettings['FirstPaymentDate']) ) {
			$arbSettings['FirstPaymentDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['arb_settings']['FirstPaymentDate'].' days'));
		}
		$params = array(
			'PaymentAmount' => $arbSettings['PaymentAmount'], // required
			'FirstPaymentAmount' => $arbSettings['FirstPaymentAmount'],
			'FirstPaymentDate' => $arbSettings['FirstPaymentDate'],
			'AccountId' => $data['Customer']['Connection'][0]['value']['Account']['Id'], // required
			'InvoiceNumber' => NULL,
			'OrderId' => $data['Transaction']['id'],
			'PaymentSubType' => $data['Transaction']['paymentSubType'], // required
			'StartDate' => $arbSettings['StartDate'], // required
			'EndDate' => $arbSettings['EndDate'],
			'ScheduleStatus' => 'Active', // required
			'ExecutionFrequencyType' => $arbSettings['ExecutionFrequencyType'], // required
			'ExecutionFrequencyParameter' => $arbSettings['ExecutionFrequencyParameter'],
			'Description' => __SYSTEM_SITE_NAME,
			'Id' => 0
		);
		return $this->_sendRequest('POST', '/recurringpayment', $params);
	}
	
/**
 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment
 * 
 * @param array $data
 * @return boolean|array
 */
	public function updateRecurringPayment($data) {
		$params = array(
			'CustomerId' => null,
			'NextScheduleDate' => null,
			'PauseUntilDate' => null,
			'FirstPaymentDone' => null,
			'DateOfLastPaymentMade' => null,
			'TotalAmountPaid' => null,
			'NumberOfPaymentsMade' => null,
			'EndDate' => null, // updatable
			'PaymentAmount' => null, // updatable
			'PaymentSubType' => null, // updatable
			'AccountId' => null, // updatable
			'InvoiceNumber' => null,
			'OrderId' => null,
			'FirstPaymentAmount' => null, // updatable (if it hasn't started yet)
			'FirstPaymentDate' => null, // updatable (if it hasn't started yet)
			'StartDate' => null, // updatable (if it hasn't started yet)
			'ScheduleStatus' => null,
			'ExecutionFrequencyType' => null, // updatable
			'ExecutionFrequencyParameter' => null, // updatable
			'Description' => null,
			'Id' => null
		);
		return $this->_sendRequest('PUT', '/recurringpayment', $params);
	}
	
/**
 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment-by-id-pause-until-enddate
 * 
 * @param array $data
 * @return boolean
 */
	public function pauseRecurringPayment($data) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$data['scheduleId'].'/pause?endDate='.$data['endDate']);
	}
	
/**
 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment-by-id-suspend
 * 
 * @param integer $scheduleId
 * @return boolean
 */
	public function suspendRecurringPayment($scheduleId) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$scheduleId.'/suspend');
	}
	
/**
 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment-by-id-resume
 * 
 * @param integer $data
 * @return boolean
 */
	public function resumeRecurringPayment($data) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$scheduleId.'/resume');
	}
	
/**
 * Returns a list of Invoice records
 * @return type
 */
	public function getInvoices() {
		return $this->_sendRequest('GET', '/invoice');
	}
/**
 * Returns an Invoice record for the specified identifier
 * @param type $invoiceId
 * @return type
 */
	public function getInvoice($invoiceId) {
		return $this->_sendRequest('GET', '/invoice/'.$invoiceId);
	}
/**
 * Returns the next available invoice number when using auto-numbering within PaySimple
 * @return type
 */
	public function getNextInvoiceNumber() {
		return $this->_sendRequest('GET', '/invoice/number');
	}
/**
 * Gets all of the payments for the specified Invoice identifier
 * @param type $invoiceId
 * @return type
 */
	public function getInvoicePayments($invoiceId) {
		return $this->_sendRequest('GET', '/invoice/'.$invoiceId.'/payments');
	}
/**
 * Gets a list of actions on an Invoice given the specified identifier
 * @param type $invoiceId
 * @return type
 */
	public function getInvoiceActions($invoiceId) {
		return $this->_sendRequest('GET', '/invoice/'.$invoiceId.'/actions');
	}
/**
 * Returns a list of Line Item records for the specified Invoice identifier
 * @param type $invoiceId
 * @return type
 */
	public function getInvoiceLineItems($invoiceId) {
		return $this->_sendRequest('GET', '/invoice/'.$invoiceId.'/invoicelineitems');
	}
/**
 * Creates an Invoice record when provided with an Invoice object. This route does not immediately send the Invoice
 * @param type $data
 * @return type
 */
	public function createInvoice($data) {
		return $this->_sendRequest('POST', '/invoice', $data);
	}
/**
 * Updates an Invoice record when provided with an Invoice object
 * @param type $data
 * @return type
 */
	public function updateInvoice($data) {
		return $this->_sendRequest('PUT', '/invoice', $data);
	}
/**
 * Sends or resends an Invoice record given an identifier (via email)
 * @param type $invoiceId
 * @return type
 */
	public function sendInvoice($invoiceId) {
		return $this->_sendRequest('PUT', '/invoice/'.$invoiceId.'/send');
	}
/**
 * Marks an Invoice as paid or partially paid when provided with an Invoice identifier and a Received Payment object.
 * @param type $data
 * @return type
 */
	public function addInvoicePayment($data) {
		return $this->_sendRequest('PUT', '/invoice/'.$data['invoiceId'].'/externalpayment', $data);
	}
/**
 * Marks an Invoice paid when provided with an identifier. NOTE: A body is not required for this message.
 * @param type $invoiceId
 * @return type
 */
	public function markInvoicePaid($invoiceId) {
		return $this->_sendRequest('PUT', '/invoice/'.$invoiceId.'/markpaid');
	}
/**
 * Marks an Invoice unpaid when provided with an identifier. NOTE: A body is not required for this message.
 * @param type $invoiceId
 * @return type
 */
	public function markInvoiceUnpaid($invoiceId) {
		return $this->_sendRequest('PUT', '/invoice/'.$invoiceId.'/markunpaid');
	}
/**
 * Marks an Invoice as sent when provided with an identifier. NOTE: A body is not required for this message.
 * @param type $invoiceId
 * @return type
 */
	public function markInvoiceSent($invoiceId) {
		return $this->_sendRequest('PUT', '/invoice/'.$invoiceId.'/marksent');
	}
/**
 * Marks an Invoice as cancelled when provided with an identifier. NOTE: A body is not required for this message.
 * @param type $invoiceId
 * @return type
 */
	public function markInvoiceCancelled($invoiceId) {
		return $this->_sendRequest('PUT', '/invoice/'.$invoiceId.'/cancel');
	}
/**
 * Deletes an Invoice record when provided with an identifier. NOTE: A body is not required for this message.
 * @param type $invoiceId
 * @return type
 */
	public function deleteInvoice($invoiceId) {
		return $this->_sendRequest('DELETE', '/invoice/'.$invoiceId);
	}

	/**
 * @param integer $customerId
 * @return boolean|array
 */
	public function findCustomerById($customerId) {
		return $this->_sendRequest('GET', '/customer/' . $customerId);
	}

/**
 * try to find their email in the current customer list
 * @param string $email
 * @return boolean|array
 */
	public function findCustomerByEmail($email) {
		$customerList = $this->getCustomerList();
		if ($customerList) {
			foreach ($customerList as $customer) {
				if ($customer['Email'] == $email) {
					$user = $customer;
					break;
				}
			}
		}
		if ($user) {
			return $user;
		} else {
			return FALSE;
		}
	}

/**
 * This function executes upon failure
 * @todo obsolete..?  seems that it may be.
 */
	public function echoErrors() {
		//debug($this->errors);
		foreach ($this->errors as $error) {
			$this->response['reason_text'] .= '<li>' . $error . '</li>';
		}
		$this->response['response_code'] = 0;
		new Exception($this->response['reason_text']);
	}

/**
 *
 * @param type $cardNumber
 * @return boolean|integer
 */
	public function getIssuer($cardNumber) {

		App::uses('Validation', 'Utility');
		if ( Validation::cc($cardNumber, array('visa')) ) {
			$cardType = 'Visa';
		} elseif ( Validation::cc($cardNumber, array('amex')) ) {
			$cardType = 'Amex';
		} elseif ( Validation::cc($cardNumber, array('mc')) ) {
			$cardType = 'Master';
		} elseif ( Validation::cc($cardNumber, array('disc')) ) {
			$cardType = 'Discover';
		} else {
			$cardType = 'Unsupported';
		}

		$paySimpleCodes = array(
			'Unsupported' => FALSE,
			'Visa' => 12,
			'Discover' => 15,
			'Master' => 13,
			'Amex' => 14,
		);

		return $paySimpleCodes[$cardType];
	}

/**
 * Prepares and sends your request to the API servers
 * 
 * @param string $method POST | GET | UPDATE | DELETE
 * @param string $action PaySimple API endpoint
 * @param array $data A PaySimple API Request Body packet as an array
 * @return boolean|array Returns Exception/FALSE or the "Response" array
 */
	public function _sendRequest($method, $action, $data = NULL) {
        if ($this->config['environment'] == 'sandbox') {
			$endpoint = 'https://sandbox-api.paysimple.com/v4';
		} else {
			$endpoint = 'https://api.paysimple.com/v4';
		}

		$timestamp = gmdate("c");
		$hmac = hash_hmac("sha256", $timestamp, $this->config['sharedSecret'], true); //note the raw output parameter
		$hmac = base64_encode($hmac);

		$request = array(
			'method' => $method,
			'uri' => $endpoint . $action,
			'header' => array(
				'Authorization' => "PSSERVER AccessId = {$this->config['apiUsername']}; Timestamp = {$timestamp}; Signature = {$hmac};"
			),
		);
		if ($data !== NULL) {
			$request['header']['Content-Type'] = 'application/json';
			$request['header']['Content-Length'] = strlen(json_encode($data));
			$request['body'] = json_encode($data);
		}
		$result = $this->_httpSocket->request($request);
		return $this->_handleResult($result, $data);
	}
	
	
/**
 * 
 * @param Object $result An httpSocket response object
 * @param Array $data The PaySimple API Request Body packet as an array that was used for the request
 * @return Array The entire Response packet of a valid API call
 * @throws Exception The error message to display to the visitor
 */
	private function _handleResult($result, $data) {
		$responseCode = $result->code;
		$badResponseCodes = array(400, 401, 403, 404, 405, 500);
		// build error message
		$result->body = json_decode($result->body, TRUE); // was in the if below;  // JOEL PLEASE CHECK IF THIS BROKE ANYTHING
		
		if (in_array($responseCode, $badResponseCodes)) {
			if (isset($result->body['Meta']['Errors']['ErrorMessages'])) {
				$message = '';
				
				//$message = $result['Meta']['Errors']['ErrorCode']. ' '; // this might be a redundant message
				foreach ($result->body['Meta']['Errors']['ErrorMessages'] as $error) {
					$this->errors[] = $error['Message'];
					$message .= '<p>'.$error['Message'].'</p>';
				}
			} else {
				$this->errors[] = $message = '<p>Uncaught error : 23692732470912876</p>' . ZuhaInflector::flatten($result);
			}
			
//			// we need to know if this was an ARB that was declined ??
//			if($data['Transaction']['is_arb']) {
//				$arbErrorMessage = $result['Meta']['Errors']['ErrorMessages'][0]['Message'];
//				if(strpos($arbErrorMessage, 'was saved, but the first scheduled payment failed')) {
//					
//				}
//			}
			
			// some error logging perhaps?
//			CakeLog::write('failed_transactions', $responseCode);
//			CakeLog::write('failed_transactions', $request);
//			CakeLog::write('failed_transactions', $result);
			
			// throw error message to display to the visitor

			throw new Exception($message);
			return FALSE;
		} else {
			// return entire Response packet of a valid API call
			return $result->body['Response']; // was $result['Response']; // JOEL PLEASE CHECK IF THIS BROKE ANYTHING
		}
	}

}