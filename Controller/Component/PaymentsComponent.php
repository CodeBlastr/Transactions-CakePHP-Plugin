<?php

/**
 * Payment Gateways integrated 
 */
class PaymentsComponent extends Object {

	public $paysettings = array();
	public $response = array();
	public $paymentComponent = null;
	public $Controller = null;
	public $recurring = false;

	public function initialize() {
		
	}

	public function beforeRender() {
		
	}

	public function beforeRedirect() {
		
	}

	public function shutdown() {
		
	}


	public function startup(Controller $controller) {
    	$this->Controller = $controller;
        if (!defined('__TRANSACTIONS_DEFAULT_PAYMENT')) {
            $this->Controller->Session->setFlash(__('Payment configuration required.'));
            $this->Controller->redirect(array('plugin' => false, 'controller' => 'settings', 'action' => 'index'));
        }
	}

/**
 * set recurring value default is false
 */
	public function recurring($val = false) {
		$this->recurring = $val;
	}

	public function loadComponent($components = array()) {
        if (!empty($components)) {
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
        } else {
            throw new NotFoundException('Site payment settings have not been configured.');
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
	public function Pay($data = null) {
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

/**
 * function changeSubscription() use to change the recurring profile status
 * profileId: id of recurrence, can be profile id of payer
 * action: suspended or cancelled
 */
	public function ManageRecurringPaymentsProfileStatus($data = null) {
		$this->loadComponent(ucfirst(strtolower($data['Mode'])));
		$this->paymentComponent->ManageRecurringPaymentsProfileStatus($data['profileId'], $data['action']);

		return $this->paymentComponent->response;
	}

/**
 * @todo Probably not used and could be deleted if we do a site search for it and verify (4/8/2013 - RK)
 */
	public function createAccount($data) {
		return $this->paymentComponent->createAccount($data);
	}

}