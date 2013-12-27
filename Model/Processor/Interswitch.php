<?php
/**
 * Interswitch
 *
 * @Author:Agbolade odusami
 * @email:support@lufem.com.ng
 */
class Interswitch extends AppModel {

	public $name = 'Interswitch';

	public $config = array('environment' => 'sandbox', 'apiUsername' => '', 'sharedSecret' => '', 'subdomain' => '');

	public $useTable = false;

	public $paysettings = array();
	public $response = array();
	public $payInfo = array();
	public $recurring = false;

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		if (defined('__TRANSACTIONS_INTERSWITCH')) {
			$settings = unserialize(__TRANSACTIONS_INTERSWITCH);
			$this->config = Set::merge($this->config, $config, $settings);
		}

		// check required config
		if (empty($this->config['apiUsername']) || empty($this->config['sharedSecret'])) {
			throw new Exception('Payment configuration NOT setup, contact admin with error code : 44889');
		}
	}

	public function pay($data) {
		$postData = json_encode(array(
			'intent' => 'sale',
			'redirect_url' => FULL_BASE_URL.'/transactions/transactions/success',
			'total' => $data['Transaction']['total'],
			'currency' => 'NGN',
			'email' => $data['Customer']['email'],
			'clientname' => $data['Customer']['full_name'],
			'lastname' => $data['Customer']['last_name'],
			'invoiceid' => $data['Transaction']['id']
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config['subdomain'].'/logtra.php');
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
			header('Location: '.$this->config['subdomain'].'/webpay-new.php?txnref=' . $response['txnref']);
			exit;
		}

	}

	public function executePayment($data) {
		$amount = $data['total'];
		$hash = $data['key'];
		$txRef = $data['order_number'];
		$msg = base64_decode($data['response_msg']);
		$string_to_hash = $this->config['sharedSecret'] . $this->config['apiUsername'] . $txRef . $data['total'];
		
		$checkhash = md5($string_to_hash);
		if ($hash == $checkhash) {
			if ($data['credit_card_processed'] == "Y") {
				return $msg;
			} else {
				throw new Exception($msg, 1);
			}
		} else {
			throw new Exception("Error Processing Request", 1);
		}
	}

}
