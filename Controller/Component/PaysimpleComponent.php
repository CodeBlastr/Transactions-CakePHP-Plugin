<?php

/**
 * This still needs a lot of work..  @see PaysimpleComponent::Pay()
 * 
 * @author Joel Byrnes <joel@razorit.com>
 * @link https://sandbox-api.paysimple.com/v4/Help/
 */
App::uses('HttpSocket', 'Network/Http');

class PaysimpleComponent extends Component {

	public $config = array(
		'environment' => 'sandbox',
		'apiUsername' => '',
		'sharedSecret' => '',
	);
	public $errors = false;
	public $response = array();

	public function __construct(ComponentCollection $collection, $config = array()) {
		parent::__construct($collection, $config);
		if (defined('__ORDERS_TRANSACTIONS_PAYSIMPLE')) {
			$settings = unserialize(__ORDERS_TRANSACTIONS_PAYSIMPLE);
		}

		$this->config = Set::merge($this->config, $config, $settings);

		$this->_httpSocket = new HttpSocket();
	}

/**
 * @todo Logged in Users should pass 'Connection' data here
 * @param array $data
 */
	public function Pay($data) {

		if (!isset($data['Connection'])) {
			// create a user in their system and return data for us to save
			try {

				// create their Customer
				$userData = $this->createCustomer($data);
				$data['Connection']['Paysimple']['Customer']['Id'] = $userData['Id'];

				// add their payment method to their Account List
				if (!empty($data['Transaction']['ach_account_number'])) {
					$accountData = $this->addAchAccount($data);
					$data['Connection']['Paysimple']['Account']['Ach'][] = $accountData;
					$data['Connection']['Paysimple']['Account']['Id'] = $accountData['Id'];
					$data['Transaction']['paymentSubType'] = 'Web';
				} else {
					$accountData = $this->addCreditCardAccount($data);
					$data['Connection']['Paysimple']['Account']['CreditCard'][] = $accountData;
					$data['Connection']['Paysimple']['Account']['Id'] = $accountData['Id'];
					$data['Transaction']['paymentSubType'] = 'Moto';
				}

				// charge them using their newly submitted payment method
				$paymentData = $this->createPayment($data);
				$data['Transaction']['Payment'] = $paymentData;

				return $data;
			} catch (Exception $exc) {
				throw new Exception($exc->getMessage());
			}
		} else {
			// They have Connection, we must have a PaySimple ID for them.
			// Notes:
			// ideally we should save their accounts to our database, so we have a reusable ID for them,
			// i.e. #1 = My Debit Card, #2 = My Secret Checking Account..
			// Currently my code would compare the account they submitted against what PaySimple has saved for them,
			// and create it if it's not already there, then set it to default, and use it.
			try {

				if (!empty($data['Transaction']['ach_account_number'])) {
					$data['Transaction']['paymentSubType'] = 'Web';
				} else {
					$data['Transaction']['paymentSubType'] = 'Moto';
				}
				
				$this->createPayment($data);
				$data['Transaction']['Payment'] = $paymentData;

				return $data;
			} catch (Exception $exc) {
				throw new Exception($exc->getMessage());
			}
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
			'FirstName' => $data['TransactionPayment'][0]['first_name'],
			'LastName' => $data['TransactionPayment'][0]['last_name'],
			//'Company' => $data['Meta']['company'],
			'BillingAddress' => array(
				'StreetAddress1' => $data['TransactionPayment'][0]['street_address_1'],
				'StreetAddress2' => $data['TransactionPayment'][0]['street_address_2'],
				'City' => $data['TransactionPayment'][0]['city'],
				'StateCode' => $data['TransactionPayment'][0]['state'],
				'ZipCode' => $data['TransactionPayment'][0]['zip'],
			),
			'ShippingSameAsBilling' => true,
			'Email' => $data['Customer']['email'],
			'Phone' => $data['Customer']['phone'],
		);

		if ($data['TransactionPayment'][0]['shipping'] == 'checked') {
			// their shipping is not the same as their billing
			$params['ShippingSameAsBilling'] = false;
			$params['BillingAddress'] = array(
				'StreetAddress1' => $data['TransactionShipment'][0]['street_address_1'],
				'StreetAddress2' => $data['TransactionShipment'][0]['street_address_2'],
				'City' => $data['TransactionShipment'][0]['city'],
				'StateCode' => $data['TransactionShipment'][0]['state'],
				'ZipCode' => $data['TransactionShipment'][0]['zip'],
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
			'CustomerId' => $data['Connection']['Paysimple']['Customer']['Id'],
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

		$params = array(
			'Id' => 0,
			'IsDefault' => true,
			'IsCheckingAccount' => $data['Transaction']['ach_is_checking_account'],
			'RoutingNumber' => $data['Transaction']['ach_routing_number'],
			'AccountNumber' => $data['Transaction']['ach_account_number'],
			'BankName' => $data['Transaction']['ach_bank_name'],
			'CustomerId' => $data['Connection']['Paysimple']['Customer']['Id']
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
			'AccountId' => ($data['Connection']['Paysimple']['Account']['Id']),
			'InvoiceId' => NULL,
			'Amount' => $data['Transaction']['order_charge'],
			'IsDebit' => false,
			'InvoiceNumber' => NULL,
			'PurchaseOrderNumber' => NULL,
			'OrderId' => NULL,
			'Description' => __SYSTEM_SITE_NAME, //$data['Transaction']['description'],
			'CVV' => $data['Transaction']['card_sec'],
			'PaymentSubType' => $data['Transaction']['paymentSubType'],
			'Id' => 0
		);

		return $this->_sendRequest('POST', '/payment', $params);
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
		debug($this->errors);
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
//	debug($result);
		$responseCode = $result->code;
		$result = json_decode($result->body, TRUE);
//debug($request);
//debug($responseCode);
//break;
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
			
			throw new Exception($message);
			return FALSE;
			
		} else {
			return $result['Response'];
		}
	}

}