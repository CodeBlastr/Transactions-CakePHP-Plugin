<?php

/**
 * 
 * @author Joel Byrnes <joel@razorit.com>
 * @link https://sandbox-api.paysimple.com/v4/Help/
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
		}

		$this->config = Set::merge($this->config, $config, $settings);

		$this->_httpSocket = new HttpSocket();
	}


/**
 * 
 * @param array $data
 * @return type
 * @throws Exception
 */
	public function Pay($data) {
		//debug($data);
		
		try {

			// Do we need to save a New Customer or are we using an Existing Customer
			if (empty($data['Customer']['Connection'])) {
				// create their Customer
				$userData = $this->createCustomer($data);
				$data['Customer']['Connection'][0]['value']['Customer']['Id'] = $userData['Id'];
			} else {
				// we have their customer, unserialize the data
				$data['Customer']['Connection'][0]['value'] = unserialize($data['Customer']['Connection'][0]['value']);
			}

			// Do we need to save a New Payment Method, or are they using a Saved Payment Method
			if (!empty($data['Transaction']['ach_account_number'])) {
				// ACH Account
				$accountData = $this->addAchAccount($data);
				$data['Customer']['Connection'][0]['value']['Account']['Ach'][] = $accountData;
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $accountData['Id'];
				$data['Transaction']['paymentSubType'] = 'Web';
			} elseif (!empty($data['Transaction']['card_number'])) {
				// Credit Card Account
				$accountData = $this->addCreditCardAccount($data);
				$data['Customer']['Connection'][0]['value']['Account']['CreditCard'][] = $accountData;
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $accountData['Id'];
				$data['Transaction']['paymentSubType'] = 'Moto';
			} else {
				// they are using a Saved Payment Method; defined by an Id
				$data['Customer']['Connection'][0]['value']['Account']['Id'] = $data['Transaction']['paysimple_account'];
			}

			// make the actual payment
			if($data['Transaction']['is_arb']) {
				$paymentData = $this->createRecurringPayment($data);
				$data['Customer']['Connection'][0]['value']['Arb']['scheduleId'] = $paymentData['Id'];
			} else {
				$paymentData = $this->createPayment($data);
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

		$params = array(
			'FirstName' => $data['TransactionAddress'][0]['first_name'],
			'LastName' => $data['TransactionAddress'][0]['last_name'],
			//'Company' => $data['Meta']['company'],
			'BillingAddress' => array(
				'StreetAddress1' => $data['TransactionAddress'][0]['street_address_1'],
				'StreetAddress2' => $data['TransactionAddress'][0]['street_address_2'],
				'City' => $data['TransactionAddress'][0]['city'],
				'StateCode' => $data['TransactionAddress'][0]['state'],
				'ZipCode' => $data['TransactionAddress'][0]['zip'],
			),
			'ShippingSameAsBilling' => true,
			'Email' => $data['Customer']['email'],
			//'Phone' => $data['Customer']['phone'],
			'Phone' => $data['TransactionAddress'][0]['phone'],
		);

		if ($data['TransactionAddress'][0]['shipping'] == 'checked') {
			// their shipping is not the same as their billing
			$params['ShippingSameAsBilling'] = false;
			$params['BillingAddress'] = array(
				'StreetAddress1' => $data['TransactionAddress'][1]['street_address_1'],
				'StreetAddress2' => $data['TransactionAddress'][1]['street_address_2'],
				'City' => $data['TransactionAddress'][1]['city'],
				'StateCode' => $data['TransactionAddress'][1]['state'],
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
			'Amount' => $data['Transaction']['order_charge'],
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
		
		// format StartDate
		if(empty($arbSettings['StartDate'])) {
			$arbSettings['StartDate'] = 0;
		}
		$arbSettings['StartDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['StartDate'].' days'));

		// format EndDate
		if(!empty($arbSettings['EndDate'])) {
			$arbSettings['EndDate'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + '.$arbSettings['arb_settings']['EndDate'].' days'));
		}
//		debug($arbSettings);
//		break;
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
	 * @param type $data
	 * @return boolean
	 */
	public function pauseRecurringPayment($data) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$data['scheduleId'].'/pause?endDate='.$data['endDate']);
	}
	
	/**
	 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment-by-id-suspend
	 * 
	 * @param type $scheduleId
	 * @return boolean
	 */
	public function suspendRecurringPayment($scheduleId) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$scheduleId.'/suspend');
	}
	
	/**
	 * @link https://sandbox-api.paysimple.com/v4/Help/RecurringPayment#put-recurringpayment-by-id-resume
	 * 
	 * @param type $data
	 * @return boolean
	 */
	public function resumeRecurringPayment($data) {
		return $this->_sendRequest('PUT', '/recurringpayment/'.$scheduleId.'/resume');
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
		if (Validation::cc($cardNumber, 'visa')) {
			$cardType = 'Visa';
		} elseif (Validation::cc($cardNumber, 'amex')) {
			$cardType = 'Amex';
		} elseif (Validation::cc($cardNumber, 'mc')) {
			$cardType = 'Master';
		} elseif (Validation::cc($cardNumber, 'disc')) {
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
 *
 * @param string $method
 * @param string $action
 * @param array $data
 * @return boolean|array Returns FALSE or the "Response" array
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
			$data = json_encode($data);
			$request['header']['Content-Type'] = 'application/json';
			$request['header']['Content-Length'] = strlen($data);
			$request['body'] = $data;
		}

		$result = $this->_httpSocket->request($request);
		$responseCode = $result->code;
		$result = json_decode($result->body, TRUE);

		$badResponseCodes = array(400, 401, 403, 404, 405, 500);
		if (in_array($responseCode, $badResponseCodes)) {
			if (is_string($result)) {
				$this->errors[] = $message = $result;
			} elseif (isset($result['Meta']['Errors']['ErrorMessages'])) {
				$message = '';
				foreach ($result['Meta']['Errors']['ErrorMessages'] as $error) {
					$this->errors[] = $error['Message'];
					$message .= $error['Message'];
				}
			} else {
				$this->errors[] = $result;
				$message = $result;
			}
//			debug($request);
//			debug($responseCode);
//			debug($result);
//			break;
			throw new Exception($message);
			return FALSE;
			
		} else {
			return $result['Response'];
		}
	}

}