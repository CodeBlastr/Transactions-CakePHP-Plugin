<?php

App::uses('AppModel', 'Model');

/**
 * Essentialy an Interface Class for creating a new payment processor.
 * Interface Classes apparently don't support class variables, so we are using an Abstract Class instead.
 * Doing it this way ensures that you implement the required functions.
 * 
 * To Use:
 * require_once(ROOT . DS . 'app' . DS . 'Plugin' . DS . 'Transactions' . DS . 'Model' . DS . 'Processor' . DS . 'AbstractProcessor.php');
 * class Mypaymentgateway extends AbstractProcessor {}
 */
abstract class AbstractProcessor extends AppModel {

/**
 * @var string (required) The Propercase name of the processor. (capitalize the first letter)
 */
	public $name;

/**
 * @var bool Is this current transaction going to have a recurring payment?
 */
	public $recurring = false;

/**
 * sent from BuyableBehavior
 * @var array 
 */
	public $statusTypes;

/**
 * Ensures that your implementation has the required class variables.
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 * @throws Exception
 */
	public final function __construct($id = false, $table = null, $ds = null) {
		if (empty($this->name)) {
			throw new Exception(get_class($this) . ' must have a $name');
		}
//		if (!isset($this->recurring)) {
//			throw new Exception(get_class($this) . ' must have a $recurring');
//		}
//		if (!isset($this->statusTypes)) {
//			throw new Exception(get_class($this) . ' must have a $statusTypes');
//		}
		$this->setup();
		parent::__construct($id, $table, $ds);
	}

/**
 * Handle configuration options, etc.
 */
	abstract protected function setup();

/**
 * Do what you need to do to process the payment, then return $data
 */
	abstract protected function pay($data = null);
}
