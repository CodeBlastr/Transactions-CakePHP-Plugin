<?php
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');

/***
 * class BluePayment
 * -Added additional fields that were not addressed by API Phone, Email, CustomID 1, Custom ID 2 (Bobby Bush - InDesign Firm, Inc.)
 * -Added function for the processing of ACH Transactions  (Bobby Bush - InDesign Firm, Inc.)
 *
 * This class provides the ability to perform credit
 * card transactions through BluePay's v2.0 interface.
 * This is done by performing a POST (using PHP's
 * CURL wrappers), then recieving and parsing the
 * response.
 *
 * A few notes:
 *
 * - set tab spacing to 3, for optimal viewing
 *
 * - PAYMENT_TYPE of ACH is not dealt with at all ( NOW IT IS)  :)
 *
 * - Rebilling could be further developed (i.e.
 * automatically format parameters better, such
 * as to be able to use UNIX timestamp for the
 * first date parameter, etc.)
 *
 * - Level 2 qualification is in place, but I'm not
 * really sure how it is used, so did not do any
 * more than allow for the parameters to be set.
 *
 * - this class has not been fully tested
 *
 * - there is little to no parameter error
 * checking (i.e. sending a NAME1 of over 16
 * characters is allowed, but will yeild an 'E'
 * (error) STATUS response)
 *
 * - this class is written in PHP 5 (and is _not_
 * compatable with any previous versions)
 */
class Bluepay extends AppModel {

	/* merchant supplied parameters */
	protected $accountId;
	// ACCOUNT_ID
	protected $userId;
	// USER_ID (optional)
	protected $tps;
	// TAMPER_PROOF_SEAL
	protected $transType;
	// TRANS_TYPE (AUTH, SALE, REFUND, or CAPTURE)   
	protected $payType;
	// PAYMENT_TYPE (CREDIT or ACH)
	protected $mode;
	// MODE (TEST or LIVE)
	protected $masterId;
	// MASTER_ID (optional)
	protected $secretKey;
	// used to generate the TPS

	/* customer supplied fields, (not required if
	 MASTER_ID is set) */
	protected $account;
	// PAYMENT_ACCOUNT (i.e. credit card number)
	protected $cvv2;
	// CARD_CVVS
	protected $expire;
	// CARD_EXPIRE
	protected $ssn;
	// SSN (Only required for ACH)
	protected $birthdate;
	// BIRTHDATE (only required for ACH)
	protected $custId;
	// CUST_ID (only required for ACH)
	protected $custIdState;
	// CUST_ID_STATE (only required for ACH)
	protected $amount;
	// AMOUNT
	protected $name1;
	// NAME1
	protected $name2;
	// NAME2
	protected $addr1;
	// ADDR1
	protected $addr2;
	// ADDR2 (optional)
	protected $city;
	// CITY
	protected $state;
	// STATE
	protected $zip;
	// ZIP
	protected $country;
	// COUNTRY
	protected $memo;
	// MEMO (optinal)

	/* feilds for level 2 qualification */
	protected $orderId;
	// ORDER_ID
	protected $invoiceId;
	// INVOICE_ID
	protected $tip;
	// AMOUNT_TIP
	protected $tax;
	// AMOUNT_TAX

	/* rebilling (only with trans type of SALE or AUTH) */
	protected $doRebill;
	// DO_REBILL
	protected $rebDate;
	// REB_FIRST_DATE
	protected $rebExpr;
	// REB_EXPR
	protected $rebCycles;
	// REB_CYCLES
	protected $rebAmount;
	// REB_AMOUNT

	/* additional fraud scrubbing for an AUTH */
	protected $doAutocap;
	// DO_AUTOCAP
	protected $avsAllowed;
	// AVS_ALLOWED
	protected $cvv2Allowed;
	// CVV2_ALLOWED

	/* bluepay response output */
	protected $response;

	/* parsed response values */
	protected $transId;
	protected $status;
	protected $avsResp;
	protected $cvv2Resp;
	protected $authCode;
	protected $message;
	protected $rebid;
	
/**
 * Required var, sent from BuyableBehavior (duplicated here for Unit Tests to work)
 * 
 * @access public
 * @var array
 */
	public $statusTypes = array(
		'paid' => 'paid',
		'open' => 'open',
		'pending' => 'pending',
		'used' => 'used'
	); 

