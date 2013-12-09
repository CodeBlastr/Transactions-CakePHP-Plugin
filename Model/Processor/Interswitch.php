<?php
/**
 * Interswitch
 *
 * @Author:Agbolade odusami
 * @email:support@lufem.com.ng
 */
class Interswitch extends AppModel {

	public $name = 'Interswitch';

	public $config = array('environment' => 'sandbox', 'apiUsername' => '', 'sharedSecret' => '', );

	public $useTable = false;

	public $paysettings = array();
	public $response = array();
	public $payInfo = array();
	public $recurring = false;

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		if (defined('__TRANSACTIONS_INTERSWITCH')) {
			$settings = unserialize(__TRANSACTIONS_PAYSIMPLE);
			$this->config = Set::merge($this->config, $config, $settings);
		}

		// check required config
		if (empty($this->config['apiUsername']) || empty($this->config['sharedSecret'])) {
			throw new Exception('Payment configuration NOT setup, contact admin with error code : 44889');
		}
		if (!in_array('Connections', CakePlugin::loaded())) {
			throw new Exception('Connections plugin is required, contact admin with error code : 44888');
		}

	}

	public function pay($data) {
		$postData = json_encode(array('intent' => 'sale', 'redirect_url' => 'http://ttysoon.localhost/transactions/transactions/success', 'total' => $data['Transaction']['total'], 'currency' => 'NGN', 'email' => $data['Customer']['email'], 'clientname' => $data['Customer']['full_name'], 'lastname' => $data['Customer']['last_name'], 'invoiceid' => $data['Transaction']['id']));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://pay.ttysoon.com/logtra.php');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: " . strlen($postData)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$response = curl_exec($ch);
		$err = curl_error($ch);

		curl_close($ch);
		if ($err) {
			throw new Exception("Error Processing Request", 1);
		} else {

			$out = explode("\r\n\r\n", $response);
			$response = json_decode($out[1], true);

			// save stuff to session.  we have to redirect to interswitch.com next.
			CakeSession::write('Transaction.data', $data);
			CakeSession::write('Transaction.modelName', $this->modelName);
			CakeSession::write('Transaction.Interswitch.txnref', $response['txnref']);
			header('Location: http://pay.ttysoon.com/webpay-new.php?txnref=' . $response['txnref']);
		}

	}

	public function executePayment($dataP) {

		$amount = $dataP['total'];
		$hash = $dataP['key'];
		$txRef = $dataP['order_number'];
		$msg = base64_decode($dataP['response_msg']);
		$string_to_hash = $this->config['sharedSecret'] . $this->config['apiUsername'] . $txRef . $order->order_total;
		$checkhash = md5($string_to_hash);
		if ($hash == $checkhash) {
			if ($status == "Y") {
				return true;

			} else {
				throw new Exception($msg, 1);
			}

		} else {
			throw new Exception("Error Processing Request", 1);
		}
	}

	/*
	 * @params
	 * $profileId: profile id of buyer
	 * $action: to suspend , cancel, reactivate the reccuring profile
	 */
	/**
	 * Parse the response from Interswitch into a more readable array
	 * makes doing validation changes easier.
	 *
	 */
}
