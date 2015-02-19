<?php

require_once(ROOT . DS . 'app' . DS . 'Plugin' . DS . 'Transactions' . DS . 'Model' . DS . 'Processor' . DS . 'AbstractProcessor.php');
require_once(VENDORS . DS . 'usaepay' . DS . 'usaepay.php');

class Usaepay extends AbstractProcessor {

	public $name = 'Usaepay';

	public $config = array(
		'environment' => 'sandbox',
		'sourceKey' => '',
		'sourceKeyPin' => ''
	);

	public function setup() {
		if (defined('__TRANSACTIONS_USAEPAY')) {
			$this->config = Set::merge($this->config, unserialize(__TRANSACTIONS_USAEPAY));
		}

		// check required config
		if (empty($this->config['sourceKey']) || empty($this->config['sourceKeyPin'])) {
			throw new Exception('Payment configuration NOT setup, contact admin with error code : 29838');
		}
//		if (!in_array(CakePlugin::loaded('Connections'))) {
//			throw new Exception('Connections plugin is required, contact admin with error code : 91873');
//		}
	}

	public function pay($data = null) {
		$tran = new umTransaction;
//		$tran->Test();exit; // ensures it's sorta installed correctly.
		$tran->key = $this->config['sourceKey'];
		$tran->pin = $this->config['sourceKeyPin'];

		$tran->usesandbox = ($this->config['environment'] === 'sandbox') ? true : false;
		$tran->ip = $_SERVER['REMOTE_ADDR'];
		$tran->testmode = 0;	// Change this to 0 for the transaction to process

		$tran->command = "cc:sale";
		
		$tran->card = $data['Transaction']['card_number'];
		$tran->exp = !empty($data[$this->modelName]['card_expire']) ? $data[$this->modelName]['card_expire'] : $data[$this->modelName]['card_exp_month'] . substr($data[$this->modelName]['card_exp_year'], -2);
		$tran->amount = $data['Transaction']['total'];
		$tran->orderid = $data['Transaction']['id'];
		$tran->cardholder = $data['TransactionAddress'][0]['first_name'] . ' ' . $data['TransactionAddress'][0]['last_name'];
		$tran->street = trim($data['TransactionAddress'][0]['street_address_1'] . ' ' . $data['TransactionAddress'][0]['street_address_2']);
		$tran->zip = $data['TransactionAddress'][0]['zip'];
		$tran->description = __SYSTEM_SITE_NAME;
		$tran->cvv2 = $data['Transaction']['card_sec'];

		if ($tran->process()) {
			return $data;
		} else {
			$errorMessage = $tran->result . ': ' . $tran->error;
			$errorMessage .= ($this->transporterror) ? ' ' . $this->transporterror : '';
			throw new Exception($errorMessage);
		}
	}

}
