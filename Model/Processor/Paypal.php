<?php
/**
 * Paypal Direct Payment API
 *
 * @todo update support for ARB
 * @todo update support for Chained Payments
 */
App::import('Vendor', 'paypal', array('file' => 'paypal/paypal.php'));
class Paypal extends AppModel {

	public $useTable = false;

	public $paysettings = array();
	public $response = array();
	public $payInfo = array();
	public $recurring = false;

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		if (defined('__TRANSACTIONS_PAYPAL')) {
			$this->paysettings = unserialize(__TRANSACTIONS_PAYPAL);
		} else {
			throw new Exception('Payment configuration NOT setup, contact admin with error code : 947941');
		}
	}

	public function pay($data) {
		
		try {
			$this->getAccessToken();
		} catch (Exception $e) {
			throw new Exception($e->msg());
		}
		
		$postData = json_encode(array(
			'intent' => 'sale',
			'redirect_urls' => array(
				'return_url' => 'http://ttysoon.localhost/transactions/transactions/success',
				'cancel_url' => 'http://ttysoon.localhost/transactions/transactions/cart'
			),
			'payer' => array(
				'payment_method' => 'paypal'
			),
			'transactions' => array(
				array(
					'amount' => array(
						'total' => $data['Transaction']['total'],
						'currency' => 'USD'
					),
					'description' => 'Purchase from ' . __SYSTEM_SITE_NAME
				)
			)
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->paysettings['API_ENDPOINT'] . '/v1/payments/payment');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: ".$this->paysettings['token_type']." ".$this->paysettings['access_token'], "Content-length: " . strlen($postData)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		$response = json_decode($response, true);

		if ($httpCode === 201) {
			// save stuff to session.  we have to redirect to paypal.com next.
			CakeSession::write('Transaction.data', $data);
			CakeSession::write('Transaction.modelName', $this->modelName);
			CakeSession::write('Transaction.Paypal.id', $response['id']);
			CakeSession::write('Transaction.Paypal.token_type', $this->paysettings['token_type']);
			CakeSession::write('Transaction.Paypal.access_token', $this->paysettings['access_token']);
			// all set to redirect
			foreach ($response['links'] as $link) {
				if ($link['method'] === 'REDIRECT') {
					header('Location: ' . $link['href']);
					exit();
				}
			}
			break;
		} else {
			throw new Exception("Error Processing Request", 1);
		}
	}


	public function getAccessToken() {
		$data = 'grant_type=client_credentials';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->paysettings['API_ENDPOINT'] . '/v1/oauth2/token');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_USERPWD, $this->paysettings['API_CLIENT_ID'].':'.$this->paysettings['API_SECRET']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Accept-Language: en_US", "Content-length: " . strlen($data)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		$response = json_decode($response, true);

		if ($httpCode === 200) {
			$this->paysettings['token_type'] = $response['token_type'];
			$this->paysettings['access_token'] = $response['access_token'];
		} else {
			throw new Exception("Error Processing Request", 1);
		}
	}
	
	
	public function executePayment($payerId) {
		$data = '{ "payer_id" : "'.$payerId.'"}';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->paysettings['API_ENDPOINT'] . '/v1/payments/payment/'.CakeSession::read('Transaction.Paypal.id').'/execute/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: ".CakeSession::read('Transaction.Paypal.token_type')." ".CakeSession::read('Transaction.Paypal.access_token'), "Content-length: " . strlen($data)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		$response = json_decode($response, true);

		if ($httpCode === 200) {
			return true;
		} else {
			throw new Exception("Error Processing Request", 1);
		}
	}

	public function payOLD($paymentInfo, $function = "DoDirectPayment") {
		$paypal = new PaypalApi();
		$this->payInfo = $paymentInfo;
		$paypal->setPaySettings($this->paysettings);

		if ($paymentInfo['Transaction']['mode'] === 'PAYPAL.ACCOUNT') {
			// send parameters to PayPal
			$paymentInfo['Transaction']['returnUrl'] = 'http://ttysoon.localhost/transactions/transactions/success';
			$paymentInfo['Transaction']['cancelUrl'] = 'http://ttysoon.localhost/transactions/transactions/cart';
			$res = $paypal->SetExpressCheckout($paymentInfo);

			debug($res);

			if ($this->_responseIsGood($res)) {
				// At this point, we need to redirect to paypal to get authorization.
				// need to... do something here..
				// maybe just change the order status to pending or something..
				header('Location: ' . $this->paysettings['PAYPAL_URL'] . $res['TOKEN']);
				exit();
			} else {
				throw new Exception('<b>PayPal Error: </b> ' . $res['L_LONGMESSAGE0'], 1);
			}
		} elseif ($paymentInfo['Transaction']['mode'] === 'PAYPAL.CC') {
			$res = $paypal->DoDirectPayment($paymentInfo);
			debug($res);
			if ($this->_responseIsGood($res)) {
				// do stuff with $res
			} else {
				throw new Exception("Error Processing Request", 1);
			}
		}

		debug($paymentInfo);
		break;

		// if ($this->recurring && !empty($paymentInfo['Billing']['arb_profile_id'])) {
		// // if existing profile recurring id for arb, update the subscription
		// $res = $paypal->UpdateRecurringPaymentsProfile($paymentInfo);
		//
		// } elseif ($this->recurring) {
		// // create a new subscription of recurring type
		// $res = $paypal->CreateRecurringPaymentsProfile($paymentInfo);
		//
		// } elseif ($function === "DoDirectPayment") {
		// $res = $paypal->DoDirectPayment($paymentInfo);
		//
		// } elseif ($function === "SetExpressCheckout") {
		// $res = $paypal->SetExpressCheckout($paymentInfo);
		//
		// } elseif ($function === "GetExpressCheckoutDetails") {
		// $res = $paypal->GetExpressCheckoutDetails($paymentInfo);
		//
		// } elseif ($function === "DoExpressCheckoutPayment") {
		// $res = $paypal->DoExpressCheckoutPayment($paymentInfo);
		//
		// } else {
		// $res = "Function Does Not Exist!";
		// }

		$this->_parsePaypalResponse($res);
	}

	/**
	 * Quick check for errors sent back from PayPal
	 *
	 * @todo Ideally, we should be writing out these errors to a log file.
	 *
	 * @param array $response Associative array of info that came back from PayPal
	 * @return boolean
	 */
	protected function _responseIsGood($response) {
		if ($nvpReqArray['ACK'] === 'Failure' || $nvpReqArray['ACK'] === 'FailureWithWarning') {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * @params $data and $amount
	 * it returns the response text if its successful
	 */
	public function chainedPayment($data, $amount) {
		if (defined('__TRANSACTIONS_CHAINED_PAYMENT')) {
			App::import('Component', 'Transactions.Chained');
			$component = new ChainedComponent();
			if (method_exists($component, 'initialize')) {
				$component->initialize($this->Controller);
			}
			if (method_exists($component, 'startup')) {
				$component->startup($this->Controller);
			}
			$component->chainedSettings($data['Billing']);
			$component->Pay($amount);
			if ($component->response['response_code'] == 1) {
				return " Payment has been transfered to its vendors";
			}
		}
	}

	public function recurring($val = false) {
		$this->recurring = $val;
	}

	/*
	 * @params
	 * $profileId: profile id of buyer
	 * $action: to suspend , cancel, reactivate the reccuring profile
	 */
	public function ManageRecurringPaymentsProfileStatus($profileId, $action) {
		$paypal = new Paypal();
		$paypal->setPaySettings($this->paysettings);
		$res = $paypal->ManageRecurringPaymentsProfileStatus($profileId, $action);

		$this->_parsePaypalResponse($res);
	}

	/**
	 * Parse the response from Paypal into a more readable array
	 * makes doing validation changes easier.
	 *
	 */
	protected function _parsePaypalResponse($parsedResponse = null) {
		if ($parsedResponse) {
			$parsedResponse['reason_code'] = $parsedResponse['ACK'];
			switch ($parsedResponse['ACK']) {
				case 'Success' :
					$parsedResponse['reason_text'] = 'Successful Payment';
					if (defined('__TRANSACTIONS_CHAINED_PAYMENT')) {
						$parsedResponse['reason_text'] .= $this->chainedPayment($this->payInfo, $parsedResponse['AMT']);
					}
					$parsedResponse['response_code'] = 1;
					$parsedResponse['description'] = 'Transaction Completed';
					break;
				case 'SuccessWithWarning' :
					$parsedResponse['response_code'] = 1;
					$parsedResponse['reason_text'] = $parsedResponse['L_SHORTMESSAGE0'];
					$parsedResponse['description'] = $parsedResponse['L_LONGMESSAGE0'];
					break;
				case 'FailureWithWarning' :
				case 'Failure' :
					$parsedResponse['response_code'] = 3;
					// similar to authorize
					$parsedResponse['reason_text'] = $parsedResponse['L_SHORTMESSAGE0'];
					$parsedResponse['description'] = $parsedResponse['L_LONGMESSAGE0'];
					break;
			}
			if (isset($parsedResponse['AMT'])) {
				$parsedResponse['amount'] = $parsedResponse['AMT'];
			}
			if (isset($parsedResponse['TRANSACTIONID'])) {
				$parsedResponse['transaction_id'] = $parsedResponse['TRANSACTIONID'];
			}

			// if PROFILEID is set then it is recurring payment and it will get profile info
			if (isset($parsedResponse['PROFILEID'])) {
				$paypal = new Paypal();
				$paypal->setPaySettings($this->paysettings);

				$res = $paypal->GetRecurringPaymentsProfileDetails($parsedResponse['PROFILEID']);

				// recurrence type missing
				$parsedResponse['transaction_id'] = $res['PROFILEID'];
				$parsedResponse['description'] = $res['DESC'];
				$parsedResponse['is_arb'] = 1;
				$parsedResponse['arb_payment_start_date'] = $res['PROFILESTARTDATE'];
				$parsedResponse['arb_payment_end_date'] = $res['FINALPAYMENTDUEDATE'];
				$parsedResponse['amount'] = $res['AMT'];
				$parsedResponse['meta'] = "CORRELATIONID:{$res['CORRELATIONID']}, BUILD:{$res['BUILD']}, STATUS:{$res['STATUS']}" . "BILLINGPERIOD:{$res['BILLINGPERIOD']}, BILLINGFREQUENCY:{$res['BILLINGFREQUENCY']}, TOTALBILLINGCYCLES:{$res['TOTALBILLINGCYCLES']}";
			}

			if (isset($parsedResponse['CVV2MATCH']) && isset($parsedResponse['CORRELATIONID'])) {
				$parsedResponse['meta'] = "CORRELATIONID:{$parsedResponse['CORRELATIONID']}, CVV2MATCH:{$parsedResponse['CVV2MATCH']}";
			}
		}
		$this->response = $parsedResponse;
	}

}
