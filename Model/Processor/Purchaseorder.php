<?php
App::uses('AppModel', 'Model');
/**
 * P
 */

class Purchaseorder extends AppModel {

	public $name = 'PurchaseOrder';
	
	public $statusTypes = ''; // required var, sent from BuyableBehavior

	public $components = array();

	public function pay($data) {
		$this->modelName = !empty($this->modelName) ? $this->modelName : 'Transaction';
		$data[$this->modelName]['status'] = $this->statusTypes['pending'];
		return $data;
	}

}
