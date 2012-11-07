<?php

/**
 * Payment Gateways integrated 
 */
class PaymentsComponent extends Object {

	var $paysettings = array();
	var $response = array();
	var $paymentComponent = null;
	var $Controller = null;
	var $recurring = false;

	function initialize() {
		
	}

	function beforeRender() {
		
	}

	function beforeRedirect() {
		
	}

	function shutdown() {
		
	}

	// class variables go here 
	function startup(&$controller) {
		$this->Controller = $controller;
		// This method takes a reference to the controller which is loading it. 
		// Perform controller initialization here. 
	}

	// set recurring value default is false
	function recurring($val = false) {
		$this->recurring = $val;
	}

	function loadComponent($components = array()) {

		foreach ((array) $components as $component => $config) {
			if (is_int($component)) {
				$component = $config;
				$config = null;
			}
			list($plugin, $componentName) = pluginSplit($component);
			if (isset($this->Controller->{$componentName})) {
				continue;
			}

			$component = 'Transactions.' . $component;

			App::import('Component', $component);
			$componentFullName = $componentName . 'Component';
			$component = new $componentFullName(new ComponentCollection(), $config);

			if (method_exists($component, 'initialize')) {
				$component->initialize($this->Controller);
			}
			if (method_exists($component, 'startup')) {
				$component->startup($this->Controller);
			}
			$this->paymentComponent = $component;
		}
	}

/**
 * The Pay Function.
 * This function takes $data to process a payment with the merchant account defined by Transaction.mode
 * If there are any problems processing the payment, an exception should be raised with it's details for the user.
 * If the payment is successful, the $data array, with any necessary additions, should be returned.
 * 
 * @param array $data
 * @return array
 * @throws Exception
 */
	function Pay($data = null) {
		try {
			$paymentProcessor = ucfirst(strtolower($data['Transaction']['mode']));
			$paymentProcessor = explode('.', $paymentProcessor);
			$paymentProcessor = $paymentProcessor[0];

			$this->loadComponent($paymentProcessor);
			if ($this->recurring) {
				$this->paymentComponent->recurring(true);
				
				return $this->paymentComponent->Pay($data);
				
			} else {
				
				return $this->paymentComponent->Pay($data);
				
			}

			//return $this->paymentComponent->response;

		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

/*
 * function changeSubscription() use to change the recurring profile status
 * profileId: id of recurrence, can be profile id of payer
 * action: suspended or cancelled
 */

	function ManageRecurringPaymentsProfileStatus($data = null) {

		$this->loadComponent(ucfirst(strtolower($data['Mode'])));
		$this->paymentComponent->ManageRecurringPaymentsProfileStatus($data['profileId'], $data['action']);

		return $this->paymentComponent->response;
	}

	public function createAccount($data) {
		return $this->paymentComponent->createAccount($data);
	}

}