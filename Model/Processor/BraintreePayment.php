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

		}else{
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

            $paymentData = $this->doSales($data);
            if($paymentData){

                $data[$this->modelName]['processor_response'] = $paymentData->transaction->processorResponseText;
                $data[$this->modelName]['processor_transaction_id'] = $paymentData->transaction->id;
                $data[$this->modelName]['Payment'] = $paymentData;
                $data[$this->modelName]['status'] = $paymentData->success ? $this->statusTypes['paid'] : $this->statusTypes['failed'];
            }else{
                throw new Exception($paymentData->message);
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
    public function createSubMerchantFundingAccount($data){

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
        if($result->success){
            return $result->success;
        }else{
            throw new Exception($result->message);
        }
    }
    public function getSubMerchantFundingAccount($accountId){
        if(!empty($accountId)){
            return Braintree_MerchantAccount::find($accountId);
        }else{
            throw new Exception('Funding Account Not Found: ' . $accountId);
        }
    }

    private function isNeedEscrow($data){
        $item = $data['TransactionItem'][0];
        if(is_null($item['is_virtual']) || empty($item['is_virtual'])){
            return true;
        }
        return false;
    }

    public function doSales($data) {
        $data['brainTree']['creditCard']['expirationDate'] = $data['brainTree']['creditCard']['month'] . '/' . $data['brainTree']['creditCard']['year'];
        unset($data['brainTree']['creditCard']['year'],$data['brainTree']['creditCard']['month']);
        $params['creditCard'] = $data['brainTree']['creditCard'];
        $params['amount'] = $data['Transaction']['total'];
        $params['orderId'] = $data['Transaction']['id'];

        if($this->isNeedEscrow($data)){
            $subMerchant = $data['TransactionItem'][0]['_associated']['seller']['merchant_account'];
            if(empty($subMerchant)){
               throw new Exception('Seller does not have funding account');
            }
            $params['options'] = array(
                'submitForSettlement' => true,
                'holdInEscrow' => true,
            );
            $params['serviceFeeAmount'] = 10;
            $params['merchantAccountId'] = $subMerchant;

        }

        return Braintree_Transaction::sale($params);


    }


	public function getCustomerList() {
		return $this->_sendRequest('GET', '/customer');
	}


    public function createCustomer($data) {

	}

	
	public function createRecurringPayment($data) {
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
		$result = $this->Http->request($request);
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