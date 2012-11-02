<?php
/**
 * This still needs a lot of work..  @see PaysimpleComponent::Pay()
 * 
 * @author Joel Byrnes <joel@razorit.com>
 * @link https://sandbox-api.paysimple.com/v4/Help/
 */
App::uses('HttpSocket', 'Network/Http');

class PaysimpleComponent extends Component {

  public
		  $config = array(
			  'environment' => 'sandbox',
			  'apiUsername' => '',
			  'sharedSecret' => '',
		  ),
		  $errors = false,
		  $response = array();

  public function __construct($config = array()) {
	parent::__construct($config);
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
debug($data); break;

	if (!$data['Connection']) {
	  // create a user in their system and save it in our Connections table
	  try {
		$this->createCustomer($data);
		if (!empty($data['Transaction']['ach_account_number'])) {
		  $this->addAchAccount($data);
		} else {
		  $this->addCreditCardAccount($data);
		}
		$this->createPayment($data);
	  } catch (Exception $exc) {
		debug($exc->getMessage());
		break;
	  }
	} else {
	  // They have Connection, we must have a PaySimple ID for them.
	  // Notes:
	  // ideally we should save their accounts to our database, so we have a reusable ID for them,
	  // i.e. #1 = My Debit Card, #2 = My Secret Checking Account..
	  // Currently my code would compare the account they submitted against what PaySimple has saved for them,
	  // and create it if it's not already there, then set it to default, and use it.
	  try {
		$this->createPayment($data);
	  } catch (Exception $exc) {
		debug($exc->getMessage());
		break;
	  }
	}


	$user = $this->findCustomerByEmail($data['Customer']['email']);

	if ($user) {
	  // we found this user..
	  // check if payment method exists
	  $currentAccounts = $this->getAccounts($user['Id']);
	  if ($currentAccounts) {
		if (!empty($currentAccounts['CreditCardAccounts']) && !empty($data['Transaction']['card_number'])) {
		  $lastFourDigits = substr($data['Transaction']['card_number'], -4, 4);
		  $accountExists = false;
		  foreach ($currentAccounts['CreditCardAccounts'] as $creditCardAccount) {
			$ccLastFourDigits = substr($creditCardAccount['CreditCardNumber'], -4, 4);
			if ($ccLastFourDigits == $lastFourDigits) {
			  $accountExists = true;
			  $accountId = $creditCardAccount['Id'];
			  break;
			}
		  }
		}
		if (!empty($currentAccounts['AchAccounts']) && !empty($data['Transaction']['ach_account_number'])) {
		  $lastFourDigits = substr($data['Transaction']['ach_account_number'], -4, 4);
		  $accountExists = false;
		  foreach ($currentAccounts['AchAccounts'] as $achAccount) {
			$achLastFourDigits = substr($achAccount['AccountNumber'], -4, 4);
			if ($achLastFourDigits == $lastFourDigits) {
			  $accountExists = true;
			  $accountId = $achAccount['Id'];
			  break;
			}
		  }
		}
	  }

	  if (!isset($accountId)) {
		// this is a new payment method by an existing customer
		if (isset($data['Transaction']['card_number'])) {
		  // add their credit card
		  $params = array(
			  'CreditCardNumber' => $data['Transaction']['card_number'],
			  'ExpirationDate' => $data['Transaction']['card_exp_month'] . '-' . $data['Transaction']['card_exp_year'],
			  'CustomerId' => $user['Id'],
		  );
		  $addCreditSuccess = $this->addCreditCardAccount($params);
		  if ($addCreditSuccess) {
			$accountId = $addCreditSuccess['Id'];
		  }
		}
		if (isset($data['Transaction']['Ach'])) {
		  // add their ACH account
		  $params = array(
			  'IsCheckingAccount' => $data['Transaction']['ach_is_checking_account'],
			  'RoutingNumber' => $data['Transaction']['ach_routing_number'],
			  'AccountNumber' => $data['Transaction']['ach_account_number'],
			  'BankName' => $data['Transaction']['ach_bank_name'],
			  'CustomerId' => $user['Id']
		  );
		  $addAchSuccess = $this->addAchAccount($params);
		  if ($addAchSuccess) {
			$accountId = $addAchSuccess['Id'];
		  }
		}
	  }

	  if (isset($accountId)) {
		// proceed with the payment
		$params = array(
			'AccountId' => $accountId,
			'InvoiceId' => NULL,
			'Amount' => $data['Transaction']['order_charge'],
			'IsDebit' => false,
			'InvoiceNumber' => NULL,
			'PurchaseOrderNumber' => NULL,
			'OrderId' => NULL,
			'Description' => $data['Transaction']['description'],
			'CVV' => $data['Transaction']['card_sec'],
			'PaymentSubType' => 'Web',
			'Id' => 0
		);
		$paymentSuccess = $this->createPayment($params);
		if ($paymentSuccess) {
		  //$this->success = true;
		  $this->response['response_code'] = 1;
		} else {
		  $this->echoErrors();
		}
	  } else {
		$this->echoErrors();
	  }
	} else {

	  // user not found by email, create a new customer
	  $createSuccess = $this->createCustomer($data);
	  //var_dump($createSuccess);
	  if ($createSuccess) {

		if (isset($data['CreditCard'])) {
		  // add their credit card
		  $params = array(
			  'CreditCardNumber' => $data['Transaction']['card_number'],
			  'ExpirationDate' => $data['Transaction']['card_exp_month'] . '-' . $data['Transaction']['card_exp_year'],
			  'CustomerId' => $createSuccess['Id'],
		  );
		  $addCreditSuccess = $this->addCreditCardAccount($params);
		  if ($addCreditSuccess) {
			$accountId = $addCreditSuccess['Id'];
		  }
		}
		if (isset($data['Ach'])) {
		  // add their ACH account
		  $params = array(
			  'IsCheckingAccount' => $data['Transaction']['ach_is_checking_account'],
			  'RoutingNumber' => $data['Transaction']['ach_routing_number'],
			  'AccountNumber' => $data['Transaction']['ach_account_number'],
			  'BankName' => $data['Transaction']['ach_bank_name'],
			  'CustomerId' => $createSuccess['Id']
		  );
		  $addAchSuccess = $this->addAchAccount($params);
		  if ($addAchSuccess) {
			$accountId = $addAchSuccess['Id'];
		  }
		}

		// now process the payment
		if ($accountId) {
		  $params = array(
			  'AccountId' => $accountId,
			  'InvoiceId' => NULL,
			  'Amount' => $data['Transaction']['order_charge'],
			  'IsDebit' => false,
			  'InvoiceNumber' => NULL,
			  'PurchaseOrderNumber' => NULL,
			  'OrderId' => NULL,
			  'Description' => $data['Transaction']['description'],
			  'CVV' => $data['Transaction']['card_sec'],
			  'PaymentSubType' => 'Web',
			  'Id' => 0
		  );
		  $paymentSuccess = $this->createPayment($params);
		  if ($paymentSuccess) {
			//$this->success = true;
			$this->response['response_code'] = 1;
		  } else { //echo '290';
			$this->echoErrors();
		  }
		} else { //echo '293';
		  $this->echoErrors();
		}
	  } else { //echo '296';
		$this->echoErrors();
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
   *
   * @param array $data
   * @return boolean|array
   */
  public function createCustomer($data) {
	
	$params = array(
		'FirstName' => $data['TransactionPayment']['first_name'],
		'LastName' => $data['TransactionPayment']['last_name'],
		//'Company' => $data['Meta']['company'],
		'BillingAddress' => array(
			'StreetAddress1' => $data['TransactionPayment']['street_address_1'],
			'StreetAddress2' => $data['TransactionPayment']['street_address_2'],
			'City' => $data['TransactionPayment']['city'],
			'StateCode' => $data['TransactionPayment']['state'],
			'ZipCode' => $data['TransactionPayment']['zip'],
		),
		'ShippingSameAsBilling' => true,
		'Email' => $data['Customer']['email'],
		'Phone' => $data['Customer']['phone'],
	);

	if($data['TransactionPayment']['shipping'] == 'checked') {
	  // their shipping is not the same as their billing
	  $params['ShippingSameAsBilling'] = false;
	  $params['BillingAddress'] = array(
			'StreetAddress1' => $data['TransactionPayment']['street_address_1'],
			'StreetAddress2' => $data['TransactionPayment']['street_address_2'],
			'City' => $data['TransactionPayment']['city'],
			'StateCode' => $data['TransactionPayment']['state'],
			'ZipCode' => $data['TransactionPayment']['zip'],
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
   *
   * @param array $data
   * @return boolean|array
   */
  public function addCreditCardAccount($data) {

	$data['Id'] = 0;
	$data['IsDefault'] = true;
	$data['Issuer'] = $this->getIssuer($data['CreditCard']['card_number']);

	return $this->_sendRequest('POST', '/account/creditcard', $data);
  }

  /**
   *
   * @param array $data
   * @return boolean|array
   */
  public function addAchAccount($data) {

	$data['Id'] = 0;
	$data['IsDefault'] = true;

	return $this->_sendRequest('POST', '/account/ach', $data);
  }

  /**
   * Creates a Payment record when provided with a Payment object.
   * This is a one-time payment that will be created on the current date for the Customer with the specified Account Id.
   * 
   * @param array $data
   * @return boolean|array
   */
  public function createPayment($data) {
	return $this->_sendRequest('POST', '/payment', $data);
  }

  /**
   * @param integer $customerId
   * @return boolean|array
   */
  public function findCustomerById($customerId) {
	return $this->_sendRequest('GET', '/customer/'.$customerId);
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

	return $paySimpleCodes[$CreditCardType];
  }

  /**
   *
   * @param string $method
   * @param string $action
   * @param array $data
   * @return boolean|array
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
		$this->errors[] = $result;
	  } elseif (isset($result['Meta']['Errors']['ErrorMessages'])) {
		foreach ($result['Meta']['Errors']['ErrorMessages'] as $error) {
		  $this->errors[] = $error['Message'];
		}
	  } else {
		$this->errors[] = $result;
	  }
	  return FALSE;
	} else {
	  return $result['Response'];
	}
  }

}