<?php
App::uses('ModelBehavior', 'Model');

/**
 * Buyable Behavior class file.
 *
 * The ability to exchange real money by interfacing with a payment processor
 * 
 * Usage is :
 * Attach behavior to a model, and when you save 
 *
 * @filesource
 * @author			Richard Kersey
 * @copyright       Buildrr LLC
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link            https://github.com/zuha/Transactions-Zuha-Cakephp-Plugin
 */
class BuyableBehavior extends ModelBehavior {

/**
 * Behavior settings
 * 
 * @access public
 * @var array
 */
	public $settings = array();

/**
 * The full results of Model::find() that are modified and saved
 * as a new copy.
 *
 * @access public
 * @var array
 */
	public $record = array();

/**
 * Default values for settings.
 *
 * - recursive: whether to copy hasMany and hasOne records
 * - habtm: whether to copy hasAndBelongsToMany associations
 * - stripFields: fields to strip during copy process
 * - ignore: aliases of any associations that should be ignored, using dot (.) notation.
 * will look in the $this->contain array.
 *
 * @access private
 * @var array
 */
    protected $defaults = array(  // was $paysettings in the component version
		);

/**
 * Response
 * 
 * @access public
 * @var array
 */
	public $response = array();
	//public $paymentComponent = null;  // from the component
	//public $Controller = null;  // from the component

/**
 * Recurring
 * 
 * @access public
 * @var array
 */
	public $recurring = false;


/**
 * Configuration method.
 *
 * @param object $Model Model object
 * @param array $config Config array
 * @access public
 * @return boolean
 */
    public function setup($Model, $config = array()) {
    	$this->settings = array_merge($this->defaults, $config);
		$this->modelName = !empty($this->settings['modelAlias']) ? $this->settings['modelAlias'] : $Model->alias;
		$this->foreignKey =  !empty($this->settings['foreignKeyName']) ? $this->settings['foreignKeyName'] : $Model->primaryKey;
    	return true;
	}

/**
 * Set recurring value default is false
 */
	public function recurring($val = false) {
		$this->recurring = $val;
	}

/**
 * Load Processor (should be datasource right?)
 */
	public function loadProcessor($processor = array()) {
        if (!empty($processor)) {
			App::uses($processor, 'Transactions.Model/Processor');
			$this->Processor = new $processor;
    		// there was a foreach here, in the payments component but it seemed to always end up with
    		// one component anyway, so I simplified this to just load the single processor asked for
        } else {
            throw new NotFoundException('Site payment settings have not been configured.');
        }
	}


/**
 * The Buy Function.
 * This function takes $data to process a payment with the merchant account defined by Transaction.mode
 * If there are any problems processing the payment, an exception should be raised with it's details for the user.
 * If the payment is successful, the $data array, with any necessary additions, should be returned.
 * 
 * @param array $data
 * @return array
 * @throws Exception
 */
	public function buy(Model $Model, $data = null) {
		if (!defined('__TRANSACTIONS_DEFAULT_PAYMENT')) {
        	throw new Exception('Payment configuration required', 1);
        }	
		try {
			$paymentProcessor = ucfirst(strtolower($data['Transaction']['mode']));
			$paymentProcessor = explode('.', $paymentProcessor);
			$paymentProcessor = $paymentProcessor[0]; 

			$this->loadProcessor($paymentProcessor);
			
			if ($this->recurring) {
				$this->Processor->recurring = true;	
			}
			
            return $this->Processor->pay($data);
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
 	// was ManageRecurringPaymentsProfileStatus
	public function editPaymenProfile($data = null) {
		debug($data);
		break;
		
		$this->loadComponent(ucfirst(strtolower($data['Mode'])));
		$this->paymentComponent->ManageRecurringPaymentsProfileStatus($data['profileId'], $data['action']);

		return $this->paymentComponent->response;
	}
	
	
}