	/* constants */
	const MODE = 'TEST';
	// either TEST or LIVE
	const POST_URL = 'https://secure.bluepay.com/interfaces/bp20post';
	// the url to post to
	const ACCOUNT_ID = '';
	// the default account id
	const SECRET_KEY = '';
		
	// the default secret key

	/* STATUS response constants */
	const STATUS_DECLINE = '0';
	// DECLINE
	const STATUS_APPROVED = '1';
	// APPROVED
	const STATUS_ERROR = 'E';
	// ERROR

	/***
	 * __construct()
	 *
	 * Constructor method, sets the account, secret key,
	 * and the mode properties. These will default to
	 * the constant values if not specified.
	 */
	public function __construct() {
		if (defined('__TRANSACTIONS_BLUEPAY')) {
			$config = unserialize(__TRANSACTIONS_BLUEPAY);
			$this->accountId = $config['accountId'];
			$this->secretKey = $config['secretKey'];
			$this->mode = !empty($config['mode']) ? $config['mode'] : self::MODE;
		} else {
			throw new Exception(__('Bluepay configuration not setup.'));
			break;
		}
	}
	
	
    //method function pay attribute $data = null
	public function pay($data = null) {
		$this->modelName = !empty($this->modelName) ? $this->modelName : 'Transaction';

		try {
			// SET FINAL TRANSACTION DATA 
			$this->finalizeData($data);
			
			// run the transaction 
			$this->sendTransaction($data);
			
			// get the data to return
			$data = $this->returnData($data);
		
            return $data;
			
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		
	}


/**
 * Finalize the data we'll return from the pay function
 * 
 * @param array $data
 * @return array $data
 */
	public function returnData($data) {		
		// check if Customer Connection already exists AGAIN
		if(empty($data['Customer']['Connection'][0]['value'])){	
			// if it doesn't, set it			
			if($data['Transaction']['mode'] == 'BLUEPAY.ACH') {
				// check if it's a credit card or ach AGAIN to see which $data fields to set
				// set ACH field data
				$data['Customer']['Connection'][0]['value']['Account']['BankAccount'][0] = array(
				 	'RoutingNumber' => $data['Transaction']['ach_routing_number'],
				 	'AccountNumber' => '************' . substr($data['Transaction']['ach_account_number'], -4),
					'BankName' => $data['Transaction']['ach_bank_name'],
					'IsCheckingAccount' => $data['Transaction']['ach_is_checking_account'],
					'BillingZipCode' => $data['TransactionAddress'][0]['zip'],
					'IsDefault' => 1,
					'CreatedOn' => date('Y-m-d h:i:s'),
					'TransactionId' => $this->transId,
				);
			} else {
				// else (default to Credit Card)
				$expireDate = empty($data['Transaction']['card_expire']) ? sprintf('%02d', $data['Transaction']['card_exp_month']) . '/20' . substr($data['Transaction']['card_exp_year'], 2) : $data['Transaction']['card_expire']; // make this a function which parses more examples of dates, and put it in a more global place
				$data['Customer']['Connection'][0]['value']['Account']['CreditCard'][0] = array(
				 	'CreditCardNumber' => '************' . substr($data['Transaction']['card_number'], -4),
					'ExpirationDate' => $expireDate,
					'BillingZipCode' => $data['TransactionAddress'][0]['zip'],
					'IsDefault' => 1,
					'CreatedOn' => date('Y-m-d h:i:s'),
					'TransactionId' => $this->transId,
				);
			}
		}
		
		//Set data processor response value
		$data[$this->modelName]['processor_response'] = $this->message;
		// set status			  	  			 						
		$data[$this->modelName]['status'] = $this->statusTypes['paid'];
				
		return $data;
	} 	

/**
 * Fire the transaction
 */
 	public function sendTransaction($data) {
		$this->rebAdd($data);
		$this->sale($data['Transaction']['total']); // sets the amount for both sales, and trial period of ARB's / Rebilling
		$this->process();
		
		//if response is not 1 then throw new error msg to user using processor response msg		 
	  	if($this->status != 1) {
	  		throw new Exception($this->message); //throw msg using the message method	  
	  	}
		return $data;
 	}

/**
 * This is where we set all the right transaction data
 */
	public function finalizeData($data) {
		// check if Customer Connection already exists from the incoming data 
		if(!empty($data['Customer']['Connection'][0]['value'])){
			$value = unserialize($data['Customer']['Connection'][0]['value']);
			// this could break something, added for use on beef checkout for 30 day classified ad RK 3/9/2014
			$value = unserialize($value) ? unserialize($value) : $value;
			// get key (could be CreditCard could be BankAccount)
			$key = key($value['Account']);
			// if yes  use rebSale($transId) with transactionId, NOTE : get the transaction Id from the Customer Connection
			if (!empty($value['Account'][$key][0]['TransactionId'])) {
				$this->rebSale($value['Account'][$key][0]['TransactionId']);
			}
		}				
		// see if this is an ACH or a Credit Card transaction				
		if($data['Transaction']['mode'] == 'BLUEPAY.ACH'){
			$this->setCustACHInfo($data);
		} else {			
			$this->setCustInfo($data);
		}
		return $data;
	}


	/***
	 * sale()
	 *
	 * Will perform a SALE transaction with the amount
	 * specified.
	 */
	public function sale($amount) {
		$this->transType = "SALE";
		$this->amount = self::formatAmount($amount);
	}

	/***
	 * rebSale()
	 *
	 * Will perform a sale based on a previous transaction.
	 * If the amount is not specified, then it will use
	 * the amount of the previous transaction.
	 */
	public function rebSale($transId) {
		$this->masterId = $transId;
	}

	/***
	 * auth()
	 *
	 * Will perform an AUTH transaction with the amount
	 * specified.
	 */
	public function auth($amount) {

		$this->transType = "AUTH";
		$this->amount = self::formatAmount($amount);
	}

	/***
	 * autocapAuth()
	 *
	 * Will perform an auto-capturing AUTH using the
	 * provided AVS and CVV2 proofing.
	 */
	public function autocapAuth($amount, $avsAllow = null, $cvv2Allow = null) {

		$this->auth($amount);
		$this->setAutocap();
		$this->addAvsProofing($avsAllow);
		$this->addCvv2Proofing($avsAllow);
	}

	/***
	 * addLevel2Qual()
	 *
	 * Adds additional level 2 qualification parameters.
	 */
	public function addLevel2Qual($orderId = null, $invoiceId = null, $tip = null, $tax = null) {

		$this->orderId = $orderId;
		$this->invoiceId = $invoiceId;
		$this->tip = $tip;
		$this->tax = $tax;
	}

	/***
	 * refund()
	 *
	 * Will do a refund of a previous transaction.
	 */
	public function refund($transId) {

		$this->transType = "REFUND";
		$this->masterId = $transId;
	}

	/***
	 * capture()
	 *
	 * Will capture a pending AUTH transaction.
	 */
	public function capture($transId) {

		$this->transType = "CAPTURE";
		$this->masterId = $transId;
	}

	/***
	 * rebAdd()
	 *
	 * Will add a rebilling cycle.
	 * @todo we need a list of incoming frequency types to map
	 */
	public function rebAdd($data) {
		if (!empty($data['Transaction']['is_arb']) && !empty($data['TransactionItem'][0]['arb_settings'])) {
			$arbData = unserialize($data['TransactionItem'][0]['arb_settings']);
					
			// required : "3 DAY" to run every three days, "1 MONTH" to run monthly, "1 YEAR" to run yearly, "2 WEEK" to run bi-weekly, etc.
			$frequencyType = $arbData['ExecutionFrequencyType'] == 'Annually' ? '1 YEAR' : null;
			$frequencyType = $arbData['ExecutionFrequencyType'] == 'Monthly' ? '1 MONTH' : null;
			$frequencyType = $arbData['ExecutionFrequencyType'] == 'BiWeekly' ? '2 WEEK' : null;
			$frequencyType = empty($frequencyType) ? '1 MONTH' : null;
			
			
			$this->doRebill = '1';
			$this->rebAmount = self::formatAmount($arbData['PaymentAmount']);
			$this->rebDate = !empty($arbData['FirstPaymentDate']) ? $arbData['FirstPaymentDate'] : date('Y-m-d h:i:s');
			$this->rebExpr = $frequencyType; 
			$this->rebCycles = $arbData['ExecutionFrequencyParameter'];
		}
	}

	/***
	 * addAvsProofing()
	 *
	 * Will set which AVS responses are allowed (only
	 * applicable when doing an AUTH)
	 */
	public function addAvsProofing($allow) {

		$this->avsAllowed = $allow;
	}

	/***
	 * addCvv2Proofing()
	 *
	 * Will set which CVV2 responses are allowed (only
	 * applicable when doing an AUTH)
	 */
	public function addCvv2Proofing($allow) {

		$this->cvv2Allowed = $allow;
	}

	/***
	 * setAutocap()
	 *
	 * Will turn auto-capturing on (only applicable
	 * when doing an AUTH)
	 */
	public function setAutocap() {

		$this->doAutocap = '1';
	}

	/***
	 * setCustACHInfo()
	 *
	 * Sets the customer specified info.
	 */
	public function setCustACHInfo($data) {
		// parameters were : $routenum, $accntnum, $accttype, $name1, $name2, $addr1, $city, $state, $zip, $country, $phone, $email, $customid1 = null, $customid2 = null, $addr2 = null, $memo = null
		if (empty($this->masterId)) {
			$accttype = $data['Transaction']['ach_is_checking_account'] == '1' ? 'C' : 'S' ;  //if ach_is_checking_account = 1 'C' else 'S'
			$routenum = $data['Transaction']['ach_routing_number'];
			$acctnum = $data['Transaction']['ach_account_number'];
			$this->account = $accttype . ":" . $routenum . ":" . $acctnum;
			$this->payType = 'ACH';
		}
		
		$this->name1 = $data['TransactionAddress'][0]['first_name']; //$name1;
		$this->name2 = $data['TransactionAddress'][0]['last_name']; //$name2;
		$this->addr1 = $data['TransactionAddress'][0]['street_address_1']; //$addr1;
		$this->addr2 = $data['TransactionAddress'][0]['street_address_2']; //$addr2;
		$this->city = $data['TransactionAddress'][0]['city']; //city;
		$this->state = $data['TransactionAddress'][0]['state']; //$state;
		$this->zip = $data['TransactionAddress'][0]['zip']; //$zip;
		$this->country = $data['TransactionAddress'][0]['country']; //"USA";
		$this->phone = $data['TransactionAddress'][0]['phone']; //$phone;
		$this->email = $data['TransactionAddress'][0]['email']; //$email;
		$this->customid1 = null;
		$this->customid2 = null;
		$this->memo = null;
	}

	/***
	 * setCustInfo()
	 *
	 * Sets the customer specified info.
	 */
	public function setCustInfo($data) {
		if (empty($this->masterId)) {
			$this->account = $data['Transaction']['card_number']; // $this->account
			$this->cvv2 = $data['Transaction']['card_sec']; // $this->cvv2 
			$this->expire = empty($data['Transaction']['card_expire']) ? sprintf('%02d', $data['Transaction']['card_exp_month']) . '/20' . substr($data['Transaction']['card_exp_year'], 2) : $data['Transaction']['card_expire']; // make this a function which parses more examples of dates, and put it in a more global place
				
		}
		$this->name1 = $data['TransactionAddress'][0]['first_name'];
		$this->name2 = $data['TransactionAddress'][0]['last_name'];
		$this->addr1 = $data['TransactionAddress'][0]['street_address_1'];
		$this->addr2 = $data['TransactionAddress'][0]['street_address_2'];
		$this->city = $data['TransactionAddress'][0]['city']; //$this->city
		$this->state = $data['TransactionAddress'][0]['state']; //$this->state
		$this->zip = $data['TransactionAddress'][0]['zip']; //$this->zip
		$this->country = $data['TransactionAddress'][0]['country']; //$this->country = "USA"
		$this->phone = $data['TransactionAddress'][0]['phone']; //$this->phone
		$this->email = $data['TransactionAddress'][0]['email']; //$this->email
		$this->customid1 = null; //$data['Transaction']['customid1']; //$this->customid1
		$this->customid2 = null; //$data['Transaction']['customid2' == null]; //$this->customid2
		$this->memo = null; //$data['Transacton']['memo']; 
	}

	/***
	 * formatAmount()
	 *
	 * Will format an amount value to be in the
	 * expected format for the POST.
	 */
	public static function formatAmount($amount) {

		return sprintf("%01.2f", (float)$amount);
	}

	/***
	 * setOrderId()
	 *
	 * Sets the ORDER_ID parameter.
	 */
	public function setOrderId($orderId) {

		$this->orderId = $orderId;
	}

	/***
	 * 
	 * USED 
	 * 
	 * 
	 * calcTPS()
	 *
	 * Calculates & returns the tamper proof seal md5.
	 */
	protected final function calcTPS() {

		$hashstr =  $this->secretKey . 
					$this->accountId . 
					$this->transType . 
					$this->amount . 
					$this->masterId .
					$this->name1 . 
					$this->account;

		return bin2hex(md5($hashstr, true));
	}

	/***
	 * processACH()
	 *
	 * Will first generate the tamper proof seal, then
	 * populate the POST query, then send it, and store
	 * the response, and finally parse the response.
	 */
	public function processACH() {

		/* calculate the tamper proof seal */
		$tps = $this->calcTPS();

		//echo $this->account;

		/* fill in the fields */
		$fields = array(
			'ACCOUNT_ID' => $this->accountId, 
			'USER_ID' => $this->userId, 
			'TAMPER_PROOF_SEAL' => $tps, 
			'TRANS_TYPE' => $this->transType, 
			'PAYMENT_TYPE' => $this->payType, 
			'MODE' => $this->mode, 
			'MASTER_ID' => $this->masterId, 
			'PAYMENT_ACCOUNT' => $this->account, 
			'SSN' => $this->ssn, 
			'BIRTHDATE' => $this->birthdate, 
			'CUST_ID' => $this->custId, 
			'CUST_ID_STATE' => $this->custIdState, 
			'AMOUNT' => $this->amount, 
			'NAME1' => $this->name1, 
			'NAME2' => $this->name2, 
			'ADDR1' => $this->addr1, 
			'ADDR2' => $this->addr2, 
			'CITY' => $this->city, 
			'STATE' => $this->state, 
			'ZIP' => $this->zip, 
			'PHONE' => $this->phone, 
			'EMAIL' => $this->email, 
			'COUNTRY' => $this->country, 
			'MEMO' => $this->memo, 
			'CUSTOM_ID' => $this->customid1, 
			'CUSTOM_ID2' => $this->customid2, 
			'ORDER_ID' => $this->orderId, 
			'INVOICE_ID' => $this->invoiceId, 
			'AMOUNT_TIP' => $this->tip, 
			'AMOUNT_TAX' => $this->tax, 
			'DO_REBILL' => $this->doRebill, 
			'REB_FIRST_DATE' => $this->rebDate, 
			'REB_EXPR' => $this->rebExpr, 
			'REB_CYCLES' => $this->rebCycles, 
			'REB_AMOUNT' => $this->rebAmount, 
			'CUSTOMER_IP' => $_SERVER['REMOTE_ADDR']
			);
			
		/* perform the transaction */
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::POST_URL);
		// Set the URL
		curl_setopt($ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0");
		// Cosmetic
		curl_setopt($ch, CURLOPT_POST, 1);
		// Perform a POST
		// curl_setopt($ch, CURLOPT_CAINFO, "c:\\windows\\ca-bundle.crt"); // Name of the file to verify the server's cert against
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// Turns off verification of the SSL certificate.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// If not set, curl prints output to the browser
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

		$this->response = curl_exec($ch);

		curl_close($ch);

		/* parse the response */
		$this->parseResponse();
	}

	/***
	 * process()
	 *
	 * Will first generate the tamper proof seal, then
	 * populate the POST query, then send it, and store
	 * the response, and finally parse the response.
	 */
	public function process() {
		if ($this->payType == 'ACH') {
			return $this->processACH(); 
		}

		/* calculate the tamper proof seal */
		$tps = $this->calcTPS();

		//echo $this->account;

		/* fill in the fields */
		$fields = array(
			'ACCOUNT_ID' => $this->accountId, 
			'USER_ID' => $this->userId, 
			'TAMPER_PROOF_SEAL' => $tps, 
			'TRANS_TYPE' => $this->transType, 
			'PAYMENT_TYPE' => $this->payType, 
			'MODE' => $this->mode, 
			'MASTER_ID' => $this->masterId, 
			'PAYMENT_ACCOUNT' => $this->account, 
			'CARD_CVV2' => $this->cvv2, 
			'CARD_EXPIRE' => $this->expire, 
			'SSN' => $this->ssn, 
			'BIRTHDATE' => $this->birthdate, 
			'CUST_ID' => $this->custId, 
			'CUST_ID_STATE' => $this->custIdState, 
			'AMOUNT' => $this->amount, 
			'NAME1' => $this->name1, 
			'NAME2' => $this->name2, 
			'ADDR1' => $this->addr1, 
			'ADDR2' => $this->addr2, 
			'CITY' => $this->city, 
			'STATE' => $this->state, 
			'ZIP' => $this->zip, 
			'PHONE' => $this->phone, 
			'EMAIL' => $this->email, 
			'COUNTRY' => $this->country, 
			'MEMO' => $this->memo, 
			'CUSTOM_ID' => $this->customid1, 
			'CUSTOM_ID2' => $this->customid2, 
			'ORDER_ID' => $this->orderId, 
			'INVOICE_ID' => $this->invoiceId, 
			'AMOUNT_TIP' => $this->tip, 
			'AMOUNT_TAX' => $this->tax, 
			'DO_REBILL' => $this->doRebill, 
			'REB_FIRST_DATE' => $this->rebDate, 
			'REB_EXPR' => $this->rebExpr, 
			'REB_CYCLES' => $this->rebCycles, 
			'REB_AMOUNT' => $this->rebAmount, 
			'DO_AUTOCAP' => $this->doAutocap, 
			'AVS_ALLOWED' => $this->avsAllowed, 
			'CVV2_ALLOWED' => $this->cvv2Allowed, 
			'CUSTOMER_IP' => $_SERVER['REMOTE_ADDR'],
			'DUPLICATE_OVERRIDE' => 1
			);
			
		/* perform the transaction */
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::POST_URL);
		// Set the URL
		curl_setopt($ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0");
		// Cosmetic
		curl_setopt($ch, CURLOPT_POST, 1);
		// Perform a POST
		// curl_setopt($ch, CURLOPT_CAINFO, "c:\\windows\\ca-bundle.crt"); // Name of the file to verify the server's cert against
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// Turns off verification of the SSL certificate.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// If not set, curl prints output to the browser
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

		$this->response = curl_exec($ch);

		curl_close($ch);

		/* parse the response */
		$this->parseResponse();
	}

	/***
	 * parseResponse()
	 *
	 * This method will parse the response parameter values
	 * into the respective properties.
	 */
	protected function parseResponse() {

		parse_str($this->response);

		/* TRANS_ID */
		$this->transId = $TRANS_ID;

		/* STATUS */
		$this->status = $STATUS;

		/* AVS */
		$this->avsResp = $AVS;

		/* CVV2 */
		$this->cvv2Resp = $CVV2;

		/* AUTH_CODE */
		$this->authCode = $AUTH_CODE;

		/* MESSAGE */
		$this->message = $MESSAGE;

		/* REBID */
		$this->rebid = $REBID;
	}

	/***
	 * get[property]()
	 *
	 * Getter methods, return the respective property
	 * values.
	 */
	public function getResponse() {
		return $this->response;
	}

	public function getTransId() {
		return $this->transId;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getAvsResp() {
		return $this->avsResp;
	}

	public function getCvv2Resp() {
		return $this->cvv2Resp;
	}

	public function getAuthCode() {
		return $this->authCode;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getRebid() {
		return $this->rebid;
	}

}

/* EXAMPLE

 $bp = new BluePayment();
 $bp->sale('25.00');
 $bp->setCustInfo('4111111111111111',
 '123',
 '1111',
 'Chris',
 'Jansen',
 '123 Bluepay Ln',
 'Bluesville',
 'IL',
 '60563',
 'USA',
 '123-456-7890',
 'test@bluepay.com');
 $bp->process();

 echo 'Response: '. $bp->getResponse() .'<br />'.
 'TransId: '. $bp->getTransId() .'<br />'.
 'Status: '. $bp->getStatus() .'<br />'.
 'AVS Resp: '. $bp->getAvsResp() .'<br />'.
 'CVV2 Resp: '. $bp->getCvv2Resp() .'<br />'.
 'Auth Code: '. $bp->getAuthCode() .'<br />'.
 'Message: '. $bp->getMessage() .'<br />'.
 'Rebid: '. $bp->getRebid();

 END EXAMPLE */
?>

