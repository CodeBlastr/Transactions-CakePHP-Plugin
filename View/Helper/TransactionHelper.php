<?php
/**
 * Transactions helper
 *
 * @package 	transactions
 * @subpackage 	transactions.views.helpers
 */
class TransactionHelper extends AppHelper {

/**
 * Constructor method
 * 
 */
    // public function __construct(View $View, $settings = array()) {
    	// $this->View = $View;
    	// //$this->defaults = array_merge($this->defaults, $settings);
		// parent::__construct($View, $settings);
    // }

/**
 * Find method
 */
 	public function find($type = 'first', $params = array()) {
		$Transaction = ClassRegistry::init('Transactions.Transaction');
 		return $Transaction->find($type, $params);
 	}


}