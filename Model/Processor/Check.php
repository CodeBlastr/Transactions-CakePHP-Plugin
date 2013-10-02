<?php
App::uses('AppModel', 'Model');
/**
 * P
 */

class Check extends AppModel {


	public $name = 'Check';

	public $statusTypes = ''; // required var, sent from BuyableBehavior
	
	public function pay($data) {
		$this->modelName = !empty($this->modelName) ? $this->modelName : 'Transaction';
		$data[$this->modelName]['status'] = $this->statusTypes['paid'];
		return $data;
	}

}
